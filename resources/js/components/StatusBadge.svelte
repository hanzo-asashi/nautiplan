<script lang="ts">
    let {
        status,
    }: {
        status: string;
    } = $props();

    const normalized = $derived(status?.toLowerCase() || '');

    const badgeStyles = $derived.by(() => {
        switch (normalized) {
            case 'approved':
            case 'active':
            case 'completed':
            case 'success':
            case 'verified':
                return 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20 dark:text-emerald-400 dark:bg-emerald-500/15';
            case 'proposed':
            case 'in_review':
            case 'pending':
                return 'bg-amber-500/10 text-amber-700 border-amber-500/20 dark:text-amber-400 dark:bg-amber-500/15';
            case 'draft':
                return 'bg-zinc-500/10 text-zinc-700 border-zinc-500/20 dark:text-zinc-400 dark:bg-zinc-500/15';
            case 'in_progress':
            case 'running':
                return 'bg-blue-500/10 text-blue-700 border-blue-500/20 dark:text-blue-400 dark:bg-blue-500/15';
            case 'rejected':
            case 'cancelled':
            case 'failed':
            case 'locked':
            case 'archived':
                return 'bg-rose-500/10 text-rose-700 border-rose-500/20 dark:text-rose-400 dark:bg-rose-500/15';
            case 'revision':
                return 'bg-purple-500/10 text-purple-700 border-purple-500/20 dark:text-purple-400 dark:bg-purple-500/15';
            default:
                return 'bg-zinc-500/10 text-zinc-700 border-zinc-500/20 dark:text-zinc-400 dark:bg-zinc-500/15';
        }
    });

    const label = $derived(
        status
            .split('_')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' '),
    );
</script>

<span
    class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-medium transition-colors duration-200 {badgeStyles}"
>
    {label}
</span>
