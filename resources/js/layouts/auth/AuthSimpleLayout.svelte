<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { home } from '@/routes';
    import type { Snippet } from 'svelte';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();
</script>

<div
    class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
>
    <div class="w-full max-w-sm">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <Link
                    href={home()}
                    class="flex flex-col items-center gap-3 font-medium group transition-all"
                >
                    <div
                        class="flex aspect-square size-12 items-center justify-center rounded-xl bg-primary text-white shadow-md shadow-primary/25 transition-transform duration-300 group-hover:scale-105"
                    >
                        <AppLogoIcon class="size-7 text-white" />
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <span
                            class="font-extrabold tracking-tight text-foreground text-2xl"
                            >NautiPlan</span
                        >
                        <span
                            class="text-xs text-muted-foreground font-semibold mt-0.5"
                            >Poltekpel Barombong</span
                        >
                    </div>
                    <span class="sr-only">{title}</span>
                </Link>
                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">{title}</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>
            </div>
            {@render children?.()}
        </div>
    </div>
</div>
