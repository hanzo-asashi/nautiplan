<script lang="ts">
    let {
        data = [],
        title = 'Status Breakdown',
    }: {
        data: Array<{ label: string; value: number; color: string }>;
        title?: string;
    } = $props();

    const total = $derived(data.reduce((sum, item) => sum + item.value, 0));

    // Compute cumulative percentages for SVG dash arrays
    const slices = $derived.by(() => {
        let accumulatedPercent = 0;

        return data.map((item) => {
            const percent = total > 0 ? (item.value / total) * 100 : 0;
            const start = accumulatedPercent;
            accumulatedPercent += percent;

            return {
                ...item,
                percent,
                dashArray: `${percent} ${100 - percent}`,
                dashOffset: 100 - start + 25, // offset by 25 to start at top (12 o'clock)
            };
        });
    });
</script>

<div
    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm flex flex-col justify-between"
>
    <h3 class="text-base font-semibold text-foreground mb-4">{title}</h3>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
        <!-- SVG Donut Chart -->
        <div class="relative size-32 shrink-0">
            <svg viewBox="0 0 42 42" class="size-full">
                <!-- Background track -->
                <circle
                    cx="21"
                    cy="21"
                    r="15.915"
                    fill="transparent"
                    class="stroke-zinc-200 dark:stroke-zinc-800"
                    stroke-width="4.2"
                ></circle>

                {#each slices as slice}
                    {#if slice.percent > 0}
                        <circle
                            cx="21"
                            cy="21"
                            r="15.915"
                            fill="transparent"
                            stroke={slice.color}
                            stroke-width="4.2"
                            stroke-dasharray={slice.dashArray}
                            stroke-dashoffset={slice.dashOffset}
                            class="transition-all duration-500"
                        ></circle>
                    {/if}
                {/each}
            </svg>
            <div
                class="absolute inset-0 flex flex-col items-center justify-center"
            >
                <span class="text-2xl font-bold text-foreground">{total}</span>
                <span
                    class="text-[10px] uppercase tracking-wider text-muted-foreground"
                    >Total</span
                >
            </div>
        </div>

        <!-- Legend -->
        <div class="flex-1 w-full space-y-2">
            {#each data as item}
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <div
                            class="size-3 rounded-full"
                            style="background-color: {item.color}"
                        ></div>
                        <span class="text-muted-foreground font-medium"
                            >{item.label}</span
                        >
                    </div>
                    <span class="font-bold text-foreground">{item.value}</span>
                </div>
            {/each}
        </div>
    </div>
</div>
