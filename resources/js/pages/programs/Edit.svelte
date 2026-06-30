<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as programIndex } from '@/routes/programs';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Programs',
                href: programIndex(),
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
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';
    import { update } from '@/routes/programs';

    let {
        program,
        renstras = [],
        units = [],
        fiscalYears = [],
    }: {
        program: {
            id: number;
            code: string;
            name: string;
            description: string | null;
            renstra_id: number | null;
            unit_id: number;
            fiscal_year_id: number;
            objective: string | null;
            status: string;
            priority: string;
            start_date: string | null;
            end_date: string | null;
            total_budget: number;
            indicators?: Array<{
                id?: number;
                code: string;
                name: string;
                target_value: number;
                actual_value?: number | null;
                unit_of_measure: string;
            }>;
        };
        renstras: Array<{ id: number; title: string }>;
        units: Array<{ id: number; name: string; code: string }>;
        fiscalYears: Array<{ id: number; year: number }>;
    } = $props();

    const form = useForm({
        code: program.code,
        name: program.name,
        description: program.description || '',
        renstra_id: (program.renstra_id === null
            ? ''
            : program.renstra_id) as any,
        unit_id: program.unit_id,
        fiscal_year_id: program.fiscal_year_id,
        objective: program.objective || '',
        status: program.status,
        priority: program.priority,
        start_date: program.start_date || '',
        end_date: program.end_date || '',
        total_budget: program.total_budget,
        indicators: program.indicators
            ? program.indicators.map((ind) => ({ ...ind }))
            : [],
    });

    function addIndicator() {
        form.indicators = [
            ...form.indicators,
            {
                code: '',
                name: '',
                target_value: 0,
                actual_value: 0,
                unit_of_measure: '',
            },
        ];
    }

    function removeIndicator(index: number) {
        form.indicators = form.indicators.filter((_, i) => i !== index);
    }

    function handleSubmit(e: Event) {
        e.preventDefault();
        form.renstra_id = form.renstra_id === '' ? null : form.renstra_id;
        form.put(toUrl(update({ program: program.id })));
    }
</script>

<AppHead title={`Edit Program - ${program.name}`} />

<div class="p-6 space-y-6 w-full">
    <PageHeader
        title={`Edit Program: ${program.name}`}
        description="Perbarui kode program, status, dan target pencapaian indikator program"
    >
        {#snippet actions()}
            <Link
                href={toUrl(programIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5"
            >
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        {/snippet}
    </PageHeader>

    <form
        onsubmit={handleSubmit}
        class="space-y-6 bg-card/40 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm"
    >
        <!-- General info -->
        <div class="space-y-4">
            <h3
                class="text-sm font-bold text-foreground border-b border-sidebar-border/30 pb-2"
            >
                Informasi Umum
            </h3>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div class="grid gap-4 grid-cols-3">
                        <div class="space-y-1 col-span-1">
                            <label
                                for="code"
                                class="text-sm font-semibold text-foreground"
                                >Kode Program</label
                            >
                            <input
                                type="text"
                                id="code"
                                bind:value={form.code}
                                class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                required
                            />
                            {#if form.errors.code}
                                <p class="text-xs text-rose-500 font-medium">
                                    {form.errors.code}
                                </p>
                            {/if}
                        </div>

                        <div class="space-y-1 col-span-2">
                            <label
                                for="name"
                                class="text-sm font-semibold text-foreground"
                                >Nama Program</label
                            >
                            <input
                                type="text"
                                id="name"
                                bind:value={form.name}
                                class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                required
                            />
                            {#if form.errors.name}
                                <p class="text-xs text-rose-500 font-medium">
                                    {form.errors.name}
                                </p>
                            {/if}
                        </div>
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
                    </div>

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
                            {#each renstras as ren}
                                <option value={ren.id}>{ren.title}</option>
                            {/each}
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label
                            for="description"
                            class="text-sm font-semibold text-foreground"
                            >Deskripsi Program</label
                        >
                        <textarea
                            id="description"
                            bind:value={form.description}
                            placeholder="Deskripsi program utama..."
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary min-h-[100px] transition-all"
                        ></textarea>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label
                            for="total_budget"
                            class="text-sm font-semibold text-foreground"
                            >Pagu Belanja (IDR)</label
                        >
                        <input
                            type="number"
                            id="total_budget"
                            bind:value={form.total_budget}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            min="0"
                            required
                        />
                    </div>

                    <div class="grid gap-4 grid-cols-2">
                        <div class="space-y-1">
                            <label
                                for="priority"
                                class="text-sm font-semibold text-foreground"
                                >Prioritas</label
                            >
                            <select
                                id="priority"
                                bind:value={form.priority}
                                class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                                required
                            >
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
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
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 grid-cols-2">
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
                            />
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
                            />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label
                            for="objective"
                            class="text-sm font-semibold text-foreground"
                            >Tujuan Program</label
                        >
                        <textarea
                            id="objective"
                            bind:value={form.objective}
                            placeholder="Tujuan pencapaian program..."
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary min-h-[100px] transition-all"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicators -->
        <div class="space-y-4 pt-4 border-t border-sidebar-border/30">
            <div class="flex items-center justify-between pb-2">
                <h3 class="text-sm font-bold text-foreground">
                    Indikator Kinerja Program
                </h3>
                <button
                    type="button"
                    onclick={addIndicator}
                    class="inline-flex items-center gap-1.5 text-xs bg-primary/10 hover:bg-primary/15 text-primary px-2.5 py-1.5 rounded-lg font-semibold transition-colors cursor-pointer"
                >
                    <Plus class="size-3.5" />
                    Tambah Indikator
                </button>
            </div>

            {#if form.indicators.length === 0}
                <p
                    class="text-sm text-muted-foreground/60 italic text-center py-4"
                >
                    Belum ada indikator program. Klik "Tambah Indikator" untuk
                    menambahkan.
                </p>
            {:else}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {#each form.indicators as ind, index}
                        <div
                            class="p-4 rounded-lg border border-sidebar-border/40 bg-zinc-50/50 dark:bg-zinc-900/40 space-y-3 shadow-sm hover:border-sidebar-border transition-all"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-foreground"
                                    >Indikator #{index + 1}</span
                                >
                                <button
                                    type="button"
                                    onclick={() => removeIndicator(index)}
                                    class="text-xs text-rose-500 hover:underline cursor-pointer flex items-center gap-1"
                                >
                                    <Trash2 class="size-3.5" />
                                    Hapus
                                </button>
                            </div>

                            <div class="grid gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-semibold text-foreground"
                                        >Kode Indikator</label
                                    >
                                    <input
                                        type="text"
                                        bind:value={ind.code}
                                        placeholder="E.g., IKP-01"
                                        class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                        required
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-semibold text-foreground"
                                        >Nama Indikator</label
                                    >
                                    <input
                                        type="text"
                                        bind:value={ind.name}
                                        placeholder="Nama indikator program..."
                                        class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                        required
                                    />
                                </div>
                                <div class="grid gap-2 grid-cols-2">
                                    <div class="space-y-1">
                                        <label
                                            class="text-xs font-semibold text-foreground"
                                            >Target</label
                                        >
                                        <input
                                            type="number"
                                            bind:value={ind.target_value}
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                            min="0"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <label
                                            class="text-xs font-semibold text-foreground"
                                            >Satuan</label
                                        >
                                        <input
                                            type="text"
                                            bind:value={ind.unit_of_measure}
                                            placeholder="E.g., %"
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                            required
                                        />
                                    </div>
                                </div>
                                {#if ind.id}
                                    <div class="space-y-1">
                                        <label
                                            class="text-xs font-semibold text-foreground"
                                            >Realisasi (Aktual)</label
                                        >
                                        <input
                                            type="number"
                                            bind:value={ind.actual_value}
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                            min="0"
                                        />
                                    </div>
                                {/if}
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- Submit Section -->
        <div
            class="flex justify-end gap-3 border-t border-sidebar-border/30 pt-4"
        >
            <Link
                href={toUrl(programIndex())}
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
