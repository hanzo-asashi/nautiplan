<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as programIndex } from '@/routes/programs';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Programs',
                href: programIndex(),
            },
            {
                title: 'Detail',
                href: '',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import Edit2 from 'lucide-svelte/icons/edit-2';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import StatusBadge from '@/components/StatusBadge.svelte';
    import { formatRupiah } from '@/lib/utils';
    import { toUrl } from '@/lib/utils';
    import { edit } from '@/routes/programs';

    let {
        program,
    }: {
        program: {
            id: number;
            code: string;
            name: string;
            description: string | null;
            objective: string | null;
            status: string;
            priority: string;
            total_budget: number;
            start_date: string | null;
            end_date: string | null;
            fiscal_year?: { year: number } | null;
            renstra?: { title: string } | null;
            unit?: { name: string; code: string } | null;
            creator?: { name: string } | null;
            indicators: Array<{
                id: number;
                code: string;
                name: string;
                target_value: number;
                actual_value: number | null;
                unit_of_measure: string;
            }>;
            activities: Array<{
                id: number;
                code: string;
                name: string;
                status: string;
                unit?: { name: string; code: string } | null;
            }>;
        };
    } = $props();
</script>

<AppHead title={`Detail Program - ${program.name}`} />

<div class="p-6 space-y-6">
    <PageHeader
        title={`[${program.code}] ${program.name}`}
        description={`Unit: ${program.unit?.name || ''} | Tahun Anggaran ${program.fiscal_year?.year || ''}`}
    >
        {#snippet actions()}
            <Link
                href={toUrl(programIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5"
            >
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
            <Link
                href={toUrl(edit({ program: program.id }))}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5"
            >
                <Edit2 class="size-4" />
                Edit Program
            </Link>
        {/snippet}
    </PageHeader>

    <div class="grid gap-6 md:grid-cols-3">
        <!-- Info Column -->
        <div class="md:col-span-2 space-y-6">
            <!-- Vision & Mission card -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm"
            >
                <div>
                    <h3
                        class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                    >
                        Deskripsi Program
                    </h3>
                    <p class="mt-2 text-foreground leading-relaxed">
                        {program.description || 'Deskripsi belum diisi.'}
                    </p>
                </div>

                {#if program.objective}
                    <div class="border-t border-sidebar-border/30 pt-4">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1"
                        >
                            Tujuan / Sasaran Program
                        </h3>
                        <p class="text-foreground leading-relaxed">
                            {program.objective}
                        </p>
                    </div>
                {/if}
            </div>

            <!-- Indicators card -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm"
            >
                <h3
                    class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4"
                >
                    Indikator Keberhasilan Program
                </h3>

                {#if !program.indicators || program.indicators.length === 0}
                    <p
                        class="text-sm text-muted-foreground/60 italic text-center py-6"
                    >
                        Belum ada indikator program.
                    </p>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-sidebar-border text-muted-foreground font-medium"
                                >
                                    <th class="pb-3 pr-4">Kode</th>
                                    <th class="pb-3 pr-4">Nama Indikator</th>
                                    <th class="pb-3 pr-4 text-right">Target</th>
                                    <th class="pb-3 text-right">Realisasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/40">
                                {#each program.indicators as ind}
                                    <tr
                                        class="hover:bg-zinc-100/40 dark:hover:bg-zinc-900/40 transition-colors"
                                    >
                                        <td
                                            class="py-3 pr-4 font-semibold text-foreground"
                                            >{ind.code}</td
                                        >
                                        <td class="py-3 pr-4 text-foreground"
                                            >{ind.name}</td
                                        >
                                        <td
                                            class="py-3 pr-4 text-right text-muted-foreground"
                                            >{ind.target_value}
                                            {ind.unit_of_measure}</td
                                        >
                                        <td
                                            class="py-3 text-right font-bold text-primary"
                                            >{ind.actual_value || 0}
                                            {ind.unit_of_measure}</td
                                        >
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </div>

            <!-- Activities under Program -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm"
            >
                <h3
                    class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4"
                >
                    Daftar Kegiatan Pelaksana
                </h3>

                {#if !program.activities || program.activities.length === 0}
                    <p
                        class="text-sm text-muted-foreground/60 italic text-center py-6"
                    >
                        Belum ada kegiatan yang terdaftar di bawah program ini.
                    </p>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-sidebar-border text-muted-foreground font-medium"
                                >
                                    <th class="pb-3 pr-4">Kode</th>
                                    <th class="pb-3 pr-4">Nama Kegiatan</th>
                                    <th class="pb-3 pr-4">Unit Pelaksana</th>
                                    <th class="pb-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/40">
                                {#each program.activities as act}
                                    <tr
                                        class="hover:bg-zinc-100/40 dark:hover:bg-zinc-900/40 transition-colors"
                                    >
                                        <td
                                            class="py-3 pr-4 font-semibold text-foreground"
                                            >{act.code}</td
                                        >
                                        <td
                                            class="py-3 pr-4 font-medium text-foreground"
                                            >{act.name}</td
                                        >
                                        <td
                                            class="py-3 pr-4 text-muted-foreground"
                                            >[{act.unit?.code || ''}] {act.unit
                                                ?.name || ''}</td
                                        >
                                        <td class="py-3 text-center">
                                            <StatusBadge status={act.status} />
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm"
            >
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Status & Pagu
                </h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Status</span>
                        <StatusBadge status={program.status} />
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Prioritas</span>
                        <span
                            class="font-semibold px-2 py-0.5 rounded text-xs
                            {program.priority === 'critical'
                                ? 'bg-rose-500/10 text-rose-500'
                                : ''}
                            {program.priority === 'high'
                                ? 'bg-amber-500/10 text-amber-500'
                                : ''}
                            {program.priority === 'medium'
                                ? 'bg-blue-500/10 text-blue-500'
                                : ''}
                            {program.priority === 'low'
                                ? 'bg-zinc-500/10 text-zinc-500'
                                : ''}
                        "
                        >
                            {program.priority.toUpperCase()}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Pagu Anggaran</span>
                        <span class="font-bold text-foreground"
                            >{formatRupiah(program.total_budget)}</span
                        >
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Tahun Anggaran</span
                        >
                        <span class="font-semibold text-foreground"
                            >{program.fiscal_year?.year || '-'}</span
                        >
                    </div>
                    {#if program.creator}
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Pembuat</span>
                            <span class="font-semibold text-foreground"
                                >{program.creator.name}</span
                            >
                        </div>
                    {/if}
                </div>
            </div>

            {#if program.renstra}
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-2 shadow-sm text-sm"
                >
                    <h3
                        class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                    >
                        Kaitan Renstra
                    </h3>
                    <p class="font-semibold text-foreground">
                        {program.renstra.title}
                    </p>
                </div>
            {/if}
        </div>
    </div>
</div>
