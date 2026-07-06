<script lang="ts">
    import { router, page } from '@inertiajs/svelte';
    import Bell from 'lucide-svelte/icons/bell';
    import Check from 'lucide-svelte/icons/check';
    import Info from 'lucide-svelte/icons/info';
    import { onDestroy, onMount } from 'svelte';
    import { Button } from '@/components/ui/button';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { toUrl } from '@/lib/utils';
    import notificationsRoute from '@/routes/notifications';

    let notifications = $state<
        Array<{
            id: string;
            title: string;
            message: string;
            type: string;
            read_at: string | null;
            created_at: string;
        }>
    >([]);

    const unreadCount = $derived(
        notifications.filter((n) => !n.read_at).length,
    );

    let pollTimer: ReturnType<typeof setInterval> | null = null;

    async function fetchNotifications() {
        try {
            const url = toUrl(notificationsRoute.stream());
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const data = await response.json();
                if (Array.isArray(data)) {
                    notifications = data;
                }
            }
        } catch {
            // Silently ignore fetch errors
        }
    }

    onMount(() => {
        fetchNotifications();
        pollTimer = setInterval(fetchNotifications, 30000); // Poll every 30 seconds

        // Request browser notification permission
        if (
            typeof window !== 'undefined' &&
            'Notification' in window &&
            Notification.permission === 'default'
        ) {
            Notification.requestPermission();
        }
    });

    onDestroy(() => {
        if (pollTimer) {
            clearInterval(pollTimer);
        }
    });

    function markAsRead(id: string) {
        router.post(
            toUrl(notificationsRoute.read({ notification: id })),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    notifications = notifications.map((n) =>
                        n.id === id
                            ? { ...n, read_at: new Date().toISOString() }
                            : n,
                    );
                },
            },
        );
    }

    function markAllAsRead() {
        router.post(
            toUrl(notificationsRoute.readAll()),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    notifications = notifications.map((n) => ({
                        ...n,
                        read_at: new Date().toISOString(),
                    }));
                },
            },
        );
    }

    function formatTimeAgo(dateStr: string): string {
        try {
            const date = new Date(dateStr);
            const seconds = Math.floor(
                (new Date().getTime() - date.getTime()) / 1000,
            );

            if (seconds < 60) {
                return 'Baru saja';
            }

            const minutes = Math.floor(seconds / 60);

            if (minutes < 60) {
                return `${minutes}m yang lalu`;
            }

            const hours = Math.floor(minutes / 60);

            if (hours < 24) {
                return `${hours}j yang lalu`;
            }

            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
            });
        } catch {
            return '';
        }
    }
</script>

<DropdownMenu>
    <DropdownMenuTrigger asChild>
        {#snippet children(props)}
            <Button
                variant="ghost"
                size="icon"
                class="relative group h-9 w-9 cursor-pointer"
                onclick={props.onclick}
                aria-expanded={props['aria-expanded']}
                data-state={props['data-state']}
                aria-label="Notifikasi, {unreadCount} belum dibaca"
            >
                <Bell
                    class="size-5 opacity-80 group-hover:opacity-100 transition-opacity"
                />
                {#if unreadCount > 0}
                    <span
                        class="absolute top-1.5 right-1.5 size-2 rounded-full bg-rose-500 ring-2 ring-background animate-pulse"
                    ></span>
                {/if}
            </Button>
        {/snippet}
    </DropdownMenuTrigger>

    <DropdownMenuContent
        align="end"
        class="w-80 p-0 overflow-hidden shadow-xl rounded-xl border border-sidebar-border/30 bg-card/95 backdrop-blur-md"
    >
        <!-- Header -->
        <div
            class="p-4 border-b border-sidebar-border/30 flex items-center justify-between"
        >
            <span
                class="text-xs font-bold text-foreground flex items-center gap-1.5"
            >
                <Bell class="size-4 text-primary" />
                Notifikasi
                {#if unreadCount > 0}
                    <span
                        class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-primary text-white leading-none"
                    >
                        {unreadCount} baru
                    </span>
                {/if}
            </span>
            {#if unreadCount > 0}
                <button
                    onclick={markAllAsRead}
                    class="text-[10px] text-primary hover:underline font-semibold cursor-pointer"
                >
                    Tandai semua dibaca
                </button>
            {/if}
        </div>

        <!-- List -->
        <div
            class="max-h-[300px] overflow-y-auto divide-y divide-sidebar-border/20"
        >
            {#if notifications.length === 0}
                <div
                    class="p-8 text-center text-xs text-muted-foreground/60 italic space-y-2"
                >
                    <Info class="size-6 text-muted-foreground/30 mx-auto" />
                    <p>Tidak ada notifikasi baru.</p>
                </div>
            {:else}
                {#each notifications as item (item.id)}
                    <div
                        class="p-3.5 text-left transition-colors flex items-start gap-3 hover:bg-zinc-50/50 dark:hover:bg-zinc-900/20 relative group
                            {item.read_at ? 'opacity-60' : 'bg-primary/5'}"
                    >
                        <div class="space-y-1 flex-1">
                            <h4
                                class="text-[11px] font-bold text-foreground leading-tight"
                            >
                                {item.title}
                            </h4>
                            <p
                                class="text-[10px] text-muted-foreground leading-relaxed"
                            >
                                {item.message}
                            </p>
                            <span
                                class="text-[9px] text-muted-foreground/75 block pt-1"
                                >{formatTimeAgo(item.created_at)}</span
                            >
                        </div>

                        {#if !item.read_at}
                            <button
                                onclick={() => markAsRead(item.id)}
                                class="p-1 rounded-md bg-zinc-100 hover:bg-primary/10 hover:text-primary dark:bg-zinc-800 text-muted-foreground opacity-0 group-hover:opacity-100 transition-all cursor-pointer"
                                title="Tandai telah dibaca"
                                aria-label="Tandai notifikasi ini sebagai telah dibaca"
                            >
                                <Check class="size-3" />
                            </button>
                        {/if}
                    </div>
                {/each}
            {/if}
        </div>
    </DropdownMenuContent>
</DropdownMenu>
