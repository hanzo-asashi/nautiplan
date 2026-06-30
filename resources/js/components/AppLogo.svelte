<script lang="ts">
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
</script>

<div
    class="flex aspect-square size-9 items-center justify-center rounded-sm bg-gradient-to-tr from-primary to-cyan-400 text-white shadow-lg shadow-primary/20 transition-all duration-300 group-hover:scale-105 group-hover:shadow-primary/30"
>
    <AppLogoIcon
        class="size-5.5 text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.2)] animate-pulse-subtle"
    />
</div>
<div
    class="ml-3 grid flex-1 text-left text-sm group-data-[collapsible=icon]:hidden"
>
    <span
        class="truncate leading-tight font-extrabold tracking-tight text-sidebar-foreground"
    >
        NAUTI<span class="text-primary font-black">PLAN</span>
    </span>
    <span
        class="text-[9px] tracking-wide truncate text-sidebar-foreground/50 font-bold uppercase"
        >Poltekpel Barombong</span
    >
</div>
