<script module lang="ts">
    import { dashboard } from '@/routes';
    import { kpi as kpiIndex } from '@/routes/monitoring';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Capaian KPI',
                href: kpiIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import AlertCircle from 'lucide-svelte/icons/alert-circle';
    import CheckCircle2 from 'lucide-svelte/icons/check-circle-2';
    import Search from 'lucide-svelte/icons/search';
    import Target from 'lucide-svelte/icons/target';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import AppHead from '@/components/AppHead.svelte';
    import DonutChart from '@/components/charts/DonutChart.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import StatsCard from '@/components/StatsCard.svelte';

    let {
        stats,
        quarter_stats,
        unit_stats = [],
        indicators = [],
    }: {
        stats: {
            total_indicators: number;
            total_iku: number;
            total_ikk: number;
            achieved_count: number;
            in_progress_count: number;
            no_progress_count: number;
            average_achievement: number;
        };
        quarter_stats: Record<string, { total: number; average: number }>;
        unit_stats: Array<{
            unit_name: string;
            total: number;
            achieved: number;
            average: number;
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
            achievement: number;
            activity_name: string;
            unit_name: string;
        }>;
    } = $props();

    // Filters state
    let searchQuery = $state('');
    let selectedType = $state('all');
    let selectedQuarter = $state('all');
    let selectedStatus = $state('all');

    // Filtered indicators list
    const filteredIndicators = $derived(
        indicators.filter((ind) => {
            const matchesSearch =
                ind.code.toLowerCase().includes(searchQuery.toLowerCase()) ||
                ind.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                ind.activity_name
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase());

            const matchesType =
                selectedType === 'all' || ind.indicator_type === selectedType;

            const matchesQuarter =
                selectedQuarter === 'all' || ind.quarter === selectedQuarter;

            let matchesStatus = true;

            if (selectedStatus === 'achieved') {
                matchesStatus =
                    ind.actual_value !== null &&
                    ind.actual_value >= ind.target_value;
            } else if (selectedStatus === 'in_progress') {
                matchesStatus =
                    ind.actual_value !== null &&
                    ind.actual_value > 0 &&
                    ind.actual_value < ind.target_value;
            } else if (selectedStatus === 'no_progress') {
                matchesStatus =
                    ind.actual_value === null || ind.actual_value === 0;
            }

            return (
                matchesSearch && matchesType && matchesQuarter && matchesStatus
            );
        }),
    );
</script>

<AppHead title="Capaian KPI & Monitoring" />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <!-- Header -->
    <PageHeader
        title="Dashboard Capaian KPI"
        description="Monitoring capaian Indikator Kinerja Utama (IKU) & Indikator Kinerja Kegiatan (IKK)"
    />

    <!-- Stats Grid -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatsCard
            title="Rata-rata Capaian"
            value={`${stats.average_achievement}%`}
            icon={TrendingUp}
            trendType={stats.average_achievement >= 80 ? 'up' : 'neutral'}
            description="Persentase pemenuhan target KPI"
        />
        <StatsCard
            title="Total Indikator Kinerja"
            value={stats.total_indicators}
            icon={Target}
            description={`${stats.total_iku} IKU • ${stats.total_ikk} IKK`}
        />
        <StatsCard
            title="Target Tercapai"
            value={stats.achieved_count}
            icon={CheckCircle2}
            trendType="up"
            trend={stats.total_indicators > 0
                ? `${Math.round((stats.achieved_count / stats.total_indicators) * 100)}%`
                : '0%'}
            description="Indikator memenuhi target"
        />
        <StatsCard
            title="Belum Memenuhi Target"
            value={stats.in_progress_count + stats.no_progress_count}
            icon={AlertCircle}
            trendType="down"
            trend={stats.total_indicators > 0
                ? `${Math.round(((stats.in_progress_count + stats.no_progress_count) / stats.total_indicators) * 100)}%`
                : '0%'}
            description="Sedang berjalan / belum mulai"
        />
    </div>

    <!-- Visual Charts & Progress Breakdown Section -->
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- 1. Donut Chart Status -->
        <DonutChart
            title="Breakdown Status Target"
            data={[
                {
                    label: 'Tercapai',
                    value: stats.achieved_count,
                    color: '#10b981',
                },
                {
                    label: 'Dalam Proses',
                    value: stats.in_progress_count,
                    color: '#f59e0b',
                },
                {
                    label: 'Belum Ada Progres',
                    value: stats.no_progress_count,
                    color: '#ef4444',
                },
            ]}
        />

        <!-- 2. Quarterly Progress Breakdown (Quarterly reporting visual) -->
        <div
            class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm flex flex-col justify-between"
        >
            <h3 class="text-base font-semibold text-foreground mb-4">
                Capaian Target Per Periode
            </h3>

            <div class="space-y-4 flex-1 flex flex-col justify-center">
                {#each Object.entries(quarter_stats) as [period, pStats]}
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-foreground">
                                {#if period === 'annual'}
                                    Tahunan (Annual)
                                {:else}
                                    Triwulan {period.replace('Q', '')}
                                {/if}
                            </span>
                            <span class="text-muted-foreground font-semibold">
                                {pStats.average}% ({pStats.total} Indikator)
                            </span>
                        </div>
                        <div
                            class="h-2 w-full bg-zinc-200/50 dark:bg-zinc-800/50 rounded-full overflow-hidden border border-sidebar-border/20"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-500
                                    {pStats.average >= 90
                                    ? 'bg-emerald-500'
                                    : ''}
                                    {pStats.average >= 50 && pStats.average < 90
                                    ? 'bg-amber-500'
                                    : ''}
                                    {pStats.average < 50 ? 'bg-rose-500' : ''}
                                "
                                style="width: {pStats.average}%"
                            ></div>
                        </div>
                    </div>
                {/each}
            </div>
        </div>

        <!-- 3. Unit Performance Ranking -->
        <div
            class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm flex flex-col"
        >
            <h3 class="text-base font-semibold text-foreground mb-4">
                Peringkat Capaian Unit Kerja
            </h3>

            {#if unit_stats.length === 0}
                <div
                    class="flex-1 flex items-center justify-center text-sm text-muted-foreground italic"
                >
                    Belum ada data unit kerja.
                </div>
            {:else}
                <div
                    class="flex-1 space-y-4 overflow-y-auto max-h-[250px] pr-1"
                >
                    {#each unit_stats as unit, idx}
                        <div
                            class="flex flex-col gap-1 border-b border-sidebar-border/30 pb-2 last:border-0 last:pb-0"
                        >
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span
                                    class="font-bold text-foreground line-clamp-1"
                                >
                                    {idx + 1}. {unit.unit_name}
                                </span>
                                <span
                                    class="font-semibold text-primary shrink-0"
                                >
                                    {unit.average}%
                                </span>
                            </div>
                            <div
                                class="flex items-center justify-between text-[10px] text-muted-foreground"
                            >
                                <span
                                    >{unit.achieved} dari {unit.total} target tercapai</span
                                >
                            </div>
                            <div
                                class="h-1.5 w-full bg-zinc-200/50 dark:bg-zinc-800/50 rounded-full overflow-hidden mt-0.5"
                            >
                                <div
                                    class="h-full bg-primary rounded-full"
                                    style="width: {unit.average}%"
                                ></div>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>

    <!-- Data Table & Filters -->
    <div
        class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-4"
    >
        <!-- Table Header & Filter Row -->
        <div
            class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between pb-2"
        >
            <h3 class="text-lg font-bold text-foreground">
                Daftar Detail Indikator Kinerja
            </h3>

            <!-- Search & Filters Container -->
            <div
                class="grid gap-3 sm:grid-cols-2 md:grid-cols-4 w-full lg:w-auto"
            >
                <!-- Search bar -->
                <div class="relative w-full min-w-[200px]">
                    <Search
                        class="absolute left-2.5 top-2.5 size-4 text-muted-foreground"
                    />
                    <input
                        type="text"
                        placeholder="Cari indikator / kegiatan..."
                        bind:value={searchQuery}
                        class="w-full pl-9 pr-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    />
                </div>

                <!-- Type filter -->
                <select
                    bind:value={selectedType}
                    class="px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary cursor-pointer transition-all w-full"
                >
                    <option value="all">Semua Tipe</option>
                    <option value="iku">IKU (Utama)</option>
                    <option value="ikk">IKK (Kegiatan)</option>
                </select>

                <!-- Quarter filter -->
                <select
                    bind:value={selectedQuarter}
                    class="px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary cursor-pointer transition-all w-full"
                >
                    <option value="all">Semua Periode</option>
                    <option value="annual">Tahunan</option>
                    <option value="Q1">Triwulan 1 (Q1)</option>
                    <option value="Q2">Triwulan 2 (Q2)</option>
                    <option value="Q3">Triwulan 3 (Q3)</option>
                    <option value="Q4">Triwulan 4 (Q4)</option>
                </select>

                <!-- Status filter -->
                <select
                    bind:value={selectedStatus}
                    class="px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary cursor-pointer transition-all w-full"
                >
                    <option value="all">Semua Status</option>
                    <option value="achieved">Tercapai (>= Target)</option>
                    <option value="in_progress">Dalam Proses (>0)</option>
                    <option value="no_progress">Belum Ada Progres (=0)</option>
                </select>
            </div>
        </div>

        <!-- Table Display -->
        {#if filteredIndicators.length === 0}
            <div
                class="text-center py-12 text-muted-foreground/60 italic text-sm"
            >
                Tidak ada data indikator kinerja yang sesuai dengan filter.
            </div>
        {:else}
            <div
                class="overflow-x-auto border border-sidebar-border/30 rounded-lg"
            >
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="bg-zinc-100/50 dark:bg-zinc-900/50 border-b border-sidebar-border/30 font-bold text-muted-foreground uppercase tracking-wider"
                        >
                            <th class="p-3">Kode</th>
                            <th class="p-3">Tipe</th>
                            <th class="p-3">Indikator Kinerja</th>
                            <th class="p-3">Unit Kerja</th>
                            <th class="p-3">Periode</th>
                            <th class="p-3 text-right">Target</th>
                            <th class="p-3 text-right">Realisasi</th>
                            <th class="p-3 text-center">Capaian (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/20">
                        {#each filteredIndicators as item}
                            <tr
                                class="hover:bg-zinc-50/30 dark:hover:bg-zinc-900/10 transition-colors"
                            >
                                <td
                                    class="p-3 font-semibold text-foreground whitespace-nowrap"
                                >
                                    {item.code}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span
                                        class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase
                                            {item.indicator_type === 'iku'
                                            ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400'
                                            : ''}
                                            {item.indicator_type === 'ikk'
                                            ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
                                            : ''}
                                        "
                                    >
                                        {item.indicator_type.toUpperCase()}
                                    </span>
                                </td>
                                <td class="p-3 max-w-xs md:max-w-md">
                                    <div
                                        class="font-bold text-foreground line-clamp-2"
                                    >
                                        {item.name}
                                    </div>
                                    <div
                                        class="text-[10px] text-muted-foreground mt-0.5 truncate"
                                    >
                                        Kegiatan: {item.activity_name}
                                    </div>
                                </td>
                                <td
                                    class="p-3 text-muted-foreground whitespace-nowrap"
                                >
                                    {item.unit_name}
                                </td>
                                <td
                                    class="p-3 font-medium text-foreground whitespace-nowrap"
                                >
                                    {#if item.quarter === 'annual'}
                                        Tahunan
                                    {:else}
                                        Triwulan {item.quarter.replace('Q', '')}
                                    {/if}
                                </td>
                                <td
                                    class="p-3 font-semibold text-foreground text-right whitespace-nowrap"
                                >
                                    {item.target_value}
                                    {item.unit_of_measure}
                                </td>
                                <td
                                    class="p-3 font-semibold text-right whitespace-nowrap
                                    {item.actual_value !== null &&
                                    item.actual_value >= item.target_value
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : ''}
                                    {item.actual_value !== null &&
                                    item.actual_value > 0 &&
                                    item.actual_value < item.target_value
                                        ? 'text-amber-500'
                                        : ''}
                                    {item.actual_value === null ||
                                    item.actual_value === 0
                                        ? 'text-rose-500'
                                        : ''}
                                "
                                >
                                    {item.actual_value !== null
                                        ? item.actual_value
                                        : '—'}
                                    {item.unit_of_measure}
                                </td>
                                <td class="p-3">
                                    <div
                                        class="flex flex-col items-center gap-1 min-w-[80px]"
                                    >
                                        <span class="font-bold text-foreground"
                                            >{item.achievement}%</span
                                        >
                                        <div
                                            class="h-1.5 w-full bg-zinc-200/50 dark:bg-zinc-800/50 rounded-full overflow-hidden"
                                        >
                                            <div
                                                class="h-full rounded-full transition-all duration-300
                                                    {item.achievement >= 100
                                                    ? 'bg-emerald-500'
                                                    : ''}
                                                    {item.achievement > 0 &&
                                                item.achievement < 100
                                                    ? 'bg-amber-500'
                                                    : ''}
                                                    {item.achievement === 0
                                                    ? 'bg-rose-500'
                                                    : ''}
                                                "
                                                style="width: {Math.min(
                                                    100,
                                                    item.achievement,
                                                )}%"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>
</div>
