<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as fiscalYearsIndex } from '@/routes/fiscal-years';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Tahun Anggaran',
                href: fiscalYearsIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import CheckCircle from 'lucide-svelte/icons/check-circle';
    import Edit2 from 'lucide-svelte/icons/edit-2';
    import Lock from 'lucide-svelte/icons/lock';
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import Unlock from 'lucide-svelte/icons/unlock';
    import XCircle from 'lucide-svelte/icons/x-circle';
    import AppHead from '@/components/AppHead.svelte';
    import ConfirmDialog from '@/components/ConfirmDialog.svelte';
    import EmptyState from '@/components/EmptyState.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';
    import {
        create,
        edit,
        destroy,
        toggleActive,
        toggleLock,
    } from '@/routes/fiscal-years';

    let {
        fiscalYears,
    }: {
        fiscalYears: {
            data: Array<{
                id: number;
                year: number;
                start_date: string;
                end_date: string;
                is_active: boolean;
                is_locked: boolean;
            }>;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
    } = $props();

    let confirmDeleteOpen = $state(false);
    let itemToDelete = $state<any>(null);

    function confirmDelete(item: any) {
        itemToDelete = item;
        confirmDeleteOpen = true;
    }

    function handleDelete() {
        if (!itemToDelete) {
            return;
        }

        router.delete(toUrl(destroy({ fiscal_year: itemToDelete.id })), {
            onSuccess: () => {
                confirmDeleteOpen = false;
                itemToDelete = null;
            },
        });
    }

    function handleToggleActive(id: number) {
        router.post(toUrl(toggleActive({ fiscal_year: id })));
    }

    // Wayfinder routes might have slightly different names. Let's make sure toggleLock points to the correct named post route.
    function handleToggleLock(id: number) {
        router.post(toUrl(toggleLock({ fiscal_year: id })));
    }

    function formatDate(dateStr: string | null | undefined): string {
        if (!dateStr) {
            return '-';
        }

        try {
            const date = new Date(dateStr);

            if (isNaN(date.getTime())) {
                return dateStr;
            }

            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        } catch {
            return dateStr;
        }
    }
</script>

<AppHead title="Daftar Tahun Anggaran" />

<div class="p-6 space-y-6">
    <PageHeader
        title="Tahun Anggaran"
        description="Kelola periode tahun anggaran aktif dan status kunci anggaran"
    >
        {#snippet actions()}
            <Link
                href={toUrl(create())}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-colors cursor-pointer gap-1.5"
            >
                <Plus class="size-4" />
                Tambah Tahun
            </Link>
        {/snippet}
    </PageHeader>

    {#if fiscalYears.data.length === 0}
        <EmptyState
            title="Tidak ada tahun anggaran"
            description="Mulai kelola perencanaan anggaran dengan menambahkan tahun anggaran baru."
        >
            {#snippet actions()}
                <Link
                    href={toUrl(create())}
                    class="inline-flex h-9 items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-medium shadow-md shadow-primary/20 hover:bg-primary/90 transition-colors cursor-pointer"
                >
                    Tambah Tahun Pertama
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
                            <th class="pb-3 pr-4">Tahun</th>
                            <th class="pb-3 pr-4">Tanggal Mulai</th>
                            <th class="pb-3 pr-4">Tanggal Selesai</th>
                            <th class="pb-3 pr-4 text-center">Status Aktif</th>
                            <th class="pb-3 pr-4 text-center">Terkunci</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/40">
                        {#each fiscalYears.data as year}
                            <tr
                                class="hover:bg-zinc-100/40 dark:hover:bg-zinc-900/40 transition-colors"
                            >
                                <td
                                    class="py-3 pr-4 font-bold text-foreground text-base"
                                    >{year.year}</td
                                >
                                <td class="py-3 pr-4 text-foreground"
                                    >{formatDate(year.start_date)}</td
                                >
                                <td class="py-3 pr-4 text-foreground"
                                    >{formatDate(year.end_date)}</td
                                >
                                <td class="py-3 pr-4 text-center">
                                    <button
                                        onclick={() =>
                                            handleToggleActive(year.id)}
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold cursor-pointer transition-all
                                            {year.is_active
                                            ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400'
                                            : 'bg-zinc-500/10 text-zinc-500 hover:bg-zinc-500/20'}"
                                    >
                                        {#if year.is_active}
                                            <CheckCircle class="size-3.5" />
                                            Aktif
                                        {:else}
                                            <XCircle class="size-3.5" />
                                            Non-aktif
                                        {/if}
                                    </button>
                                </td>
                                <td class="py-3 pr-4 text-center">
                                    <button
                                        onclick={() =>
                                            handleToggleLock(year.id)}
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold cursor-pointer transition-all
                                            {year.is_locked
                                            ? 'bg-rose-500/10 text-rose-700 dark:text-rose-400'
                                            : 'bg-emerald-500/10 text-emerald-750 dark:text-emerald-400 hover:bg-emerald-500/20'}"
                                    >
                                        {#if year.is_locked}
                                            <Lock class="size-3.5" />
                                            Terkunci
                                        {:else}
                                            <Unlock class="size-3.5" />
                                            Terbuka
                                        {/if}
                                    </button>
                                </td>
                                <td class="py-3 text-right">
                                    <div class="inline-flex gap-2">
                                        <Link
                                            href={toUrl(
                                                edit({ fiscal_year: year.id }),
                                            )}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
                                            title="Edit"
                                        >
                                            <Edit2 class="size-4" />
                                        </Link>
                                        <button
                                            onclick={() => confirmDelete(year)}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-rose-500 hover:bg-rose-500/10 transition-colors cursor-pointer"
                                            title="Hapus"
                                            disabled={year.is_active ||
                                                year.is_locked}
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
            {#if fiscalYears.links.length > 3}
                <div
                    class="flex items-center justify-center gap-1.5 pt-6 border-t border-sidebar-border/30 mt-4"
                >
                    {#each fiscalYears.links as link}
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
    title="Hapus Tahun Anggaran"
    description={`Apakah Anda yakin ingin menghapus tahun anggaran ${itemToDelete?.year || ''}? Tindakan ini tidak dapat dibatalkan.`}
    confirmLabel="Hapus"
    onConfirm={handleDelete}
/>
