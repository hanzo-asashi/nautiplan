<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as unitsIndex } from '@/routes/units';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Unit Kerja',
                href: unitsIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Edit2 from 'lucide-svelte/icons/edit-2';
    import Plus from 'lucide-svelte/icons/plus';
    import Search from 'lucide-svelte/icons/search';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import ConfirmDialog from '@/components/ConfirmDialog.svelte';
    import EmptyState from '@/components/EmptyState.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import StatusBadge from '@/components/StatusBadge.svelte';
    import { toUrl } from '@/lib/utils';
    import { create, edit, destroy } from '@/routes/units';

    let {
        units,
        filters,
    }: {
        units: {
            data: Array<{
                id: number;
                code: string;
                name: string;
                is_active: boolean;
                parent?: { name: string } | null;
                head?: { name: string } | null;
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
            toUrl(unitsIndex()),
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

        router.delete(toUrl(destroy({ unit: itemToDelete.id })), {
            onSuccess: () => {
                confirmDeleteOpen = false;
                itemToDelete = null;
            },
        });
    }
</script>

<AppHead title="Daftar Unit Kerja" />

<div class="p-6 space-y-6">
    <PageHeader
        title="Unit Kerja"
        description="Kelola struktur unit organisasi Poltekpel Barombong"
    >
        {#snippet actions()}
            <Link
                href={toUrl(create())}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5"
            >
                <Plus class="size-4" />
                Tambah Unit
            </Link>
        {/snippet}
    </PageHeader>

    <!-- Search bar -->
    <div
        class="flex items-center gap-4 bg-card/40 backdrop-blur-md p-4 rounded-xl border border-sidebar-border/50"
    >
        <form onsubmit={handleSearch} class="relative flex-1 max-w-md">
            <Search
                class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground"
            />
            <input
                type="text"
                placeholder="Cari Unit..."
                bind:value={searchQuery}
                class="w-full pl-9 pr-4 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary transition-colors"
            />
        </form>
    </div>

    {#if units.data.length === 0}
        <EmptyState
            title="Tidak ada data Unit Kerja"
            description="Mulai kelola unit kerja dengan menambahkan data baru."
        >
            {#snippet actions()}
                <Link
                    href={toUrl(create())}
                    class="inline-flex h-9 items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 hover:bg-primary/90 transition-colors cursor-pointer"
                >
                    Tambah Unit Kerja Pertama
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
                            <th class="pb-3 pr-4">Kode</th>
                            <th class="pb-3 pr-4">Nama Unit</th>
                            <th class="pb-3 pr-4">Unit Induk</th>
                            <th class="pb-3 pr-4">Kepala Unit</th>
                            <th class="pb-3 pr-4 text-center">Status</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/40">
                        {#each units.data as uni}
                            <tr
                                class="hover:bg-zinc-100/40 dark:hover:bg-zinc-900/40 transition-colors"
                            >
                                <td
                                    class="py-3 pr-4 font-semibold text-foreground"
                                    >{uni.code}</td
                                >
                                <td
                                    class="py-3 pr-4 font-bold text-foreground text-sm"
                                    >{uni.name}</td
                                >
                                <td class="py-3 pr-4 text-muted-foreground"
                                    >{uni.parent?.name || '-'}</td
                                >
                                <td class="py-3 pr-4 text-muted-foreground"
                                    >{uni.head?.name || '-'}</td
                                >
                                <td class="py-3 pr-4 text-center">
                                    <StatusBadge
                                        status={uni.is_active
                                            ? 'active'
                                            : 'inactive'}
                                    />
                                </td>
                                <td class="py-3 text-right">
                                    <div class="inline-flex gap-2">
                                        <Link
                                            href={toUrl(edit({ unit: uni.id }))}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
                                            title="Edit"
                                        >
                                            <Edit2 class="size-4" />
                                        </Link>
                                        <button
                                            onclick={() => confirmDelete(uni)}
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
            {#if units.links.length > 3}
                <div
                    class="flex items-center justify-center gap-1.5 pt-6 border-t border-sidebar-border/30 mt-4"
                >
                    {#each units.links as link}
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
    title="Hapus Unit Kerja"
    description={`Apakah Anda yakin ingin menghapus unit "${itemToDelete?.name || ''}"? Tindakan ini tidak dapat dibatalkan.`}
    confirmLabel="Hapus"
    onConfirm={handleDelete}
/>
