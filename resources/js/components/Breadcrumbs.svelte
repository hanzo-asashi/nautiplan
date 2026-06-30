<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

    let {
        breadcrumbs = [],
    }: {
        breadcrumbs: BreadcrumbItemType[];
    } = $props();
</script>

<nav
    aria-label="breadcrumb"
    class="text-sm font-medium text-zinc-500 dark:text-zinc-400"
>
    <ol class="flex flex-wrap items-center gap-2">
        {#each breadcrumbs as item, index (item.href || index)}
            <li class="inline-flex items-center gap-2">
                {#if index === breadcrumbs.length - 1}
                    <span class="text-zinc-900 dark:text-zinc-100 font-semibold"
                        >{item.title}</span
                    >
                {:else}
                    <Link
                        href={item.href}
                        class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors"
                    >
                        {item.title}
                    </Link>
                {/if}
            </li>
            {#if index !== breadcrumbs.length - 1}
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
            {/if}
        {/each}
    </ol>
</nav>
