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
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Search from 'lucide-svelte/icons/search';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';
    import { show as reportShow } from '@/routes/monitoring/reports';

    let {
        activities = [],
    }: {
        activities: Array<{
            id: number;
            code: string;
            name: string;
            unit_name: string;
            quarters: Record<
                string,
                'none' | 'draft' | 'submitted' | 'reviewed'
            >;
        }>;
    } = $props();

    let searchQuery = $state('');

    const filteredActivities = $derived(
        activities.filter((act) => {
            return (
                act.code.toLowerCase().includes(searchQuery.toLowerCase()) ||
                act.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                act.unit_name.toLowerCase().includes(searchQuery.toLowerCase())
            );
        }),
    );

    const getStatusStyle = (
        status: 'none' | 'draft' | 'submitted' | 'reviewed',
    ) => {
        switch (status) {
            case 'none':
                return 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700/50 hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50';
            case 'draft':
                return 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20 hover:bg-sky-500/15';
            case 'submitted':
                return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20 hover:bg-amber-500/15';
            case 'reviewed':
                return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/15';
        }
    };

    const getStatusLabel = (
        status: 'none' | 'draft' | 'submitted' | 'reviewed',
    ) => {
        switch (status) {
            case 'none':
                return 'Belum Dibuat';
            case 'draft':
                return 'Draft Laporan';
            case 'submitted':
                return 'Laporan Dikirim';
            case 'reviewed':
                return 'Selesai Monev';
        }
    };
</script>

<AppHead title="Laporan & Evaluasi Triwulanan" />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <PageHeader
        title="Laporan & Evaluasi Triwulan"
        description="Penyusunan laporan progres kinerja unit kerja serta verifikasi monitoring & evaluasi (M&E) triwulanan."
    />

    <!-- Main Content Area -->
    <div
        class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-4"
    >
        <!-- Search bar -->
        <div
            class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between pb-2"
        >
            <h3 class="text-base font-bold text-foreground">
                Monitoring Kegiatan & Laporan Kinerja
            </h3>

            <div class="relative w-full sm:max-w-xs">
                <Search
                    class="absolute left-2.5 top-2.5 size-4 text-muted-foreground"
                />
                <input
                    type="text"
                    placeholder="Cari kegiatan / unit..."
                    bind:value={searchQuery}
                    class="w-full pl-9 pr-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                />
            </div>
        </div>

        <!-- Table -->
        {#if filteredActivities.length === 0}
            <div
                class="text-center py-12 text-muted-foreground/60 italic text-sm"
            >
                Tidak ada data kegiatan yang sesuai pencarian.
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
                            <th class="p-3">Kegiatan</th>
                            <th class="p-3">Unit Kerja</th>
                            <th class="p-3 text-center">Triwulan 1 (Q1)</th>
                            <th class="p-3 text-center">Triwulan 2 (Q2)</th>
                            <th class="p-3 text-center">Triwulan 3 (Q3)</th>
                            <th class="p-3 text-center">Triwulan 4 (Q4)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/20">
                        {#each filteredActivities as activity}
                            <tr
                                class="hover:bg-zinc-50/30 dark:hover:bg-zinc-900/10 transition-colors"
                            >
                                <td
                                    class="p-3 font-semibold text-foreground whitespace-nowrap"
                                >
                                    {activity.code}
                                </td>
                                <td
                                    class="p-3 max-w-xs md:max-w-md font-bold text-foreground"
                                >
                                    {activity.name}
                                </td>
                                <td
                                    class="p-3 text-muted-foreground whitespace-nowrap"
                                >
                                    {activity.unit_name}
                                </td>
                                {#each ['Q1', 'Q2', 'Q3', 'Q4'] as q}
                                    <td class="p-3 text-center">
                                        <Link
                                            href={toUrl(
                                                reportShow({
                                                    activity: activity.id,
                                                    quarter: q,
                                                }),
                                            )}
                                            class="inline-block px-2.5 py-1 text-[10px] font-semibold border rounded-full transition-all duration-300 {getStatusStyle(
                                                activity.quarters[q],
                                            )}"
                                        >
                                            {getStatusLabel(
                                                activity.quarters[q],
                                            )}
                                        </Link>
                                    </td>
                                {/each}
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>
</div>
