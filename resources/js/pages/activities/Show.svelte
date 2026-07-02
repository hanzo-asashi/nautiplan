<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as activitiesIndex } from '@/routes/activities';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Activities',
                href: activitiesIndex(),
            },
            {
                title: 'Detail',
                href: '',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, useForm, router, page } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import ChevronUp from 'lucide-svelte/icons/chevron-up';
    import Edit2 from 'lucide-svelte/icons/edit-2';
    import FileDown from 'lucide-svelte/icons/file-down';
    import FileUp from 'lucide-svelte/icons/file-up';
    import History from 'lucide-svelte/icons/history';
    import KanbanSquare from 'lucide-svelte/icons/kanban-square';
    import Paperclip from 'lucide-svelte/icons/paperclip';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import StatusBadge from '@/components/StatusBadge.svelte';
    import { formatRupiah, toUrl } from '@/lib/utils';
    import {
        edit,
        submitApproval,
        kanban,
        revisions,
    } from '@/routes/activities';
    import { upload, deleteMethod } from '@/routes/activities/documents';
    import { pdf as activityPdf } from '@/routes/reports/activity';

    let {
        activity,
    }: {
        activity: {
            id: number;
            code: string;
            name: string;
            description: string | null;
            status: string;
            priority: string;
            progress_percentage: number;
            location: string | null;
            start_date: string | null;
            end_date: string | null;
            program?: { name: string; code: string } | null;
            unit?: { name: string; code: string } | null;
            fiscal_year?: { year: number } | null;
            responsible_user?: { name: string } | null;
            sub_activities: Array<{
                id: number;
                name: string;
                description: string | null;
                status: string;
                progress_percentage: number;
                assigned_user?: { name: string } | null;
            }>;
            budgets: Array<{
                id: number;
                budget_category: string;
                account_code: string | null;
                account_name: string | null;
                description: string | null;
                amount: number;
                realizations: Array<{
                    id: number;
                    amount: number;
                    realization_date: string;
                    description: string | null;
                    realization_type: string;
                    vendor_name: string | null;
                    vendor_address: string | null;
                    vendor_npwp: string | null;
                    procurement_number: string | null;
                    procurement_date: string | null;
                    sp2d_number: string | null;
                    sp2d_date: string | null;
                }>;
            }>;
            indicators: Array<{
                id: number;
                code: string;
                name: string;
                indicator_type: 'iku' | 'ikk';
                target_value: number;
                actual_value: number | null;
                unit_of_measure: string;
                quarter: string;
            }>;
            documents: Array<{
                id: number;
                parent_id: number | null;
                version: number;
                file_name: string;
                file_path: string;
                file_type: string;
                file_size: number;
                description: string | null;
                uploader?: { name: string } | null;
            }>;
            approval_request?: {
                id: number;
                status: string;
                notes: string | null;
                steps: Array<{
                    id: number;
                    step_order: number;
                    role_id: number;
                    approver_id: number | null;
                    status: string;
                    notes: string | null;
                    acted_at: string | null;
                    role?: { display_name: string } | null;
                    approver?: { name: string } | null;
                }>;
            } | null;
        };
    } = $props();

    const user = $derived(page.props.auth.user as any);
    const isAdmin = $derived(
        user?.is_super_admin || user?.roles?.includes('admin'),
    );
    const canEdit = $derived(activity.status === 'draft' || isAdmin);

    function calculateAchievement(
        target: number,
        actual: number | null,
    ): number {
        if (!target) {
            return 0;
        }

        return Math.round(((actual || 0) / target) * 100);
    }

    const uploadForm = useForm({
        file: null as File | null,
        description: '',
        parent_id: null as number | null,
    });

    let fileInput: HTMLInputElement;
    let isDragging = $state(false);
    let expandedDocs = $state<Record<number, boolean>>({});

    function handleFileChange(e: Event) {
        const target = e.target as HTMLInputElement;

        if (target.files && target.files.length > 0) {
            uploadForm.file = target.files[0];
        }
    }

    function handleDragOver(e: DragEvent) {
        e.preventDefault();
        isDragging = true;
    }

    function handleDragLeave() {
        isDragging = false;
    }

    function handleDrop(e: DragEvent) {
        e.preventDefault();
        isDragging = false;
        const file = e.dataTransfer?.files[0];

        if (file) {
            uploadForm.file = file;
        }
    }

    function selectParentForVersion(parentId: number) {
        uploadForm.parent_id = parentId;
    }

    function cancelVersionUpload() {
        uploadForm.parent_id = null;
    }

    function toggleExpandDoc(docId: number) {
        expandedDocs[docId] = !expandedDocs[docId];
    }

    function handleUpload(e: Event) {
        e.preventDefault();

        if (!uploadForm.file) {
            return;
        }

        uploadForm.post(toUrl(upload({ activity: activity.id })), {
            onSuccess: () => {
                uploadForm.reset();

                if (fileInput) {
                    fileInput.value = '';
                }
            },
        });
    }

    function handleDeleteDoc(docId: number) {
        if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
            router.delete(toUrl(deleteMethod({ document: docId })));
        }
    }

    function formatDate(dateStr: string | null | undefined): string {
        if (!dateStr) {
            return '-';
        }

        try {
            const date = new Date(dateStr);

            if (isNaN(date.getTime())) {
                return dateStr;
            }

            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        } catch {
            return dateStr;
        }
    }
</script>

<AppHead title={`Detail Kegiatan - ${activity.name}`} />

<div class="p-6 space-y-6">
    <PageHeader
        title={`[${activity.code}] ${activity.name}`}
        description={`Unit Pelaksana: ${activity.unit?.name || ''} | TA ${activity.fiscal_year?.year || ''}`}
    >
        {#snippet actions()}
            <Link
                href={toUrl(activitiesIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5 whitespace-nowrap"
            >
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
            <a
                href={toUrl(activityPdf({ activity: activity.id }))}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5 whitespace-nowrap"
            >
                <FileDown class="size-4" />
                Unduh PDF
            </a>
            <Link
                href={toUrl(kanban({ activity: activity.id }))}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5 whitespace-nowrap"
            >
                <KanbanSquare class="size-4" />
                Papan Kanban
            </Link>
            <Link
                href={toUrl(revisions({ activity: activity.id }))}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5 whitespace-nowrap"
            >
                <History class="size-4" />
                Riwayat Perubahan
            </Link>
            {#if canEdit}
                <Link
                    href={toUrl(edit({ activity: activity.id }))}
                    class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5 whitespace-nowrap"
                >
                    <Edit2 class="size-4" />
                    Edit Kegiatan
                </Link>
            {/if}
            {#if activity.status === 'draft'}
                <button
                    onclick={() =>
                        router.post(
                            toUrl(submitApproval({ activity: activity.id })),
                        )}
                    class="inline-flex h-9 items-center justify-center rounded-md bg-emerald-600 hover:bg-emerald-600/90 text-white px-4 py-2 text-sm font-medium shadow-md shadow-emerald-500/20 transition-colors cursor-pointer gap-1.5 whitespace-nowrap"
                >
                    Ajukan Persetujuan
                </button>
            {/if}
        {/snippet}
    </PageHeader>

    <div class="grid gap-6 md:grid-cols-3">
        <!-- Info Column -->
        <div class="md:col-span-2 space-y-6">
            <!-- Description Overview -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm"
            >
                <div>
                    <h3
                        class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                    >
                        Deskripsi Kegiatan
                    </h3>
                    <p class="mt-2 text-foreground leading-relaxed">
                        {activity.description || 'Deskripsi belum diisi.'}
                    </p>
                </div>

                <div
                    class="grid gap-4 sm:grid-cols-2 border-t border-sidebar-border/30 pt-4"
                >
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-muted-foreground font-medium"
                            >Program Kerja Induk</span
                        >
                        <span class="font-semibold text-foreground"
                            >[{activity.program?.code || ''}] {activity.program
                                ?.name || ''}</span
                        >
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-muted-foreground font-medium"
                            >Lokasi</span
                        >
                        <span class="font-semibold text-foreground"
                            >{activity.location || '-'}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Sub Activities -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm"
            >
                <h3
                    class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4"
                >
                    Tahapan & Sub-Kegiatan
                </h3>

                {#if !activity.sub_activities || activity.sub_activities.length === 0}
                    <p
                        class="text-sm text-muted-foreground/60 italic text-center py-6"
                    >
                        Belum ada rincian sub-kegiatan.
                    </p>
                {:else}
                    <div class="space-y-4">
                        {#each activity.sub_activities as sub}
                            <div
                                class="p-4 rounded-lg border border-sidebar-border/40 bg-zinc-50/50 dark:bg-zinc-900/40 space-y-2"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <h4
                                            class="font-bold text-sm text-foreground"
                                        >
                                            {sub.name}
                                        </h4>
                                        {#if sub.description}
                                            <p
                                                class="text-xs text-muted-foreground mt-0.5"
                                            >
                                                {sub.description}
                                            </p>
                                        {/if}
                                    </div>
                                    <StatusBadge status={sub.status} />
                                </div>
                                <div
                                    class="flex justify-between items-center text-xs text-muted-foreground pt-2 border-t border-sidebar-border/10 mt-2"
                                >
                                    <span
                                        >Petugas: <strong
                                            class="text-foreground"
                                            >{sub.assigned_user?.name ||
                                                '-'}</strong
                                        ></span
                                    >
                                    <span
                                        >Progress: <strong
                                            class="text-foreground"
                                            >{sub.progress_percentage}%</strong
                                        ></span
                                    >
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>

            <!-- Indikator Kinerja (IKU/IKK) -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-4"
            >
                <div
                    class="flex items-center justify-between border-b border-sidebar-border/30 pb-3"
                >
                    <h3
                        class="text-sm font-semibold uppercase tracking-wider text-muted-foreground"
                    >
                        Indikator Kinerja (IKU / IKK)
                    </h3>
                </div>

                {#if !activity.indicators || activity.indicators.length === 0}
                    <p
                        class="text-sm text-muted-foreground/60 italic text-center py-6"
                    >
                        Belum ada indikator kinerja yang didefinisikan.
                    </p>
                {:else}
                    <div class="space-y-4">
                        {#each activity.indicators as indicator}
                            <div
                                class="p-4 rounded-lg border border-sidebar-border/40 bg-zinc-50/50 dark:bg-zinc-900/40 space-y-3"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-primary/10 text-primary"
                                            >
                                                {indicator.indicator_type.toUpperCase()}
                                            </span>
                                            <span
                                                class="text-xs font-semibold text-muted-foreground"
                                            >
                                                {indicator.code}
                                            </span>
                                            <span
                                                class="text-xs font-medium text-muted-foreground/70"
                                            >
                                                • {indicator.quarter ===
                                                'annual'
                                                    ? 'Tahunan'
                                                    : indicator.quarter}
                                            </span>
                                        </div>
                                        <h4
                                            class="font-bold text-sm text-foreground"
                                        >
                                            {indicator.name}
                                        </h4>
                                    </div>
                                </div>

                                <div
                                    class="grid grid-cols-3 gap-2 text-xs pt-2 border-t border-sidebar-border/10"
                                >
                                    <div>
                                        <span
                                            class="text-muted-foreground block text-[10px] uppercase font-medium"
                                            >Target</span
                                        >
                                        <span
                                            class="font-semibold text-foreground"
                                        >
                                            {Number(indicator.target_value)}
                                            {indicator.unit_of_measure}
                                        </span>
                                    </div>
                                    <div>
                                        <span
                                            class="text-muted-foreground block text-[10px] uppercase font-medium"
                                            >Realisasi</span
                                        >
                                        <span
                                            class="font-semibold text-foreground"
                                        >
                                            {indicator.actual_value !== null
                                                ? `${Number(indicator.actual_value)} ${indicator.unit_of_measure}`
                                                : '-'}
                                        </span>
                                    </div>
                                    <div>
                                        <span
                                            class="text-muted-foreground block text-[10px] uppercase font-medium"
                                            >Capaian</span
                                        >
                                        <span
                                            class="font-bold text-emerald-600 dark:text-emerald-400"
                                        >
                                            {calculateAchievement(
                                                Number(indicator.target_value),
                                                indicator.actual_value,
                                            )}%
                                        </span>
                                    </div>
                                </div>
                                <div
                                    class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5"
                                >
                                    <div
                                        class="bg-emerald-500 h-1.5 rounded-full transition-all duration-300"
                                        style={`width: ${Math.min(calculateAchievement(Number(indicator.target_value), indicator.actual_value), 100)}%`}
                                    ></div>
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>

            <!-- Budgets and Realization -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-4"
            >
                <h3
                    class="text-sm font-semibold uppercase tracking-wider text-muted-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Rencana Anggaran & Realisasi Belanja
                </h3>

                {#if !activity.budgets || activity.budgets.length === 0}
                    <p
                        class="text-sm text-muted-foreground/60 italic text-center py-6"
                    >
                        Belum ada rincian pagu anggaran.
                    </p>
                {:else}
                    {#each activity.budgets as budget}
                        <div
                            class="space-y-2 border-b border-sidebar-border/30 pb-4 last:border-0 last:pb-0"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs uppercase font-semibold px-2 py-0.5 rounded bg-primary/10 text-primary"
                                            >{budget.budget_category}</span
                                        >
                                        {#if budget.account_code}
                                            <span
                                                class="text-xs font-semibold px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-muted-foreground"
                                            >
                                                Kode: {budget.account_code}
                                            </span>
                                        {/if}
                                    </div>
                                    <p
                                        class="text-sm font-medium mt-1 text-foreground"
                                    >
                                        {budget.description || 'Pagu Belanja'}
                                        {#if budget.account_name}
                                            <span
                                                class="text-xs text-muted-foreground font-medium block mt-0.5"
                                            >
                                                Akun: {budget.account_name}
                                            </span>
                                        {/if}
                                    </p>
                                </div>
                                <span class="font-bold text-foreground"
                                    >{formatRupiah(budget.amount)}</span
                                >
                            </div>

                            {#if budget.realizations.length > 0}
                                <div
                                    class="pl-4 border-l-2 border-emerald-500/30 space-y-2 mt-2"
                                >
                                    <h5
                                        class="text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                                    >
                                        Riwayat Realisasi:
                                    </h5>
                                    {#each budget.realizations as real}
                                        <div
                                            class="flex flex-col sm:flex-row sm:justify-between border-b border-zinc-100 dark:border-zinc-800/40 pb-2 last:border-0 last:pb-0 text-xs text-muted-foreground"
                                        >
                                            <div class="space-y-0.5">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <span
                                                        class="font-medium text-foreground"
                                                        >{real.description ||
                                                            'Realisasi'}</span
                                                    >
                                                    {#if real.realization_type === 'surat_pesanan'}
                                                        <span
                                                            class="px-1 py-0.5 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded text-[9px] uppercase tracking-wider font-semibold"
                                                        >
                                                            Surat Pesanan
                                                        </span>
                                                    {:else}
                                                        <span
                                                            class="px-1 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-500 rounded text-[9px] uppercase tracking-wider font-semibold"
                                                        >
                                                            Non-Pengadaan
                                                        </span>
                                                    {/if}
                                                </div>
                                                <p
                                                    class="text-[10px] text-muted-foreground"
                                                >
                                                    Tanggal: {formatDate(
                                                        real.realization_date,
                                                    )}
                                                    {#if real.vendor_name}
                                                        | Vendor: <strong
                                                            class="text-foreground"
                                                            >{real.vendor_name}</strong
                                                        >
                                                    {/if}
                                                    {#if real.procurement_number}
                                                        | SP: <strong
                                                            class="text-foreground"
                                                            >{real.procurement_number}</strong
                                                        >
                                                    {/if}
                                                </p>
                                            </div>
                                            <div
                                                class="flex items-center gap-3 mt-1 sm:mt-0 font-bold text-emerald-600 dark:text-emerald-400"
                                            >
                                                <span
                                                    >{formatRupiah(
                                                        real.amount,
                                                    )}</span
                                                >
                                                {#if real.realization_type === 'surat_pesanan'}
                                                    <a
                                                        href={`/reports/realization/${real.id}/pdf`}
                                                        target="_blank"
                                                        class="text-[9px] px-1.5 py-0.5 rounded border border-primary/30 text-primary hover:bg-primary hover:text-white transition-colors cursor-pointer"
                                                    >
                                                        PDF SP
                                                    </a>
                                                {/if}
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    {/each}
                {/if}
            </div>
        </div>

        <!-- Sidebar (Metadata + Document Uploads) -->
        <div class="space-y-6">
            <!-- Metadata -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm"
            >
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Status & Tanggal
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Status</span>
                        <StatusBadge status={activity.status} />
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Prioritas</span>
                        <span
                            class="font-semibold px-2 py-0.5 rounded text-xs uppercase
                            {activity.priority === 'critical'
                                ? 'bg-rose-500/10 text-rose-500'
                                : ''}
                            {activity.priority === 'high'
                                ? 'bg-amber-500/10 text-amber-500'
                                : ''}
                            {activity.priority === 'medium'
                                ? 'bg-blue-500/10 text-blue-500'
                                : ''}
                            {activity.priority === 'low'
                                ? 'bg-zinc-500/10 text-zinc-500'
                                : ''}
                        "
                        >
                            {activity.priority}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Progress</span>
                        <span class="font-bold text-foreground"
                            >{activity.progress_percentage}%</span
                        >
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Mulai</span>
                        <span class="font-semibold text-foreground"
                            >{formatDate(activity.start_date)}</span
                        >
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Selesai</span>
                        <span class="font-semibold text-foreground"
                            >{formatDate(activity.end_date)}</span
                        >
                    </div>
                    {#if activity.responsible_user}
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground"
                                >Penanggung Jawab</span
                            >
                            <span class="font-semibold text-foreground"
                                >{activity.responsible_user.name}</span
                            >
                        </div>
                    {/if}
                </div>
            </div>

            <!-- Approval Timeline -->
            {#if activity.approval_request}
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm"
                >
                    <h3
                        class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-sidebar-border/30 pb-2"
                    >
                        Status Persetujuan
                    </h3>

                    <div class="space-y-4">
                        {#each activity.approval_request.steps as step, idx}
                            <div
                                class="relative pl-6 pb-2 last:pb-0 border-l border-sidebar-border/40 last:border-transparent"
                            >
                                <!-- Bullet point -->
                                <div
                                    class="absolute -left-[5px] top-1 size-2 rounded-full border
                                    {step.status === 'approved'
                                        ? 'bg-emerald-500 border-emerald-600'
                                        : ''}
                                    {step.status === 'rejected'
                                        ? 'bg-rose-500 border-rose-600'
                                        : ''}
                                    {step.status === 'pending'
                                        ? 'bg-zinc-300 dark:bg-zinc-700 border-zinc-400'
                                        : ''}
                                "
                                ></div>

                                <div class="space-y-1">
                                    <div
                                        class="flex justify-between items-center text-xs"
                                    >
                                        <span class="font-bold text-foreground"
                                            >Langkah {idx + 1}: {step.role
                                                ?.display_name ||
                                                step.role_id}</span
                                        >
                                        <span
                                            class="text-[9px] font-bold uppercase
                                            {step.status === 'approved'
                                                ? 'text-emerald-500'
                                                : ''}
                                            {step.status === 'rejected'
                                                ? 'text-rose-500'
                                                : ''}
                                            {step.status === 'pending'
                                                ? 'text-muted-foreground'
                                                : ''}
                                        "
                                        >
                                            {step.status === 'approved'
                                                ? 'Setuju'
                                                : ''}
                                            {step.status === 'rejected'
                                                ? 'Tolak'
                                                : ''}
                                            {step.status === 'pending'
                                                ? 'Menunggu'
                                                : ''}
                                        </span>
                                    </div>
                                    {#if step.approver}
                                        <div
                                            class="text-[10px] text-muted-foreground"
                                        >
                                            Oleh: <span
                                                class="font-semibold text-foreground"
                                                >{step.approver.name}</span
                                            >
                                        </div>
                                    {/if}
                                    {#if step.notes}
                                        <p
                                            class="text-[10px] text-foreground bg-background/50 border border-sidebar-border/30 rounded p-1.5 italic"
                                        >
                                            "{step.notes}"
                                        </p>
                                    {/if}
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
            {/if}

            <!-- Document Upload & List -->
            <div
                class="rounded-xl border transition-all duration-300 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm
                    {isDragging
                    ? 'border-primary ring-2 ring-primary bg-primary/5 border-dashed scale-[1.02]'
                    : 'border-sidebar-border/50'}"
                role="region"
                aria-label="Area seret dan lepas dokumen lampiran"
                ondragover={handleDragOver}
                ondragleave={handleDragLeave}
                ondrop={handleDrop}
            >
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-sidebar-border/30 pb-2 flex justify-between items-center"
                >
                    Lampiran Dokumen
                    {#if isDragging}
                        <span class="text-primary font-black animate-pulse"
                            >Lepas File Di Sini</span
                        >
                    {/if}
                </h3>

                <!-- Upload form -->
                <form onsubmit={handleUpload} class="space-y-3">
                    {#if uploadForm.parent_id}
                        {@const parentDoc = activity.documents.find(
                            (d) => d.id === uploadForm.parent_id,
                        )}
                        <div
                            class="p-2 bg-amber-500/10 border border-amber-500/20 text-amber-600 rounded-md text-[10px] font-semibold flex items-center justify-between"
                        >
                            <span
                                >Mengunggah versi baru untuk: <strong
                                    class="truncate"
                                    >{parentDoc?.file_name}</strong
                                ></span
                            >
                            <button
                                type="button"
                                onclick={cancelVersionUpload}
                                class="text-[9px] text-zinc-500 hover:text-foreground font-black uppercase cursor-pointer"
                            >
                                Batal
                            </button>
                        </div>
                    {/if}

                    <div class="flex flex-col gap-1">
                        <input
                            type="file"
                            bind:this={fileInput}
                            onchange={handleFileChange}
                            class="hidden"
                            accept=".pdf,.docx,.xlsx,.png,.jpg"
                        />
                        <button
                            type="button"
                            onclick={() => fileInput?.click()}
                            class="w-full flex flex-col items-center justify-center gap-1.5 py-4 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 bg-background text-xs font-semibold text-muted-foreground hover:text-foreground hover:bg-accent transition-all cursor-pointer"
                            aria-label="Pilih file untuk diunggah"
                        >
                            <FileUp class="size-5 text-muted-foreground" />
                            {#if uploadForm.file}
                                <span
                                    class="text-primary truncate px-4 font-bold"
                                    >{uploadForm.file.name}</span
                                >
                            {:else}
                                <span class="text-[10px]"
                                    >Tarik & lepas file atau Klik untuk memilih</span
                                >
                                <span
                                    class="text-[8px] text-muted-foreground/60"
                                    >(Max 10MB: PDF, DOCX, XLSX, PNG, JPG)</span
                                >
                            {/if}
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <input
                            type="text"
                            bind:value={uploadForm.description}
                            placeholder="Deskripsi singkat..."
                            class="flex-1 px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary"
                            aria-label="Deskripsi singkat berkas lampiran"
                        />

                        <button
                            type="submit"
                            disabled={uploadForm.processing || !uploadForm.file}
                            class="px-4 flex items-center justify-center gap-1 h-8 rounded-md bg-primary hover:bg-primary/95 text-white text-xs font-medium transition-colors cursor-pointer disabled:opacity-50"
                        >
                            Unggah
                        </button>
                    </div>
                </form>

                <!-- Document List -->
                {#if !activity.documents || activity.documents.length === 0}
                    <p
                        class="text-xs text-muted-foreground/60 italic text-center py-2"
                    >
                        Belum ada dokumen diunggah.
                    </p>
                {:else}
                    {@const rootDocs = activity.documents.filter(
                        (d) => !d.parent_id,
                    )}
                    <div
                        class="space-y-3 border-t border-sidebar-border/20 pt-3 mt-3"
                    >
                        {#each rootDocs as doc}
                            {@const versions = activity.documents
                                .filter((d) => d.parent_id === doc.id)
                                .sort((a, b) => b.version - a.version)}
                            {@const isExpanded = !!expandedDocs[doc.id]}

                            <div
                                class="p-3.5 rounded-xl bg-zinc-50/50 dark:bg-zinc-900/40 border border-sidebar-border/20 space-y-2"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="flex items-start gap-2 min-w-0">
                                        <Paperclip
                                            class="size-4 text-muted-foreground shrink-0 mt-0.5"
                                        />
                                        <div class="min-w-0">
                                            <a
                                                href={`/storage/${doc.file_path}`}
                                                target="_blank"
                                                class="text-xs font-bold text-foreground hover:text-primary hover:underline truncate block"
                                                title={doc.file_name}
                                            >
                                                {doc.file_name}
                                            </a>
                                            <div
                                                class="flex flex-wrap items-center gap-1.5 text-[9px] text-muted-foreground/75 mt-0.5"
                                            >
                                                <span>Versi {doc.version}</span>
                                                <span>•</span>
                                                <span
                                                    >{doc.uploader?.name ||
                                                        'Sistem'}</span
                                                >
                                            </div>
                                            {#if doc.description}
                                                <p
                                                    class="text-[10px] text-muted-foreground mt-1 leading-relaxed"
                                                >
                                                    {doc.description}
                                                </p>
                                            {/if}
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center gap-1 shrink-0"
                                    >
                                        <!-- Version history toggle -->
                                        {#if versions.length > 0}
                                            <button
                                                onclick={() =>
                                                    toggleExpandDoc(doc.id)}
                                                class="p-1 hover:bg-zinc-200/50 rounded text-muted-foreground hover:text-foreground cursor-pointer flex items-center gap-1"
                                                title="Riwayat Versi"
                                                aria-label="Tampilkan riwayat versi untuk {doc.file_name}"
                                                aria-expanded={isExpanded}
                                            >
                                                <History class="size-3.5" />
                                                <span
                                                    class="text-[9px] font-bold"
                                                    >+{versions.length}</span
                                                >
                                                {#if isExpanded}
                                                    <ChevronUp class="size-3" />
                                                {:else}
                                                    <ChevronDown
                                                        class="size-3"
                                                    />
                                                {/if}
                                            </button>
                                        {/if}

                                        <!-- Upload new version button -->
                                        <button
                                            onclick={() =>
                                                selectParentForVersion(doc.id)}
                                            class="p-1 hover:bg-zinc-200/50 rounded text-muted-foreground hover:text-primary cursor-pointer"
                                            title="Unggah Versi Baru"
                                            aria-label="Unggah versi baru untuk dokumen {doc.file_name}"
                                        >
                                            <FileUp class="size-3.5" />
                                        </button>

                                        <!-- Delete document -->
                                        <button
                                            onclick={() =>
                                                handleDeleteDoc(doc.id)}
                                            class="text-rose-500 hover:text-rose-600 p-1 hover:bg-rose-500/10 rounded cursor-pointer"
                                            title="Hapus"
                                            aria-label="Hapus dokumen {doc.file_name}"
                                        >
                                            <Trash2 class="size-3.5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Versions Drawer -->
                                {#if isExpanded && versions.length > 0}
                                    <div
                                        class="pl-6 border-l border-sidebar-border/30 space-y-2 mt-2 pt-2 bg-zinc-100/10 dark:bg-zinc-950/10 p-2.5 rounded-lg"
                                    >
                                        <h5
                                            class="text-[9px] font-bold text-muted-foreground uppercase tracking-wider mb-1.5 flex items-center gap-1"
                                        >
                                            <History class="size-3" />
                                            Riwayat Perubahan Versi
                                        </h5>
                                        {#each versions as ver}
                                            <div
                                                class="flex items-center justify-between gap-3 text-[10px] text-muted-foreground p-1 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/30 rounded transition-colors"
                                            >
                                                <div class="min-w-0">
                                                    <a
                                                        href={`/storage/${ver.file_path}`}
                                                        target="_blank"
                                                        class="font-semibold text-primary hover:underline truncate block"
                                                        title={ver.file_name}
                                                    >
                                                        v{ver.version}: {ver.file_name}
                                                    </a>
                                                    <span
                                                        class="text-[8px] block text-muted-foreground/60 mt-0.5"
                                                    >
                                                        Diunggah oleh {ver
                                                            .uploader?.name ||
                                                            'Sistem'}
                                                    </span>
                                                </div>
                                                <button
                                                    onclick={() =>
                                                        handleDeleteDoc(ver.id)}
                                                    class="text-rose-500 hover:text-rose-600 p-0.5 hover:bg-rose-500/10 rounded cursor-pointer shrink-0"
                                                    title="Hapus Versi"
                                                    aria-label="Hapus versi {ver.version} dokumen {ver.file_name}"
                                                >
                                                    <Trash2 class="size-3" />
                                                </button>
                                            </div>
                                        {/each}
                                    </div>
                                {/if}
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>
