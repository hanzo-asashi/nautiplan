<script lang="ts">
    import type { BreadcrumbItem } from '@/types';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import NotificationBell from '@/components/NotificationBell.svelte';
    import { SidebarTrigger } from '@/components/ui/sidebar';

    let {
        breadcrumbs = [],
    }: {
        breadcrumbs?: BreadcrumbItem[];
    } = $props();
</script>

<header
    class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
>
    <div class="flex items-center gap-2">
        <SidebarTrigger class="-ml-1" />
        {#if breadcrumbs && breadcrumbs.length > 0}
            <Breadcrumbs {breadcrumbs} />
        {/if}
    </div>

    <div class="flex items-center gap-2 mr-2">
        <NotificationBell />
    </div>
</header>
