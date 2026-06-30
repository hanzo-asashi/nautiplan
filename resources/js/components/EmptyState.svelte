<script lang="ts">
    import FolderOpen from 'lucide-svelte/icons/folder-open';
    import type { Snippet } from 'svelte';

    let {
        title = 'No records found',
        description = 'Get started by creating a new record.',
        icon: Icon = FolderOpen,
        actions,
    }: {
        title?: string;
        description?: string;
        icon?: any;
        actions?: Snippet;
    } = $props();
</script>

<div
    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/50 p-12 text-center bg-card/10 backdrop-blur-sm"
>
    <div
        class="mx-auto flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary"
    >
        <Icon class="size-6" />
    </div>
    <h3 class="mt-4 text-sm font-semibold text-foreground">{title}</h3>
    <p class="mt-2 text-sm text-muted-foreground max-w-sm">{description}</p>
    {#if actions}
        <div class="mt-6">
            {@render actions()}
        </div>
    {/if}
</div>
