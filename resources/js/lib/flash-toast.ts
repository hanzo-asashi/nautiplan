import { router } from '@inertiajs/svelte';
import { toast } from 'svelte-sonner';

export function initializeFlashToast(): void {
    router.on('navigate', (event) => {
        const page = (event as CustomEvent).detail?.page;
        const flash = page?.props?.flash as
            { success?: string; error?: string } | undefined;

        if (flash?.success) {
            toast.success(flash.success);
        }

        if (flash?.error) {
            toast.error(flash.error);
        }
    });
}
