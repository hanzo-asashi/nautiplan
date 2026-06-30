<script lang="ts">
    import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import type { Snippet } from 'svelte';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<AppLayout {breadcrumbs}>
    {@render children?.()}
</AppLayout>
