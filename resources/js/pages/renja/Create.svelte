<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as renjaIndex } from '@/routes/renja';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Rencana Kerja',
                href: renjaIndex(),
            },
            {
                title: 'Tambah',
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

    let {
        fiscalYears = [],
        renstras = [],
        units = [],
    }: {
        fiscalYears: Array<{ id: number; year: number }>;
        renstras: Array<{ id: number; title: string }>;
        units: Array<{ id: number; name: string; code: string }>;
    } = $props();

    const form = useForm({
        title: '',
        fiscal_year_id: fiscalYears.length > 0 ? fiscalYears[0].id : '',
        renstra_id: '' as any,
        unit_id: units.length > 0 ? units[0].id : '',
        status: 'draft',
        total_budget: 0,
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        form.renstra_id = form.renstra_id === '' ? null : form.renstra_id;
        form.post(toUrl(renjaIndex()));
    }
</script>

<AppHead title="Tambah Rencana Kerja" />

<div class="p-6 space-y-6 w-full">
    <PageHeader
        title="Tambah Rencana Kerja Tahunan"
        description="Buat rencana kerja tahunan baru untuk unit pelaksana"
    >
        {#snippet actions()}
            <Link
                href={toUrl(renjaIndex())}
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
            <!-- Left Column -->
            <div class="space-y-4">
                <div class="space-y-1">
                    <label
                        for="title"
                        class="text-sm font-semibold text-foreground"
                        >Judul Rencana Kerja (Renja)</label
                    >
                    <input
                        type="text"
                        id="title"
                        bind:value={form.title}
                        placeholder="E.g., Rencana Kerja Unit Diklat 2026"
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        required
                    />
                    {#if form.errors.title}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.title}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1">
                    <label
                        for="fiscal_year_id"
                        class="text-sm font-semibold text-foreground"
                        >Tahun Anggaran</label
                    >
                    <select
                        id="fiscal_year_id"
                        bind:value={form.fiscal_year_id}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                        required
                    >
                        {#each fiscalYears as fy}
                            <option value={fy.id}>{fy.year}</option>
                        {/each}
                    </select>
                    {#if form.errors.fiscal_year_id}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.fiscal_year_id}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1">
                    <label
                        for="unit_id"
                        class="text-sm font-semibold text-foreground"
                        >Unit Kerja</label
                    >
                    <select
                        id="unit_id"
                        bind:value={form.unit_id}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                        required
                    >
                        {#each units as unit}
                            <option value={unit.id}
                                >[{unit.code}] {unit.name}</option
                            >
                        {/each}
                    </select>
                    {#if form.errors.unit_id}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.unit_id}
                        </p>
                    {/if}
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <div class="space-y-1">
                    <label
                        for="renstra_id"
                        class="text-sm font-semibold text-foreground"
                        >Kaitan Renstra (Rencana Strategis)</label
                    >
                    <select
                        id="renstra_id"
                        bind:value={form.renstra_id}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                    >
                        <option value="">-- Tanpa Kaitan --</option>
                        {#each renstras as r}
                            <option value={r.id}>{r.title}</option>
                        {/each}
                    </select>
                    {#if form.errors.renstra_id}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.renstra_id}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1">
                    <label
                        for="total_budget"
                        class="text-sm font-semibold text-foreground"
                        >Pagu Anggaran (IDR)</label
                    >
                    <input
                        type="number"
                        id="total_budget"
                        bind:value={form.total_budget}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        min="0"
                        required
                    />
                    {#if form.errors.total_budget}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.total_budget}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1">
                    <label
                        for="status"
                        class="text-sm font-semibold text-foreground"
                        >Status</label
                    >
                    <select
                        id="status"
                        bind:value={form.status}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                        required
                    >
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="revision">Revision</option>
                        <option value="archived">Archived</option>
                    </select>
                    {#if form.errors.status}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.status}
                        </p>
                    {/if}
                </div>
            </div>
        </div>

        <div
            class="flex justify-end gap-3 pt-4 border-t border-sidebar-border/30"
        >
            <Link
                href={toUrl(renjaIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground outline-none cursor-pointer"
            >
                Batal
            </Link>
            <button
                type="submit"
                disabled={form.processing}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-5 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-all cursor-pointer disabled:opacity-50"
            >
                {form.processing ? 'Menyimpan...' : 'Simpan'}
            </button>
        </div>
    </form>
</div>
