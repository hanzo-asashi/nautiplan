<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as renstraIndex } from '@/routes/renstra';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Renstra',
                href: renstraIndex(),
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
    import { toUrl } from '@/lib/utils';
    import { edit } from '@/routes/renstra';

    let {
        renstra,
    }: {
        renstra: {
            id: number;
            title: string;
            description: string | null;
            start_year: number;
            end_year: number;
            status: string;
            vision: string | null;
            mission: string[] | null;
            indicators: Array<{
                id: number;
                code: string;
                name: string;
                target_value: number;
                unit_of_measure: string;
                baseline_value: number;
            }>;
            creator?: { name: string };
        };
    } = $props();
</script>

<AppHead title={`Detail Renstra - ${renstra.title}`} />

<div class="p-6 space-y-6">
    <PageHeader
        title={renstra.title}
        description={`Periode Rencana Kerja Jangka Menengah ${renstra.start_year} - ${renstra.end_year}`}
    >
        {#snippet actions()}
            <Link
                href={toUrl(renstraIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5"
            >
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
            <Link
                href={toUrl(edit({ renstra: renstra.id }))}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5"
            >
                <Edit2 class="size-4" />
                Edit Renstra
            </Link>
        {/snippet}
    </PageHeader>

    <!-- Main Detail Grid -->
    <div class="grid gap-6 md:grid-cols-3">
        <!-- Info Column -->
        <div class="md:col-span-2 space-y-6">
            <!-- Vision & Mission card -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm"
            >
                <div>
                    <h3
                        class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                    >
                        Visi Utama
                    </h3>
                    <p
                        class="mt-2 text-base font-bold text-foreground leading-relaxed"
                    >
                        {renstra.vision || 'Visi belum didefinisikan.'}
                    </p>
                </div>

                <div class="border-t border-sidebar-border/30 pt-4">
                    <h3
                        class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-3"
                    >
                        Misi Instansi
                    </h3>
                    {#if !renstra.mission || renstra.mission.length === 0}
                        <p class="text-sm text-muted-foreground/60 italic">
                            Misi belum didefinisikan.
                        </p>
                    {:else}
                        <ol
                            class="list-decimal pl-5 space-y-2 text-sm text-foreground"
                        >
                            {#each renstra.mission as mis}
                                <li>{mis}</li>
                            {/each}
                        </ol>
                    {/if}
                </div>
            </div>

            <!-- Indicators card -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm"
            >
                <h3
                    class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4"
                >
                    Indikator Sasaran Strategis
                </h3>

                {#if renstra.indicators.length === 0}
                    <p
                        class="text-sm text-muted-foreground/60 italic text-center py-6"
                    >
                        Belum ada indikator sasaran strategis.
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
                                    <th class="pb-3 pr-4 text-right"
                                        >Baseline</th
                                    >
                                    <th class="pb-3 text-right">Target</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/40">
                                {#each renstra.indicators as ind}
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
                                            >{ind.baseline_value}
                                            {ind.unit_of_measure}</td
                                        >
                                        <td
                                            class="py-3 text-right font-bold text-primary"
                                            >{ind.target_value}
                                            {ind.unit_of_measure}</td
                                        >
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </div>
        </div>

        <!-- Sidebar Info Column -->
        <div class="space-y-6">
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm"
            >
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Status & Metadata
                </h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Status Renstra</span
                        >
                        <StatusBadge status={renstra.status} />
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Tahun Mulai</span>
                        <span class="font-semibold text-foreground"
                            >{renstra.start_year}</span
                        >
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Tahun Selesai</span>
                        <span class="font-semibold text-foreground"
                            >{renstra.end_year}</span
                        >
                    </div>
                    {#if renstra.creator}
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground"
                                >Dibuat Oleh</span
                            >
                            <span class="font-semibold text-foreground"
                                >{renstra.creator.name}</span
                            >
                        </div>
                    {/if}
                </div>
            </div>

            {#if renstra.description}
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-2 shadow-sm text-sm"
                >
                    <h3
                        class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                    >
                        Catatan Tambahan
                    </h3>
                    <p class="text-muted-foreground leading-relaxed">
                        {renstra.description}
                    </p>
                </div>
            {/if}
        </div>
    </div>
</div>
