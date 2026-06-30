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
    import { Link, useForm, router } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import Edit2 from 'lucide-svelte/icons/edit-2';
    import FileUp from 'lucide-svelte/icons/file-up';
    import Paperclip from 'lucide-svelte/icons/paperclip';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import StatusBadge from '@/components/StatusBadge.svelte';
    import { formatRupiah } from '@/lib/utils';
    import { toUrl } from '@/lib/utils';
    import { edit } from '@/routes/activities';
    import { upload, deleteMethod } from '@/routes/activities/documents';

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
                description: string | null;
                amount: number;
                realizations: Array<{
                    id: number;
                    amount: number;
                    realization_date: string;
                    description: string | null;
                }>;
            }>;
            documents: Array<{
                id: number;
                file_name: string;
                file_path: string;
                file_type: string;
                file_size: number;
                description: string | null;
                uploader?: { name: string } | null;
            }>;
        };
    } = $props();

    const uploadForm = useForm({
        file: null as File | null,
        description: '',
    });

    let fileInput: HTMLInputElement;

    function handleFileChange(e: Event) {
        const target = e.target as HTMLInputElement;

        if (target.files && target.files.length > 0) {
            uploadForm.file = target.files[0];
        }
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
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5"
            >
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
            <Link
                href={toUrl(edit({ activity: activity.id }))}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5"
            >
                <Edit2 class="size-4" />
                Edit Kegiatan
            </Link>
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
                                    <span
                                        class="text-xs uppercase font-semibold px-2 py-0.5 rounded bg-primary/10 text-primary"
                                        >{budget.budget_category}</span
                                    >
                                    <p
                                        class="text-sm font-medium mt-1 text-foreground"
                                    >
                                        {budget.description || 'Pagu Belanja'}
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
                                            class="flex justify-between text-xs text-muted-foreground"
                                        >
                                            <span
                                                >{real.description ||
                                                    'Realisasi'} ({formatDate(
                                                    real.realization_date,
                                                )})</span
                                            >
                                            <span
                                                class="font-bold text-emerald-600 dark:text-emerald-400"
                                                >{formatRupiah(
                                                    real.amount,
                                                )}</span
                                            >
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

            <!-- Document Upload & List -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm"
            >
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Lampiran Dokumen
                </h3>

                <!-- Upload form -->
                <form onsubmit={handleUpload} class="space-y-2">
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
                            class="w-full flex items-center justify-center gap-1.5 h-9 rounded-md border border-dashed border-zinc-200 dark:border-zinc-800 bg-background text-xs font-semibold text-muted-foreground hover:text-foreground hover:bg-accent transition-colors cursor-pointer"
                        >
                            <FileUp class="size-4" />
                            {#if uploadForm.file}
                                {uploadForm.file.name}
                            {:else}
                                Pilih File Dokumen (Max 10MB)
                            {/if}
                        </button>
                    </div>

                    <input
                        type="text"
                        bind:value={uploadForm.description}
                        placeholder="Deskripsi singkat..."
                        class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary"
                    />

                    <button
                        type="submit"
                        disabled={uploadForm.processing || !uploadForm.file}
                        class="w-full flex items-center justify-center gap-1 h-8 rounded-md bg-primary hover:bg-primary/95 text-white text-xs font-medium transition-colors cursor-pointer disabled:opacity-50"
                    >
                        Unggah Dokumen
                    </button>
                </form>

                <!-- Document List -->
                {#if !activity.documents || activity.documents.length === 0}
                    <p
                        class="text-xs text-muted-foreground/60 italic text-center py-2"
                    >
                        Belum ada dokumen diunggah.
                    </p>
                {:else}
                    <div
                        class="space-y-2 border-t border-sidebar-border/20 pt-3 mt-3"
                    >
                        {#each activity.documents as doc}
                            <div
                                class="flex items-start justify-between gap-2 p-2 rounded bg-zinc-50/50 dark:bg-zinc-900/40 border border-sidebar-border/20"
                            >
                                <div class="flex items-start gap-1.5 min-w-0">
                                    <Paperclip
                                        class="size-3.5 text-muted-foreground shrink-0 mt-0.5"
                                    />
                                    <div class="min-w-0">
                                        <a
                                            href={`/storage/${doc.file_path}`}
                                            target="_blank"
                                            class="text-xs font-medium text-primary hover:underline truncate block"
                                            title={doc.file_name}
                                        >
                                            {doc.file_name}
                                        </a>
                                        {#if doc.description}
                                            <p
                                                class="text-[10px] text-muted-foreground leading-tight mt-0.5"
                                            >
                                                {doc.description}
                                            </p>
                                        {/if}
                                    </div>
                                </div>
                                <button
                                    onclick={() => handleDeleteDoc(doc.id)}
                                    class="text-rose-500 hover:text-rose-600 p-1 hover:bg-rose-500/10 rounded shrink-0 cursor-pointer"
                                    title="Hapus"
                                >
                                    <Trash2 class="size-3.5" />
                                </button>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>
