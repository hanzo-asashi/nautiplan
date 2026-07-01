<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as reportIndex } from '@/routes/monitoring/reports';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Laporan & Monev',
                href: reportIndex(),
            },
            {
                title: 'Formulir Laporan & Evaluasi',
                href: '#',
            },
        ],
    };
</script>

<script lang="ts">
    import { useForm, Link } from '@inertiajs/svelte';
    import AlertTriangle from 'lucide-svelte/icons/alert-triangle';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import CheckCircle from 'lucide-svelte/icons/check-circle';
    import FileDown from 'lucide-svelte/icons/file-down';
    import FileText from 'lucide-svelte/icons/file-text';
    import Info from 'lucide-svelte/icons/info';
    import ShieldAlert from 'lucide-svelte/icons/shield-alert';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { formatRupiah, toUrl } from '@/lib/utils';
    import {
        store as reportStore,
        evaluate as reportEvaluate,
    } from '@/routes/monitoring/reports';
    import { pdf as quarterlyPdf } from '@/routes/reports/quarterly';

    let {
        activity,
        quarter,
        indicators = [],
        sub_activities = [],
        report = null,
        auth_is_admin = false,
    }: {
        activity: {
            id: number;
            code: string;
            name: string;
            description: string | null;
            unit_name: string;
            total_budget: number;
            total_realized: number;
            progress_percentage: number;
        };
        quarter: string;
        indicators: Array<{
            id: number;
            code: string;
            name: string;
            target_value: number;
            actual_value: number | null;
            unit_of_measure: string;
            achievement: number;
        }>;
        sub_activities: Array<{
            id: number;
            name: string;
            progress_percentage: number;
        }>;
        report: {
            id: number;
            status: 'draft' | 'submitted' | 'reviewed';
            progress_description: string;
            obstacles: string;
            solutions: string;
            submitted_by_name: string | null;
            submitted_at: string | null;
            evaluation_score: number | null;
            evaluation_notes: string | null;
            recommendations: string | null;
            reviewed_by_name: string | null;
            reviewed_at: string | null;
        } | null;
        auth_is_admin: boolean;
    } = $props();

    // Active tab
    let activeTab = $state('overview'); // 'overview', 'report', 'evaluation'

    // Form for Operator Laporan
    const reportForm = useForm({
        status: report?.status || 'draft',
        progress_description: report?.progress_description || '',
        obstacles: report?.obstacles || '',
        solutions: report?.solutions || '',
    });

    // Form for Reviewer Evaluasi
    const evaluationForm = useForm({
        evaluation_score: report?.evaluation_score || 80,
        evaluation_notes: report?.evaluation_notes || '',
        recommendations: report?.recommendations || '',
    });

    const submitReport = (status: 'draft' | 'submitted') => {
        reportForm.status = status;
        reportForm.post(
            toUrl(reportStore({ activity: activity.id, quarter: quarter })),
            {
                preserveScroll: true,
            },
        );
    };

    const submitEvaluation = () => {
        evaluationForm.post(
            toUrl(reportEvaluate({ activity: activity.id, quarter: quarter })),
            {
                preserveScroll: true,
            },
        );
    };
</script>

<AppHead title={`Laporan & Monev - ${activity.name}`} />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <!-- Header with Back link -->
    <div class="flex flex-col gap-3">
        <Link
            href={toUrl(reportIndex())}
            class="flex items-center gap-1.5 text-xs text-muted-foreground hover:text-primary transition-colors w-fit"
        >
            <ArrowLeft class="size-3.5" />
            Kembali ke Daftar Laporan
        </Link>
        <PageHeader
            title={`Laporan & Evaluasi - ${quarter}`}
            description={`Kegiatan: [${activity.code}] ${activity.name}`}
        >
            {#snippet actions()}
                <a
                    href={toUrl(
                        quarterlyPdf({
                            activity: activity.id,
                            quarter: quarter,
                        }),
                    )}
                    class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5"
                >
                    <FileDown class="size-4" />
                    Unduh PDF (Monev)
                </a>
            {/snippet}
        </PageHeader>
    </div>

    <!-- Layout Grid -->
    <div class="grid gap-6 lg:grid-cols-4 items-start">
        <!-- Sidebar Navigation Tabs -->
        <div class="lg:col-span-1 flex flex-col gap-1.5">
            <button
                onclick={() => (activeTab = 'overview')}
                class="flex items-center gap-2.5 px-4 py-3 text-xs font-semibold rounded-lg text-left transition-all border
                    {activeTab === 'overview'
                    ? 'bg-primary/10 border-primary/20 text-primary shadow-sm'
                    : 'bg-card/40 border-sidebar-border/30 text-muted-foreground hover:text-foreground hover:bg-card/65'}"
            >
                <FileText class="size-4" />
                Ringkasan Kinerja
            </button>
            <button
                onclick={() => (activeTab = 'report')}
                class="flex items-center gap-2.5 px-4 py-3 text-xs font-semibold rounded-lg text-left transition-all border
                    {activeTab === 'report'
                    ? 'bg-primary/10 border-primary/20 text-primary shadow-sm'
                    : 'bg-card/40 border-sidebar-border/30 text-muted-foreground hover:text-foreground hover:bg-card/65'}"
            >
                <Info class="size-4" />
                Laporan Kinerja Unit
                {#if report?.status === 'submitted'}
                    <span class="ml-auto size-2 rounded-full bg-amber-500"
                    ></span>
                {:else if report?.status === 'reviewed'}
                    <span class="ml-auto size-2 rounded-full bg-emerald-500"
                    ></span>
                {/if}
            </button>
            <button
                onclick={() => (activeTab = 'evaluation')}
                class="flex items-center gap-2.5 px-4 py-3 text-xs font-semibold rounded-lg text-left transition-all border
                    {activeTab === 'evaluation'
                    ? 'bg-primary/10 border-primary/20 text-primary shadow-sm'
                    : 'bg-card/40 border-sidebar-border/30 text-muted-foreground hover:text-foreground hover:bg-card/65'}"
            >
                <ShieldAlert class="size-4" />
                Evaluasi Monev
                {#if report?.status === 'reviewed'}
                    <span class="ml-auto size-2 rounded-full bg-emerald-500"
                    ></span>
                {/if}
            </button>

            <!-- Activity Metadata Sidebar -->
            <div
                class="mt-4 p-4 rounded-xl border border-sidebar-border/50 bg-card/20 backdrop-blur-md space-y-3 text-[11px]"
            >
                <h4
                    class="font-bold text-foreground uppercase tracking-wider text-[10px] text-muted-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Detail Kegiatan
                </h4>
                <div>
                    <span class="text-muted-foreground block font-medium"
                        >Unit Pelaksana</span
                    >
                    <span class="font-bold text-foreground"
                        >{activity.unit_name}</span
                    >
                </div>
                <div>
                    <span class="text-muted-foreground block font-medium"
                        >Pagu Anggaran</span
                    >
                    <span
                        class="font-bold text-foreground text-yellow-600 dark:text-yellow-500"
                        >{formatRupiah(activity.total_budget)}</span
                    >
                </div>
                <div>
                    <span class="text-muted-foreground block font-medium"
                        >Realisasi Belanja</span
                    >
                    <span
                        class="font-bold text-foreground text-emerald-600 dark:text-emerald-400"
                        >{formatRupiah(activity.total_realized)}</span
                    >
                </div>
                <div>
                    <span class="text-muted-foreground block font-medium"
                        >Progres Fisik</span
                    >
                    <span class="font-bold text-foreground"
                        >{activity.progress_percentage}%</span
                    >
                </div>
            </div>
        </div>

        <!-- Main Tab Contents Panel -->
        <div class="lg:col-span-3">
            {#if activeTab === 'overview'}
                <!-- OVERVIEW TAB -->
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-6"
                >
                    <div>
                        <h3 class="text-base font-bold text-foreground mb-1">
                            Ringkasan Kinerja & Indikator
                        </h3>
                        <p class="text-xs text-muted-foreground">
                            Realisasi anggaran dan pemenuhan indikator target
                            untuk triwulan {quarter}.
                        </p>
                    </div>

                    <!-- Budget progress bar -->
                    <div
                        class="space-y-2 border-b border-sidebar-border/30 pb-4"
                    >
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-foreground"
                                >Persentase Realisasi Anggaran</span
                            >
                            <span class="font-semibold text-primary">
                                {activity.total_budget > 0
                                    ? Math.round(
                                          (activity.total_realized /
                                              activity.total_budget) *
                                              100,
                                      )
                                    : 0}%
                            </span>
                        </div>
                        <div
                            class="h-2 w-full bg-zinc-200/50 dark:bg-zinc-800/50 rounded-full overflow-hidden border border-sidebar-border/20"
                        >
                            <div
                                class="h-full bg-primary rounded-full"
                                style="width: {activity.total_budget > 0
                                    ? Math.min(
                                          100,
                                          Math.round(
                                              (activity.total_realized /
                                                  activity.total_budget) *
                                                  100,
                                          ),
                                      )
                                    : 0}%"
                            ></div>
                        </div>
                    </div>

                    <!-- Indicators specific to the quarter -->
                    <div class="space-y-4">
                        <h4
                            class="text-xs font-bold text-foreground uppercase tracking-wider text-muted-foreground"
                        >
                            Target Indikator Kinerja ({quarter})
                        </h4>
                        {#if indicators.length === 0}
                            <div
                                class="text-xs text-muted-foreground/60 italic p-3 bg-zinc-50/50 dark:bg-zinc-900/50 rounded-lg border border-sidebar-border/20"
                            >
                                Tidak ada indikator kinerja target yang
                                dijadwalkan pada {quarter} ini.
                            </div>
                        {:else}
                            <div class="space-y-4">
                                {#each indicators as ind (ind.id)}
                                    <div
                                        class="p-3.5 bg-zinc-50/50 dark:bg-zinc-900/40 border border-sidebar-border/30 rounded-lg space-y-2.5"
                                    >
                                        <div
                                            class="flex justify-between items-start gap-3"
                                        >
                                            <div>
                                                <span
                                                    class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-primary/10 text-primary border border-primary/20 uppercase tracking-wider"
                                                    >{ind.code}</span
                                                >
                                                <h5
                                                    class="text-xs font-bold text-foreground mt-1.5"
                                                >
                                                    {ind.name}
                                                </h5>
                                            </div>
                                            <div class="text-right text-xs">
                                                <div
                                                    class="text-muted-foreground"
                                                >
                                                    Target: <span
                                                        class="font-bold text-foreground"
                                                        >{ind.target_value}
                                                        {ind.unit_of_measure}</span
                                                    >
                                                </div>
                                                <div class="mt-0.5">
                                                    Realisasi: <span
                                                        class="font-bold text-foreground"
                                                        >{ind.actual_value !==
                                                        null
                                                            ? ind.actual_value
                                                            : '—'}
                                                        {ind.unit_of_measure}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-1.5">
                                            <div
                                                class="flex items-center justify-between text-[10px]"
                                            >
                                                <span
                                                    class="text-muted-foreground"
                                                    >Kemajuan Capaian</span
                                                >
                                                <span
                                                    class="font-bold text-foreground"
                                                    >{ind.achievement}%</span
                                                >
                                            </div>
                                            <div
                                                class="h-1.5 w-full bg-zinc-200/50 dark:bg-zinc-800/50 rounded-full overflow-hidden"
                                            >
                                                <div
                                                    class="h-full rounded-full transition-all duration-300
                                                        {ind.achievement >= 100
                                                        ? 'bg-emerald-500'
                                                        : ''}
                                                        {ind.achievement > 0 &&
                                                    ind.achievement < 100
                                                        ? 'bg-amber-500'
                                                        : ''}
                                                        {ind.achievement === 0
                                                        ? 'bg-rose-500'
                                                        : ''}
                                                    "
                                                    style="width: {Math.min(
                                                        100,
                                                        ind.achievement,
                                                    )}%"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>

                    <!-- Sub activities progress -->
                    <div class="space-y-3">
                        <h4
                            class="text-xs font-bold text-foreground uppercase tracking-wider text-muted-foreground"
                        >
                            Sub Kegiatan & Progres Fisik
                        </h4>
                        {#if sub_activities.length === 0}
                            <div
                                class="text-xs text-muted-foreground/60 italic p-3 bg-zinc-50/50 dark:bg-zinc-900/50 rounded-lg border border-sidebar-border/20"
                            >
                                Tidak ada sub kegiatan terdaftar.
                            </div>
                        {:else}
                            <div
                                class="divide-y divide-sidebar-border/20 border border-sidebar-border/30 rounded-lg overflow-hidden bg-zinc-50/50 dark:bg-zinc-900/30"
                            >
                                {#each sub_activities as sub}
                                    <div
                                        class="flex items-center justify-between p-3 text-xs"
                                    >
                                        <span
                                            class="font-semibold text-foreground"
                                            >{sub.name}</span
                                        >
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="font-bold text-foreground"
                                                >{sub.progress_percentage}%</span
                                            >
                                            <div
                                                class="w-20 h-1.5 bg-zinc-200/50 dark:bg-zinc-800/50 rounded-full overflow-hidden"
                                            >
                                                <div
                                                    class="h-full bg-primary"
                                                    style="width: {sub.progress_percentage}%"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>
            {:else if activeTab === 'report'}
                <!-- REPORT TAB -->
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-6"
                >
                    <div
                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-sidebar-border/30 pb-4"
                    >
                        <div>
                            <h3
                                class="text-base font-bold text-foreground mb-1"
                            >
                                Laporan Kinerja Unit Kerja
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Laporkan perkembangan, hambatan, serta solusi
                                kegiatan untuk periode {quarter}.
                            </p>
                        </div>
                        <!-- Status badge -->
                        {#if report}
                            <span
                                class="px-2.5 py-1 text-[10px] font-bold uppercase rounded border
                                    {report.status === 'draft'
                                    ? 'bg-sky-500/10 text-sky-600 border-sky-500/20'
                                    : ''}
                                    {report.status === 'submitted'
                                    ? 'bg-amber-500/10 text-amber-600 border-amber-500/20'
                                    : ''}
                                    {report.status === 'reviewed'
                                    ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20'
                                    : ''}
                                "
                            >
                                {report.status === 'draft' ? 'Draft' : ''}
                                {report.status === 'submitted' ? 'Dikirim' : ''}
                                {report.status === 'reviewed'
                                    ? 'Selesai Monev'
                                    : ''}
                            </span>
                        {:else}
                            <span
                                class="px-2.5 py-1 text-[10px] font-bold uppercase rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border-zinc-200 dark:border-zinc-700/50"
                            >
                                Belum Dibuat
                            </span>
                        {/if}
                    </div>

                    {#if report?.status === 'submitted' || report?.status === 'reviewed'}
                        <!-- Read Only View for submitted/reviewed report -->
                        <div class="space-y-5">
                            <div
                                class="p-4 bg-zinc-50/50 dark:bg-zinc-900/40 border border-sidebar-border/30 rounded-lg flex items-start gap-3"
                            >
                                <CheckCircle
                                    class="size-5 text-emerald-500 mt-0.5 shrink-0"
                                />
                                <div class="text-xs space-y-1">
                                    <p class="font-bold text-foreground">
                                        Laporan Kinerja Telah Dikirim
                                    </p>
                                    <p class="text-muted-foreground">
                                        Laporan dikirim oleh <span
                                            class="font-bold text-foreground"
                                            >{report.submitted_by_name}</span
                                        >
                                        pada {report.submitted_at}.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <h4
                                        class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2"
                                    >
                                        Deskripsi Perkembangan Kegiatan
                                    </h4>
                                    <div
                                        class="p-3 bg-zinc-50/20 dark:bg-zinc-900/20 border border-sidebar-border/20 rounded-md text-xs text-foreground whitespace-pre-wrap"
                                    >
                                        {report.progress_description}
                                    </div>
                                </div>

                                <div>
                                    <h4
                                        class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2"
                                    >
                                        Kendala / Hambatan
                                    </h4>
                                    <div
                                        class="p-3 bg-zinc-50/20 dark:bg-zinc-900/20 border border-sidebar-border/20 rounded-md text-xs text-foreground whitespace-pre-wrap"
                                    >
                                        {report.obstacles}
                                    </div>
                                </div>

                                <div>
                                    <h4
                                        class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2"
                                    >
                                        Solusi / Tindak Lanjut
                                    </h4>
                                    <div
                                        class="p-3 bg-zinc-50/20 dark:bg-zinc-900/20 border border-sidebar-border/20 rounded-md text-xs text-foreground whitespace-pre-wrap"
                                    >
                                        {report.solutions}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {:else}
                        <!-- Editable Form -->
                        <form
                            onsubmit={(e) => e.preventDefault()}
                            class="space-y-5"
                        >
                            <div class="space-y-2">
                                <label
                                    for="progress_description"
                                    class="text-xs font-bold text-foreground"
                                    >Deskripsi Perkembangan Kegiatan <span
                                        class="text-rose-500">*</span
                                    ></label
                                >
                                <textarea
                                    id="progress_description"
                                    rows="4"
                                    bind:value={reportForm.progress_description}
                                    placeholder="Tuliskan rincian kegiatan fisik yang telah terealisasi..."
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                                ></textarea>
                                {#if reportForm.errors.progress_description}
                                    <p
                                        class="text-[10px] text-rose-500 font-semibold"
                                    >
                                        {reportForm.errors.progress_description}
                                    </p>
                                {/if}
                            </div>

                            <div class="space-y-2">
                                <label
                                    for="obstacles"
                                    class="text-xs font-bold text-foreground"
                                    >Kendala / Hambatan <span
                                        class="text-rose-500">*</span
                                    ></label
                                >
                                <textarea
                                    id="obstacles"
                                    rows="3"
                                    bind:value={reportForm.obstacles}
                                    placeholder="Sebutkan hambatan yang dihadapi dalam pencapaian target kegiatan..."
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                                ></textarea>
                                {#if reportForm.errors.obstacles}
                                    <p
                                        class="text-[10px] text-rose-500 font-semibold"
                                    >
                                        {reportForm.errors.obstacles}
                                    </p>
                                {/if}
                            </div>

                            <div class="space-y-2">
                                <label
                                    for="solutions"
                                    class="text-xs font-bold text-foreground"
                                    >Solusi / Tindak Lanjut <span
                                        class="text-rose-500">*</span
                                    ></label
                                >
                                <textarea
                                    id="solutions"
                                    rows="3"
                                    bind:value={reportForm.solutions}
                                    placeholder="Tuliskan tindakan yang akan dilakukan untuk mengatasi kendala tersebut..."
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                                ></textarea>
                                {#if reportForm.errors.solutions}
                                    <p
                                        class="text-[10px] text-rose-500 font-semibold"
                                    >
                                        {reportForm.errors.solutions}
                                    </p>
                                {/if}
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button
                                    type="button"
                                    onclick={() => submitReport('draft')}
                                    disabled={reportForm.processing}
                                    class="px-4 py-2 text-xs font-bold bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 rounded-md disabled:opacity-50 transition-all cursor-pointer"
                                >
                                    Simpan Draft
                                </button>
                                <button
                                    type="button"
                                    onclick={() => submitReport('submitted')}
                                    disabled={reportForm.processing}
                                    class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary/90 text-white rounded-md disabled:opacity-50 transition-all cursor-pointer"
                                >
                                    Kirim Laporan
                                </button>
                            </div>
                        </form>
                    {/if}
                </div>
            {:else}
                <!-- EVALUATION TAB -->
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-6"
                >
                    <div class="border-b border-sidebar-border/30 pb-4">
                        <h3 class="text-base font-bold text-foreground mb-1">
                            Verifikasi & Evaluasi Monev
                        </h3>
                        <p class="text-xs text-muted-foreground">
                            Berikan nilai evaluasi, catatan perkembangan, serta
                            rekomendasi perbaikan kinerja.
                        </p>
                    </div>

                    {#if !report || report.status === 'draft'}
                        <!-- Report not submitted warning -->
                        <div
                            class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-lg flex items-start gap-3"
                        >
                            <AlertTriangle
                                class="size-5 text-amber-500 mt-0.5 shrink-0"
                            />
                            <div class="text-xs space-y-1">
                                <p class="font-bold text-foreground">
                                    Laporan Belum Dikirim
                                </p>
                                <p class="text-muted-foreground">
                                    Evaluasi monitoring & evaluasi (M&E) hanya
                                    dapat dilakukan setelah unit pelaksana
                                    mengirimkan laporan kinerja triwulanan.
                                </p>
                            </div>
                        </div>
                    {:else if report.status === 'reviewed'}
                        <!-- Evaluation Read-Only view -->
                        <div class="space-y-5">
                            <div
                                class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-lg flex items-start gap-3"
                            >
                                <CheckCircle
                                    class="size-5 text-emerald-500 mt-0.5 shrink-0"
                                />
                                <div class="text-xs space-y-1">
                                    <p class="font-bold text-foreground">
                                        Evaluasi Selesai
                                    </p>
                                    <p class="text-muted-foreground">
                                        Evaluasi telah diverifikasi oleh
                                        reviewer <span
                                            class="font-bold text-foreground"
                                            >{report.reviewed_by_name}</span
                                        >
                                        pada {report.reviewed_at}.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div
                                    class="p-4 bg-zinc-50/50 dark:bg-zinc-900/40 border border-sidebar-border/30 rounded-lg flex flex-col justify-between items-center text-center"
                                >
                                    <span
                                        class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider"
                                        >Nilai Evaluasi</span
                                    >
                                    <span
                                        class="text-4xl font-extrabold text-primary my-2"
                                        >{report.evaluation_score}</span
                                    >
                                    <span
                                        class="text-[10px] font-medium text-emerald-600 dark:text-emerald-400"
                                        >Skala 1 - 100</span
                                    >
                                </div>
                                <div class="sm:col-span-2 space-y-4">
                                    <div>
                                        <h4
                                            class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2"
                                        >
                                            Catatan Evaluasi / Rekomendasi
                                        </h4>
                                        <div
                                            class="p-3 bg-zinc-50/20 dark:bg-zinc-900/20 border border-sidebar-border/20 rounded-md text-xs text-foreground whitespace-pre-wrap"
                                        >
                                            {report.evaluation_notes}
                                        </div>
                                    </div>

                                    <div>
                                        <h4
                                            class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2"
                                        >
                                            Rekomendasi Tindak Lanjut
                                        </h4>
                                        <div
                                            class="p-3 bg-zinc-50/20 dark:bg-zinc-900/20 border border-sidebar-border/20 rounded-md text-xs text-foreground whitespace-pre-wrap"
                                        >
                                            {report.recommendations}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {:else}
                        <!-- Reviewer Input Form (Admin / Super Admin only) -->
                        {#if auth_is_admin}
                            <form
                                onsubmit={(e) => e.preventDefault()}
                                class="space-y-5"
                            >
                                <div class="space-y-2">
                                    <label
                                        for="evaluation_score"
                                        class="text-xs font-bold text-foreground"
                                        >Nilai Evaluasi Kinerja (1 - 100) <span
                                            class="text-rose-500">*</span
                                        ></label
                                    >
                                    <input
                                        id="evaluation_score"
                                        type="number"
                                        min="1"
                                        max="100"
                                        bind:value={
                                            evaluationForm.evaluation_score
                                        }
                                        class="w-full sm:max-w-[200px] px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                    />
                                    {#if evaluationForm.errors.evaluation_score}
                                        <p
                                            class="text-[10px] text-rose-500 font-semibold"
                                        >
                                            {evaluationForm.errors
                                                .evaluation_score}
                                        </p>
                                    {/if}
                                </div>

                                <div class="space-y-2">
                                    <label
                                        for="evaluation_notes"
                                        class="text-xs font-bold text-foreground"
                                        >Catatan Evaluasi <span
                                            class="text-rose-500">*</span
                                        ></label
                                    >
                                    <textarea
                                        id="evaluation_notes"
                                        rows="4"
                                        bind:value={
                                            evaluationForm.evaluation_notes
                                        }
                                        placeholder="Berikan ulasan dan penilaian atas capaian kinerja triwulanan unit kerja..."
                                        class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                                    ></textarea>
                                    {#if evaluationForm.errors.evaluation_notes}
                                        <p
                                            class="text-[10px] text-rose-500 font-semibold"
                                        >
                                            {evaluationForm.errors
                                                .evaluation_notes}
                                        </p>
                                    {/if}
                                </div>

                                <div class="space-y-2">
                                    <label
                                        for="recommendations"
                                        class="text-xs font-bold text-foreground"
                                        >Rekomendasi Tindak Lanjut <span
                                            class="text-rose-500">*</span
                                        ></label
                                    >
                                    <textarea
                                        id="recommendations"
                                        rows="3"
                                        bind:value={
                                            evaluationForm.recommendations
                                        }
                                        placeholder="Tuliskan rekomendasi tindakan perbaikan atau arahan tindak lanjut..."
                                        class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                                    ></textarea>
                                    {#if evaluationForm.errors.recommendations}
                                        <p
                                            class="text-[10px] text-rose-500 font-semibold"
                                        >
                                            {evaluationForm.errors
                                                .recommendations}
                                        </p>
                                    {/if}
                                </div>

                                <div class="pt-2">
                                    <button
                                        type="button"
                                        onclick={submitEvaluation}
                                        disabled={evaluationForm.processing}
                                        class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary/90 text-white rounded-md disabled:opacity-50 transition-all cursor-pointer"
                                    >
                                        Simpan & Selesaikan Evaluasi
                                    </button>
                                </div>
                            </form>
                        {:else}
                            <!-- Operator warning (cannot evaluate) -->
                            <div
                                class="p-4 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700/50 rounded-lg flex items-start gap-3"
                            >
                                <AlertTriangle
                                    class="size-5 text-zinc-500 mt-0.5 shrink-0"
                                />
                                <div class="text-xs space-y-1">
                                    <p class="font-bold text-foreground">
                                        Akses Terbatas
                                    </p>
                                    <p class="text-muted-foreground">
                                        Anda tidak memiliki hak akses sebagai
                                        Reviewer. Evaluasi Monev hanya dapat
                                        diisi oleh Administrator.
                                    </p>
                                </div>
                            </div>
                        {/if}
                    {/if}
                </div>
            {/if}
        </div>
    </div>
</div>
