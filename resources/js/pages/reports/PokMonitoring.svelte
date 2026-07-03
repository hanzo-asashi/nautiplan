<script module lang="ts">
    import { dashboard } from '@/routes';
    import { pokMonitoring as pokMonitoringRoute } from '@/routes/reports';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Evaluasi POK',
                href: pokMonitoringRoute(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import FileSpreadsheet from 'lucide-svelte/icons/file-spreadsheet';
    import FileText from 'lucide-svelte/icons/file-text';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { formatRupiah } from '@/lib/utils';
    import {
        pokRealization as exportPokRealization,
        rekapOutput,
        rekapKomponen,
    } from '@/routes/reports/export';

    let {
        tree = [],
        fiscalYears = [],
        filters = { fiscal_year_id: null },
    }: {
        tree: Array<any>;
        fiscalYears: Array<{ id: number; year: number; is_active: boolean }>;
        filters: { fiscal_year_id: number | null };
    } = $props();

    let expanded = $state<Record<string, boolean>>({});

    function toggle(key: string) {
        expanded[key] = !expanded[key];
    }

    function handleYearChange(event: Event) {
        const target = event.target as HTMLSelectElement;
        router.get(
            pokMonitoringRoute.url({
                query: { fiscal_year_id: target.value },
            }),
        );
    }

    function handleExportExcel() {
        const url = exportPokRealization.url({
            query: { fiscal_year_id: filters.fiscal_year_id },
        });
        window.open(url, '_blank');
    }

    function handleExportRekapOutput() {
        const url = rekapOutput.url({
            query: { fiscal_year_id: filters.fiscal_year_id },
        });
        window.open(url, '_blank');
    }

    function handleExportRekapKomponen() {
        const url = rekapKomponen.url({
            query: { fiscal_year_id: filters.fiscal_year_id },
        });
        window.open(url, '_blank');
    }

    function getPercentage(realisasi: number, pagu: number): number {
        if (!pagu) {
            return 0;
        }

        return Math.min(100, Math.round((realisasi / pagu) * 100));
    }

    function getProgressColor(percent: number): string {
        if (percent < 40) {
            return 'bg-rose-500';
        }

        if (percent < 80) {
            return 'bg-amber-500';
        }

        return 'bg-emerald-500';
    }

    function getBgColor(type: string): string {
        switch (type) {
            case 'program':
                return 'bg-zinc-100 dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800';
            case 'activity':
                return 'bg-white dark:bg-zinc-950/40 border-zinc-100 dark:border-zinc-800/80';
            case 'output':
                return 'bg-zinc-50/50 dark:bg-zinc-950/20 border-zinc-100 dark:border-zinc-900';
            case 'sub_output':
                return 'bg-zinc-50/20 dark:bg-zinc-950/10 border-zinc-100/50 dark:border-zinc-900/50';
            case 'component':
                return 'bg-white dark:bg-background border-zinc-100 dark:border-zinc-900';
            case 'sub_component':
                return 'bg-zinc-50/60 dark:bg-zinc-900/20 border-zinc-100 dark:border-zinc-800';
            default:
                return 'bg-white dark:bg-background border-transparent';
        }
    }
</script>

<AppHead title="Evaluasi POK" />

<div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
    >
        <PageHeader
            title="Evaluasi Realisasi DIPA / POK"
            description="Pantau penyerapan pagu anggaran belanja secara hirarkis dan unduh rekapitulasi data realisasi."
        />

        <div class="flex flex-wrap items-center gap-3">
            <select
                value={filters.fiscal_year_id || ''}
                onchange={handleYearChange}
                class="px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer font-medium"
            >
                {#each fiscalYears as fy}
                    <option value={fy.id.toString()}
                        >Tahun {fy.year} {fy.is_active ? '(Aktif)' : ''}</option
                    >
                {/each}
            </select>

            <button
                onclick={handleExportExcel}
                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors cursor-pointer"
            >
                <FileSpreadsheet class="w-3.5 h-3.5" />
                Ekspor Excel
            </button>

            <button
                onclick={handleExportRekapOutput}
                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors cursor-pointer"
            >
                <FileText class="w-3.5 h-3.5" />
                Rekap Output (PDF)
            </button>

            <button
                onclick={handleExportRekapKomponen}
                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer"
            >
                <FileText class="w-3.5 h-3.5" />
                Rekap Komponen (PDF)
            </button>
        </div>
    </div>

    <!-- Tree View Container -->
    <div
        class="bg-card text-card-foreground border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden"
    >
        <div
            class="p-4 bg-zinc-50/80 dark:bg-zinc-900/50 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center text-xs font-bold text-muted-foreground uppercase tracking-wider"
        >
            <span>Struktur Hirarki DIPA & MAK</span>
            <div class="flex items-center gap-16 mr-4">
                <span class="w-28 text-right">Pagu</span>
                <span class="w-28 text-right">Realisasi</span>
                <span class="w-28 text-right font-semibold">Sisa</span>
                <span class="w-16 text-center">% Realisasi</span>
            </div>
        </div>

        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
            {#if tree.length === 0}
                <div class="p-8 text-center text-xs text-muted-foreground">
                    Tidak ada data struktur POK untuk tahun anggaran terpilih.
                </div>
            {:else}
                {#each tree as prog (prog.id)}
                    {@const progKey = `prog-${prog.id}`}
                    {@const progOpen = expanded[progKey] ?? false}
                    {@const progPercent = getPercentage(
                        prog.realisasi,
                        prog.pagu,
                    )}

                    <!-- PROGRAM ROW -->
                    <div
                        class={`border-l-4 border-l-primary flex flex-col transition-all ${getBgColor('program')}`}
                    >
                        <div
                            class="flex items-center justify-between p-3.5 hover:bg-zinc-200/40 dark:hover:bg-zinc-800/40 cursor-pointer"
                            onclick={() => toggle(progKey)}
                        >
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                {#if progOpen}
                                    <ChevronDown
                                        class="w-4 h-4 text-muted-foreground shrink-0"
                                    />
                                {:else}
                                    <ChevronRight
                                        class="w-4 h-4 text-muted-foreground shrink-0"
                                    />
                                {/if}
                                <span
                                    class="px-2 py-0.5 text-[10px] font-extrabold bg-primary/10 text-primary rounded shrink-0 uppercase"
                                    >Program</span
                                >
                                <span
                                    class="font-bold text-xs shrink-0 font-mono text-muted-foreground"
                                    >{prog.code}</span
                                >
                                <span
                                    class="font-semibold text-xs truncate text-foreground"
                                    >{prog.name}</span
                                >
                            </div>
                            <div
                                class="flex items-center gap-16 text-xs mr-4 shrink-0 font-mono"
                            >
                                <span
                                    class="w-28 text-right text-foreground font-semibold"
                                    >{formatRupiah(prog.pagu, false)}</span
                                >
                                <span
                                    class="w-28 text-right text-emerald-600 dark:text-emerald-400 font-semibold"
                                    >{formatRupiah(prog.realisasi, false)}</span
                                >
                                <span
                                    class="w-28 text-right text-rose-600 dark:text-rose-450 font-bold"
                                    >{formatRupiah(prog.sisa, false)}</span
                                >
                                <div
                                    class="w-16 flex items-center justify-center gap-1.5"
                                >
                                    <span
                                        class="font-bold text-foreground text-center shrink-0 w-8"
                                        >{progPercent}%</span
                                    >
                                    <div
                                        class="w-6 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded overflow-hidden"
                                    >
                                        <div
                                            class={`h-full ${getProgressColor(progPercent)}`}
                                            style={`width: ${progPercent}%`}
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {#if progOpen}
                            <div
                                class="pl-4 divide-y divide-zinc-150 dark:divide-zinc-850 bg-zinc-50/30"
                            >
                                {#each prog.children as act (act.id)}
                                    {@const actKey = `act-${act.id}`}
                                    {@const actOpen = expanded[actKey] ?? false}
                                    {@const actPercent = getPercentage(
                                        act.realisasi,
                                        act.pagu,
                                    )}

                                    <!-- ACTIVITY ROW -->
                                    <div
                                        class={`flex flex-col ${getBgColor('activity')}`}
                                    >
                                        <div
                                            class="flex items-center justify-between p-3 hover:bg-zinc-100 dark:hover:bg-zinc-900/50 cursor-pointer"
                                            onclick={() => toggle(actKey)}
                                        >
                                            <div
                                                class="flex items-center gap-2 min-w-0 flex-1 pl-2"
                                            >
                                                {#if actOpen}
                                                    <ChevronDown
                                                        class="w-3.5 h-3.5 text-muted-foreground shrink-0"
                                                    />
                                                {:else}
                                                    <ChevronRight
                                                        class="w-3.5 h-3.5 text-muted-foreground shrink-0"
                                                    />
                                                {/if}
                                                <span
                                                    class="px-1.5 py-0.5 text-[9px] font-bold bg-indigo-500/10 text-indigo-500 rounded shrink-0 uppercase"
                                                    >Kegiatan</span
                                                >
                                                <span
                                                    class="font-bold text-xs shrink-0 font-mono text-muted-foreground"
                                                    >{act.code}</span
                                                >
                                                <span
                                                    class="font-semibold text-xs text-foreground truncate"
                                                    >{act.name}</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-16 text-xs mr-4 shrink-0 font-mono"
                                            >
                                                <span
                                                    class="w-28 text-right text-foreground"
                                                    >{formatRupiah(
                                                        act.pagu,
                                                        false,
                                                    )}</span
                                                >
                                                <span
                                                    class="w-28 text-right text-emerald-600 dark:text-emerald-400"
                                                    >{formatRupiah(
                                                        act.realisasi,
                                                        false,
                                                    )}</span
                                                >
                                                <span
                                                    class="w-28 text-right text-rose-600 dark:text-rose-450 font-bold"
                                                    >{formatRupiah(
                                                        act.sisa,
                                                        false,
                                                    )}</span
                                                >
                                                <div
                                                    class="w-16 flex items-center justify-center gap-1.5"
                                                >
                                                    <span
                                                        class="font-bold text-foreground text-center shrink-0 w-8"
                                                        >{actPercent}%</span
                                                    >
                                                    <div
                                                        class="w-6 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded overflow-hidden"
                                                    >
                                                        <div
                                                            class={`h-full ${getProgressColor(actPercent)}`}
                                                            style={`width: ${actPercent}%`}
                                                        ></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {#if actOpen}
                                            <div
                                                class="pl-6 divide-y divide-zinc-150/60 dark:divide-zinc-850/60 bg-zinc-50/10"
                                            >
                                                {#each act.children as out (out.id)}
                                                    {@const outKey = `out-${out.id}`}
                                                    {@const outOpen =
                                                        expanded[outKey] ??
                                                        false}
                                                    {@const outPercent =
                                                        getPercentage(
                                                            out.realisasi,
                                                            out.pagu,
                                                        )}

                                                    <!-- OUTPUT ROW -->
                                                    <div
                                                        class={`flex flex-col ${getBgColor('output')}`}
                                                    >
                                                        <div
                                                            class="flex items-center justify-between p-2.5 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/40 cursor-pointer"
                                                            onclick={() =>
                                                                toggle(outKey)}
                                                        >
                                                            <div
                                                                class="flex items-center gap-2 min-w-0 flex-1 pl-2"
                                                            >
                                                                {#if outOpen}
                                                                    <ChevronDown
                                                                        class="w-3.5 h-3.5 text-muted-foreground shrink-0"
                                                                    />
                                                                {:else}
                                                                    <ChevronRight
                                                                        class="w-3.5 h-3.5 text-muted-foreground shrink-0"
                                                                    />
                                                                {/if}
                                                                <span
                                                                    class="px-1.5 py-0.5 text-[9px] font-semibold bg-sky-500/10 text-sky-600 dark:text-sky-400 rounded shrink-0 uppercase"
                                                                    >Output</span
                                                                >
                                                                <span
                                                                    class="font-bold text-xs shrink-0 font-mono text-muted-foreground"
                                                                    >{out.code}</span
                                                                >
                                                                <span
                                                                    class="text-xs text-foreground truncate"
                                                                    >{out.name}</span
                                                                >
                                                            </div>
                                                            <div
                                                                class="flex items-center gap-16 text-xs mr-4 shrink-0 font-mono"
                                                            >
                                                                <span
                                                                    class="w-28 text-right text-muted-foreground"
                                                                    >{formatRupiah(
                                                                        out.pagu,
                                                                        false,
                                                                    )}</span
                                                                >
                                                                <span
                                                                    class="w-28 text-right text-emerald-600 dark:text-emerald-500"
                                                                    >{formatRupiah(
                                                                        out.realisasi,
                                                                        false,
                                                                    )}</span
                                                                >
                                                                <span
                                                                    class="w-28 text-right text-rose-500 font-semibold"
                                                                    >{formatRupiah(
                                                                        out.sisa,
                                                                        false,
                                                                    )}</span
                                                                >
                                                                <div
                                                                    class="w-16 flex items-center justify-center gap-1.5"
                                                                >
                                                                    <span
                                                                        class="font-bold text-foreground text-center shrink-0 w-8"
                                                                        >{outPercent}%</span
                                                                    >
                                                                    <div
                                                                        class="w-6 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded overflow-hidden"
                                                                    >
                                                                        <div
                                                                            class={`h-full ${getProgressColor(outPercent)}`}
                                                                            style={`width: ${outPercent}%`}
                                                                        ></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {#if outOpen}
                                                            <div
                                                                class="pl-6 divide-y divide-zinc-150/40 dark:divide-zinc-850/40"
                                                            >
                                                                {#each out.children as subOut (subOut.id)}
                                                                    {@const subOutKey = `subout-${subOut.id}`}
                                                                    {@const subOutOpen =
                                                                        expanded[
                                                                            subOutKey
                                                                        ] ??
                                                                        false}
                                                                    {@const subOutPercent =
                                                                        getPercentage(
                                                                            subOut.realisasi,
                                                                            subOut.pagu,
                                                                        )}

                                                                    <!-- SUB OUTPUT ROW -->
                                                                    <div
                                                                        class={`flex flex-col ${getBgColor('sub_output')}`}
                                                                    >
                                                                        <div
                                                                            class="flex items-center justify-between p-2.5 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/30 cursor-pointer"
                                                                            onclick={() =>
                                                                                toggle(
                                                                                    subOutKey,
                                                                                )}
                                                                        >
                                                                            <div
                                                                                class="flex items-center gap-2 min-w-0 flex-1 pl-2"
                                                                            >
                                                                                {#if subOutOpen}
                                                                                    <ChevronDown
                                                                                        class="w-3 h-3 text-muted-foreground shrink-0"
                                                                                    />
                                                                                {:else}
                                                                                    <ChevronRight
                                                                                        class="w-3 h-3 text-muted-foreground shrink-0"
                                                                                    />
                                                                                {/if}
                                                                                <span
                                                                                    class="px-1 py-0.5 text-[8px] font-semibold bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded shrink-0 uppercase"
                                                                                    >Sub
                                                                                    Output</span
                                                                                >
                                                                                <span
                                                                                    class="font-bold text-xs shrink-0 font-mono text-muted-foreground"
                                                                                    >{subOut.code}</span
                                                                                >
                                                                                <span
                                                                                    class="text-xs text-foreground truncate"
                                                                                    >{subOut.name}</span
                                                                                >
                                                                            </div>
                                                                            <div
                                                                                class="flex items-center gap-16 text-xs mr-4 shrink-0 font-mono"
                                                                            >
                                                                                <span
                                                                                    class="w-28 text-right text-muted-foreground"
                                                                                    >{formatRupiah(
                                                                                        subOut.pagu,
                                                                                        false,
                                                                                    )}</span
                                                                                >
                                                                                <span
                                                                                    class="w-28 text-right text-emerald-600 dark:text-emerald-500"
                                                                                    >{formatRupiah(
                                                                                        subOut.realisasi,
                                                                                        false,
                                                                                    )}</span
                                                                                >
                                                                                <span
                                                                                    class="w-28 text-right text-rose-500 font-semibold"
                                                                                    >{formatRupiah(
                                                                                        subOut.sisa,
                                                                                        false,
                                                                                    )}</span
                                                                                >
                                                                                <div
                                                                                    class="w-16 flex items-center justify-center gap-1.5"
                                                                                >
                                                                                    <span
                                                                                        class="font-bold text-foreground text-center shrink-0 w-8"
                                                                                        >{subOutPercent}%</span
                                                                                    >
                                                                                    <div
                                                                                        class="w-6 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded overflow-hidden"
                                                                                    >
                                                                                        <div
                                                                                            class={`h-full ${getProgressColor(subOutPercent)}`}
                                                                                            style={`width: ${subOutPercent}%`}
                                                                                        ></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        {#if subOutOpen}
                                                                            <div
                                                                                class="pl-6 divide-y divide-zinc-100 dark:divide-zinc-900"
                                                                            >
                                                                                {#each subOut.children as comp (comp.id)}
                                                                                    {@const compKey = `comp-${comp.id}`}
                                                                                    {@const compOpen =
                                                                                        expanded[
                                                                                            compKey
                                                                                        ] ??
                                                                                        false}
                                                                                    {@const compPercent =
                                                                                        getPercentage(
                                                                                            comp.realisasi,
                                                                                            comp.pagu,
                                                                                        )}

                                                                                    <!-- COMPONENT ROW -->
                                                                                    <div
                                                                                        class={`flex flex-col ${getBgColor('component')}`}
                                                                                    >
                                                                                        <div
                                                                                            class="flex items-center justify-between p-2 hover:bg-zinc-100/40 dark:hover:bg-zinc-800/20 cursor-pointer"
                                                                                            onclick={() =>
                                                                                                toggle(
                                                                                                    compKey,
                                                                                                )}
                                                                                        >
                                                                                            <div
                                                                                                class="flex items-center gap-2 min-w-0 flex-1 pl-2"
                                                                                            >
                                                                                                {#if compOpen}
                                                                                                    <ChevronDown
                                                                                                        class="w-3.5 h-3.5 text-muted-foreground shrink-0"
                                                                                                    />
                                                                                                {:else}
                                                                                                    <ChevronRight
                                                                                                        class="w-3.5 h-3.5 text-muted-foreground shrink-0"
                                                                                                    />
                                                                                                {/if}
                                                                                                <span
                                                                                                    class="px-1 py-0.5 text-[8px] font-semibold bg-emerald-500/10 text-emerald-600 rounded shrink-0 uppercase"
                                                                                                    >Komponen</span
                                                                                                >
                                                                                                <span
                                                                                                    class="font-bold text-xs shrink-0 font-mono text-muted-foreground"
                                                                                                    >{comp.code}</span
                                                                                                >
                                                                                                <span
                                                                                                    class="text-xs text-foreground truncate"
                                                                                                    >{comp.name}</span
                                                                                                >
                                                                                            </div>
                                                                                            <div
                                                                                                class="flex items-center gap-16 text-xs mr-4 shrink-0 font-mono"
                                                                                            >
                                                                                                <span
                                                                                                    class="w-28 text-right text-muted-foreground"
                                                                                                    >{formatRupiah(
                                                                                                        comp.pagu,
                                                                                                        false,
                                                                                                    )}</span
                                                                                                >
                                                                                                <span
                                                                                                    class="w-28 text-right text-emerald-600 dark:text-emerald-500"
                                                                                                    >{formatRupiah(
                                                                                                        comp.realisasi,
                                                                                                        false,
                                                                                                    )}</span
                                                                                                >
                                                                                                <span
                                                                                                    class="w-28 text-right text-rose-500 font-semibold"
                                                                                                    >{formatRupiah(
                                                                                                        comp.sisa,
                                                                                                        false,
                                                                                                    )}</span
                                                                                                >
                                                                                                <div
                                                                                                    class="w-16 flex items-center justify-center gap-1.5"
                                                                                                >
                                                                                                    <span
                                                                                                        class="font-bold text-foreground text-center shrink-0 w-8"
                                                                                                        >{compPercent}%</span
                                                                                                    >
                                                                                                    <div
                                                                                                        class="w-6 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded overflow-hidden"
                                                                                                    >
                                                                                                        <div
                                                                                                            class={`h-full ${getProgressColor(compPercent)}`}
                                                                                                            style={`width: ${compPercent}%`}
                                                                                                        ></div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        {#if compOpen}
                                                                                            <div
                                                                                                class="pl-6 divide-y divide-zinc-100 dark:divide-zinc-900 bg-zinc-50/5"
                                                                                            >
                                                                                                {#each comp.children as subComp (subComp.id)}
                                                                                                    {@const subCompKey = `subcomp-${subComp.id}`}
                                                                                                    {@const subCompOpen =
                                                                                                        expanded[
                                                                                                            subCompKey
                                                                                                        ] ??
                                                                                                        false}
                                                                                                    {@const subCompPercent =
                                                                                                        getPercentage(
                                                                                                            subComp.realisasi,
                                                                                                            subComp.pagu,
                                                                                                        )}

                                                                                                    <!-- SUB COMPONENT ROW -->
                                                                                                    <div
                                                                                                        class={`flex flex-col ${getBgColor('sub_component')}`}
                                                                                                    >
                                                                                                        <div
                                                                                                            class="flex items-center justify-between p-2 hover:bg-zinc-100/30 dark:hover:bg-zinc-800/10 cursor-pointer"
                                                                                                            onclick={() =>
                                                                                                                toggle(
                                                                                                                    subCompKey,
                                                                                                                )}
                                                                                                        >
                                                                                                            <div
                                                                                                                class="flex items-center gap-2 min-w-0 flex-1 pl-2"
                                                                                                            >
                                                                                                                {#if subCompOpen}
                                                                                                                    <ChevronDown
                                                                                                                        class="w-3.5 h-3.5 text-muted-foreground shrink-0"
                                                                                                                    />
                                                                                                                {:else}
                                                                                                                    <ChevronRight
                                                                                                                        class="w-3.5 h-3.5 text-muted-foreground shrink-0"
                                                                                                                    />
                                                                                                                {/if}
                                                                                                                <span
                                                                                                                    class="px-1 py-0.5 text-[8px] font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded shrink-0 uppercase"
                                                                                                                    >Sub
                                                                                                                    Komp</span
                                                                                                                >
                                                                                                                <span
                                                                                                                    class="font-bold text-xs shrink-0 font-mono text-muted-foreground"
                                                                                                                    >{subComp.code}</span
                                                                                                                >
                                                                                                                <span
                                                                                                                    class="text-xs text-foreground truncate"
                                                                                                                    >{subComp.name}</span
                                                                                                                >
                                                                                                            </div>
                                                                                                            <div
                                                                                                                class="flex items-center gap-16 text-xs mr-4 shrink-0 font-mono"
                                                                                                            >
                                                                                                                <span
                                                                                                                    class="w-28 text-right text-muted-foreground"
                                                                                                                    >{formatRupiah(
                                                                                                                        subComp.pagu,
                                                                                                                        false,
                                                                                                                    )}</span
                                                                                                                >
                                                                                                                <span
                                                                                                                    class="w-28 text-right text-emerald-600 dark:text-emerald-500"
                                                                                                                    >{formatRupiah(
                                                                                                                        subComp.realisasi,
                                                                                                                        false,
                                                                                                                    )}</span
                                                                                                                >
                                                                                                                <span
                                                                                                                    class="w-28 text-right text-rose-500 font-semibold"
                                                                                                                    >{formatRupiah(
                                                                                                                        subComp.sisa,
                                                                                                                        false,
                                                                                                                    )}</span
                                                                                                                >
                                                                                                                <div
                                                                                                                    class="w-16 flex items-center justify-center gap-1.5"
                                                                                                                >
                                                                                                                    <span
                                                                                                                        class="font-bold text-foreground text-center shrink-0 w-8"
                                                                                                                        >{subCompPercent}%</span
                                                                                                                    >
                                                                                                                    <div
                                                                                                                        class="w-6 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded overflow-hidden"
                                                                                                                    >
                                                                                                                        <div
                                                                                                                            class={`h-full ${getProgressColor(subCompPercent)}`}
                                                                                                                            style={`width: ${subCompPercent}%`}
                                                                                                                        ></div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>

                                                                                                        {#if subCompOpen}
                                                                                                            <div
                                                                                                                class="pl-8 divide-y divide-zinc-100 dark:divide-zinc-900/60 bg-zinc-100/10 dark:bg-zinc-900/10"
                                                                                                            >
                                                                                                                {#each subComp.children as budget (budget.id)}
                                                                                                                    {@const budgetPercent =
                                                                                                                        getPercentage(
                                                                                                                            budget.realisasi,
                                                                                                                            budget.pagu,
                                                                                                                        )}

                                                                                                                    <!-- ACCOUNT BUDGET (AKUN) ROW -->
                                                                                                                    <div
                                                                                                                        class="flex items-center justify-between p-2 text-xs font-mono"
                                                                                                                    >
                                                                                                                        <div
                                                                                                                            class="flex items-center gap-2 min-w-0 flex-1"
                                                                                                                        >
                                                                                                                            <span
                                                                                                                                class="w-1.5 h-1.5 rounded-full bg-zinc-400 shrink-0"

                                                                                                                            ></span>
                                                                                                                            <span
                                                                                                                                class="px-1 py-0.5 text-[8.5px] font-bold bg-zinc-500/10 text-muted-foreground rounded shrink-0 uppercase font-sans"
                                                                                                                                >Akun</span
                                                                                                                            >
                                                                                                                            <span
                                                                                                                                class="font-bold text-muted-foreground shrink-0"
                                                                                                                                >{budget.code}</span
                                                                                                                            >
                                                                                                                            <span
                                                                                                                                class="text-muted-foreground truncate font-sans font-medium"
                                                                                                                                >{budget.name}</span
                                                                                                                            >
                                                                                                                        </div>
                                                                                                                        <div
                                                                                                                            class="flex items-center gap-16 text-xs mr-4 shrink-0 font-mono"
                                                                                                                        >
                                                                                                                            <span
                                                                                                                                class="w-28 text-right text-muted-foreground/80"
                                                                                                                                >{formatRupiah(
                                                                                                                                    budget.pagu,
                                                                                                                                    false,
                                                                                                                                )}</span
                                                                                                                            >
                                                                                                                            <span
                                                                                                                                class="w-28 text-right text-emerald-600/80 dark:text-emerald-500/80"
                                                                                                                                >{formatRupiah(
                                                                                                                                    budget.realisasi,
                                                                                                                                    false,
                                                                                                                                )}</span
                                                                                                                            >
                                                                                                                            <span
                                                                                                                                class="w-28 text-right text-rose-500/80"
                                                                                                                                >{formatRupiah(
                                                                                                                                    budget.sisa,
                                                                                                                                    false,
                                                                                                                                )}</span
                                                                                                                            >
                                                                                                                            <div
                                                                                                                                class="w-16 flex items-center justify-center gap-1.5"
                                                                                                                            >
                                                                                                                                <span
                                                                                                                                    class="font-bold text-muted-foreground/80 text-center shrink-0 w-8"
                                                                                                                                    >{budgetPercent}%</span
                                                                                                                                >
                                                                                                                                <div
                                                                                                                                    class="w-6 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded overflow-hidden"
                                                                                                                                >
                                                                                                                                    <div
                                                                                                                                        class={`h-full ${getProgressColor(budgetPercent)}`}
                                                                                                                                        style={`width: ${budgetPercent}%`}
                                                                                                                                    ></div>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                {/each}
                                                                                                            </div>
                                                                                                        {/if}
                                                                                                    </div>
                                                                                                {/each}
                                                                                            </div>
                                                                                        {/if}
                                                                                    </div>
                                                                                {/each}
                                                                            </div>
                                                                        {/if}
                                                                    </div>
                                                                {/each}
                                                            </div>
                                                        {/if}
                                                    </div>
                                                {/each}
                                            </div>
                                        {/if}
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>
                {/each}
            {/if}
        </div>
    </div>
</div>
