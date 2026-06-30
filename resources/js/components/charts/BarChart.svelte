<script lang="ts">
    import { formatRupiah } from '@/lib/utils';

    let {
        data = [],
        title = 'Budget Comparison (Pagu vs Realisasi)',
    }: {
        data: Array<{ label: string; value1: number; value2: number }>;
        title?: string;
    } = $props();

    // Find the max value to scale the bars
    const maxValue = $derived(
        Math.max(...data.flatMap((d) => [d.value1, d.value2]), 1000000),
    );
</script>

<div
    class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm"
>
    <h3 class="text-base font-semibold text-foreground mb-6">{title}</h3>

    <div class="space-y-6">
        {#each data as item}
            <div class="space-y-2">
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-sm gap-1"
                >
                    <span class="font-medium text-foreground max-w-md truncate"
                        >{item.label}</span
                    >
                    <div class="flex space-x-4 text-xs shrink-0">
                        <span class="text-primary font-medium"
                            >Pagu: {formatRupiah(item.value1)}</span
                        >
                        <span
                            class="text-emerald-500 dark:text-emerald-400 font-medium"
                            >Realisasi: {formatRupiah(item.value2)}</span
                        >
                    </div>
                </div>

                <div
                    class="relative h-6 w-full bg-zinc-200/50 dark:bg-zinc-800/50 rounded-full overflow-hidden flex flex-col justify-center border border-sidebar-border/30"
                >
                    <!-- Pagu Bar (Primary Blue) -->
                    <div
                        class="absolute left-0 top-0 h-3 bg-primary/70 transition-all duration-500 rounded-tr"
                        style="width: {(item.value1 / maxValue) * 100}%"
                    ></div>
                    <!-- Realisasi Bar (Emerald Green) -->
                    <div
                        class="absolute left-0 bottom-0 h-3 bg-emerald-500/70 transition-all duration-500 rounded-br"
                        style="width: {(item.value2 / maxValue) * 100}%"
                    ></div>
                </div>
            </div>
        {/each}
    </div>
</div>
