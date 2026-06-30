<script lang="ts">
    import { Link, useForm } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';
    import { index as renstraIndex, update } from '@/routes/renstra';

    let {
        renstra,
    }: {
        renstra: {
            id: number;
            title: string;
            start_year: number;
            end_year: number;
            vision: string;
            mission: string[];
            description?: string;
            status: string;
            indicators: Array<{
                id?: number;
                code: string;
                name: string;
                baseline_value: number;
                target_value: number;
                unit_of_measure: string;
            }>;
        };
    } = $props();

    const form = useForm({
        title: renstra.title,
        start_year: renstra.start_year,
        end_year: renstra.end_year,
        vision: renstra.vision || '',
        mission:
            renstra.mission && renstra.mission.length > 0
                ? [...renstra.mission]
                : [''],
        status: renstra.status,
        description: renstra.description || '',
        indicators: renstra.indicators
            ? renstra.indicators.map((ind) => ({
                  code: ind.code,
                  name: ind.name,
                  baseline_value: ind.baseline_value,
                  target_value: ind.target_value,
                  unit_of_measure: ind.unit_of_measure,
              }))
            : [],
    });

    function addMission() {
        form.mission = [...form.mission, ''];
    }

    function removeMission(index: number) {
        form.mission = form.mission.filter((_, i) => i !== index);

        if (form.mission.length === 0) {
            form.mission = [''];
        }
    }

    function addIndicator() {
        form.indicators = [
            ...form.indicators,
            {
                code: '',
                name: '',
                target_value: 0,
                unit_of_measure: '',
                baseline_value: 0,
            },
        ];
    }

    function removeIndicator(index: number) {
        form.indicators = form.indicators.filter((_, i) => i !== index);
    }

    function handleSubmit(e: Event) {
        e.preventDefault();
        form.mission = form.mission.filter((m) => m.trim() !== '');
        form.put(toUrl(update({ renstra: renstra.id })));
    }
</script>

<AppHead title="Edit Renstra" />

<div class="p-6 space-y-6 w-full">
    <PageHeader
        title="Edit Rencana Strategis (Renstra)"
        description="Perbarui informasi visi, misi, dan indikator strategis"
    >
        {#snippet actions()}
            <Link
                href={toUrl(renstraIndex())}
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
        <!-- General Info Section -->
        <div class="space-y-4">
            <h3
                class="text-sm font-bold text-foreground border-b border-sidebar-border/30 pb-2"
            >
                Informasi Umum
            </h3>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="space-y-1 md:col-span-4">
                    <label
                        for="title"
                        class="text-sm font-semibold text-foreground"
                        >Judul Renstra</label
                    >
                    <input
                        type="text"
                        id="title"
                        bind:value={form.title}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        required
                    />
                    {#if form.errors.title}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.title}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1 col-span-1">
                    <label
                        for="start_year"
                        class="text-sm font-semibold text-foreground"
                        >Tahun Mulai</label
                    >
                    <input
                        type="number"
                        id="start_year"
                        bind:value={form.start_year}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        min="2020"
                        required
                    />
                    {#if form.errors.start_year}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.start_year}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1 col-span-1">
                    <label
                        for="end_year"
                        class="text-sm font-semibold text-foreground"
                        >Tahun Selesai</label
                    >
                    <input
                        type="number"
                        id="end_year"
                        bind:value={form.end_year}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        min="2020"
                        required
                    />
                    {#if form.errors.end_year}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.end_year}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1 col-span-2">
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
                        <option value="archived">Archived</option>
                    </select>
                    {#if form.errors.status}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.status}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1 md:col-span-4">
                    <label
                        for="description"
                        class="text-sm font-semibold text-foreground"
                        >Deskripsi / Catatan</label
                    >
                    <textarea
                        id="description"
                        bind:value={form.description}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary min-h-[80px] transition-all"
                    ></textarea>
                    {#if form.errors.description}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.description}
                        </p>
                    {/if}
                </div>
            </div>
        </div>

        <!-- Vision & Mission Section -->
        <div class="space-y-4 pt-4 border-t border-sidebar-border/30">
            <h3 class="text-sm font-bold text-foreground pb-1">Visi & Misi</h3>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Visi Utama -->
                <div class="space-y-1">
                    <label
                        for="vision"
                        class="text-sm font-semibold text-foreground"
                        >Visi Utama</label
                    >
                    <textarea
                        id="vision"
                        bind:value={form.vision}
                        placeholder="Masukkan visi instansi..."
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary min-h-[140px] transition-all"
                    ></textarea>
                    {#if form.errors.vision}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.vision}
                        </p>
                    {/if}
                </div>

                <!-- Misi Instansi -->
                <div class="space-y-2 flex flex-col">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-semibold text-foreground"
                            >Misi Instansi</label
                        >
                        <button
                            type="button"
                            onclick={addMission}
                            class="inline-flex items-center gap-1 text-xs text-primary font-semibold hover:underline cursor-pointer"
                        >
                            <Plus class="size-3" />
                            Tambah Misi
                        </button>
                    </div>

                    <div class="space-y-2 overflow-y-auto max-h-[160px] pr-1">
                        {#each form.mission as _, index}
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-sm font-semibold text-muted-foreground w-4 text-right"
                                    >{index + 1}.</span
                                >
                                <input
                                    type="text"
                                    bind:value={form.mission[index]}
                                    placeholder={`Misi ke-${index + 1}`}
                                    class="flex-1 px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                    required
                                />
                                <button
                                    type="button"
                                    onclick={() => removeMission(index)}
                                    class="p-2 text-rose-500 hover:bg-rose-500/10 rounded-lg transition-colors cursor-pointer"
                                    title="Hapus Misi"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        {/each}
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicators Section -->
        <div class="space-y-4 pt-4 border-t border-sidebar-border/30">
            <div class="flex items-center justify-between pb-2">
                <h3 class="text-sm font-bold text-foreground">
                    Indikator Sasaran Strategis
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
                    Belum ada indikator sasaran strategis. Klik "Tambah
                    Indikator" untuk menambahkan.
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
                                        placeholder="E.g., IKU-01"
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
                                        placeholder="Nama indikator..."
                                        class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                        required
                                    />
                                </div>
                                <div class="grid gap-2 grid-cols-3">
                                    <div class="space-y-1">
                                        <label
                                            class="text-xs font-semibold text-foreground"
                                            >Baseline</label
                                        >
                                        <input
                                            type="number"
                                            bind:value={ind.baseline_value}
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                            min="0"
                                            required
                                        />
                                    </div>
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
                                            placeholder="%, unit, dll."
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                            required
                                        />
                                    </div>
                                </div>
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
                href={toUrl(renstraIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground outline-none cursor-pointer"
            >
                Batal
            </Link>
            <button
                type="submit"
                disabled={form.processing}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-5 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-all cursor-pointer disabled:opacity-50"
            >
                {form.processing ? 'Menyimpan...' : 'Perbarui Renstra'}
            </button>
        </div>
    </form>
</div>
