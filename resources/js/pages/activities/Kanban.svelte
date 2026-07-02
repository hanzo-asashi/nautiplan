<script module lang="ts">
    import { dashboard } from '@/routes';
    import { show as showActivity } from '@/routes/activities';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Kegiatan',
                href: '#',
            },
            {
                title: 'Papan Kanban',
                href: '',
            },
        ],
    };
</script>

<script lang="ts">
    import { router, Link } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import CalendarDays from 'lucide-svelte/icons/calendar-days';
    import CheckCircle from 'lucide-svelte/icons/check-circle';
    import Circle from 'lucide-svelte/icons/circle';
    import Loader2 from 'lucide-svelte/icons/loader-2';
    import PlayCircle from 'lucide-svelte/icons/play-circle';
    import UserIcon from 'lucide-svelte/icons/user';
    import XCircle from 'lucide-svelte/icons/x-circle';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';
    import { updateStatus as updateStatusRoute } from '@/routes/sub-activities';

    let {
        activity,
    }: {
        activity: {
            id: number;
            code: string;
            name: string;
            description: string | null;
            unit: { name: string };
            fiscal_year: { year: number };
            sub_activities: Array<{
                id: number;
                name: string;
                description: string | null;
                status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
                start_date: string | null;
                end_date: string | null;
                progress_percentage: number;
                assigned_user?: { id: number; name: string } | null;
            }>;
        };
    } = $props();

    // Track which card is currently being edited/saving
    let editingCardId = $state<number | null>(null);
    let tempProgress = $state(0);
    let savingCardId = $state<number | null>(null);

    // Columns structure
    const columns = [
        {
            id: 'pending',
            title: 'Belum Mulai',
            color: 'border-zinc-200 dark:border-zinc-800 bg-zinc-500/5',
            icon: Circle,
            iconColor: 'text-zinc-500',
        },
        {
            id: 'in_progress',
            title: 'Sedang Berjalan',
            color: 'border-blue-500/30 bg-blue-500/5',
            icon: PlayCircle,
            iconColor: 'text-blue-500',
        },
        {
            id: 'completed',
            title: 'Selesai',
            color: 'border-emerald-500/30 bg-emerald-500/5',
            icon: CheckCircle,
            iconColor: 'text-emerald-500',
        },
        {
            id: 'cancelled',
            title: 'Dibatalkan',
            color: 'border-rose-500/30 bg-rose-500/5',
            icon: XCircle,
            iconColor: 'text-rose-500',
        },
    ] as const;

    function startEditProgress(cardId: number, currentProgress: number) {
        editingCardId = cardId;
        tempProgress = currentProgress;
    }

    function cancelEditProgress() {
        editingCardId = null;
    }

    function updateStatus(
        subActivityId: number,
        newStatus: (typeof columns)[number]['id'],
        newProgress?: number,
    ) {
        const sub = activity.sub_activities.find((s) => s.id === subActivityId);

        if (!sub) {
            return;
        }

        const progress =
            newProgress !== undefined
                ? newProgress
                : newStatus === 'completed'
                  ? 100
                  : sub.progress_percentage;
        savingCardId = subActivityId;
        editingCardId = null;

        router
            .optimistic((props: any) => {
                const updatedSubs = props.activity.sub_activities.map(
                    (s: typeof sub) => {
                        if (s.id === subActivityId) {
                            return {
                                ...s,
                                status: newStatus,
                                progress_percentage: progress,
                            };
                        }

                        return s;
                    },
                );

                return {
                    ...props,
                    activity: {
                        ...props.activity,
                        sub_activities: updatedSubs,
                    },
                };
            })
            .put(
                toUrl(updateStatusRoute({ subActivity: subActivityId })),
                {
                    status: newStatus,
                    progress_percentage: progress,
                },
                {
                    preserveScroll: true,
                    onFinish: () => {
                        savingCardId = null;
                    },
                },
            );
    }

    function formatDateRange(start: string | null, end: string | null): string {
        if (!start && !end) {
            return 'Rencana tanggal belum diatur';
        }

        const startStr = start
            ? new Date(start).toLocaleDateString('id-ID', {
                  day: 'numeric',
                  month: 'short',
              })
            : '?';
        const endStr = end
            ? new Date(end).toLocaleDateString('id-ID', {
                  day: 'numeric',
                  month: 'short',
                  year: '2-digit',
              })
            : '?';

        return `${startStr} - ${endStr}`;
    }
</script>

<AppHead title={`Papan Kanban - ${activity.name}`} />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <!-- Header Area -->
    <div class="flex flex-col gap-3">
        <Link
            href={toUrl(showActivity({ activity: activity.id }))}
            class="flex items-center gap-1.5 text-xs text-muted-foreground hover:text-primary transition-colors w-fit"
        >
            <ArrowLeft class="size-3.5" />
            Kembali ke Detail Kegiatan
        </Link>
        <PageHeader
            title="Papan Kanban Sub-Kegiatan"
            description={`Mengelola tugas operasional untuk kegiatan: [${activity.code}] ${activity.name}`}
        />
    </div>

    <!-- Info Stats Cards -->
    <div class="grid gap-4 grid-cols-2 md:grid-cols-4">
        {#each columns as col}
            {@const count = activity.sub_activities.filter(
                (s) => s.status === col.id,
            ).length}
            <div
                class="p-4 rounded-xl border border-sidebar-border/30 bg-card/40 backdrop-blur-md flex items-center justify-between"
            >
                <div>
                    <span
                        class="text-xs text-muted-foreground font-semibold block"
                        >{col.title}</span
                    >
                    <span class="text-xl font-black text-foreground block mt-1"
                        >{count}</span
                    >
                </div>
                <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                    <col.icon class="size-5 {col.iconColor}" />
                </div>
            </div>
        {/each}
    </div>

    <!-- Kanban Grid -->
    <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-start flex-1 min-h-[500px]"
    >
        {#each columns as column}
            {@const columnTasks = activity.sub_activities.filter(
                (s) => s.status === column.id,
            )}

            <div
                class="rounded-xl border {column.color} flex flex-col h-full min-h-[400px] shadow-sm"
            >
                <!-- Column Header -->
                <div
                    class="p-4 border-b border-sidebar-border/30 flex items-center justify-between"
                >
                    <div class="flex items-center gap-2">
                        <column.icon class="size-4 {column.iconColor}" />
                        <span class="text-xs font-bold text-foreground"
                            >{column.title}</span
                        >
                    </div>
                    <span
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-200/50 dark:bg-zinc-800 text-muted-foreground"
                    >
                        {columnTasks.length}
                    </span>
                </div>

                <!-- Cards List -->
                <div
                    class="p-3 space-y-3 flex-1 overflow-y-auto max-h-[600px] min-h-[150px]"
                >
                    {#if columnTasks.length === 0}
                        <div
                            class="h-32 border border-dashed border-sidebar-border/20 rounded-lg flex items-center justify-center text-center text-[10px] text-muted-foreground/60 p-4"
                        >
                            Tarik atau pindahkan sub-kegiatan ke kolom ini.
                        </div>
                    {:else}
                        {#each columnTasks as task (task.id)}
                            <div
                                class="p-4 bg-background border border-sidebar-border/20 rounded-xl hover:border-sidebar-border/50 hover:shadow-md transition-all space-y-3 relative group"
                            >
                                <!-- Name and Description -->
                                <div class="space-y-1">
                                    <h4
                                        class="text-xs font-bold text-foreground line-clamp-2 leading-relaxed"
                                    >
                                        {task.name}
                                    </h4>
                                    {#if task.description}
                                        <p
                                            class="text-[10px] text-muted-foreground line-clamp-2"
                                        >
                                            {task.description}
                                        </p>
                                    {/if}
                                </div>

                                <!-- Date & Assignment -->
                                <div
                                    class="space-y-1.5 pt-1 text-[10px] text-muted-foreground border-t border-sidebar-border/10"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <CalendarDays class="size-3 shrink-0" />
                                        <span
                                            >{formatDateRange(
                                                task.start_date,
                                                task.end_date,
                                            )}</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <UserIcon class="size-3 shrink-0" />
                                        <span class="truncate"
                                            >{task.assigned_user?.name ||
                                                'Belum ditugaskan'}</span
                                        >
                                    </div>
                                </div>

                                <!-- Progress Percentage -->
                                <div class="space-y-1 pt-1">
                                    <div
                                        class="flex justify-between items-center text-[10px] font-semibold"
                                    >
                                        <span class="text-muted-foreground"
                                            >Progres:</span
                                        >
                                        <span class="text-primary font-bold"
                                            >{task.progress_percentage}%</span
                                        >
                                    </div>
                                    <div
                                        class="h-1.5 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden"
                                    >
                                        <div
                                            class="h-full bg-primary rounded-full transition-all duration-300"
                                            style="width: {task.progress_percentage}%"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Card Actions -->
                                <div
                                    class="pt-2 flex flex-wrap gap-1.5 items-center border-t border-sidebar-border/10 justify-between"
                                >
                                    <!-- Progress Quick Edit -->
                                    {#if editingCardId === task.id}
                                        <div
                                            class="flex items-center gap-2 w-full pt-1"
                                        >
                                            <input
                                                type="range"
                                                min="0"
                                                max="100"
                                                bind:value={tempProgress}
                                                class="flex-1 accent-primary h-1"
                                                aria-label="Progres Sub-Kegiatan (%)"
                                            />
                                            <span
                                                class="text-[9px] font-bold shrink-0"
                                                >{tempProgress}%</span
                                            >
                                            <button
                                                onclick={() =>
                                                    updateStatus(
                                                        task.id,
                                                        task.status,
                                                        tempProgress,
                                                    )}
                                                class="px-1.5 py-0.5 bg-primary text-white text-[8px] font-bold rounded hover:bg-primary/95 cursor-pointer"
                                            >
                                                Simpan
                                            </button>
                                            <button
                                                onclick={cancelEditProgress}
                                                class="px-1.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-[8px] font-bold rounded hover:bg-zinc-200 cursor-pointer"
                                            >
                                                Batal
                                            </button>
                                        </div>
                                    {:else}
                                        <button
                                            onclick={() =>
                                                startEditProgress(
                                                    task.id,
                                                    task.progress_percentage,
                                                )}
                                            class="text-[9px] text-primary font-bold hover:underline cursor-pointer"
                                        >
                                            Ubah Progres
                                        </button>
                                    {/if}

                                    <!-- Status Selector Dropdown -->
                                    <div
                                        class="flex items-center gap-1.5 ml-auto"
                                    >
                                        {#if savingCardId === task.id}
                                            <Loader2
                                                class="size-3 animate-spin text-primary"
                                            />
                                        {:else}
                                            <select
                                                value={task.status}
                                                aria-label="Ubah Status Sub-Kegiatan"
                                                onchange={(e) =>
                                                    updateStatus(
                                                        task.id,
                                                        (
                                                            e.target as HTMLSelectElement
                                                        )
                                                            .value as (typeof columns)[number]['id'],
                                                    )}
                                                class="px-1.5 py-0.5 text-[9px] bg-background border border-zinc-200 dark:border-zinc-800 rounded outline-none focus:border-primary font-semibold text-muted-foreground cursor-pointer"
                                            >
                                                {#each columns as col}
                                                    <option value={col.id}
                                                        >{col.title}</option
                                                    >
                                                {/each}
                                            </select>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        {/each}
                    {/if}
                </div>
            </div>
        {/each}
    </div>
</div>
