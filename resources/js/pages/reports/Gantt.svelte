<script module lang="ts">
    import { dashboard } from '@/routes';
    import { gantt as ganttIndex } from '@/routes/reports';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Gantt Chart Timeline',
                href: ganttIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import Info from 'lucide-svelte/icons/info';
    import Search from 'lucide-svelte/icons/search';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';

    let {
        activities = [],
        fiscalYears = [],
        units = [],
        filters = { fiscal_year_id: null, unit_id: null },
    }: {
        activities: Array<{
            id: number;
            code: string;
            name: string;
            status: string;
            start_date: string | null;
            end_date: string | null;
            progress_percentage: number;
            unit_name: string | null;
            sub_activities: Array<{
                id: number;
                name: string;
                status: string;
                start_date: string | null;
                end_date: string | null;
                progress_percentage: number;
                assigned_user_name: string | null;
            }>;
        }>;
        fiscalYears: Array<{ id: number; year: number; is_active: boolean }>;
        units: Array<{ id: number; name: string; code: string }>;
        filters: { fiscal_year_id: number | null; unit_id: number | null };
    } = $props();

    let selectedYearId = $state(
        filters.fiscal_year_id ||
            fiscalYears.find((f) => f.is_active)?.id ||
            fiscalYears[0]?.id,
    );
    let selectedUnitId = $state(filters.unit_id || '');
    let searchQuery = $state('');
    let expandedActivities = $state<Record<number, boolean>>({});

    const selectedYear = $derived(
        fiscalYears.find((f) => f.id === selectedYearId)?.year || 2026,
    );

    const filteredActivities = $derived(
        activities.filter((act) => {
            const matchesSearch =
                act.code.toLowerCase().includes(searchQuery.toLowerCase()) ||
                act.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                (act.unit_name &&
                    act.unit_name
                        .toLowerCase()
                        .includes(searchQuery.toLowerCase()));

            return matchesSearch;
        }),
    );

    const months = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Agt',
        'Sep',
        'Okt',
        'Nov',
        'Des',
    ];

    function toggleExpand(id: number) {
        expandedActivities[id] = !expandedActivities[id];
    }

    function applyFilters() {
        router.visit(ganttIndex().url, {
            data: {
                fiscal_year_id: selectedYearId,
                unit_id: selectedUnitId || undefined,
            },
            preserveState: true,
        });
    }

    // Mathematical projection of date range to percentage positions in the selected year
    function getTimelineCoords(
        startDateStr: string | null,
        endDateStr: string | null,
        year: number,
    ) {
        if (!startDateStr || !endDateStr) {
            return { left: 0, width: 0 };
        }

        const start = new Date(startDateStr);
        const end = new Date(endDateStr);
        const yearStart = new Date(year, 0, 1);
        const yearEnd = new Date(year, 11, 31, 23, 59, 59);

        // Clamp dates to the boundaries of the selected year
        const clampedStart = start < yearStart ? yearStart : start;
        const clampedEnd = end > yearEnd ? yearEnd : end;

        if (clampedStart > clampedEnd) {
            return { left: 0, width: 0 };
        }

        const totalMs = yearEnd.getTime() - yearStart.getTime();
        const leftMs = clampedStart.getTime() - yearStart.getTime();
        const durationMs = clampedEnd.getTime() - clampedStart.getTime();

        const left = Math.max(0, Math.min(100, (leftMs / totalMs) * 100));
        const width = Math.max(
            1,
            Math.min(100 - left, (durationMs / totalMs) * 100),
        );

        return { left, width };
    }

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'approved':
                return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400';
            case 'in_progress':
                return 'bg-sky-500/10 border-sky-500/20 text-sky-600 dark:text-sky-400';
            case 'completed':
                return 'bg-emerald-600/20 border-emerald-600/30 text-emerald-700 dark:text-emerald-300';
            case 'cancelled':
                return 'bg-rose-500/10 border-rose-500/20 text-rose-600 dark:text-rose-400';
            case 'proposed':
                return 'bg-amber-500/10 border-amber-500/20 text-amber-600 dark:text-amber-400';
            case 'draft':
            default:
                return 'bg-zinc-500/10 border-zinc-500/20 text-zinc-600 dark:text-zinc-400';
        }
    };

    const getStatusLabel = (status: string) => {
        switch (status) {
            case 'draft':
                return 'Draft';
            case 'proposed':
                return 'Ditinjau';
            case 'approved':
                return 'Disetujui';
            case 'in_progress':
                return 'Berjalan';
            case 'completed':
                return 'Selesai';
            case 'cancelled':
                return 'Batal';
            default:
                return status;
        }
    };
</script>

<AppHead title="Gantt Chart Kegiatan" />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <PageHeader
        title="Gantt Chart Timeline"
        description="Visualisasi jadwal pelaksanaan seluruh kegiatan pelayaran beserta sub-kegiatannya dalam setahun anggaran."
    />

    <!-- Filter Bar -->
    <div
        class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4"
    >
        <div class="flex flex-wrap items-center gap-3">
            <!-- Year select -->
            <div class="flex items-center gap-1.5">
                <span class="text-xs text-muted-foreground font-semibold"
                    >Tahun:</span
                >
                <select
                    bind:value={selectedYearId}
                    onchange={applyFilters}
                    class="px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary font-medium"
                >
                    {#each fiscalYears as fy}
                        <option value={fy.id}
                            >{fy.year} {fy.is_active ? '(Aktif)' : ''}</option
                        >
                    {/each}
                </select>
            </div>

            <!-- Unit select -->
            <div class="flex items-center gap-1.5">
                <span class="text-xs text-muted-foreground font-semibold"
                    >Unit:</span
                >
                <select
                    bind:value={selectedUnitId}
                    onchange={applyFilters}
                    class="px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary font-medium max-w-[200px]"
                >
                    <option value="">Semua Unit Kerja</option>
                    {#each units as unit}
                        <option value={unit.id}
                            >[{unit.code}] {unit.name}</option
                        >
                    {/each}
                </select>
            </div>
        </div>

        <!-- Search input -->
        <div class="relative w-full md:max-w-xs">
            <Search
                class="absolute left-2.5 top-2.5 size-4 text-muted-foreground"
            />
            <input
                type="text"
                placeholder="Cari kegiatan..."
                bind:value={searchQuery}
                class="w-full pl-9 pr-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
            />
        </div>
    </div>

    <!-- Timeline Wrapper -->
    <div
        class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md shadow-sm overflow-hidden flex flex-col"
    >
        <!-- Gantt Viewport Container -->
        <div class="overflow-x-auto min-w-full">
            <div class="min-w-[900px] flex flex-col">
                <!-- Timeline Header (Months) -->
                <div
                    class="flex border-b border-sidebar-border/40 bg-zinc-50/50 dark:bg-zinc-950/30"
                >
                    <!-- Left Column Header Space -->
                    <div
                        class="w-[300px] shrink-0 p-3 text-xs font-bold text-muted-foreground border-r border-sidebar-border/30"
                    >
                        Daftar Kegiatan ({filteredActivities.length})
                    </div>
                    <!-- Right Timeline Months Header -->
                    <div class="flex-1 flex relative">
                        {#each months as month}
                            <div
                                class="flex-1 text-center p-3 text-xs font-bold text-muted-foreground border-r border-sidebar-border/30 last:border-r-0"
                            >
                                {month}
                            </div>
                        {/each}
                    </div>
                </div>

                <!-- Timeline Body -->
                {#if filteredActivities.length === 0}
                    <div
                        class="p-12 text-center text-muted-foreground/60 italic text-sm"
                    >
                        Tidak ada data kegiatan yang dijadwalkan pada rentang
                        filter ini.
                    </div>
                {:else}
                    <div class="divide-y divide-sidebar-border/20">
                        {#each filteredActivities as activity (activity.id)}
                            {@const coords = getTimelineCoords(
                                activity.start_date,
                                activity.end_date,
                                selectedYear,
                            )}

                            <!-- Main Activity Row -->
                            <div
                                class="flex hover:bg-zinc-50/20 dark:hover:bg-zinc-900/5 transition-colors"
                            >
                                <!-- Left Sidebar Activity Title -->
                                <div
                                    class="w-[300px] shrink-0 p-3 border-r border-sidebar-border/30 flex gap-2 items-start"
                                >
                                    <button
                                        onclick={() =>
                                            toggleExpand(activity.id)}
                                        class="mt-0.5 text-muted-foreground hover:text-foreground cursor-pointer shrink-0"
                                        disabled={activity.sub_activities
                                            .length === 0}
                                    >
                                        {#if activity.sub_activities.length > 0}
                                            {#if expandedActivities[activity.id]}
                                                <ChevronDown class="size-4" />
                                            {:else}
                                                <ChevronRight class="size-4" />
                                            {/if}
                                        {:else}
                                            <div class="size-4"></div>
                                        {/if}
                                    </button>
                                    <div class="space-y-1">
                                        <div
                                            class="flex flex-wrap items-center gap-1.5"
                                        >
                                            <span
                                                class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded border {getStatusColor(
                                                    activity.status,
                                                )}"
                                            >
                                                {getStatusLabel(
                                                    activity.status,
                                                )}
                                            </span>
                                            {#if activity.unit_name}
                                                <span
                                                    class="text-[9px] font-semibold text-muted-foreground bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded"
                                                >
                                                    {activity.unit_name}
                                                </span>
                                            {/if}
                                        </div>
                                        <h4
                                            class="text-xs font-bold text-foreground line-clamp-2"
                                        >
                                            [{activity.code}] {activity.name}
                                        </h4>
                                        <div
                                            class="text-[10px] text-muted-foreground"
                                        >
                                            Jadwal: {activity.start_date} s/d {activity.end_date}
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Timeline Bar Grid -->
                                <div
                                    class="flex-1 flex relative items-center py-4 bg-zinc-50/10 dark:bg-zinc-950/10"
                                >
                                    <!-- Background Month Vertical Grids -->
                                    <div
                                        class="absolute inset-0 flex pointer-events-none"
                                    >
                                        {#each months as _}
                                            <div
                                                class="flex-1 border-r border-sidebar-border/10 last:border-r-0 h-full"
                                            ></div>
                                        {/each}
                                    </div>

                                    <!-- Activity Timeline Bar -->
                                    {#if coords.width > 0}
                                        <div
                                            class="absolute h-8 rounded-lg bg-sky-500/10 dark:bg-sky-500/20 border border-sky-500/30 shadow-sm transition-all duration-300 group hover:border-sky-500 cursor-help"
                                            style="left: {coords.left}%; width: {coords.width}%;"
                                        >
                                            <!-- Progress shading -->
                                            <div
                                                class="h-full bg-sky-500/20 dark:bg-sky-500/40 rounded-l-lg transition-all duration-300"
                                                style="width: {activity.progress_percentage}%"
                                            ></div>

                                            <!-- Info indicator inside bar -->
                                            <span
                                                class="absolute inset-0 flex items-center justify-between px-3 text-[10px] font-bold text-sky-700 dark:text-sky-300 pointer-events-none truncate"
                                            >
                                                <span
                                                    >{activity.progress_percentage}%</span
                                                >
                                                <span
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity"
                                                >
                                                    <Info
                                                        class="size-3 inline-block"
                                                    />
                                                </span>
                                            </span>

                                            <!-- Tooltip -->
                                            <div
                                                class="absolute z-10 hidden group-hover:block bottom-10 left-1/2 -translate-x-1/2 w-64 bg-card border border-sidebar-border p-3 rounded-lg shadow-lg space-y-2 pointer-events-none text-xs"
                                            >
                                                <p
                                                    class="font-bold text-foreground"
                                                >
                                                    [{activity.code}] {activity.name}
                                                </p>
                                                <p
                                                    class="text-muted-foreground"
                                                >
                                                    Unit: <span
                                                        class="font-semibold text-foreground"
                                                        >{activity.unit_name}</span
                                                    >
                                                </p>
                                                <p
                                                    class="text-muted-foreground"
                                                >
                                                    Mulai: <span
                                                        class="font-semibold text-foreground"
                                                        >{activity.start_date}</span
                                                    >
                                                </p>
                                                <p
                                                    class="text-muted-foreground"
                                                >
                                                    Selesai: <span
                                                        class="font-semibold text-foreground"
                                                        >{activity.end_date}</span
                                                    >
                                                </p>
                                                <p
                                                    class="text-muted-foreground"
                                                >
                                                    Progres: <span
                                                        class="font-bold text-primary"
                                                        >{activity.progress_percentage}%</span
                                                    >
                                                </p>
                                            </div>
                                        </div>
                                    {:else}
                                        <span
                                            class="text-[10px] text-rose-500 italic pl-3"
                                            >Jadwal diluar rentang {selectedYear}</span
                                        >
                                    {/if}
                                </div>
                            </div>

                            <!-- Expanded Sub-activities timeline rows -->
                            {#if expandedActivities[activity.id] && activity.sub_activities.length > 0}
                                {#each activity.sub_activities as sub (sub.id)}
                                    {@const subCoords = getTimelineCoords(
                                        sub.start_date,
                                        sub.end_date,
                                        selectedYear,
                                    )}
                                    <div
                                        class="flex bg-zinc-50/20 dark:bg-zinc-950/20 border-b border-sidebar-border/10"
                                    >
                                        <!-- Left Sidebar Subtitle -->
                                        <div
                                            class="w-[300px] shrink-0 pl-10 pr-3 py-2 border-r border-sidebar-border/30 flex items-start"
                                        >
                                            <div class="space-y-0.5">
                                                <h5
                                                    class="text-[11px] font-bold text-foreground/80 line-clamp-1"
                                                >
                                                    {sub.name}
                                                </h5>
                                                {#if sub.assigned_user_name}
                                                    <div
                                                        class="text-[9px] text-muted-foreground"
                                                    >
                                                        PJ: {sub.assigned_user_name}
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                        <!-- Right Timeline Sub Bar -->
                                        <div
                                            class="flex-1 flex relative items-center py-2"
                                        >
                                            <!-- Vertical grid lines -->
                                            <div
                                                class="absolute inset-0 flex pointer-events-none"
                                            >
                                                {#each months as _}
                                                    <div
                                                        class="flex-1 border-r border-sidebar-border/10 last:border-r-0 h-full"
                                                    ></div>
                                                {/each}
                                            </div>

                                            <!-- Sub-activity Bar -->
                                            {#if subCoords.width > 0}
                                                <div
                                                    class="absolute h-5 rounded bg-zinc-400/10 dark:bg-zinc-400/20 border border-zinc-400/30 group hover:border-zinc-400 cursor-help"
                                                    style="left: {subCoords.left}%; width: {subCoords.width}%;"
                                                >
                                                    <div
                                                        class="h-full bg-emerald-500/20 rounded-l transition-all duration-300"
                                                        style="width: {sub.progress_percentage}%"
                                                    ></div>
                                                    <span
                                                        class="absolute inset-0 flex items-center justify-between px-2 text-[8px] font-bold text-foreground/75 pointer-events-none truncate"
                                                    >
                                                        <span
                                                            >{sub.progress_percentage}%</span
                                                        >
                                                    </span>

                                                    <!-- Tooltip -->
                                                    <div
                                                        class="absolute z-10 hidden group-hover:block bottom-7 left-1/2 -translate-x-1/2 w-56 bg-card border border-sidebar-border p-2 rounded shadow-md space-y-1 pointer-events-none text-[10px]"
                                                    >
                                                        <p
                                                            class="font-bold text-foreground"
                                                        >
                                                            {sub.name}
                                                        </p>
                                                        <p
                                                            class="text-muted-foreground"
                                                        >
                                                            PJ: <span
                                                                class="font-semibold text-foreground"
                                                                >{sub.assigned_user_name ||
                                                                    '-'}</span
                                                            >
                                                        </p>
                                                        <p
                                                            class="text-muted-foreground"
                                                        >
                                                            Mulai: <span
                                                                class="font-semibold text-foreground"
                                                                >{sub.start_date}</span
                                                            >
                                                        </p>
                                                        <p
                                                            class="text-muted-foreground"
                                                        >
                                                            Selesai: <span
                                                                class="font-semibold text-foreground"
                                                                >{sub.end_date}</span
                                                            >
                                                        </p>
                                                        <p
                                                            class="text-muted-foreground"
                                                        >
                                                            Progres: <span
                                                                class="font-bold text-emerald-600"
                                                                >{sub.progress_percentage}%</span
                                                            >
                                                        </p>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                {/each}
                            {/if}
                        {/each}
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>
