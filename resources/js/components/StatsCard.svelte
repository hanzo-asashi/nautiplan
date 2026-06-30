<script lang="ts">
    let {
        title,
        value,
        description = '',
        icon: Icon,
        trend = '',
        trendType = 'neutral',
    }: {
        title: string;
        value: string | number;
        description?: string;
        icon?: any;
        trend?: string;
        trendType?: 'up' | 'down' | 'neutral';
    } = $props();
</script>

<div
    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:border-primary/20"
>
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-muted-foreground">{title}</span>
        {#if Icon}
            <div class="rounded-lg p-2 bg-primary/10 text-primary">
                <Icon class="size-5" />
            </div>
        {/if}
    </div>

    <div class="mt-4">
        <span class="text-3xl font-extrabold tracking-tight text-foreground"
            >{value}</span
        >

        {#if trend || description}
            <div class="mt-2 flex items-center gap-2">
                {#if trend}
                    <span
                        class="text-xs font-semibold px-1.5 py-0.5 rounded-full
                        {trendType === 'up'
                            ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
                            : ''}
                        {trendType === 'down'
                            ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400'
                            : ''}
                        {trendType === 'neutral'
                            ? 'bg-zinc-500/15 text-zinc-600 dark:text-zinc-400'
                            : ''}
                    "
                    >
                        {trend}
                    </span>
                {/if}
                {#if description}
                    <span class="text-xs text-muted-foreground"
                        >{description}</span
                    >
                {/if}
            </div>
        {/if}
    </div>
</div>
