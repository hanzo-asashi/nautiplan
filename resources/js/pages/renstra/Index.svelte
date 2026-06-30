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
                title: 'Rencana Strategis (Renstra)',
                href: renstraIndex(),
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
    import { create, show, edit, destroy } from '@/routes/renstra';

    let {
        renstras,
        filters,
    }: {
        renstras: {
            data: Array<{
                id: number;
                title: string;
                description: string | null;
                start_year: number;
                end_year: number;
                status: string;
                indicators_count: number;
            }>;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
        filters: { search?: string };
    } = $props();

    let searchQuery = $state(filters.search || '');
    let confirmDeleteOpen = $state(false);
    let itemToDelete = $state<any>(null);

    function handleSearch(e: Event) {
        e.preventDefault();
        router.get(
            toUrl(renstraIndex()),
            { search: searchQuery },
            { preserveState: true },
        );
    }

    function confirmDelete(item: any) {
        itemToDelete = item;
        confirmDeleteOpen = true;
    }

    function handleDelete() {
        if (!itemToDelete) {
            return;
        }

        router.delete(toUrl(destroy({ renstra: itemToDelete.id })), {
            onSuccess: () => {
                confirmDeleteOpen = false;
                itemToDelete = null;
            },
        });
    }
</script>

<AppHead title="Daftar Renstra" />

<div class="p-6 space-y-6">
    <PageHeader
        title="Rencana Strategis (Renstra)"
        description="Manajemen sasaran strategis jangka menengah Poltekpel Barombong"
    >
        {#snippet actions()}
            <Link
                href={toUrl(create())}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5"
            >
                <Plus class="size-4" />
                Tambah Renstra
            </Link>
        {/snippet}
    </PageHeader>

    <!-- Filter & Search Bar -->
    <div
        class="flex items-center gap-4 bg-card/40 backdrop-blur-md p-4 rounded-xl border border-sidebar-border/50"
    >
        <form onsubmit={handleSearch} class="relative flex-1 max-w-md">
            <Search
                class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground"
            />
            <input
                type="text"
                placeholder="Cari Renstra..."
                bind:value={searchQuery}
                class="w-full pl-9 pr-4 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary transition-colors"
            />
        </form>
    </div>

    <!-- Content Table / Grid -->
    {#if renstras.data.length === 0}
        <EmptyState
            title="Tidak ada data Renstra"
            description="Mulai kelola rencana strategis jangka menengah dengan menambahkan data baru."
        >
            {#snippet actions()}
                <Link
                    href={toUrl(create())}
                    class="inline-flex h-9 items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 hover:bg-primary/90 transition-colors cursor-pointer"
                >
                    Tambah Renstra Pertama
                </Link>
            {/snippet}
        </EmptyState>
    {:else}
        <div class="grid gap-6 md:grid-cols-2">
            {#each renstras.data as ren}
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm flex flex-col justify-between hover:shadow-md hover:border-primary/20 transition-all duration-300"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-semibold px-2.5 py-1 bg-primary/10 text-primary rounded-full"
                            >
                                Periode {ren.start_year} - {ren.end_year}
                            </span>
                            <StatusBadge status={ren.status} />
                        </div>

                        <h3
                            class="text-lg font-bold text-foreground line-clamp-1"
                        >
                            {ren.title}
                        </h3>

                        {#if ren.description}
                            <p
                                class="text-sm text-muted-foreground line-clamp-2 leading-relaxed"
                            >
                                {ren.description}
                            </p>
                        {:else}
                            <p class="text-sm text-muted-foreground/60 italic">
                                Tidak ada deskripsi.
                            </p>
                        {/if}

                        <div
                            class="text-xs font-medium text-muted-foreground/80 pt-2 flex gap-4"
                        >
                            <span
                                >Indikator Sasaran: <strong
                                    class="text-foreground"
                                    >{ren.indicators_count}</strong
                                ></span
                            >
                        </div>
                    </div>

                    <div
                        class="flex justify-end gap-2 border-t border-sidebar-border/30 pt-4 mt-6"
                    >
                        <Link
                            href={toUrl(show({ renstra: ren.id }))}
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
                            title="Detail"
                        >
                            <Eye class="size-4" />
                        </Link>
                        <Link
                            href={toUrl(edit({ renstra: ren.id }))}
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
                </div>
            {/each}
        </div>

        <!-- Simple pagination links -->
        {#if renstras.links.length > 3}
            <div class="flex items-center justify-center gap-1.5 pt-4">
                {#each renstras.links as link}
                    {#if link.url}
                        <Link
                            href={link.url}
                            class="px-3.5 py-1.5 text-xs font-medium rounded-md border border-zinc-200/50 dark:border-zinc-800 transition-colors 
                                {link.active
                                ? 'bg-primary text-white border-primary shadow-sm shadow-primary/20'
                                : 'bg-background hover:bg-accent hover:text-accent-foreground'}"
                        >
                            {@html link.label}
                        </Link>
                    {:else}
                        <span
                            class="px-3.5 py-1.5 text-xs font-medium text-muted-foreground/50 cursor-not-allowed"
                        >
                            {@html link.label}
                        </span>
                    {/if}
                {/each}
            </div>
        {/if}
    {/if}
</div>

<ConfirmDialog
    bind:open={confirmDeleteOpen}
    title="Hapus Rencana Strategis"
    description={`Apakah Anda yakin ingin menghapus "${itemToDelete?.title || ''}"? Seluruh indikator di dalamnya juga akan terhapus.`}
    confirmLabel="Hapus"
    onConfirm={handleDelete}
/>
