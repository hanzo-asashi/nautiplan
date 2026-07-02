<script module lang="ts">
    import { dashboard } from '@/routes';
    import { calendar as calendarRoute } from '@/routes/reports';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Kalender & Penjadwalan',
                href: calendarRoute(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import CalendarIcon from 'lucide-svelte/icons/calendar';
    import ChevronLeft from 'lucide-svelte/icons/chevron-left';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import Info from 'lucide-svelte/icons/info';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';

    let {
        activities = [],
        fiscalYears = [],
        units = [],
        filters = { fiscal_year_id: null, unit_id: null, status: null },
    }: {
        activities: Array<{
            id: number;
            code: string;
            name: string;
            status: string;
            priority: string;
            start_date: string;
            end_date: string;
            unit_name: string;
            progress_percentage: number;
            sub_activities: Array<{
                id: number;
                name: string;
                status: string;
                start_date: string | null;
                end_date: string | null;
                progress_percentage: number;
                assigned_user_name?: string | null;
            }>;
        }>;
        fiscalYears: Array<{ id: number; year: number; is_active: boolean }>;
        units: Array<{ id: number; name: string; code: string }>;
        filters: {
            fiscal_year_id: number | null;
            unit_id: number | null;
            status: string | null;
        };
    } = $props();

    // Get active/selected year
    const activeYear = $derived(
        fiscalYears.find((f) => f.id === filters.fiscal_year_id)?.year ||
            new Date().getFullYear(),
    );

    // Track active month (0 = January, 11 = December)
    let currentMonth = $state(new Date().getMonth());

    const monthNames = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
    ];

    const dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

    // Reactive calendar grid construction
    const calendarDays = $derived.by(() => {
        const days: Array<{
            dayNumber: number;
            date: Date;
            isCurrentMonth: boolean;
        }> = [];
        // First day of selected month/year. Day index: 0 = Sun, 1 = Mon, ..., 6 = Sat
        const firstDay = new Date(activeYear, currentMonth, 1);
        // Normalize first day to start with Monday (0 = Mon, 6 = Sun)
        let firstDayIndex = firstDay.getDay() - 1;

        if (firstDayIndex === -1) {
            firstDayIndex = 6;
        } // Sunday

        // Total days in selected month
        const totalDays = new Date(activeYear, currentMonth + 1, 0).getDate();

        // Previous month padding
        const prevMonthTotalDays = new Date(
            activeYear,
            currentMonth,
            0,
        ).getDate();

        for (let i = firstDayIndex - 1; i >= 0; i--) {
            const dayNum = prevMonthTotalDays - i;
            const date = new Date(
                currentMonth === 0 ? activeYear - 1 : activeYear,
                currentMonth === 0 ? 11 : currentMonth - 1,
                dayNum,
            );
            days.push({ dayNumber: dayNum, date, isCurrentMonth: false });
        }

        // Current month days
        for (let i = 1; i <= totalDays; i++) {
            const date = new Date(activeYear, currentMonth, i);
            days.push({ dayNumber: i, date, isCurrentMonth: true });
        }

        // Next month padding to fill grid to multiples of 7
        const totalCells = Math.ceil(days.length / 7) * 7;
        const nextMonthPadding = totalCells - days.length;

        for (let i = 1; i <= nextMonthPadding; i++) {
            const date = new Date(
                currentMonth === 11 ? activeYear + 1 : activeYear,
                currentMonth === 11 ? 0 : currentMonth + 1,
                i,
            );
            days.push({ dayNumber: i, date, isCurrentMonth: false });
        }

        return days;
    });

    // Track selected day for details (defaults to today's date if in current month)
    let selectedDate = $state<Date>(
        new Date(activeYear, currentMonth, new Date().getDate()),
    );

    // Flatten all events (activities and sub-activities) for mapping
    const events = $derived.by(() => {
        const list: Array<{
            id: number;
            type: 'activity' | 'sub_activity';
            code?: string;
            name: string;
            status: string;
            start: Date;
            end: Date;
            progress: number;
            unit?: string;
            assignee?: string;
        }> = [];

        activities.forEach((act) => {
            if (act.start_date && act.end_date) {
                list.push({
                    id: act.id,
                    type: 'activity',
                    code: act.code,
                    name: act.name,
                    status: act.status,
                    start: new Date(act.start_date),
                    end: new Date(act.end_date),
                    progress: act.progress_percentage,
                    unit: act.unit_name,
                });
            }

            act.sub_activities.forEach((sub) => {
                if (sub.start_date && sub.end_date) {
                    list.push({
                        id: sub.id,
                        type: 'sub_activity',
                        name: sub.name,
                        status: sub.status,
                        start: new Date(sub.start_date),
                        end: new Date(sub.end_date),
                        progress: sub.progress_percentage,
                        assignee: sub.assigned_user_name || undefined,
                        unit: act.unit_name,
                    });
                }
            });
        });

        return list;
    });

    // Get events for a specific day
    function getEventsForDate(date: Date) {
        // Reset time for safe comparison
        const checkDate = new Date(
            date.getFullYear(),
            date.getMonth(),
            date.getDate(),
        );

        return events.filter((event) => {
            const start = new Date(
                event.start.getFullYear(),
                event.start.getMonth(),
                event.start.getDate(),
            );
            const end = new Date(
                event.end.getFullYear(),
                event.end.getMonth(),
                event.end.getDate(),
            );

            return checkDate >= start && checkDate <= end;
        });
    }

    // Selected day's active events
    const selectedDayEvents = $derived(getEventsForDate(selectedDate));

    function selectDate(date: Date) {
        selectedDate = date;
    }

    function prevMonth() {
        if (currentMonth === 0) {
            currentMonth = 11;
            // Seek if previous year exists
            const prevYear = fiscalYears.find((f) => f.year === activeYear - 1);

            if (prevYear) {
                applyFilters({ fiscal_year_id: prevYear.id });
            }
        } else {
            currentMonth--;
        }
    }

    function nextMonth() {
        if (currentMonth === 11) {
            currentMonth = 0;
            // Seek if next year exists
            const nextYear = fiscalYears.find((f) => f.year === activeYear + 1);

            if (nextYear) {
                applyFilters({ fiscal_year_id: nextYear.id });
            }
        } else {
            currentMonth++;
        }
    }

    function applyFilters(newFilters: Partial<typeof filters>) {
        router.visit(calendarRoute().url, {
            data: { ...filters, ...newFilters },
            preserveState: true,
        });
    }
</script>

<AppHead title="Kalender & Penjadwalan" />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <PageHeader
        title="Kalender & Penjadwalan"
        description="Pantau linimasa jadwal pelaksanaan kegiatan utama serta sub-kegiatan operasional secara terintegrasi."
    />

    <!-- Filter Toolbar -->
    <div
        class="flex flex-wrap gap-4 items-center justify-between p-4 bg-card/40 border border-sidebar-border/30 rounded-xl backdrop-blur-md"
    >
        <div class="flex flex-wrap gap-3 items-center">
            <!-- Year -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-muted-foreground font-semibold"
                    >Tahun:</span
                >
                <select
                    value={filters.fiscal_year_id}
                    aria-label="Tahun Anggaran"
                    onchange={(e) =>
                        applyFilters({
                            fiscal_year_id: Number(
                                (e.target as HTMLSelectElement).value,
                            ),
                        })}
                    class="px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md font-semibold cursor-pointer outline-none"
                >
                    {#each fiscalYears as fy}
                        <option value={fy.id}>{fy.year}</option>
                    {/each}
                </select>
            </div>

            <!-- Unit -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-muted-foreground font-semibold"
                    >Unit:</span
                >
                <select
                    value={filters.unit_id || ''}
                    aria-label="Unit Kerja"
                    onchange={(e) =>
                        applyFilters({
                            unit_id: (e.target as HTMLSelectElement).value
                                ? Number((e.target as HTMLSelectElement).value)
                                : null,
                        })}
                    class="px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md font-semibold cursor-pointer outline-none max-w-[200px]"
                >
                    <option value="">Semua Unit</option>
                    {#each units as unit}
                        <option value={unit.id}>{unit.name}</option>
                    {/each}
                </select>
            </div>

            <!-- Status -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-muted-foreground font-semibold"
                    >Status:</span
                >
                <select
                    value={filters.status || ''}
                    aria-label="Status Kegiatan"
                    onchange={(e) =>
                        applyFilters({
                            status:
                                (e.target as HTMLSelectElement).value || null,
                        })}
                    class="px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md font-semibold cursor-pointer outline-none"
                >
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="proposed">Diusulkan</option>
                    <option value="approved">Disetujui</option>
                    <option value="in_progress">Berjalan</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Batal</option>
                </select>
            </div>
        </div>

        <!-- Month Navigation -->
        <div class="flex items-center gap-3">
            <button
                onclick={prevMonth}
                class="p-1.5 border border-zinc-200 dark:border-zinc-800 bg-background/50 hover:bg-zinc-100 rounded-md transition-colors cursor-pointer"
                aria-label="Bulan sebelumnya"
            >
                <ChevronLeft class="size-4" />
            </button>
            <span
                class="text-xs font-bold text-foreground min-w-[120px] text-center uppercase tracking-wide"
            >
                {monthNames[currentMonth]}
                {activeYear}
            </span>
            <button
                onclick={nextMonth}
                class="p-1.5 border border-zinc-200 dark:border-zinc-800 bg-background/50 hover:bg-zinc-100 rounded-md transition-colors cursor-pointer"
                aria-label="Bulan berikutnya"
            >
                <ChevronRight class="size-4" />
            </button>
        </div>
    </div>

    <!-- Calendar Layout Grid -->
    <div class="grid gap-6 lg:grid-cols-3 items-start flex-1">
        <!-- Calendar Grid (2 Cols) -->
        <div
            class="lg:col-span-2 rounded-xl border border-sidebar-border/30 bg-card/40 shadow-sm overflow-hidden backdrop-blur-md"
        >
            <!-- Day names -->
            <div
                class="grid grid-cols-7 border-b border-sidebar-border/30 bg-zinc-100/50 dark:bg-zinc-900/50 text-center py-2 text-[10px] font-bold text-muted-foreground uppercase tracking-wider"
            >
                {#each dayNames as day}
                    <div>{day}</div>
                {/each}
            </div>

            <!-- Days cells -->
            <div
                class="grid grid-cols-7 divide-x divide-y divide-sidebar-border/20"
            >
                {#each calendarDays as day}
                    {@const dayEvents = getEventsForDate(day.date)}
                    {@const isSelected =
                        selectedDate.getDate() === day.date.getDate() &&
                        selectedDate.getMonth() === day.date.getMonth() &&
                        selectedDate.getFullYear() === day.date.getFullYear()}
                    {@const isToday =
                        new Date().getDate() === day.date.getDate() &&
                        new Date().getMonth() === day.date.getMonth() &&
                        new Date().getFullYear() === day.date.getFullYear()}

                    <button
                        onclick={() => selectDate(day.date)}
                        class="h-24 p-2 text-left relative flex flex-col justify-between hover:bg-zinc-100/30 dark:hover:bg-zinc-900/20 transition-all cursor-pointer
                            {day.isCurrentMonth
                            ? 'text-foreground'
                            : 'text-muted-foreground/40 bg-zinc-50/20 dark:bg-zinc-950/5'}
                            {isSelected
                            ? 'bg-primary/5 ring-1 ring-primary'
                            : ''}"
                        aria-label="{day.date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}. Ada {dayEvents.length} kegiatan."
                        aria-current={isSelected ? 'date' : undefined}
                    >
                        <!-- Day Number -->
                        <span
                            class="text-xs font-bold rounded-full size-6 flex items-center justify-center
                            {isToday
                                ? 'bg-primary text-white shadow-md'
                                : 'text-foreground/80'}"
                        >
                            {day.dayNumber}
                        </span>

                        <!-- Event mini indicators -->
                        <div class="space-y-1 w-full overflow-hidden">
                            {#each dayEvents.slice(0, 3) as event}
                                <div
                                    class="h-1.5 w-full rounded-full truncate text-[8px] px-1 font-bold flex items-center justify-center leading-none text-white
                                    {event.type === 'activity'
                                        ? 'bg-primary'
                                        : 'bg-amber-500'}"
                                >
                                    <span class="hidden sm:inline truncate"
                                        >{event.type === 'activity'
                                            ? event.code
                                            : event.name}</span
                                    >
                                </div>
                            {/each}
                            {#if dayEvents.length > 3}
                                <div
                                    class="text-[8px] text-muted-foreground text-center font-bold"
                                >
                                    +{dayEvents.length - 3} agenda
                                </div>
                            {/if}
                        </div>
                    </button>
                {/each}
            </div>
        </div>

        <!-- Selected Day Details Sidebar -->
        <div
            class="rounded-xl border border-sidebar-border/30 bg-card/40 p-5 shadow-sm backdrop-blur-md space-y-4"
        >
            <div
                class="flex items-center gap-2 border-b border-sidebar-border/20 pb-3"
            >
                <CalendarIcon class="size-4 text-primary" />
                <h3
                    class="text-xs font-bold text-foreground uppercase tracking-wide"
                >
                    Jadwal: {selectedDate.toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                    })}
                </h3>
            </div>

            <!-- List of events -->
            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                {#if selectedDayEvents.length === 0}
                    <div
                        class="p-8 text-center text-xs text-muted-foreground/60 italic space-y-2"
                    >
                        <Info class="size-8 text-muted-foreground/40 mx-auto" />
                        <p>
                            Tidak ada kegiatan atau sub-kegiatan yang
                            dijadwalkan pada hari ini.
                        </p>
                    </div>
                {:else}
                    {#each selectedDayEvents as event}
                        <div
                            class="p-3 bg-background border border-sidebar-border/20 rounded-xl space-y-2 hover:border-sidebar-border/30 transition-all shadow-sm"
                        >
                            <div class="flex justify-between items-start gap-2">
                                <span
                                    class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                    {event.type === 'activity'
                                        ? 'bg-primary/10 text-primary border border-primary/20'
                                        : 'bg-amber-500/10 text-amber-600 border border-amber-500/20'}"
                                >
                                    {event.type === 'activity'
                                        ? 'Kegiatan'
                                        : 'Sub-Kegiatan'}
                                </span>
                                <span class="text-[9px] font-bold text-primary"
                                    >{event.progress}%</span
                                >
                            </div>

                            <h4 class="text-xs font-bold text-foreground">
                                {#if event.code}
                                    <span
                                        class="text-muted-foreground font-semibold mr-1"
                                        >[{event.code}]</span
                                    >
                                {/if}
                                {event.name}
                            </h4>

                            <div
                                class="text-[10px] text-muted-foreground space-y-1 leading-relaxed"
                            >
                                <p>
                                    <span
                                        class="font-semibold text-foreground/75"
                                        >Unit Kerja:</span
                                    >
                                    {event.unit}
                                </p>
                                <p>
                                    <span
                                        class="font-semibold text-foreground/75"
                                        >Mulai:</span
                                    >
                                    {event.start.toLocaleDateString('id-ID', {
                                        day: 'numeric',
                                        month: 'short',
                                    })}
                                </p>
                                <p>
                                    <span
                                        class="font-semibold text-foreground/75"
                                        >Selesai:</span
                                    >
                                    {event.end.toLocaleDateString('id-ID', {
                                        day: 'numeric',
                                        month: 'short',
                                    })}
                                </p>
                                {#if event.assignee}
                                    <p>
                                        <span
                                            class="font-semibold text-foreground/75"
                                            >PJ Pelaksana:</span
                                        >
                                        {event.assignee}
                                    </p>
                                {/if}
                            </div>
                        </div>
                    {/each}
                {/if}
            </div>
        </div>
    </div>
</div>
