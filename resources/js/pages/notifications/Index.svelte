<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as notificationsIndex } from '@/routes/notifications';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Notifikasi',
                href: notificationsIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router, Link } from '@inertiajs/svelte';
    import Bell from 'lucide-svelte/icons/bell';
    import Check from 'lucide-svelte/icons/check';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { Button } from '@/components/ui/button';
    import { toUrl } from '@/lib/utils';
    import notificationsRoute from '@/routes/notifications';

    let {
        notifications = { data: [], links: [] },
    }: {
        notifications: {
            data: Array<{
                id: string;
                title: string;
                message: string;
                type: string;
                read_at: string | null;
                created_at: string;
            }>;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
    } = $props();

    const hasUnread = $derived(notifications.data.some((n) => !n.read_at));

    function markAsRead(id: string) {
        router.post(
            toUrl(notificationsRoute.read({ notification: id })),
            {},
            { preserveScroll: true },
        );
    }

    function markAllAsRead() {
        router.post(
            toUrl(notificationsRoute.readAll()),
            {},
            { preserveScroll: true },
        );
    }

    function formatDateTime(dateStr: string): string {
        try {
            return new Date(dateStr).toLocaleString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        } catch {
            return dateStr;
        }
    }
</script>

<AppHead title="Daftar Notifikasi" />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <PageHeader
        title="Daftar Notifikasi"
        description="Semua pemberitahuan sistem dan pembaruan rencana kegiatan Anda."
    >
        {#snippet actions()}
            {#if hasUnread}
                <Button
                    variant="outline"
                    size="sm"
                    onclick={markAllAsRead}
                    class="cursor-pointer gap-1.5"
                >
                    <Check class="size-4" />
                    Tandai Semua Dibaca
                </Button>
            {/if}
        {/snippet}
    </PageHeader>

    <div
        class="rounded-xl border border-sidebar-border/30 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-4"
    >
        {#if notifications.data.length === 0}
            <div
                class="p-12 text-center text-sm text-muted-foreground/60 italic space-y-3"
            >
                <Bell class="size-12 text-muted-foreground/30 mx-auto" />
                <p>Belum ada notifikasi yang masuk.</p>
            </div>
        {:else}
            <!-- Notifications List -->
            <div class="divide-y divide-sidebar-border/20">
                {#each notifications.data as item (item.id)}
                    <div
                        class="py-4 flex items-start gap-4 transition-colors relative group first:pt-0 last:pb-0
                            {item.read_at
                            ? 'opacity-60'
                            : 'bg-primary/5 rounded-xl px-4 -mx-4 border-l-2 border-primary'}"
                    >
                        <div class="space-y-1.5 flex-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider
                                    {item.type === 'approval'
                                        ? 'bg-indigo-500/10 text-indigo-600'
                                        : 'bg-zinc-500/10 text-zinc-600'}"
                                >
                                    {item.type}
                                </span>
                                <span
                                    class="text-[10px] text-muted-foreground/75"
                                    >{formatDateTime(item.created_at)}</span
                                >
                            </div>
                            <h4
                                class="text-xs font-bold text-foreground leading-snug"
                            >
                                {item.title}
                            </h4>
                            <p
                                class="text-xs text-muted-foreground leading-relaxed"
                            >
                                {item.message}
                            </p>
                        </div>

                        {#if !item.read_at}
                            <Button
                                variant="ghost"
                                size="icon"
                                onclick={() => markAsRead(item.id)}
                                class="h-8 w-8 text-muted-foreground hover:text-primary cursor-pointer"
                                title="Tandai telah dibaca"
                                aria-label="Tandai notifikasi ini sebagai telah dibaca"
                            >
                                <Check class="size-4" />
                            </Button>
                        {/if}
                    </div>
                {/each}
            </div>

            <!-- Pagination -->
            {#if notifications.links && notifications.links.length > 3}
                <div
                    class="flex items-center justify-center gap-1.5 pt-4 border-t border-sidebar-border/20"
                >
                    {#each notifications.links as link}
                        {#if link.url}
                            <Link
                                href={link.url}
                                class="px-3 py-1.5 text-xs rounded-md border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 transition-colors
                                    {link.active
                                    ? 'bg-primary text-white hover:bg-primary/95 border-primary'
                                    : 'bg-background'}"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span
                                class="px-3 py-1.5 text-xs rounded-md border border-zinc-200/50 dark:border-zinc-800/50 text-muted-foreground/45 bg-zinc-50/20"
                            >
                                {@html link.label}
                            </span>
                        {/if}
                    {/each}
                </div>
            {/if}
        {/if}
    </div>
</div>
