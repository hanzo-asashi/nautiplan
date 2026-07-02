<script lang="ts">
    import type { NavItem } from '@/types';
    import { Link } from '@inertiajs/svelte';
    import {
        SidebarGroup,
        SidebarGroupLabel,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';

    let {
        items = [],
        label = 'Platform',
    }: {
        items: NavItem[];
        label?: string;
    } = $props();

    const url = currentUrlState();
</script>

{#if items.length > 0}
    <SidebarGroup class="px-2 py-1">
        <SidebarGroupLabel
            class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/60"
            >{label}</SidebarGroupLabel
        >
        <SidebarMenu>
            {#each items as item (toUrl(item.href))}
                <SidebarMenuItem>
                    <SidebarMenuButton
                        asChild
                        isActive={url.isCurrentUrl(item.href, url.currentUrl)}
                        tooltip={item.title}
                    >
                        {#snippet children(props)}
                            <Link
                                {...props}
                                href={toUrl(item.href)}
                                class={props.class}
                            >
                                {#if item.icon}
                                    <item.icon class="size-4 shrink-0" />
                                {/if}
                                <span class="font-medium text-xs"
                                    >{item.title}</span
                                >
                            </Link>
                        {/snippet}
                    </SidebarMenuButton>
                </SidebarMenuItem>
            {/each}
        </SidebarMenu>
    </SidebarGroup>
{/if}
