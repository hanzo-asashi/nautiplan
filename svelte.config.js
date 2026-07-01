import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

const config = {
    preprocess: vitePreprocess(),
    onwarn(warning, handler) {
        // Suppress warnings for capturing initial prop values in $state()/useForm()
        // This is intentional behavior with Inertia.js forms and filter initialization
        if (warning.code === 'state_referenced_locally') {
            return;
        }

        // Suppress a11y warnings for checkbox group labels (no single associated control)
        if (warning.code === 'a11y_label_has_associated_control') {
            return;
        }

        handler(warning);
    },
};

export default config;
