<script module lang="ts">
    import { dashboard } from '@/routes';
    import { analytics as analyticsIndex } from '@/routes/reports';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Analisis & Realisasi',
                href: analyticsIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { useForm, router } from '@inertiajs/svelte';
    import AlertTriangle from 'lucide-svelte/icons/alert-triangle';
    import CheckCircle2 from 'lucide-svelte/icons/check-circle-2';
    import Download from 'lucide-svelte/icons/download';
    import FileSpreadsheet from 'lucide-svelte/icons/file-spreadsheet';
    import Upload from 'lucide-svelte/icons/upload';
    import AppHead from '@/components/AppHead.svelte';
    import BarChart from '@/components/charts/BarChart.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { formatRupiah, toUrl } from '@/lib/utils';
    import reports from '@/routes/reports';

    let {
        unitsData = [],
        programsData = [],
        multiYearData = [],
        fiscalYears = [],
        filters = { fiscal_year_id: null },
        importErrors = null,
        success = null,
    }: {
        unitsData: Array<{ label: string; value1: number; value2: number }>;
        programsData: Array<{ label: string; value1: number; value2: number }>;
        multiYearData: Array<{
            year: number;
            total_activities: number;
            pagu: number;
            realisasi: number;
            kpi_achievement: number;
        }>;
        fiscalYears: Array<{ id: number; year: number; is_active: boolean }>;
        filters: { fiscal_year_id: number | null };
        importErrors: Array<{
            row: number;
            code: string;
            messages: string[];
        }> | null;
        success: string | null;
    } = $props();

    let selectedYearId = $state(
        filters.fiscal_year_id ||
            fiscalYears.find((f) => f.is_active)?.id ||
            fiscalYears[0]?.id,
    );
    let activeTab = $state('realization'); // 'realization', 'multi-year', 'import-export'

    const selectedYear = $derived(
        fiscalYears.find((f) => f.id === selectedYearId)?.year || 2026,
    );

    const importForm = useForm({
        file: null as File | null,
    });

    function applyYearFilter() {
        router.visit(analyticsIndex().url, {
            data: { fiscal_year_id: selectedYearId },
            preserveState: true,
        });
    }

    function handleFileChange(e: Event) {
        const target = e.target as HTMLInputElement;

        if (target.files && target.files[0]) {
            importForm.file = target.files[0];
        }
    }

    function submitImport(e: Event) {
        e.preventDefault();

        if (!importForm.file) {
            return;
        }

        importForm.post(toUrl(reports.import.excel()), {
            onSuccess: () => {
                importForm.reset();
            },
        });
    }

    function triggerExport() {
        const url =
            toUrl(reports.export.excel()) + `?fiscal_year_id=${selectedYearId}`;
        window.location.href = url;
    }

    function triggerDownloadTemplate() {
        window.location.href = toUrl(reports.import.template());
    }
</script>

<AppHead title="Analisis Realisasi & Laporan" />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <PageHeader
        title="Analisis & Realisasi"
        description="Analisis grafik realisasi anggaran, data perbandingan program tahunan, serta ekspor & impor kegiatan secara massal."
    />

    <!-- Header Filter & Tabs -->
    <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-sidebar-border/30 pb-px"
    >
        <!-- Tabs Header -->
        <div class="flex gap-2">
            <button
                onclick={() => (activeTab = 'realization')}
                class="px-4 py-2.5 text-xs font-semibold border-b-2 -mb-px transition-all cursor-pointer
                    {activeTab === 'realization'
                    ? 'border-primary text-primary font-bold'
                    : 'border-transparent text-muted-foreground hover:text-foreground'}"
            >
                Realisasi Anggaran
            </button>
            <button
                onclick={() => (activeTab = 'multi-year')}
                class="px-4 py-2.5 text-xs font-semibold border-b-2 -mb-px transition-all cursor-pointer
                    {activeTab === 'multi-year'
                    ? 'border-primary text-primary font-bold'
                    : 'border-transparent text-muted-foreground hover:text-foreground'}"
            >
                Perbandingan Multi-Tahun
            </button>
            <button
                onclick={() => (activeTab = 'import-export')}
                class="px-4 py-2.5 text-xs font-semibold border-b-2 -mb-px transition-all cursor-pointer
                    {activeTab === 'import-export'
                    ? 'border-primary text-primary font-bold'
                    : 'border-transparent text-muted-foreground hover:text-foreground'}"
            >
                Hub Impor & Ekspor
            </button>
        </div>

        <!-- Year Filter (Hidden for Multi-Year tab since it aggregates all years) -->
        {#if activeTab !== 'multi-year'}
            <div
                class="flex items-center gap-2 self-end md:self-auto mb-2 md:mb-0"
            >
                <span class="text-xs text-muted-foreground font-semibold"
                    >Tahun Anggaran:</span
                >
                <select
                    bind:value={selectedYearId}
                    onchange={applyYearFilter}
                    class="px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary font-semibold"
                >
                    {#each fiscalYears as fy}
                        <option value={fy.id}
                            >{fy.year} {fy.is_active ? '(Aktif)' : ''}</option
                        >
                    {/each}
                </select>
            </div>
        {/if}
    </div>

    <!-- Active Tab Panel -->
    <div class="space-y-6">
        {#if activeTab === 'realization'}
            <!-- TAB 1: REALIZATION CHARTS -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Unit Chart -->
                {#if unitsData.length === 0}
                    <div
                        class="rounded-xl border border-sidebar-border/50 bg-card/40 p-12 text-center text-muted-foreground/60 text-sm italic"
                    >
                        Tidak ada data anggaran unit kerja pada tahun anggaran {selectedYear}.
                    </div>
                {:else}
                    <BarChart
                        title="Realisasi Anggaran Per Unit Kerja ({selectedYear})"
                        data={unitsData}
                    />
                {/if}

                <!-- Program Chart -->
                {#if programsData.length === 0}
                    <div
                        class="rounded-xl border border-sidebar-border/50 bg-card/40 p-12 text-center text-muted-foreground/60 text-sm italic"
                    >
                        Tidak ada data anggaran program pada tahun anggaran {selectedYear}.
                    </div>
                {:else}
                    <BarChart
                        title="Realisasi Anggaran Per Program Kerja ({selectedYear})"
                        data={programsData}
                    />
                {/if}
            </div>
        {:else if activeTab === 'multi-year'}
            <!-- TAB 2: MULTI-YEAR COMPARISON -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-6"
            >
                <h3 class="text-base font-bold text-foreground">
                    Tren & Capaian Multi-Tahun
                </h3>

                <!-- Table comparison -->
                <div
                    class="overflow-x-auto border border-sidebar-border/30 rounded-lg"
                >
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-zinc-100/50 dark:bg-zinc-900/50 border-b border-sidebar-border/30 font-bold text-muted-foreground uppercase tracking-wider"
                            >
                                <th class="p-3">Tahun Anggaran</th>
                                <th class="p-3 text-center">Jumlah Kegiatan</th>
                                <th class="p-3 text-right">Pagu Anggaran</th>
                                <th class="p-3 text-right"
                                    >Realisasi Anggaran</th
                                >
                                <th class="p-3 text-center"
                                    >Persentase Realisasi</th
                                >
                                <th class="p-3 text-center"
                                    >Indeks Capaian IKU/IKK</th
                                >
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/20">
                            {#each multiYearData as row}
                                {@const pctRealisasi =
                                    row.pagu > 0
                                        ? (
                                              (row.realisasi / row.pagu) *
                                              100
                                          ).toFixed(1)
                                        : '0.0'}
                                <tr
                                    class="hover:bg-zinc-50/30 dark:hover:bg-zinc-900/10 transition-colors"
                                >
                                    <td
                                        class="p-3 font-bold text-foreground text-sm"
                                        >{row.year}</td
                                    >
                                    <td
                                        class="p-3 text-center font-semibold text-foreground"
                                        >{row.total_activities}</td
                                    >
                                    <td
                                        class="p-3 text-right font-medium text-foreground"
                                        >{formatRupiah(row.pagu)}</td
                                    >
                                    <td
                                        class="p-3 text-right font-medium text-emerald-600 dark:text-emerald-400"
                                        >{formatRupiah(row.realisasi)}</td
                                    >
                                    <td
                                        class="p-3 text-center font-bold text-primary"
                                        >{pctRealisasi}%</td
                                    >
                                    <td class="p-3 text-center">
                                        <span
                                            class="inline-block px-2.5 py-0.5 rounded-full font-bold text-[10px]
                                                {row.kpi_achievement >= 90
                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'
                                                : row.kpi_achievement >= 60
                                                  ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'
                                                  : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'}"
                                        >
                                            {row.kpi_achievement}%
                                        </span>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <!-- Multi-year Highlights Grid -->
                <div class="grid gap-4 md:grid-cols-3">
                    {#each multiYearData.slice(-3) as highlight}
                        {@const rate =
                            highlight.pagu > 0
                                ? (
                                      (highlight.realisasi / highlight.pagu) *
                                      100
                                  ).toFixed(1)
                                : '0.0'}
                        <div
                            class="p-4 bg-zinc-50/50 dark:bg-zinc-950/20 border border-sidebar-border/30 rounded-xl space-y-2"
                        >
                            <h4
                                class="text-xs font-bold text-muted-foreground uppercase"
                            >
                                Ringkasan Tahun {highlight.year}
                            </h4>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground"
                                        >Efisiensi Realisasi:</span
                                    >
                                    <span class="font-bold text-primary"
                                        >{rate}%</span
                                    >
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground"
                                        >Rata-rata Capaian IKU:</span
                                    >
                                    <span class="font-bold text-emerald-600"
                                        >{highlight.kpi_achievement}%</span
                                    >
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground"
                                        >Total Kegiatan:</span
                                    >
                                    <span class="font-bold text-foreground"
                                        >{highlight.total_activities}</span
                                    >
                                </div>
                            </div>
                        </div>
                    {/each}
                </div>
            </div>
        {:else}
            <!-- TAB 3: IMPORT & EXPORT HUB -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- LEFT COLUMN: Excel Export & Template -->
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-5"
                >
                    <div class="space-y-1.5">
                        <h3
                            class="text-base font-bold text-foreground flex items-center gap-2"
                        >
                            <FileSpreadsheet class="size-5 text-primary" />
                            Ekspor & Unduh Template
                        </h3>
                        <p class="text-xs text-muted-foreground">
                            Unduh seluruh daftar rencana kegiatan ke dalam
                            berkas Excel untuk laporan dinas, atau unduh
                            template Excel resmi untuk melakukan pengisian data
                            secara massal.
                        </p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3 pt-2">
                        <!-- Template Download -->
                        <button
                            onclick={triggerDownloadTemplate}
                            class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-background/50 hover:border-primary/30 transition-all cursor-pointer flex flex-col justify-between items-start gap-3 hover:shadow-sm text-left group"
                        >
                            <Download
                                class="size-5 text-muted-foreground group-hover:text-primary transition-colors"
                            />
                            <div>
                                <span
                                    class="text-xs font-bold text-foreground block"
                                    >Template Excel Impor</span
                                >
                                <span
                                    class="text-[10px] text-muted-foreground block mt-1"
                                    >Gunakan template resmi ini agar format
                                    kolom sesuai dengan validasi sistem.</span
                                >
                            </div>
                        </button>

                        <!-- Data Export -->
                        <button
                            onclick={triggerExport}
                            class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-background/50 hover:border-primary/30 transition-all cursor-pointer flex flex-col justify-between items-start gap-3 hover:shadow-sm text-left group"
                        >
                            <FileSpreadsheet
                                class="size-5 text-muted-foreground group-hover:text-emerald-500 transition-colors"
                            />
                            <div>
                                <span
                                    class="text-xs font-bold text-foreground block"
                                    >Ekspor Kegiatan ({selectedYear})</span
                                >
                                <span
                                    class="text-[10px] text-muted-foreground block mt-1"
                                    >Unduh semua rincian kegiatan beserta pagu
                                    anggaran ke dalam Excel.</span
                                >
                            </div>
                        </button>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Excel Bulk Import Form -->
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-4"
                >
                    <div class="space-y-1.5">
                        <h3
                            class="text-base font-bold text-foreground flex items-center gap-2"
                        >
                            <Upload class="size-5 text-primary" />
                            Impor Kegiatan Massal
                        </h3>
                        <p class="text-xs text-muted-foreground">
                            Unggah berkas Excel kegiatan yang telah diisi sesuai
                            format template untuk melakukan *bulk creation*
                            kegiatan baru beserta penanggung jawabnya.
                        </p>
                    </div>

                    <!-- Success Alert -->
                    {#if success}
                        <div
                            class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-start gap-2.5 text-xs font-semibold"
                        >
                            <CheckCircle2 class="size-4 shrink-0 mt-0.5" />
                            <span>{success}</span>
                        </div>
                    {/if}

                    <!-- System Errors Alert -->
                    {#if importForm.errors.file}
                        <div
                            class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 rounded-lg flex items-start gap-2.5 text-xs font-semibold"
                        >
                            <AlertTriangle class="size-4 shrink-0 mt-0.5" />
                            <span>{importForm.errors.file}</span>
                        </div>
                    {/if}

                    <form onsubmit={submitImport} class="space-y-4 pt-2">
                        <div class="space-y-2">
                            <label
                                for="file"
                                class="text-xs font-bold text-foreground block"
                                >Pilih Berkas Excel (.xlsx, .xls) <span
                                    class="text-rose-500">*</span
                                ></label
                            >
                            <input
                                id="file"
                                type="file"
                                accept=".xlsx, .xls, .bin, .ods"
                                onchange={handleFileChange}
                                required
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-zinc-100 dark:file:bg-zinc-800 file:text-foreground hover:file:bg-zinc-200 cursor-pointer"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={importForm.processing || !importForm.file}
                            class="w-full py-2.5 text-xs font-bold bg-primary hover:bg-primary/95 text-white rounded-md transition-all cursor-pointer text-center disabled:opacity-50"
                        >
                            {importForm.processing
                                ? 'Sedang Memproses Impor...'
                                : 'Mulai Proses Impor'}
                        </button>
                    </form>
                </div>
            </div>

            <!-- BOTTOM SECTION: Bulk Import Validation Errors (Full Width) -->
            {#if importErrors && importErrors.length > 0}
                <div
                    class="rounded-xl border border-rose-500/30 bg-rose-500/5 backdrop-blur-md p-6 shadow-sm space-y-4"
                >
                    <div
                        class="flex items-center gap-2 text-rose-600 dark:text-rose-400"
                    >
                        <AlertTriangle class="size-5" />
                        <h3 class="text-base font-bold">
                            Gagal Mengimpor! Terdapat Kesalahan Validasi Data ({importErrors.length}
                            Baris Bermasalah)
                        </h3>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Proses impor dibatalkan (*rollback*) karena terdapat
                        kesalahan pada data berikut. Silakan perbaiki berkas
                        Excel Anda lalu unggah kembali.
                    </p>

                    <!-- Error Grid -->
                    <div
                        class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 max-h-[400px] overflow-y-auto pr-2"
                    >
                        {#each importErrors as err}
                            <div
                                class="p-4 bg-background border border-rose-500/20 rounded-lg space-y-2 text-xs shadow-sm"
                            >
                                <div
                                    class="flex justify-between items-center border-b border-rose-500/10 pb-1.5"
                                >
                                    <span
                                        class="font-bold text-rose-600 dark:text-rose-400"
                                        >Baris ke-{err.row}</span
                                    >
                                    <span
                                        class="font-semibold text-muted-foreground"
                                        >Kode: {err.code}</span
                                    >
                                </div>
                                <ul
                                    class="space-y-1 text-foreground/80 list-disc pl-4 text-[11px]"
                                >
                                    {#each err.messages as msg}
                                        <li>{msg}</li>
                                    {/each}
                                </ul>
                            </div>
                        {/each}
                    </div>
                </div>
            {/if}
        {/if}
    </div>
</div>
