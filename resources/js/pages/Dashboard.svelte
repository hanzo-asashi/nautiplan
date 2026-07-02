<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import Coins from 'lucide-svelte/icons/coins';
    import Folder from 'lucide-svelte/icons/folder';
    import Landmark from 'lucide-svelte/icons/landmark';
    import PiggyBank from 'lucide-svelte/icons/piggy-bank';
    import AppHead from '@/components/AppHead.svelte';
    import BarChart from '@/components/charts/BarChart.svelte';
    import StatsCard from '@/components/StatsCard.svelte';
    import { formatRupiah } from '@/lib/utils';

    let {
        stats,
        programs = [],
        recent_realizations = [],
    }: {
        stats: {
            total_programs: number;
            total_pagu: number;
            total_realisasi: number;
            sisa_anggaran: number;
            persen_realisasi: number;
        };
        programs: Array<{
            code: string;
            name: string;
            pagu: number;
            realisasi: number;
            sisa: number;
            persen: number;
        }>;
        recent_realizations: Array<{
            id: number;
            activity_name: string;
            unit_name: string;
            amount: number;
            date: string;
            description: string;
        }>;
    } = $props();

    // Prepare chart data format
    const chartData = $derived(
        programs.map((p) => ({
            label: `[${p.code}] ${p.name}`,
            value1: p.pagu,
            value2: p.realisasi,
        })),
    );
</script>

<AppHead title="Dashboard" />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <!-- Header Summary -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
            Dashboard
        </h1>
        <p class="text-sm text-muted-foreground">
            Ringkasan realisasi program & kegiatan Poltekpel Barombong
        </p>
    </div>

    <!-- Stats Grid -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatsCard
            title="Total Program"
            value={stats.total_programs}
            icon={Folder}
            description="Program kerja terdaftar"
        />
        <StatsCard
            title="Total Pagu"
            value={formatRupiah(stats.total_pagu, true)}
            icon={Landmark}
            description="Pagu DIPA BLU"
        />
        <StatsCard
            title="Total Realisasi"
            value={formatRupiah(stats.total_realisasi, true)}
            icon={Coins}
            trend={`${stats.persen_realisasi}%`}
            trendType="up"
            description="Realisasi belanja instansi"
        />
        <StatsCard
            title="Sisa Anggaran"
            value={formatRupiah(stats.sisa_anggaran, true)}
            icon={PiggyBank}
            description="Sisa sisa pagu anggaran"
        />
    </div>

    <!-- Charts & Realizations Section -->
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Budget Chart -->
        <div class="lg:col-span-2">
            <BarChart
                data={chartData}
                title="Realisasi Pagu per Program Utama"
            />
        </div>

        <!-- Recent Realizations -->
        <div
            class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm flex flex-col"
        >
            <h3 class="text-base font-semibold text-foreground mb-4">
                Realisasi Terbaru
            </h3>

            {#if recent_realizations.length === 0}
                <div
                    class="flex-1 flex flex-col items-center justify-center text-center p-6 text-muted-foreground text-sm"
                >
                    Belum ada realisasi anggaran tercatat.
                </div>
            {:else}
                <div
                    class="flex-1 space-y-4 overflow-y-auto max-h-[350px] pr-2"
                >
                    {#each recent_realizations as item (item.id)}
                        <div
                            class="flex flex-col gap-1 border-b border-sidebar-border/40 pb-3 last:border-0 last:pb-0"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <span
                                    class="text-sm font-semibold text-foreground line-clamp-1"
                                    >{item.activity_name}</span
                                >
                                <span
                                    class="text-sm font-bold text-emerald-600 dark:text-emerald-400 shrink-0"
                                    >{formatRupiah(item.amount, true)}</span
                                >
                            </div>
                            <div
                                class="flex justify-between items-center text-xs text-muted-foreground mt-0.5"
                            >
                                <span>{item.unit_name}</span>
                                <span>{item.date}</span>
                            </div>
                            {#if item.description}
                                <p
                                    class="text-xs text-muted-foreground/80 italic mt-1 line-clamp-2"
                                >
                                    "{item.description}"
                                </p>
                            {/if}
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>

    <!-- Detailed Program Breakdown Table -->
    <div
        class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm"
    >
        <h3 class="text-base font-semibold text-foreground mb-4">
            Rincian Capaian Program
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr
                        class="border-b border-sidebar-border/50 text-muted-foreground font-medium"
                    >
                        <th class="pb-3 pr-4">Kode</th>
                        <th class="pb-3 pr-4">Nama Program</th>
                        <th class="pb-3 pr-4 text-right">Pagu</th>
                        <th class="pb-3 pr-4 text-right">Realisasi</th>
                        <th class="pb-3 pr-4 text-right">Sisa</th>
                        <th class="pb-3 text-right">% Realisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/50">
                    {#each programs as prog}
                        <tr
                            class="hover:bg-zinc-100/40 dark:hover:bg-zinc-900/40 transition-colors"
                        >
                            <td class="py-3 pr-4 font-semibold text-foreground"
                                >{prog.code}</td
                            >
                            <td
                                class="py-3 pr-4 font-medium text-foreground max-w-xs sm:max-w-md truncate"
                                >{prog.name}</td
                            >
                            <td class="py-3 pr-4 text-right"
                                >{formatRupiah(prog.pagu, true)}</td
                            >
                            <td
                                class="py-3 pr-4 text-right text-emerald-600 dark:text-emerald-400 font-semibold"
                                >{formatRupiah(prog.realisasi, true)}</td
                            >
                            <td
                                class="py-3 pr-4 text-right text-muted-foreground"
                                >{formatRupiah(prog.sisa, true)}</td
                            >
                            <td class="py-3 text-right font-bold text-primary"
                                >{prog.persen}%</td
                            >
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    </div>
</div>
