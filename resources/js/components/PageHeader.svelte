<script lang="ts">
    import type { BreadcrumbItem } from '@/types';
    import type { Snippet } from 'svelte';
    import { Link } from '@inertiajs/svelte';

    let {
        title,
        description = '',
        breadcrumbs = [],
        actions,
    }: {
        title: string;
        description?: string;
        breadcrumbs?: BreadcrumbItem[];
        actions?: Snippet;
    } = $props();
</script>

<div
    class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
>
    <div class="space-y-1">
        {#if breadcrumbs.length > 0}
            <nav
                class="flex items-center text-xs font-medium text-muted-foreground mb-1 space-x-1.5"
            >
                {#each breadcrumbs as crumb, i}
                    {#if i > 0}
                        <span class="text-muted-foreground/50">/</span>
                    {/if}
                    {#if crumb.href}
                        <Link
                            href={crumb.href}
                            class="hover:text-foreground transition-colors"
                            >{crumb.title}</Link
                        >
                    {:else}
                        <span class="text-foreground/80 font-normal"
                            >{crumb.title}</span
                        >
                    {/if}
                {/each}
            </nav>
        {/if}
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
            {title}
        </h1>
        {#if description}
            <p class="text-sm text-muted-foreground">{description}</p>
        {/if}
    </div>
    {#if actions}
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            {@render actions()}
        </div>
    {/if}
</div>
