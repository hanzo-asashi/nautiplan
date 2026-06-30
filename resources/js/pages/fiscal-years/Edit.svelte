<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as fiscalYearsIndex } from '@/routes/fiscal-years';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Tahun Anggaran',
                href: fiscalYearsIndex(),
            },
            {
                title: 'Edit',
                href: '',
            },
        ],
    };
</script>

<script lang="ts">
    import { useForm, Link } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';
    import { update } from '@/routes/fiscal-years';

    let {
        fiscalYear,
    }: {
        fiscalYear: {
            id: number;
            year: number;
            start_date: string;
            end_date: string;
            is_active: boolean;
            is_locked: boolean;
        };
    } = $props();

    const form = useForm({
        year: fiscalYear.year,
        start_date: fiscalYear.start_date,
        end_date: fiscalYear.end_date,
        is_active: fiscalYear.is_active,
        is_locked: fiscalYear.is_locked,
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        form.put(toUrl(update({ fiscal_year: fiscalYear.id })));
    }
</script>

<AppHead title={`Edit Tahun Anggaran - ${fiscalYear.year}`} />

<div class="p-6 space-y-6 w-full">
    <PageHeader
        title={`Edit Tahun Anggaran: ${fiscalYear.year}`}
        description="Perbarui rentang tanggal aktif dan status kunci anggaran"
    >
        {#snippet actions()}
            <Link
                href={toUrl(fiscalYearsIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5"
            >
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        {/snippet}
    </PageHeader>

    <form
        onsubmit={handleSubmit}
        class="space-y-6 bg-card/45 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm"
    >
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Left Side: Periode -->
            <div class="space-y-4">
                <h3
                    class="text-sm font-bold text-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Periode Anggaran
                </h3>

                <div class="space-y-1">
                    <label
                        for="year"
                        class="text-sm font-semibold text-foreground"
                        >Tahun</label
                    >
                    <input
                        type="number"
                        id="year"
                        bind:value={form.year}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        min="2020"
                        max="2100"
                        required
                    />
                    {#if form.errors.year}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.year}
                        </p>
                    {/if}
                </div>

                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            for="start_date"
                            class="text-sm font-semibold text-foreground"
                            >Tanggal Mulai</label
                        >
                        <input
                            type="date"
                            id="start_date"
                            bind:value={form.start_date}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            required
                        />
                        {#if form.errors.start_date}
                            <p class="text-xs text-rose-500 font-medium">
                                {form.errors.start_date}
                            </p>
                        {/if}
                    </div>

                    <div class="space-y-1">
                        <label
                            for="end_date"
                            class="text-sm font-semibold text-foreground"
                            >Tanggal Selesai</label
                        >
                        <input
                            type="date"
                            id="end_date"
                            bind:value={form.end_date}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            required
                        />
                        {#if form.errors.end_date}
                            <p class="text-xs text-rose-500 font-medium">
                                {form.errors.end_date}
                            </p>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- Right Side: Konfigurasi Status -->
            <div class="space-y-4">
                <h3
                    class="text-sm font-bold text-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Status & Kontrol
                </h3>

                <div class="space-y-1">
                    <label
                        for="is_active"
                        class="text-sm font-semibold text-foreground"
                        >Status Aktif</label
                    >
                    <select
                        id="is_active"
                        bind:value={form.is_active}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                    >
                        <option value={false}>Non-aktif</option>
                        <option value={true}>Aktif</option>
                    </select>
                    {#if form.errors.is_active}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.is_active}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1">
                    <label
                        for="is_locked"
                        class="text-sm font-semibold text-foreground"
                        >Status Kunci</label
                    >
                    <select
                        id="is_locked"
                        bind:value={form.is_locked}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                    >
                        <option value={false}>Terbuka (Bisa diedit)</option>
                        <option value={true}>Terkunci (Belanja beku)</option>
                    </select>
                    {#if form.errors.is_locked}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.is_locked}
                        </p>
                    {/if}
                </div>
            </div>
        </div>

        <div
            class="flex justify-end gap-3 pt-4 border-t border-sidebar-border/30"
        >
            <Link
                href={toUrl(fiscalYearsIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground outline-none cursor-pointer"
            >
                Batal
            </Link>
            <button
                type="submit"
                disabled={form.processing}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-5 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-all cursor-pointer disabled:opacity-50"
            >
                {form.processing ? 'Menyimpan...' : 'Perbarui'}
            </button>
        </div>
    </form>
</div>
