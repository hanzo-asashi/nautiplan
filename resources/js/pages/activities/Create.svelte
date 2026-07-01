<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as activitiesIndex } from '@/routes/activities';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Activities',
                href: activitiesIndex(),
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
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';

    let {
        programs = [],
        renjas = [],
        units = [],
        fiscalYears = [],
        users = [],
    }: {
        programs: Array<{ id: number; name: string; code: string }>;
        renjas: Array<{ id: number; title: string }>;
        units: Array<{ id: number; name: string; code: string }>;
        fiscalYears: Array<{ id: number; year: number }>;
        users: Array<{ id: number; name: string }>;
    } = $props();

    const form = useForm({
        code: '',
        name: '',
        description: '',
        program_id: programs.length > 0 ? programs[0].id : '',
        renja_id: '' as any,
        unit_id: units.length > 0 ? units[0].id : '',
        fiscal_year_id: fiscalYears.length > 0 ? fiscalYears[0].id : '',
        responsible_user_id: '' as any,
        status: 'draft',
        priority: 'medium',
        start_date: '2026-01-01',
        end_date: '2026-12-31',
        location: '',
        sub_activities: [] as Array<{
            name: string;
            description: string;
            status: string;
            start_date: string;
            end_date: string;
            progress_percentage: number;
            assigned_to: any;
        }>,
        indicators: [] as Array<{
            code: string;
            name: string;
            indicator_type: 'iku' | 'ikk';
            target_value: number;
            actual_value: number | null;
            unit_of_measure: string;
            quarter: string;
        }>,
    });

    function addSubActivity() {
        form.sub_activities = [
            ...form.sub_activities,
            {
                name: '',
                description: '',
                status: 'pending',
                start_date: '2026-01-01',
                end_date: '2026-12-31',
                progress_percentage: 0,
                assigned_to: '',
            },
        ];
    }

    function removeSubActivity(index: number) {
        form.sub_activities = form.sub_activities.filter((_, i) => i !== index);
    }

    function addIndicator() {
        form.indicators = [
            ...form.indicators,
            {
                code: '',
                name: '',
                indicator_type: 'ikk',
                target_value: 0,
                actual_value: null,
                unit_of_measure: 'persen',
                quarter: 'annual',
            },
        ];
    }

    function removeIndicator(index: number) {
        form.indicators = form.indicators.filter((_, i) => i !== index);
    }

    function handleSubmit(e: Event) {
        e.preventDefault();
        form.renja_id = form.renja_id === '' ? null : form.renja_id;
        form.responsible_user_id =
            form.responsible_user_id === '' ? null : form.responsible_user_id;

        // Sanitize sub activities assigned_to
        form.sub_activities = form.sub_activities.map((sub) => ({
            ...sub,
            assigned_to: sub.assigned_to === '' ? null : sub.assigned_to,
        }));

        form.post(toUrl(activitiesIndex()));
    }
</script>

<AppHead title="Tambah Kegiatan" />

<div class="p-6 space-y-6 w-full">
    <PageHeader
        title="Tambah Kegiatan Baru"
        description="Buat kegiatan kerja utama pelaksana beserta penanggung jawab dan rincian sub-kegiatannya"
    >
        {#snippet actions()}
            <Link
                href={toUrl(activitiesIndex())}
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
                Informasi Kegiatan
            </h3>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div class="grid gap-4 grid-cols-3">
                        <div class="space-y-1 col-span-1">
                            <label
                                for="code"
                                class="text-sm font-semibold text-foreground"
                                >Kode Kegiatan</label
                            >
                            <input
                                type="text"
                                id="code"
                                bind:value={form.code}
                                placeholder="E.g., KGT-01"
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
                                >Nama Kegiatan</label
                            >
                            <input
                                type="text"
                                id="name"
                                bind:value={form.name}
                                placeholder="Nama kegiatan utama..."
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
                            for="program_id"
                            class="text-sm font-semibold text-foreground"
                            >Program Utama</label
                        >
                        <select
                            id="program_id"
                            bind:value={form.program_id}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                            required
                        >
                            {#each programs as prog}
                                <option value={prog.id}
                                    >[{prog.code}] {prog.name}</option
                                >
                            {/each}
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label
                            for="renja_id"
                            class="text-sm font-semibold text-foreground"
                            >Kaitan Renja</label
                        >
                        <select
                            id="renja_id"
                            bind:value={form.renja_id}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                        >
                            <option value="">-- Tanpa Kaitan --</option>
                            {#each renjas as ren}
                                <option value={ren.id}>{ren.title}</option>
                            {/each}
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label
                            for="unit_id"
                            class="text-sm font-semibold text-foreground"
                            >Unit Pelaksana</label
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
                            for="description"
                            class="text-sm font-semibold text-foreground"
                            >Deskripsi Kegiatan</label
                        >
                        <textarea
                            id="description"
                            bind:value={form.description}
                            placeholder="Deskripsi rincian kegiatan..."
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary min-h-[100px] transition-all"
                        ></textarea>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label
                            for="responsible_user_id"
                            class="text-sm font-semibold text-foreground"
                            >Penanggung Jawab</label
                        >
                        <select
                            id="responsible_user_id"
                            bind:value={form.responsible_user_id}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                        >
                            <option value="">-- Belum Ditentukan --</option>
                            {#each users as u}
                                <option value={u.id}>{u.name}</option>
                            {/each}
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label
                            for="location"
                            class="text-sm font-semibold text-foreground"
                            >Lokasi</label
                        >
                        <input
                            type="text"
                            id="location"
                            bind:value={form.location}
                            placeholder="E.g., Kampus Barombong"
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
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
                                <option value="proposed">Proposed</option>
                                <option value="approved">Approved</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 grid-cols-2">
                        <div class="space-y-1">
                            <label
                                for="start_date"
                                class="text-sm font-semibold text-foreground font-medium"
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
                                class="text-sm font-semibold text-foreground font-medium"
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
                </div>
            </div>

            <!-- Sub Activities -->
            <div class="space-y-4 pt-4">
                <div
                    class="flex items-center justify-between border-b border-sidebar-border/30 pb-2"
                >
                    <h3 class="text-base font-bold text-foreground">
                        Sub Kegiatan (Rincian Tahapan)
                    </h3>
                    <button
                        type="button"
                        onclick={addSubActivity}
                        class="inline-flex items-center gap-1.5 text-xs bg-primary/10 hover:bg-primary/15 text-primary px-2.5 py-1.5 rounded-lg font-semibold transition-colors cursor-pointer"
                    >
                        <Plus class="size-3.5" />
                        Tambah Sub-Kegiatan
                    </button>
                </div>

                {#if form.sub_activities.length === 0}
                    <p
                        class="text-sm text-muted-foreground/60 italic text-center py-4"
                    >
                        Belum ada rincian tahapan sub-kegiatan. Klik "Tambah
                        Sub-Kegiatan" jika ada.
                    </p>
                {:else}
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {#each form.sub_activities as sub, index}
                            <div
                                class="p-4 rounded-lg border border-sidebar-border/40 bg-zinc-50/50 dark:bg-zinc-900/40 space-y-3 shadow-sm hover:border-sidebar-border transition-all"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-bold text-foreground"
                                        >Tahapan #{index + 1}</span
                                    >
                                    <button
                                        type="button"
                                        onclick={() => removeSubActivity(index)}
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
                                            >Nama Tahapan</label
                                        >
                                        <input
                                            type="text"
                                            bind:value={sub.name}
                                            placeholder="E.g., Rapat awal..."
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <label
                                            class="text-xs font-semibold text-foreground"
                                            >Pelaksana Tugas</label
                                        >
                                        <select
                                            bind:value={sub.assigned_to}
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                                        >
                                            <option value=""
                                                >-- Belum Ditugaskan --</option
                                            >
                                            {#each users as u}
                                                <option value={u.id}
                                                    >{u.name}</option
                                                >
                                            {/each}
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label
                                            class="text-xs font-semibold text-foreground"
                                            >Deskripsi Tugas</label
                                        >
                                        <input
                                            type="text"
                                            bind:value={sub.description}
                                            placeholder="Catatan pengerjaan tahapan..."
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                        />
                                    </div>
                                    <div class="grid gap-2 grid-cols-2">
                                        <div class="space-y-1">
                                            <label
                                                class="text-xs font-semibold text-foreground"
                                                >Status</label
                                            >
                                            <select
                                                bind:value={sub.status}
                                                class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                                                required
                                            >
                                                <option value="pending"
                                                    >Pending</option
                                                >
                                                <option value="in_progress"
                                                    >In Progress</option
                                                >
                                                <option value="completed"
                                                    >Completed</option
                                                >
                                                <option value="cancelled"
                                                    >Cancelled</option
                                                >
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-xs font-semibold text-foreground"
                                                >Tgl Mulai</label
                                            >
                                            <input
                                                type="date"
                                                bind:value={sub.start_date}
                                                class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                            />
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label
                                            class="text-xs font-semibold text-foreground"
                                            >Progress (%)</label
                                        >
                                        <input
                                            type="number"
                                            bind:value={sub.progress_percentage}
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                            min="0"
                                            max="100"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>

            <!-- Indikator Kinerja (IKU/IKK) -->
            <div class="space-y-4 pt-4 border-t border-sidebar-border/20">
                <div
                    class="flex items-center justify-between border-b border-sidebar-border/30 pb-2"
                >
                    <h3 class="text-base font-bold text-foreground">
                        Indikator Kinerja Kegiatan (IKU / IKK)
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

                {#if form.errors.indicators}
                    <p class="text-xs text-rose-500 font-semibold">
                        {form.errors.indicators}
                    </p>
                {/if}

                {#if form.indicators.length === 0}
                    <p
                        class="text-sm text-muted-foreground/60 italic text-center py-4"
                    >
                        Belum ada indikator kinerja. Klik "Tambah Indikator"
                        jika ada.
                    </p>
                {:else}
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {#each form.indicators as ind, index}
                            <div
                                class="p-4 rounded-lg border border-sidebar-border/40 bg-zinc-50/50 dark:bg-zinc-900/40 space-y-3 shadow-sm hover:border-sidebar-border transition-all"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-bold text-foreground"
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
                                            placeholder="E.g., IKK.01"
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
                                            placeholder="E.g., Persentase taruna..."
                                            class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                            required
                                        />
                                    </div>

                                    <div class="grid gap-2 grid-cols-2">
                                        <div class="space-y-1">
                                            <label
                                                class="text-xs font-semibold text-foreground"
                                                >Tipe</label
                                            >
                                            <select
                                                bind:value={ind.indicator_type}
                                                class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                                                required
                                            >
                                                <option value="ikk"
                                                    >IKK (Kegiatan)</option
                                                >
                                                <option value="iku"
                                                    >IKU (Utama)</option
                                                >
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-xs font-semibold text-foreground"
                                                >Periode</label
                                            >
                                            <select
                                                bind:value={ind.quarter}
                                                class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                                                required
                                            >
                                                <option value="annual"
                                                    >Tahunan</option
                                                >
                                                <option value="Q1">Q1</option>
                                                <option value="Q2">Q2</option>
                                                <option value="Q3">Q3</option>
                                                <option value="Q4">Q4</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid gap-2 grid-cols-2">
                                        <div class="space-y-1">
                                            <label
                                                class="text-xs font-semibold text-foreground"
                                                >Target</label
                                            >
                                            <input
                                                type="number"
                                                step="0.01"
                                                bind:value={ind.target_value}
                                                class="w-full px-2.5 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
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
                                                placeholder="persen, dokumen..."
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
                class="flex justify-end gap-3 border-t border-sidebar-border/30 pt-4 mt-6"
            >
                <Link
                    href={toUrl(activitiesIndex())}
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
        </div>
    </form>
</div>
