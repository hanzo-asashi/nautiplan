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
                title: 'Rencana Kerja (Renja)',
                href: renjaIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Edit2 from 'lucide-svelte/icons/edit-2';
    import Eye from 'lucide-svelte/icons/eye';
    import Plus from 'lucide-svelte/icons/plus';
    import Search from 'lucide-svelte/icons/search';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import ConfirmDialog from '@/components/ConfirmDialog.svelte';
    import EmptyState from '@/components/EmptyState.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import StatusBadge from '@/components/StatusBadge.svelte';
    import { toUrl } from '@/lib/utils';
    import { formatRupiah } from '@/lib/utils';
    import { create, show, edit, destroy } from '@/routes/renja';

    let {
        renjas,
        fiscalYears = [],
        units = [],
        filters,
    }: {
        renjas: {
            data: Array<{
                id: number;
                title: string;
                status: string;
                total_budget: number;
                fiscal_year?: { year: number } | null;
                unit?: { name: string; code: string } | null;
            }>;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
        fiscalYears: Array<{ id: number; year: number }>;
        units: Array<{ id: number; name: string; code: string }>;
        filters: { search?: string; fiscal_year_id?: string; unit_id?: string };
    } = $props();

    let searchQuery = $state(filters.search || '');
    let fiscalYearId = $state(filters.fiscal_year_id || '');
    let unitId = $state(filters.unit_id || '');
    let confirmDeleteOpen = $state(false);
    let itemToDelete = $state<any>(null);

    function applyFilters() {
        router.get(
            toUrl(renjaIndex()),
            {
                search: searchQuery,
                fiscal_year_id: fiscalYearId,
                unit_id: unitId,
            },
            { preserveState: true },
        );
    }

    function handleSearch(e: Event) {
        e.preventDefault();
        applyFilters();
    }

    function confirmDelete(item: any) {
        itemToDelete = item;
        confirmDeleteOpen = true;
    }

    function handleDelete() {
        if (!itemToDelete) {
            return;
        }

        router.delete(toUrl(destroy({ renja: itemToDelete.id })), {
            onSuccess: () => {
                confirmDeleteOpen = false;
                itemToDelete = null;
            },
        });
    }
</script>

<AppHead title="Daftar Renja" />

<div class="p-6 space-y-6">
    <PageHeader
        title="Rencana Kerja Tahunan (Renja)"
        description="Kelola usulan rencana kegiatan dan anggaran tahunan unit kerja"
    >
        {#snippet actions()}
            <Link
                href={toUrl(create())}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5"
            >
                <Plus class="size-4" />
                Tambah Renja
            </Link>
        {/snippet}
    </PageHeader>

    <!-- Filters Panel -->
    <div
        class="grid gap-4 sm:grid-cols-3 bg-card/40 backdrop-blur-md p-4 rounded-xl border border-sidebar-border/50"
    >
        <form onsubmit={handleSearch} class="relative">
            <Search
                class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground"
            />
            <input
                type="text"
                placeholder="Cari Renja..."
                bind:value={searchQuery}
                class="w-full pl-9 pr-4 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary transition-colors"
            />
        </form>

        <select
            bind:value={fiscalYearId}
            onchange={applyFilters}
            class="px-3 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
        >
            <option value="">Semua Tahun Anggaran</option>
            {#each fiscalYears as fy}
                <option value={fy.id.toString()}>{fy.year}</option>
            {/each}
        </select>

        <select
            bind:value={unitId}
            onchange={applyFilters}
            class="px-3 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
        >
            <option value="">Semua Unit Kerja</option>
            {#each units as unit}
                <option value={unit.id.toString()}
                    >[{unit.code}] {unit.name}</option
                >
            {/each}
        </select>
    </div>

    {#if renjas.data.length === 0}
        <EmptyState
            title="Tidak ada data Renja"
            description="Mulai kelola rencana kerja tahunan dengan menambahkan data baru."
        >
            {#snippet actions()}
                <Link
                    href={toUrl(create())}
                    class="inline-flex h-9 items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 hover:bg-primary/90 transition-colors cursor-pointer"
                >
                    Tambah Renja Pertama
                </Link>
            {/snippet}
        </EmptyState>
    {:else}
        <div
            class="rounded-xl border border-sidebar-border/50 bg-card/45 backdrop-blur-md p-6 shadow-sm overflow-hidden"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr
                            class="border-b border-sidebar-border/50 text-muted-foreground font-medium"
                        >
                            <th class="pb-3 pr-4">Judul Renja</th>
                            <th class="pb-3 pr-4">Tahun</th>
                            <th class="pb-3 pr-4">Unit Kerja</th>
                            <th class="pb-3 pr-4 text-right">Pagu Anggaran</th>
                            <th class="pb-3 pr-4 text-center">Status</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/40">
                        {#each renjas.data as ren}
                            <tr
                                class="hover:bg-zinc-100/40 dark:hover:bg-zinc-900/40 transition-colors"
                            >
                                <td
                                    class="py-3 pr-4 font-bold text-foreground text-sm max-w-xs sm:max-w-md truncate"
                                    >{ren.title}</td
                                >
                                <td
                                    class="py-3 pr-4 text-foreground font-semibold"
                                    >{ren.fiscal_year?.year || '-'}</td
                                >
                                <td class="py-3 pr-4 text-muted-foreground"
                                    >[{ren.unit?.code || ''}] {ren.unit?.name ||
                                        '-'}</td
                                >
                                <td
                                    class="py-3 pr-4 text-right font-bold text-foreground"
                                    >{formatRupiah(ren.total_budget, true)}</td
                                >
                                <td class="py-3 pr-4 text-center">
                                    <StatusBadge status={ren.status} />
                                </td>
                                <td class="py-3 text-right">
                                    <div class="inline-flex gap-2">
                                        <Link
                                            href={toUrl(
                                                show({ renja: ren.id }),
                                            )}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
                                            title="Detail"
                                        >
                                            <Eye class="size-4" />
                                        </Link>
                                        <Link
                                            href={toUrl(
                                                edit({ renja: ren.id }),
                                            )}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
                                            title="Edit"
                                        >
                                            <Edit2 class="size-4" />
                                        </Link>
                                        <button
                                            onclick={() => confirmDelete(ren)}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-rose-500 hover:bg-rose-500/10 transition-colors cursor-pointer"
                                            title="Hapus"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {#if renjas.links.length > 3}
                <div
                    class="flex items-center justify-center gap-1.5 pt-6 border-t border-sidebar-border/30 mt-4"
                >
                    {#each renjas.links as link}
                        {#if link.url}
                            <Link
                                href={link.url}
                                class="px-3 py-1 text-xs font-semibold rounded-md border border-zinc-200/50 dark:border-zinc-800 transition-colors 
                                    {link.active
                                    ? 'bg-primary text-white border-primary shadow-sm'
                                    : 'bg-background hover:bg-accent'}"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span
                                class="px-3 py-1 text-xs font-semibold text-muted-foreground/50 cursor-not-allowed"
                            >
                                {@html link.label}
                            </span>
                        {/if}
                    {/each}
                </div>
            {/if}
        </div>
    {/if}
</div>

<ConfirmDialog
    bind:open={confirmDeleteOpen}
    title="Hapus Rencana Kerja"
    description={`Apakah Anda yakin ingin menghapus rencana kerja "${itemToDelete?.title || ''}"? Seluruh kegiatan di dalamnya juga akan terpengaruh.`}
    confirmLabel="Hapus"
    onConfirm={handleDelete}
/>
