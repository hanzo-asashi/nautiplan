<script lang="ts">
    let {
        open = $bindable(false),
        title = 'Confirm Action',
        description = 'Are you sure you want to perform this action? This cannot be undone.',
        confirmLabel = 'Confirm',
        cancelLabel = 'Cancel',
        onConfirm,
        onCancel,
    }: {
        open: boolean;
        title?: string;
        description?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        onConfirm: () => void;
        onCancel?: () => void;
    } = $props();

    let dialogEl: HTMLDialogElement;

    $effect(() => {
        if (!dialogEl) {
            return;
        }

        if (open) {
            if (!dialogEl.open) {
                dialogEl.showModal();
            }
        } else {
            if (dialogEl.open) {
                dialogEl.close();
            }
        }
    });

    function handleClose() {
        open = false;
        onCancel?.();
    }

    function handleConfirm() {
        open = false;
        onConfirm();
    }
</script>

<dialog
    bind:this={dialogEl}
    onclose={handleClose}
    onclick={(e) => {
        if (e.target === dialogEl) {
            handleClose();
        }
    }}
    class="rounded-xl border border-sidebar-border/50 bg-card/95 backdrop-blur-md p-6 shadow-xl max-w-md w-full text-foreground backdrop:bg-zinc-950/30 backdrop:backdrop-blur-sm outline-none"
>
    <div class="space-y-4">
        <div class="space-y-1.5">
            <h3 class="text-lg font-semibold">{title}</h3>
            <p class="text-sm text-muted-foreground">{description}</p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button
                type="button"
                onclick={handleClose}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground outline-none cursor-pointer"
            >
                {cancelLabel}
            </button>
            <button
                type="button"
                onclick={handleConfirm}
                class="inline-flex h-9 items-center justify-center rounded-md bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 text-sm font-medium transition-colors outline-none cursor-pointer"
            >
                {confirmLabel}
            </button>
        </div>
    </div>
</dialog>
