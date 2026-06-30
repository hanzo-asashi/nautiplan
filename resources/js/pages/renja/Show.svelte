<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as renjaIndex } from '@/routes/renja';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Rencana Kerja',
                href: renjaIndex(),
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
    import { edit } from '@/routes/renja';

    let {
        renja,
    }: {
        renja: {
            id: number;
            title: string;
            status: string;
            total_budget: number;
            fiscal_year?: { year: number } | null;
            renstra?: { title: string } | null;
            unit?: { name: string; code: string } | null;
            creator?: { name: string } | null;
            activities: Array<{
                id: number;
                code: string;
                name: string;
                status: string;
                program?: { name: string; code: string } | null;
            }>;
        };
    } = $props();
</script>

<AppHead title={`Detail Renja - ${renja.title}`} />

<div class="p-6 space-y-6">
    <PageHeader
        title={renja.title}
        description={`Unit: ${renja.unit?.name || ''} | Tahun Anggaran ${renja.fiscal_year?.year || ''}`}
    >
        {#snippet actions()}
            <Link
                href={toUrl(renjaIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5"
            >
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
            <Link
                href={toUrl(edit({ renja: renja.id }))}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5"
            >
                <Edit2 class="size-4" />
                Edit Renja
            </Link>
        {/snippet}
    </PageHeader>

    <div class="grid gap-6 md:grid-cols-3">
        <!-- Info Column -->
        <div class="md:col-span-2 space-y-6">
            <!-- Details Overview -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm"
            >
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Informasi Umum
                </h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-muted-foreground"
                            >Kaitan Rencana Strategis (Renstra)</span
                        >
                        <span class="font-semibold text-foreground"
                            >{renja.renstra?.title ||
                                'Tidak terkait dengan Renstra tertentu'}</span
                        >
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-muted-foreground"
                            >Pagu Anggaran Disetujui</span
                        >
                        <span class="font-bold text-lg text-primary"
                            >{formatRupiah(renja.total_budget)}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Activities Table -->
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm"
            >
                <h3
                    class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4"
                >
                    Kegiatan Unit Kerja
                </h3>

                {#if !renja.activities || renja.activities.length === 0}
                    <p
                        class="text-sm text-muted-foreground/60 italic text-center py-6"
                    >
                        Belum ada kegiatan yang diajukan di bawah Renja ini.
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
                                    <th class="pb-3 pr-4">Program</th>
                                    <th class="pb-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/40">
                                {#each renja.activities as act}
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
                                            class="py-3 pr-4 text-xs text-muted-foreground"
                                            >[{act.program?.code || ''}] {act
                                                .program?.name || ''}</td
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

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <div
                class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 space-y-4 shadow-sm text-sm"
            >
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Status & Pembuat
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Status Renja</span>
                        <StatusBadge status={renja.status} />
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Tahun Anggaran</span
                        >
                        <span class="font-semibold text-foreground"
                            >{renja.fiscal_year?.year || '-'}</span
                        >
                    </div>
                    {#if renja.creator}
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground"
                                >Diajukan Oleh</span
                            >
                            <span class="font-semibold text-foreground"
                                >{renja.creator.name}</span
                            >
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>
