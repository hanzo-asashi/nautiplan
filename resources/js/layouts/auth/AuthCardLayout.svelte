<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { home } from '@/routes';

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
    class="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10"
>
    <div class="flex w-full max-w-md flex-col gap-6">
        <Link
            href={home()}
            class="flex flex-col items-center gap-3 self-center font-medium group transition-all"
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
                <span class="text-xs text-muted-foreground font-semibold mt-0.5"
                    >Poltekpel Barombong</span
                >
            </div>
        </Link>

        <div class="flex flex-col gap-6">
            <Card class="rounded-xl">
                <CardHeader class="px-10 pt-8 pb-0 text-center">
                    <CardTitle class="text-xl">{title}</CardTitle>
                    <CardDescription>
                        {description}
                    </CardDescription>
                </CardHeader>
                <CardContent class="px-10 py-8">
                    {@render children?.()}
                </CardContent>
            </Card>
        </div>
    </div>
</div>
