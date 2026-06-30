<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as usersIndex } from '@/routes/users';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'User Management',
                href: usersIndex(),
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
    import { create, edit, destroy } from '@/routes/users';

    let {
        users,
        units = [],
        roles = [],
        filters,
    }: {
        users: {
            data: Array<{
                id: number;
                name: string;
                email: string;
                employee_id: string | null;
                phone: string | null;
                is_active: boolean;
                unit?: { name: string; code: string } | null;
                roles: Array<{
                    id: number;
                    name: string;
                    display_name: string;
                }>;
            }>;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
        units: Array<{ id: number; name: string; code: string }>;
        roles: Array<{ id: number; display_name: string }>;
        filters: { search?: string; unit_id?: string; role_id?: string };
    } = $props();

    let searchQuery = $state(filters.search || '');
    let unitId = $state(filters.unit_id || '');
    let roleId = $state(filters.role_id || '');
    let confirmDeleteOpen = $state(false);
    let itemToDelete = $state<any>(null);

    function applyFilters() {
        router.get(
            toUrl(usersIndex()),
            {
                search: searchQuery,
                unit_id: unitId,
                role_id: roleId,
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

        router.delete(toUrl(destroy({ user: itemToDelete.id })), {
            onSuccess: () => {
                confirmDeleteOpen = false;
                itemToDelete = null;
            },
        });
    }
</script>

<AppHead title="User Management" />

<div class="p-6 space-y-6">
    <PageHeader
        title="User Management"
        description="Kelola akun pengguna, hak akses peranan, dan penempatan unit kerja"
    >
        {#snippet actions()}
            <Link
                href={toUrl(create())}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5"
            >
                <Plus class="size-4" />
                Tambah Pengguna
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
                placeholder="Cari User..."
                bind:value={searchQuery}
                class="w-full pl-9 pr-4 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary transition-colors"
            />
        </form>

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

        <select
            bind:value={roleId}
            onchange={applyFilters}
            class="px-3 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
        >
            <option value="">Semua Hak Akses (Role)</option>
            {#each roles as role}
                <option value={role.id.toString()}>{role.display_name}</option>
            {/each}
        </select>
    </div>

    {#if users.data.length === 0}
        <EmptyState
            title="Tidak ada pengguna ditemukan"
            description="Mulai kelola otorisasi sistem dengan menambahkan pengguna baru."
        >
            {#snippet actions()}
                <Link
                    href={toUrl(create())}
                    class="inline-flex h-9 items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 hover:bg-primary/90 transition-colors cursor-pointer"
                >
                    Tambah Pengguna Pertama
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
                            <th class="pb-3 pr-4">NIP / Employee ID</th>
                            <th class="pb-3 pr-4">Nama Lengkap</th>
                            <th class="pb-3 pr-4">Unit Kerja</th>
                            <th class="pb-3 pr-4">Peranan (Roles)</th>
                            <th class="pb-3 pr-4 text-center">Status</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/40">
                        {#each users.data as usr}
                            <tr
                                class="hover:bg-zinc-100/40 dark:hover:bg-zinc-900/40 transition-colors"
                            >
                                <td
                                    class="py-3 pr-4 text-muted-foreground font-semibold"
                                    >{usr.employee_id || '-'}</td
                                >
                                <td
                                    class="py-3 pr-4 font-bold text-foreground text-sm"
                                >
                                    <div>{usr.name}</div>
                                    <div
                                        class="text-xs text-muted-foreground font-normal"
                                    >
                                        {usr.email}
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-muted-foreground"
                                    >[{usr.unit?.code || ''}] {usr.unit?.name ||
                                        '-'}</td
                                >
                                <td
                                    class="py-3 pr-4 text-muted-foreground space-x-1"
                                >
                                    {#each usr.roles as role}
                                        <span
                                            class="inline-flex items-center rounded bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary"
                                        >
                                            {role.display_name}
                                        </span>
                                    {/each}
                                </td>
                                <td class="py-3 pr-4 text-center">
                                    <StatusBadge
                                        status={usr.is_active
                                            ? 'active'
                                            : 'inactive'}
                                    />
                                </td>
                                <td class="py-3 text-right">
                                    <div class="inline-flex gap-2">
                                        <Link
                                            href={toUrl(edit({ user: usr.id }))}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
                                            title="Edit"
                                        >
                                            <Edit2 class="size-4" />
                                        </Link>
                                        <button
                                            onclick={() => confirmDelete(usr)}
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
            {#if users.links.length > 3}
                <div
                    class="flex items-center justify-center gap-1.5 pt-6 border-t border-sidebar-border/30 mt-4"
                >
                    {#each users.links as link}
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
    title="Hapus Pengguna"
    description={`Apakah Anda yakin ingin menghapus akun pengguna "${itemToDelete?.name || ''}"? Tindakan ini tidak dapat dibatalkan.`}
    confirmLabel="Hapus"
    onConfirm={handleDelete}
/>
