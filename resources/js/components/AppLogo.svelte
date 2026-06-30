<script lang="ts">
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
</script>

<div
    class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-white shadow-md shadow-primary/25"
>
    <AppLogoIcon class="size-5 text-white" />
</div>
<div
    class="ml-2 grid flex-1 text-left text-sm group-data-[collapsible=icon]:hidden"
>
    <span
        class="truncate leading-tight font-extrabold tracking-tight text-sidebar-foreground"
        >NautiPlan</span
    >
    <span class="text-[10px] truncate text-sidebar-foreground/60 font-semibold"
        >Poltekpel Barombong</span
    >
</div>
