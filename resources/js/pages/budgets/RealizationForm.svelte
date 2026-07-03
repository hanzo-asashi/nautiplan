<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as budgetsIndex } from '@/routes/budgets';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Anggaran & Realisasi',
                href: budgetsIndex(),
            },
            {
                title: 'Catat Realisasi',
                href: '',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, useForm } from '@inertiajs/svelte';
    import AlertTriangle from 'lucide-svelte/icons/alert-triangle';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import Plus from 'lucide-svelte/icons/plus';
    import Save from 'lucide-svelte/icons/save';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { formatRupiah, toUrl } from '@/lib/utils';
    import { store as storeReal } from '@/routes/budgets/realizations';

    let {
        budget,
        vendors = [],
        officers = [],
    }: {
        budget: any;
        vendors: Array<any>;
        officers: Array<any>;
    } = $props();

    const form = useForm({
        activity_budget_id: budget.id.toString(),
        realization_type: 'non_pengadaan',
        amount: 0,
        realization_date: new Date().toISOString().split('T')[0],
        description: budget.description || '',
        receipt_number: '',
        // Dokumen pencairan
        bast_number: '',
        bast_date: '',
        bap_number: '',
        bap_date: '',
        ba_penyerahan_number: '',
        ba_penyerahan_date: '',
        sp2d_number: '',
        sp2d_date: '',
        spp_number: '',
        spp_date: '',
        spm_number: '',
        spm_date: '',
        sptjb_number: '',
        sptjb_date: '',
        // Pengadaan
        procurement_type: 'surat_pesanan',
        procurement_title: budget.description || '',
        procurement_number: '',
        procurement_date: '',
        work_duration: '5 (lima) Hari Kalender',
        nota_dinas_number: '',
        nota_dinas_date: '',
        ba_pl_number: '',
        ba_pl_date: '',
        ppk_id:
            officers
                .find((o) => o.name.toLowerCase().includes('arnaldy'))
                ?.id?.toString() || '',
        kpa_id:
            officers
                .find((o) => o.name.toLowerCase().includes('sidrotul'))
                ?.id?.toString() || '',
        // Vendor
        vendor_name: '',
        vendor_npwp: '',
        vendor_address: '',
        bank_name: '',
        bank_account_number: '',
        bank_account_name: '',
        // Items list
        items: [
            {
                name: budget.description || '',
                volume: 1,
                unit: 'Paket',
                unit_price: 0,
                tax_pph21: 0,
                tax_pph21_mixed: false,
                tax_pph22: 0,
                tax_pph23: 0,
                tax_ppn: 0,
                remarks: '',
            },
        ] as Array<{
            name: string;
            volume: number;
            unit: string;
            unit_price: number;
            tax_pph21: number;
            tax_pph21_mixed: boolean;
            tax_pph22: number;
            tax_pph23: number;
            tax_ppn: number;
            remarks: string;
        }>,
    });

    const remainingBudgetBefore = $derived(
        budget.amount -
            budget.realizations.reduce(
                (sum: number, r: any) => sum + Number(r.amount),
                0,
            ),
    );

    const remainingBudgetAfter = $derived(remainingBudgetBefore - form.amount);

    function addItem() {
        form.items = [
            ...form.items,
            {
                name: '',
                volume: 1,
                unit: 'Pcs',
                unit_price: 0,
                tax_pph21: 0,
                tax_pph21_mixed: false,
                tax_pph22: 0,
                tax_pph23: 0,
                tax_ppn: 0,
                remarks: '',
            },
        ];
        calculateTotal();
    }

    function removeItem(index: number) {
        form.items = form.items.filter((_, i) => i !== index);
        calculateTotal();
    }

    function calculateTotal() {
        form.amount = form.items.reduce(
            (sum, item) => sum + Number(item.volume) * Number(item.unit_price),
            0,
        );
    }

    function handleVendorSelect(e: Event) {
        const target = e.target as HTMLSelectElement;
        const selected = vendors.find((v) => v.id.toString() === target.value);

        if (selected) {
            form.vendor_name = selected.name;
            form.vendor_npwp = selected.npwp || '';
            form.vendor_address = selected.address || '';
            form.bank_name = selected.bank_name || '';
            form.bank_account_number = selected.bank_account_number || '';
            form.bank_account_name = selected.bank_account_name || '';
        }
    }

    function handleSubmit(e: Event) {
        e.preventDefault();

        if (form.amount > remainingBudgetBefore + 1) {
            alert(
                'Peringatan: Nominal realisasi belanja melebihi sisa pagu anggaran yang tersedia!',
            );

            return;
        }

        form.post(toUrl(storeReal()));
    }
</script>

<AppHead title="Catat Realisasi Belanja" />

<div class="p-6 space-y-6 w-full max-w-[1600px] mx-auto">
    <!-- Header -->
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >
        <div class="flex items-center gap-3">
            <Link
                href={budgetsIndex()}
                class="p-2 border border-zinc-200 dark:border-zinc-800 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors text-muted-foreground hover:text-foreground cursor-pointer"
            >
                <ArrowLeft class="w-4 h-4" />
            </Link>
            <PageHeader
                title="Catat Realisasi Belanja"
                description="Input data transaksi realisasi belanja lengkap beserta rincian barang/jasa, perpajakan, kontrak pengadaan, dan dokumen pencairan."
            />
        </div>
    </div>

    <!-- Main Form Grid -->
    <form
        onsubmit={handleSubmit}
        class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start"
    >
        <!-- Left Column: Form Fields (Spans 2 columns) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Section 1: Informasi Dasar -->
            <div
                class="bg-card/45 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm space-y-4"
            >
                <h3
                    class="text-sm font-bold text-foreground flex items-center gap-2 border-b border-sidebar-border/20 pb-2"
                >
                    <span class="p-1 bg-primary/10 text-primary rounded"
                        >📝</span
                    >
                    Informasi Realisasi Dasar
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label
                            for="realization_type"
                            class="text-xs font-bold text-foreground"
                            >Tipe Realisasi <span class="text-rose-500">*</span
                            ></label
                        >
                        <select
                            id="realization_type"
                            bind:value={form.realization_type}
                            class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer font-medium"
                            required
                        >
                            <option value="non_pengadaan"
                                >Non-Pengadaan (Swakelola / LS Non-Pihak Ketiga)</option
                            >
                            <option value="surat_pesanan"
                                >Surat Pesanan / SPK (Pihak Ketiga / Vendor)</option
                            >
                        </select>
                        {#if form.errors.realization_type}
                            <p class="text-[10px] text-rose-500 font-medium">
                                {form.errors.realization_type}
                            </p>
                        {/if}
                    </div>

                    <div class="space-y-1.5">
                        <label
                            for="realization_date"
                            class="text-xs font-bold text-foreground"
                            >Tanggal Realisasi <span class="text-rose-500"
                                >*</span
                            ></label
                        >
                        <input
                            id="realization_date"
                            type="date"
                            bind:value={form.realization_date}
                            class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            required
                        />
                        {#if form.errors.realization_date}
                            <p class="text-[10px] text-rose-500 font-medium">
                                {form.errors.realization_date}
                            </p>
                        {/if}
                    </div>

                    <div class="space-y-1.5">
                        <label
                            for="receipt_number"
                            class="text-xs font-bold text-foreground"
                            >Nomor Bukti Kuitansi / Receipt</label
                        >
                        <input
                            id="receipt_number"
                            type="text"
                            bind:value={form.receipt_number}
                            placeholder="E.g., KUITANSI-102"
                            class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                        />
                        {#if form.errors.receipt_number}
                            <p class="text-[10px] text-rose-500 font-medium">
                                {form.errors.receipt_number}
                            </p>
                        {/if}
                    </div>

                    <div class="space-y-1.5">
                        <label
                            for="description"
                            class="text-xs font-bold text-foreground"
                            >Deskripsi Ringkas Belanja <span
                                class="text-rose-500">*</span
                            ></label
                        >
                        <input
                            id="description"
                            type="text"
                            bind:value={form.description}
                            placeholder="E.g., Pengadaan Bahan Habis Pakai Diklat..."
                            class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            required
                        />
                        {#if form.errors.description}
                            <p class="text-[10px] text-rose-500 font-medium">
                                {form.errors.description}
                            </p>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- Section 2: Rincian Barang / Jasa -->
            <div
                class="bg-card/45 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm space-y-4"
            >
                <div
                    class="flex items-center justify-between border-b border-sidebar-border/20 pb-2"
                >
                    <h3
                        class="text-sm font-bold text-foreground flex items-center gap-2"
                    >
                        <span
                            class="p-1 bg-emerald-500/10 text-emerald-600 rounded"
                            >🛍️</span
                        >
                        Daftar Rincian Barang / Jasa & Perpajakan
                    </h3>
                    <button
                        type="button"
                        onclick={addItem}
                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        Tambah Item
                    </button>
                </div>

                <!-- Responsive Items List -->
                <div class="space-y-4">
                    {#each form.items as item, index}
                        <div
                            class="p-4 bg-zinc-50/50 dark:bg-zinc-950/20 rounded-xl border border-zinc-150 dark:border-zinc-800/80 space-y-3"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <span
                                    class="text-xs font-bold text-muted-foreground bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded-md"
                                >
                                    Item #{index + 1}
                                </span>
                                {#if form.items.length > 1}
                                    <button
                                        type="button"
                                        onclick={() => removeItem(index)}
                                        class="text-rose-500 hover:text-rose-600 p-1 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-md transition-colors cursor-pointer"
                                        title="Hapus Item"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                {/if}
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <!-- Name -->
                                <div class="md:col-span-2 space-y-1">
                                    <label
                                        class="text-[10px] font-bold text-foreground"
                                        >Nama Barang / Jasa <span
                                            class="text-rose-500">*</span
                                        ></label
                                    >
                                    <input
                                        type="text"
                                        bind:value={item.name}
                                        placeholder="E.g., Kertas HVS A4 80gr"
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                        required
                                    />
                                </div>
                                <!-- Volume -->
                                <div class="space-y-1">
                                    <label
                                        class="text-[10px] font-bold text-foreground"
                                        >Volume <span class="text-rose-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        type="number"
                                        bind:value={item.volume}
                                        oninput={calculateTotal}
                                        min="0.01"
                                        step="any"
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium text-center"
                                        required
                                    />
                                </div>
                                <!-- Unit -->
                                <div class="space-y-1">
                                    <label
                                        class="text-[10px] font-bold text-foreground"
                                        >Satuan <span class="text-rose-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        type="text"
                                        bind:value={item.unit}
                                        placeholder="E.g., Rim / Pcs / Paket"
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium text-center"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <!-- Unit Price -->
                                <div class="space-y-1">
                                    <label
                                        class="text-[10px] font-bold text-foreground"
                                        >Harga Satuan (Rp) <span
                                            class="text-rose-500">*</span
                                        ></label
                                    >
                                    <input
                                        type="number"
                                        bind:value={item.unit_price}
                                        oninput={calculateTotal}
                                        min="0"
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-bold text-right"
                                        required
                                    />
                                </div>

                                <!-- Remarks -->
                                <div class="md:col-span-3 space-y-1">
                                    <label
                                        class="text-[10px] font-bold text-foreground"
                                        >Spesifikasi / Keterangan Tambahan</label
                                    >
                                    <input
                                        type="text"
                                        bind:value={item.remarks}
                                        placeholder="E.g., Merk Sinar Dunia, Warna Putih"
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                    />
                                </div>
                            </div>

                            <!-- Taxes Grid -->
                            <div
                                class="bg-background border border-zinc-200 dark:border-zinc-800/80 p-3 rounded-lg space-y-2"
                            >
                                <span
                                    class="text-[10px] font-bold text-primary block border-b border-zinc-150 dark:border-zinc-800 pb-1"
                                >
                                    Perpajakan (%)
                                </span>
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-4 gap-2"
                                >
                                    <div class="space-y-0.5">
                                        <label
                                            class="text-[9px] font-bold text-muted-foreground block"
                                            >PPN</label
                                        >
                                        <input
                                            type="number"
                                            bind:value={item.tax_ppn}
                                            class="w-full px-2 py-1 bg-background border border-zinc-200 dark:border-zinc-800 rounded-md text-[11px] text-right font-semibold"
                                            placeholder="0"
                                        />
                                    </div>
                                    <div class="space-y-0.5">
                                        <label
                                            class="text-[9px] font-bold text-muted-foreground block"
                                            >PPh 21</label
                                        >
                                        <input
                                            type="number"
                                            bind:value={item.tax_pph21}
                                            class="w-full px-2 py-1 bg-background border border-zinc-200 dark:border-zinc-800 rounded-md text-[11px] text-right font-semibold"
                                            placeholder="0"
                                        />
                                    </div>
                                    <div class="space-y-0.5">
                                        <label
                                            class="text-[9px] font-bold text-muted-foreground block"
                                            >PPh 22</label
                                        >
                                        <input
                                            type="number"
                                            bind:value={item.tax_pph22}
                                            class="w-full px-2 py-1 bg-background border border-zinc-200 dark:border-zinc-800 rounded-md text-[11px] text-right font-semibold"
                                            placeholder="0"
                                        />
                                    </div>
                                    <div class="space-y-0.5">
                                        <label
                                            class="text-[9px] font-bold text-muted-foreground block"
                                            >PPh 23</label
                                        >
                                        <input
                                            type="number"
                                            bind:value={item.tax_pph23}
                                            class="w-full px-2 py-1 bg-background border border-zinc-200 dark:border-zinc-800 rounded-md text-[11px] text-right font-semibold"
                                            placeholder="0"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/each}
                </div>

                <!-- Sub-total Card -->
                <div
                    class="flex items-center justify-between p-4 bg-zinc-100 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-850 font-bold text-xs"
                >
                    <span class="text-muted-foreground"
                        >Total Nilai Rincian (Dihitung Otomatis):</span
                    >
                    <span class="text-lg font-extrabold text-foreground"
                        >{formatRupiah(form.amount)}</span
                    >
                </div>
            </div>

            {#if form.realization_type === 'surat_pesanan'}
                <!-- Section 3: Informasi Kontrak & BAST (Only if Vendor type) -->
                <div
                    class="bg-card/45 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm space-y-4"
                >
                    <h3
                        class="text-sm font-bold text-foreground flex items-center gap-2 border-b border-sidebar-border/20 pb-2"
                    >
                        <span class="p-1 bg-primary/10 text-primary rounded"
                            >🛠️</span
                        >
                        Dokumen Kontrak & Serah Terima Pekerjaan
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label
                                for="procurement_type"
                                class="text-xs font-bold text-foreground"
                                >Tipe Dokumen Kontrak <span
                                    class="text-rose-500">*</span
                                ></label
                            >
                            <select
                                id="procurement_type"
                                bind:value={form.procurement_type}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer font-medium"
                                required
                            >
                                <option value="surat_pesanan"
                                    >Surat Pesanan (SP)</option
                                >
                                <option value="spk"
                                    >Surat Perintah Kerja (SPK)</option
                                >
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="procurement_title"
                                class="text-xs font-bold text-foreground"
                                >Nama Paket Pekerjaan <span
                                    class="text-rose-500">*</span
                                ></label
                            >
                            <input
                                id="procurement_title"
                                type="text"
                                bind:value={form.procurement_title}
                                placeholder="Nama paket pengadaan..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                required
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="procurement_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor SP / SPK <span class="text-rose-500"
                                    >*</span
                                ></label
                            >
                            <input
                                id="procurement_number"
                                type="text"
                                bind:value={form.procurement_number}
                                placeholder="PL.107/67/7/POLTEKPEL..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                required
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="procurement_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal SP / SPK <span class="text-rose-500"
                                    >*</span
                                ></label
                            >
                            <input
                                id="procurement_date"
                                type="date"
                                bind:value={form.procurement_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                required
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="work_duration"
                                class="text-xs font-bold text-foreground"
                                >Waktu Pelaksanaan Pekerjaan</label
                            >
                            <input
                                id="work_duration"
                                type="text"
                                bind:value={form.work_duration}
                                placeholder="E.g., 5 (lima) Hari Kalender"
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="nota_dinas_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor Nota Dinas PPK</label
                            >
                            <input
                                id="nota_dinas_number"
                                type="text"
                                bind:value={form.nota_dinas_number}
                                placeholder="ND/PPK/..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="nota_dinas_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal Nota Dinas</label
                            >
                            <input
                                id="nota_dinas_date"
                                type="date"
                                bind:value={form.nota_dinas_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="ba_pl_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor BA Pengadaan Langsung (PL)</label
                            >
                            <input
                                id="ba_pl_number"
                                type="text"
                                bind:value={form.ba_pl_number}
                                placeholder="BA-PL/..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="ba_pl_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal BA Pengadaan Langsung</label
                            >
                            <input
                                id="ba_pl_date"
                                type="date"
                                bind:value={form.ba_pl_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-sidebar-border/20 pt-4 mt-2"
                    >
                        <div class="space-y-1.5">
                            <label
                                for="bast_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor BAST Barang/Jasa</label
                            >
                            <input
                                id="bast_number"
                                type="text"
                                bind:value={form.bast_number}
                                placeholder="PL.109/57/22/POLTEKPEL..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="bast_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal BAST</label
                            >
                            <input
                                id="bast_date"
                                type="date"
                                bind:value={form.bast_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="bap_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor BA Pembayaran (BAP)</label
                            >
                            <input
                                id="bap_number"
                                type="text"
                                bind:value={form.bap_number}
                                placeholder="BAP/XXX/..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="bap_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal BAP</label
                            >
                            <input
                                id="bap_date"
                                type="date"
                                bind:value={form.bap_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="ba_penyerahan_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor BA Penyerahan</label
                            >
                            <input
                                id="ba_penyerahan_number"
                                type="text"
                                bind:value={form.ba_penyerahan_number}
                                placeholder="BA-PENYERAHAN/..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="ba_penyerahan_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal BA Penyerahan</label
                            >
                            <input
                                id="ba_penyerahan_date"
                                type="date"
                                bind:value={form.ba_penyerahan_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 4: Informasi Vendor & Pejabat (Only if Vendor type) -->
                <div
                    class="bg-card/45 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm space-y-4"
                >
                    <h3
                        class="text-sm font-bold text-foreground flex items-center gap-2 border-b border-sidebar-border/20 pb-2"
                    >
                        <span class="p-1 bg-primary/10 text-primary rounded"
                            >🏢</span
                        >
                        Pejabat & Informasi Penyedia (Vendor)
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label
                                for="kpa_id"
                                class="text-xs font-bold text-foreground"
                                >Kuasa Pengguna Anggaran (KPA)</label
                            >
                            <select
                                id="kpa_id"
                                bind:value={form.kpa_id}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer font-medium"
                            >
                                <option value="">-- Pilih KPA --</option>
                                {#each officers as off}
                                    <option value={off.id.toString()}
                                        >{off.name} (NIP: {off.employee_id})</option
                                    >
                                {/each}
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="ppk_id"
                                class="text-xs font-bold text-foreground"
                                >Pejabat Pembuat Komitmen (PPK)</label
                            >
                            <select
                                id="ppk_id"
                                bind:value={form.ppk_id}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer font-medium"
                            >
                                <option value="">-- Pilih PPK --</option>
                                {#each officers as off}
                                    <option value={off.id.toString()}
                                        >{off.name} (NIP: {off.employee_id})</option
                                    >
                                {/each}
                            </select>
                        </div>
                    </div>

                    <!-- Vendor Perbankan -->
                    <div
                        class="p-4 bg-zinc-50/60 dark:bg-zinc-900/60 rounded-xl border border-zinc-200 dark:border-zinc-800 space-y-4"
                    >
                        <span class="text-xs font-bold text-primary block"
                            >Rincian Perbankan & Legal Vendor</span
                        >

                        <div class="space-y-1.5">
                            <label
                                for="vendor_select"
                                class="text-[10px] font-bold text-foreground"
                                >Pilih Vendor Terdaftar (Auto-fill)</label
                            >
                            <select
                                id="vendor_select"
                                onchange={handleVendorSelect}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer font-medium"
                            >
                                <option value=""
                                    >-- Tulis Baru / Edit Manual --</option
                                >
                                {#each vendors as v}
                                    <option value={v.id}>{v.name}</option>
                                {/each}
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label
                                    for="vendor_name"
                                    class="text-[10px] font-bold text-foreground"
                                    >Nama Resmi Vendor <span
                                        class="text-rose-500">*</span
                                    ></label
                                >
                                <input
                                    id="vendor_name"
                                    type="text"
                                    bind:value={form.vendor_name}
                                    placeholder="E.g., CV. Indomedia Utama"
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                    required
                                />
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    for="vendor_npwp"
                                    class="text-[10px] font-bold text-foreground"
                                    >NPWP Vendor</label
                                >
                                <input
                                    id="vendor_npwp"
                                    type="text"
                                    bind:value={form.vendor_npwp}
                                    placeholder="00.000.000.0-000.000"
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="space-y-1.5">
                                <label
                                    for="bank_name"
                                    class="text-[10px] font-bold text-foreground"
                                    >Nama Bank</label
                                >
                                <input
                                    id="bank_name"
                                    type="text"
                                    bind:value={form.bank_name}
                                    placeholder="BRI / Bank Mandiri"
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    for="bank_account_number"
                                    class="text-[10px] font-bold text-foreground"
                                    >Nomor Rekening</label
                                >
                                <input
                                    id="bank_account_number"
                                    type="text"
                                    bind:value={form.bank_account_number}
                                    placeholder="0210-01-..."
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    for="bank_account_name"
                                    class="text-[10px] font-bold text-foreground"
                                    >Nama Pemilik Rekening</label
                                >
                                <input
                                    id="bank_account_name"
                                    type="text"
                                    bind:value={form.bank_account_name}
                                    placeholder="Nama Direktur/Perusahaan..."
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                                />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label
                                for="vendor_address"
                                class="text-[10px] font-bold text-foreground"
                                >Alamat Kantor Vendor</label
                            >
                            <textarea
                                id="vendor_address"
                                bind:value={form.vendor_address}
                                placeholder="Jalan Raya, No. 102, Kota..."
                                rows="2"
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary resize-none font-medium"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Dokumen Pencairan (Only if Vendor type) -->
                <div
                    class="bg-card/45 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm space-y-4"
                >
                    <h3
                        class="text-sm font-bold text-foreground flex items-center gap-2 border-b border-sidebar-border/20 pb-2"
                    >
                        <span class="p-1 bg-primary/10 text-primary rounded"
                            >📄</span
                        >
                        Dokumen Pencairan & SP2D
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label
                                for="spp_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor SPP (Permintaan Pembayaran)</label
                            >
                            <input
                                id="spp_number"
                                type="text"
                                bind:value={form.spp_number}
                                placeholder="001/SPP-LS/..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="spp_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal SPP</label
                            >
                            <input
                                id="spp_date"
                                type="date"
                                bind:value={form.spp_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="spm_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor SPM (Perintah Membayar)</label
                            >
                            <input
                                id="spm_number"
                                type="text"
                                bind:value={form.spm_number}
                                placeholder="001/SPM-LS/..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="spm_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal SPM</label
                            >
                            <input
                                id="spm_date"
                                type="date"
                                bind:value={form.spm_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="sptjb_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor SPTJB</label
                            >
                            <input
                                id="sptjb_number"
                                type="text"
                                bind:value={form.sptjb_number}
                                placeholder="001/SPTJB/..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="sptjb_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal SPTJB</label
                            >
                            <input
                                id="sptjb_date"
                                type="date"
                                bind:value={form.sptjb_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="sp2d_number"
                                class="text-xs font-bold text-foreground"
                                >Nomor SP2D</label
                            >
                            <input
                                id="sp2d_number"
                                type="text"
                                bind:value={form.sp2d_number}
                                placeholder="SP2D/XXX/..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="sp2d_date"
                                class="text-xs font-bold text-foreground"
                                >Tanggal SP2D</label
                            >
                            <input
                                id="sp2d_date"
                                type="date"
                                bind:value={form.sp2d_date}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                            />
                        </div>
                    </div>
                </div>
            {/if}
        </div>

        <!-- Right Column: Info & Action Sidebar (Stays Sticky) -->
        <div class="space-y-6 lg:sticky lg:top-6">
            <!-- Budget Info Card -->
            <div
                class="bg-card/45 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm space-y-4"
            >
                <h3
                    class="text-xs font-bold text-muted-foreground uppercase tracking-wider"
                >
                    Detail DIPA / Pagu Acuan
                </h3>

                <div class="space-y-3 text-xs">
                    <div
                        class="flex flex-col gap-1 border-b border-sidebar-border/10 pb-2"
                    >
                        <span class="text-muted-foreground">Kegiatan</span>
                        <span class="font-bold text-foreground"
                            >{budget.activity?.name}</span
                        >
                    </div>
                    <div
                        class="flex flex-col gap-1 border-b border-sidebar-border/10 pb-2"
                    >
                        <span class="text-muted-foreground"
                            >Kategori Belanja</span
                        >
                        <span class="font-bold text-foreground capitalize"
                            >{budget.budget_category.replace('_', ' ')}</span
                        >
                    </div>
                    <div
                        class="flex flex-col gap-1 border-b border-sidebar-border/10 pb-2"
                    >
                        <span class="text-muted-foreground">Akun Belanja</span>
                        <span class="font-mono font-bold text-foreground"
                            >{budget.account_code || '-'} - {budget.account_name ||
                                '-'}</span
                        >
                    </div>
                    <div
                        class="flex justify-between items-center border-b border-sidebar-border/10 pb-2"
                    >
                        <span class="text-muted-foreground">Tahun Anggaran</span
                        >
                        <span class="font-semibold text-foreground"
                            >{budget.fiscal_year?.year}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Financial Summary Card -->
            <div
                class="bg-card/45 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm space-y-4"
            >
                <h3
                    class="text-xs font-bold text-muted-foreground uppercase tracking-wider"
                >
                    Ringkasan Keuangan
                </h3>

                <div class="space-y-3 text-xs">
                    <div
                        class="flex justify-between items-center border-b border-sidebar-border/10 pb-2"
                    >
                        <span class="text-muted-foreground"
                            >Total Pagu Anggaran:</span
                        >
                        <span class="font-bold text-foreground"
                            >{formatRupiah(budget.amount)}</span
                        >
                    </div>
                    <div
                        class="flex justify-between items-center border-b border-sidebar-border/10 pb-2 text-emerald-600 dark:text-emerald-400"
                    >
                        <span>Sisa Pagu Awal:</span>
                        <span class="font-extrabold"
                            >{formatRupiah(remainingBudgetBefore)}</span
                        >
                    </div>
                    <div
                        class="flex justify-between items-center border-b border-sidebar-border/10 pb-2 text-primary"
                    >
                        <span>Realisasi Belanja Ini:</span>
                        <span class="font-extrabold text-sm"
                            >{formatRupiah(form.amount)}</span
                        >
                    </div>
                    <div
                        class="flex justify-between items-center text-foreground pt-1"
                    >
                        <span>Estimasi Sisa Pagu Akhir:</span>
                        <span
                            class="font-extrabold text-sm {remainingBudgetAfter <
                            0
                                ? 'text-rose-500'
                                : 'text-emerald-600 dark:text-emerald-400'}"
                        >
                            {formatRupiah(remainingBudgetAfter)}
                        </span>
                    </div>
                </div>

                <!-- Over-Budget Alert -->
                {#if form.amount > remainingBudgetBefore}
                    <div
                        class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 rounded-lg flex items-start gap-2.5 text-xs font-semibold"
                    >
                        <AlertTriangle class="w-4 h-4 shrink-0 mt-0.5" />
                        <div>
                            Realisasi melebihi sisa pagu anggaran! Silakan
                            sesuaikan jumlah rincian belanja.
                        </div>
                    </div>
                {/if}
            </div>

            <!-- Actions Card -->
            <div
                class="bg-card/45 backdrop-blur-md p-4 rounded-xl border border-sidebar-border/50 shadow-sm flex flex-col gap-3"
            >
                <button
                    type="submit"
                    disabled={form.processing ||
                        form.amount > remainingBudgetBefore}
                    class="w-full inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-primary text-primary-foreground font-bold text-xs hover:bg-primary/95 transition-all shadow-md cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    <Save class="w-4 h-4" />
                    {form.processing
                        ? 'Menyimpan...'
                        : 'Simpan Realisasi Belanja'}
                </button>

                <Link
                    href={budgetsIndex()}
                    class="w-full inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-background text-foreground font-semibold text-xs hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-all cursor-pointer text-center"
                >
                    Batal
                </Link>
            </div>
        </div>
    </form>
</div>
