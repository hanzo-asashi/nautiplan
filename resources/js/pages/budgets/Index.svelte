<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as budgetIndex } from '@/routes/budgets';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Pagu & Realisasi Anggaran',
                href: budgetIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router, useForm, page } from '@inertiajs/svelte';
    import CheckCircle from 'lucide-svelte/icons/check-circle';
    import Coins from 'lucide-svelte/icons/coins';
    import History from 'lucide-svelte/icons/history';
    import Landmark from 'lucide-svelte/icons/landmark';
    import Pen from 'lucide-svelte/icons/pen';
    import Percent from 'lucide-svelte/icons/percent';
    import PiggyBank from 'lucide-svelte/icons/piggy-bank';
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import EmptyState from '@/components/EmptyState.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import StatsCard from '@/components/StatsCard.svelte';
    import { toUrl } from '@/lib/utils';
    import { formatRupiah } from '@/lib/utils';
    import { deleteMethod, update } from '@/routes/budgets';
    import {
        store as storeReal,
        verify as verifyReal,
        deleteMethod as deleteReal,
    } from '@/routes/budgets/realizations';

    let {
        budgets,
        units = [],
        fiscalYears = [],
        vendors = [],
        officers = [],
        summary,
        filters,
    }: {
        budgets: {
            data: Array<{
                id: number;
                budget_category: string;
                account_code: string | null;
                account_name: string | null;
                description: string;
                amount: number;
                fiscal_year_id: number;
                activity?: {
                    id: number;
                    name: string;
                    unit?: { name: string; code: string } | null;
                } | null;
                realizations: Array<any>;
            }>;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
        units: Array<{ id: number; name: string; code: string }>;
        fiscalYears: Array<{ id: number; year: number }>;
        vendors: Array<any>;
        officers: Array<any>;
        summary: {
            total_pagu: number;
            total_realisasi: number;
            sisa_anggaran: number;
            persen_realisasi: number;
        };
        filters: {
            unit_id?: string;
            fiscal_year_id?: string;
            category?: string;
        };
    } = $props();

    // Check user roles
    const user = $derived(page.props.auth.user as any);
    const isFinanceOrAdmin = $derived(
        user?.is_super_admin ||
            user?.roles?.includes('admin') ||
            user?.roles?.includes('finance-staff'),
    );

    let unitId = $state(filters.unit_id || '');
    let fiscalYearId = $state(filters.fiscal_year_id || '');
    let category = $state(filters.category || '');

    function applyFilters() {
        router.get(
            toUrl(budgetIndex()),
            {
                unit_id: unitId,
                fiscal_year_id: fiscalYearId,
                category: category,
            },
            { preserveState: true },
        );
    }

    // Realization modal form
    let realizationModalOpen = $state(false);
    let selectedBudget = $state<any>(null);
    let activeModalTab = $state('dasar');
    let activeDropdownRealId = $state<number | null>(null);

    const remainingBudget = $derived(
        selectedBudget
            ? selectedBudget.amount -
                  selectedBudget.realizations.reduce(
                      (sum: number, r: any) => sum + Number(r.amount),
                      0,
                  )
            : 0,
    );

    const form = useForm({
        activity_budget_id: '',
        realization_type: 'non_pengadaan',
        amount: 0,
        realization_date: new Date().toISOString().split('T')[0],
        description: '',
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
        // Pengadaan
        procurement_type: 'surat_pesanan',
        procurement_title: '',
        procurement_number: '',
        procurement_date: '',
        work_duration: '',
        nota_dinas_number: '',
        nota_dinas_date: '',
        ba_pl_number: '',
        ba_pl_date: '',
        ppk_id: '',
        kpa_id: '',
        // Vendor
        vendor_name: '',
        vendor_npwp: '',
        vendor_address: '',
        bank_name: '',
        bank_account_number: '',
        bank_account_name: '',
        // Items list
        items: [] as Array<{
            name: string;
            volume: number;
            unit: string;
            unit_price: number;
            tax_pph21: number;
            tax_pph21_mixed: boolean;
            remarks: string;
        }>,
    });

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

    function openRealizationModal(budget: any) {
        selectedBudget = budget;
        form.activity_budget_id = budget.id.toString();
        form.description = budget.description;
        form.procurement_title = budget.description;
        form.amount = 0;
        form.items = [
            {
                name: budget.description,
                volume: 1,
                unit: 'Paket',
                unit_price: 0,
                tax_pph21: 0,
                tax_pph21_mixed: false,
                remarks: '',
            },
        ];

        // Reset advanced fields
        form.bast_number = '';
        form.bast_date = '';
        form.bap_number = '';
        form.bap_date = '';
        form.ba_penyerahan_number = '';
        form.ba_penyerahan_date = '';
        form.sp2d_number = '';
        form.sp2d_date = '';
        form.procurement_number = '';
        form.procurement_date = '';
        form.work_duration = '5 (lima) Hari Kalender';
        form.nota_dinas_number = '';
        form.nota_dinas_date = '';
        form.ba_pl_number = '';
        form.ba_pl_date = '';
        form.ppk_id =
            officers
                .find((o) => o.name.toLowerCase().includes('arnaldy'))
                ?.id?.toString() || '';
        form.kpa_id =
            officers
                .find((o) => o.name.toLowerCase().includes('sidrotul'))
                ?.id?.toString() || '';
        form.vendor_name = '';
        form.vendor_npwp = '';
        form.vendor_address = '';
        form.bank_name = '';
        form.bank_account_number = '';
        form.bank_account_name = '';

        activeModalTab = 'dasar';
        realizationModalOpen = true;
    }

    function handleRealizationSubmit(e: Event) {
        e.preventDefault();

        if (form.amount > remainingBudget + 1) {
            alert(
                'Peringatan: Nominal realisasi belanja melebihi sisa pagu anggaran yang tersedia!',
            );

            return;
        }

        form.post(toUrl(storeReal()), {
            onSuccess: () => {
                realizationModalOpen = false;
                form.reset();
            },
        });
    }

    function handleVerifyRealization(realId: number) {
        router.post(toUrl(verifyReal({ realization: realId })));
    }

    function handleDeleteRealization(realId: number) {
        if (
            confirm('Apakah Anda yakin ingin menghapus catatan realisasi ini?')
        ) {
            router.delete(toUrl(deleteReal({ realization: realId })));
        }
    }

    function handleDeleteBudget(budgetId: number) {
        if (confirm('Apakah Anda yakin ingin menghapus pagu anggaran ini?')) {
            router.delete(toUrl(deleteMethod({ budget: budgetId })));
        }
    }

    // Edit budget modal form
    let editBudgetModalOpen = $state(false);
    let selectedBudgetToEdit = $state<any>(null);

    const editForm = useForm({
        budget_category: '',
        account_code: '',
        account_name: '',
        description: '',
        amount: 0,
    });

    function openEditBudgetModal(budget: any) {
        selectedBudgetToEdit = budget;
        editForm.budget_category = budget.budget_category;
        editForm.account_code = budget.account_code || '';
        editForm.account_name = budget.account_name || '';
        editForm.description = budget.description;
        editForm.amount = budget.amount;
        editBudgetModalOpen = true;
    }

    function handleEditBudgetSubmit(e: Event) {
        e.preventDefault();
        editForm.put(toUrl(update({ budget: selectedBudgetToEdit.id })), {
            onSuccess: () => {
                editBudgetModalOpen = false;
                editForm.reset();
            },
        });
    }
</script>

<svelte:window onclick={() => (activeDropdownRealId = null)} />

<AppHead title="Pagu & Realisasi" />

<div class="p-6 space-y-6">
    {#snippet actions()}
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="/reports/non-procurement/pdf"
                target="_blank"
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background hover:bg-accent px-3 py-1.5 text-xs font-semibold text-foreground cursor-pointer transition-colors"
            >
                Cetak Non-Pengadaan
            </a>
            <a
                href="/reports/vendor/pdf"
                target="_blank"
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background hover:bg-accent px-3 py-1.5 text-xs font-semibold text-foreground cursor-pointer transition-colors"
            >
                Cetak Realisasi Vendor
            </a>
        </div>
    {/snippet}

    <PageHeader
        title="Pagu & Realisasi Anggaran"
        description="Kelola anggaran DIPA BLU dan pantau realisasi belanja operasional"
        {actions}
    />

    <!-- Summary cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatsCard
            title="Total Pagu"
            value={formatRupiah(summary.total_pagu, true)}
            icon={Landmark}
        />
        <StatsCard
            title="Total Realisasi"
            value={formatRupiah(summary.total_realisasi, true)}
            icon={Coins}
        />
        <StatsCard
            title="Sisa Anggaran"
            value={formatRupiah(summary.sisa_anggaran, true)}
            icon={PiggyBank}
        />
        <StatsCard
            title="Persentase Penyerapan"
            value={`${summary.persen_realisasi}%`}
            icon={Percent}
            trendType="up"
        />
    </div>

    <!-- Filters Panel -->
    <div
        class="grid gap-4 sm:grid-cols-3 bg-card/40 backdrop-blur-md p-4 rounded-xl border border-sidebar-border/50"
    >
        <select
            bind:value={unitId}
            onchange={applyFilters}
            class="px-3 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
        >
            <option value="">Semua Unit Kerja</option>
            {#each units as unit}
                <option value={unit.id.toString()}
                    >[{unit.code}] {unit.name}</option
                >
            {/each}
        </select>

        <select
            bind:value={fiscalYearId}
            onchange={applyFilters}
            class="px-3 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
        >
            <option value="">Semua Tahun Anggaran</option>
            {#each fiscalYears as fy}
                <option value={fy.id.toString()}>{fy.year}</option>
            {/each}
        </select>

        <select
            bind:value={category}
            onchange={applyFilters}
            class="px-3 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
        >
            <option value="">Semua Kategori Belanja</option>
            <option value="personnel">Personnel</option>
            <option value="goods_services">Goods & Services</option>
            <option value="capital">Capital</option>
            <option value="other">Other</option>
        </select>
    </div>

    {#if budgets.data.length === 0}
        <EmptyState
            title="Tidak ada data Anggaran"
            description="Belum ada pagu anggaran kegiatan yang sesuai dengan filter pencarian."
        />
    {:else}
        <div class="space-y-6">
            {#each budgets.data as bud (bud.id)}
                {@const totalSpent = bud.realizations.reduce(
                    (sum, r) => sum + r.amount,
                    0,
                )}
                {@const remaining = bud.amount - totalSpent}

                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/45 backdrop-blur-md p-6 shadow-sm space-y-4"
                >
                    <!-- Budget Ceiling Info -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-sidebar-border/30 pb-4"
                    >
                        <div class="space-y-1">
                            <div class="flex flex-wrap gap-2 items-center">
                                <span
                                    class="text-xs uppercase font-bold px-2 py-0.5 rounded bg-primary/10 text-primary"
                                >
                                    {bud.budget_category.replace('_', ' ')}
                                </span>
                                {#if bud.account_code}
                                    <span
                                        class="text-xs font-semibold px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-muted-foreground"
                                    >
                                        Kode: {bud.account_code}
                                    </span>
                                {/if}
                            </div>
                            <h3 class="text-base font-bold text-foreground">
                                {bud.description}
                                {#if bud.account_name}
                                    <span
                                        class="text-xs text-muted-foreground font-medium block mt-0.5"
                                    >
                                        Akun: {bud.account_name}
                                    </span>
                                {/if}
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Kegiatan: <strong class="text-foreground"
                                    >{bud.activity?.name || '-'}</strong
                                >
                                | Unit: {bud.activity?.unit?.name || '-'}
                            </p>
                        </div>

                        <div class="flex items-center gap-6 shrink-0">
                            <div class="text-right">
                                <span
                                    class="text-xs text-muted-foreground block"
                                    >Pagu</span
                                >
                                <span
                                    class="font-extrabold text-lg text-foreground"
                                    >{formatRupiah(bud.amount, true)}</span
                                >
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-xs text-muted-foreground block"
                                    >Realisasi</span
                                >
                                <span
                                    class="font-extrabold text-lg text-emerald-600 dark:text-emerald-400"
                                    >{formatRupiah(totalSpent, true)}</span
                                >
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-xs text-muted-foreground block"
                                    >Sisa</span
                                >
                                <span
                                    class="font-extrabold text-lg text-muted-foreground"
                                    >{formatRupiah(remaining, true)}</span
                                >
                            </div>

                            {#if isFinanceOrAdmin}
                                <div
                                    class="flex gap-2 pl-2 border-l border-sidebar-border/30"
                                >
                                    <button
                                        onclick={() =>
                                            openRealizationModal(bud)}
                                        class="inline-flex h-8 items-center justify-center rounded-md bg-emerald-600 hover:bg-emerald-500 text-white px-3 text-xs font-semibold shadow-sm cursor-pointer gap-1"
                                    >
                                        <Plus class="size-3.5" />
                                        Realisasi
                                    </button>
                                    <button
                                        onclick={() => openEditBudgetModal(bud)}
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-primary hover:bg-primary/10 cursor-pointer"
                                        title="Edit Pagu"
                                    >
                                        <Pen class="size-4" />
                                    </button>
                                    {#if bud.realizations.length === 0}
                                        <button
                                            onclick={() =>
                                                handleDeleteBudget(bud.id)}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-rose-500 hover:bg-rose-500/10 cursor-pointer"
                                            title="Hapus Pagu"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>

                    <!-- Realizations List -->
                    <div class="space-y-2">
                        <h4
                            class="text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-1"
                        >
                            <History class="size-3.5" />
                            Riwayat Realisasi Anggaran Belanja
                        </h4>

                        {#if bud.realizations.length === 0}
                            <p
                                class="text-xs text-muted-foreground/60 italic pl-5"
                            >
                                Belum ada realisasi belanja anggaran.
                            </p>
                        {:else}
                            <div
                                class="pl-4 border-l-2 border-emerald-500/20 space-y-3 pt-1"
                            >
                                {#each bud.realizations as real}
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs border-b border-sidebar-border/20 pb-2 last:border-0 last:pb-0"
                                    >
                                        <div class="space-y-0.5">
                                            <div
                                                class="flex items-center flex-wrap gap-2"
                                            >
                                                <span
                                                    class="font-bold text-foreground"
                                                    >{real.description}</span
                                                >
                                                {#if real.realization_type === 'surat_pesanan'}
                                                    <span
                                                        class="px-1.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 rounded font-semibold text-[9px] uppercase tracking-wider"
                                                    >
                                                        Surat Pesanan
                                                    </span>
                                                {:else}
                                                    <span
                                                        class="px-1.5 py-0.5 bg-zinc-150 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded font-semibold text-[9px] uppercase tracking-wider"
                                                    >
                                                        Non-Pengadaan
                                                    </span>
                                                {/if}
                                                {#if real.receipt_number}
                                                    <span
                                                        class="px-1.5 py-0.5 bg-zinc-200 dark:bg-zinc-800 rounded font-mono text-[10px] text-muted-foreground"
                                                        >Kuitansi: {real.receipt_number}</span
                                                    >
                                                {/if}
                                            </div>
                                            <p
                                                class="text-[10px] text-muted-foreground"
                                            >
                                                Tanggal Transaksi: {real.realization_date}
                                                {#if real.vendor_name}
                                                    | Vendor: <strong
                                                        class="text-foreground"
                                                        >{real.vendor_name}</strong
                                                    >
                                                {/if}
                                                {#if real.procurement_number}
                                                    | SP: <strong
                                                        class="text-foreground"
                                                        >{real.procurement_number}</strong
                                                    >
                                                {/if}
                                                {#if real.sp2d_number}
                                                    | SP2D: <strong
                                                        class="text-foreground"
                                                        >{real.sp2d_number}</strong
                                                    >
                                                {/if}
                                            </p>
                                        </div>

                                        <div
                                            class="flex items-center gap-4 shrink-0"
                                        >
                                            {#if real.realization_type === 'surat_pesanan'}
                                                <div
                                                    class="relative inline-block text-left"
                                                >
                                                    <button
                                                        onclick={(e) => {
                                                            e.stopPropagation();
                                                            activeDropdownRealId =
                                                                activeDropdownRealId ===
                                                                real.id
                                                                    ? null
                                                                    : real.id;
                                                        }}
                                                        class="inline-flex items-center gap-1 text-[10px] font-semibold text-primary hover:text-white bg-primary/10 hover:bg-primary px-2 py-1 rounded transition-colors cursor-pointer"
                                                    >
                                                        📄 Cetak Dokumen ▾
                                                    </button>
                                                    {#if activeDropdownRealId === real.id}
                                                        <div
                                                            class="absolute right-0 bottom-full mb-1 w-44 rounded-md shadow-lg bg-card border border-zinc-200 dark:border-zinc-800 z-50 py-1 text-xs"
                                                        >
                                                            <a
                                                                href={`/reports/realization/${real.id}/pdf`}
                                                                target="_blank"
                                                                onclick={() =>
                                                                    (activeDropdownRealId =
                                                                        null)}
                                                                class="block px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors"
                                                            >
                                                                📄 Cetak Surat
                                                                Pesanan (SP)
                                                            </a>
                                                            <a
                                                                href={`/reports/realization/${real.id}/spk`}
                                                                target="_blank"
                                                                onclick={() =>
                                                                    (activeDropdownRealId =
                                                                        null)}
                                                                class="block px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors"
                                                            >
                                                                🛠️ Cetak Kontrak
                                                                (SPK)
                                                            </a>
                                                            <a
                                                                href={`/reports/realization/${real.id}/bast`}
                                                                target="_blank"
                                                                onclick={() =>
                                                                    (activeDropdownRealId =
                                                                        null)}
                                                                class="block px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors"
                                                            >
                                                                🤝 Cetak Serah
                                                                Terima (BAST)
                                                            </a>
                                                            <a
                                                                href={`/reports/realization/${real.id}/bap`}
                                                                target="_blank"
                                                                onclick={() =>
                                                                    (activeDropdownRealId =
                                                                        null)}
                                                                class="block px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors"
                                                            >
                                                                💰 Cetak BAP
                                                                Pembayaran
                                                            </a>
                                                            <a
                                                                href={`/reports/realization/${real.id}/kwitansi`}
                                                                target="_blank"
                                                                onclick={() =>
                                                                    (activeDropdownRealId =
                                                                        null)}
                                                                class="block px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors"
                                                            >
                                                                🏷️ Cetak
                                                                Kwitansi Resmi
                                                            </a>
                                                        </div>
                                                    {/if}
                                                </div>
                                            {:else}
                                                <a
                                                    href={`/reports/realization/${real.id}/kwitansi`}
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 text-[10px] font-semibold text-zinc-600 hover:text-white bg-zinc-100 hover:bg-zinc-650 px-2 py-1 rounded transition-colors cursor-pointer dark:bg-zinc-800 dark:text-zinc-350 dark:hover:bg-zinc-700"
                                                >
                                                    🏷️ Kwitansi
                                                </a>
                                            {/if}

                                            <span
                                                class="font-bold text-emerald-600 dark:text-emerald-400"
                                                >{formatRupiah(
                                                    real.amount,
                                                    true,
                                                )}</span
                                            >

                                            {#if real.verified_at}
                                                <span
                                                    class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full"
                                                >
                                                    <CheckCircle
                                                        class="size-3"
                                                    />
                                                    Verified
                                                </span>
                                            {:else}
                                                <span
                                                    class="inline-flex items-center text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full"
                                                >
                                                    Pending
                                                </span>
                                                {#if isFinanceOrAdmin}
                                                    <button
                                                        onclick={() =>
                                                            handleVerifyRealization(
                                                                real.id,
                                                            )}
                                                        class="text-[10px] font-semibold text-primary hover:underline cursor-pointer"
                                                    >
                                                        Verifikasi
                                                    </button>
                                                {/if}
                                            {/if}

                                            {#if isFinanceOrAdmin}
                                                <button
                                                    onclick={() =>
                                                        handleDeleteRealization(
                                                            real.id,
                                                        )}
                                                    class="text-rose-500 hover:text-rose-600 p-1 hover:bg-rose-500/10 rounded cursor-pointer"
                                                    title="Hapus Transaksi"
                                                >
                                                    <Trash2 class="size-3.5" />
                                                </button>
                                            {/if}
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>
            {/each}

            <!-- Pagination -->
            {#if budgets.links.length > 3}
                <div class="flex items-center justify-center gap-1.5 pt-4">
                    {#each budgets.links as link}
                        {#if link.url}
                            <Link
                                href={link.url}
                                class="px-3 py-1 text-xs font-semibold rounded-md border border-zinc-200/50 dark:border-zinc-800 transition-colors 
                                    {link.active
                                    ? 'bg-primary text-white border-primary shadow-sm'
                                    : 'bg-background hover:bg-accent'}"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span
                                class="px-3 py-1 text-xs font-semibold text-muted-foreground/50 cursor-not-allowed"
                            >
                                {@html link.label}
                            </span>
                        {/if}
                    {/each}
                </div>
            {/if}
        </div>
    {/if}
</div>

<!-- Modal Realisasi Form -->
<!-- Let's write the custom HTML5 dialog form directly in Index.svelte for maximum robust form binding -->
{#if realizationModalOpen}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/30 backdrop-blur-sm"
    >
        <div
            class="bg-card border border-sidebar-border/50 p-6 rounded-xl shadow-xl w-full max-w-2xl max-h-[92vh] overflow-y-auto scrollbar-thin space-y-4 text-foreground"
        >
            <div
                class="flex items-center justify-between border-b border-sidebar-border/20 pb-2"
            >
                <div>
                    <h3 class="text-lg font-bold">Catat Realisasi Belanja</h3>
                    <p class="text-xs text-muted-foreground">
                        Pagu: <span class="font-bold text-foreground"
                            >{selectedBudget?.description}</span
                        >
                        (Sisa Pagu:
                        <span
                            class="font-bold text-emerald-600 dark:text-emerald-400"
                            >{formatRupiah(remainingBudget)}</span
                        >)
                    </p>
                </div>
                <button
                    type="button"
                    onclick={() => {
                        realizationModalOpen = false;
                        form.reset();
                    }}
                    class="text-muted-foreground hover:text-foreground text-sm cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- TAB MENU -->
            <div
                class="flex border-b border-zinc-200 dark:border-zinc-800 text-xs font-semibold gap-1"
            >
                <button
                    type="button"
                    onclick={() => (activeModalTab = 'dasar')}
                    class={`px-3 py-2 border-b-2 transition-all ${activeModalTab === 'dasar' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'}`}
                >
                    📁 Dasar & Rincian
                </button>
                {#if form.realization_type === 'surat_pesanan'}
                    <button
                        type="button"
                        onclick={() => (activeModalTab = 'kontrak')}
                        class={`px-3 py-2 border-b-2 transition-all ${activeModalTab === 'kontrak' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'}`}
                    >
                        🛠️ Kontrak (SPK)
                    </button>
                    <button
                        type="button"
                        onclick={() => (activeModalTab = 'serah_terima')}
                        class={`px-3 py-2 border-b-2 transition-all ${activeModalTab === 'serah_terima' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'}`}
                    >
                        🤝 Serah Terima (BAST)
                    </button>
                    <button
                        type="button"
                        onclick={() => (activeModalTab = 'pejabat')}
                        class={`px-3 py-2 border-b-2 transition-all ${activeModalTab === 'pejabat' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'}`}
                    >
                        🏢 Pejabat & Vendor
                    </button>
                {/if}
            </div>

            <form onsubmit={handleRealizationSubmit} class="space-y-4">
                <!-- TAB 1: DASAR & RINCIAN -->
                {#if activeModalTab === 'dasar'}
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tipe Realisasi</label
                                >
                                <select
                                    bind:value={form.realization_type}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
                                    required
                                >
                                    <option value="non_pengadaan"
                                        >Non-Pengadaan</option
                                    >
                                    <option value="surat_pesanan"
                                        >Surat Pesanan (Pihak Ketiga/Vendor)</option
                                    >
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tanggal Realisasi</label
                                >
                                <input
                                    type="date"
                                    bind:value={form.realization_date}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    required
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor Bukti Kuitansi / Receipt</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.receipt_number}
                                    placeholder="E.g., KUITANSI-012"
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Deskripsi Ringkas Belanja</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.description}
                                    placeholder="E.g., Pembelian ATK Diklat..."
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    required
                                />
                            </div>
                        </div>

                        <!-- DYNAMIC ITEMS TABLE -->
                        <div class="space-y-2 pt-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-primary"
                                    >Daftar Rincian Barang / Jasa</label
                                >
                                <button
                                    type="button"
                                    onclick={addItem}
                                    class="px-2 py-1 text-[10px] bg-emerald-600 text-white rounded hover:bg-emerald-500 flex items-center gap-1 cursor-pointer font-semibold transition-colors"
                                >
                                    ＋ Tambah Item
                                </button>
                            </div>

                            <div
                                class="border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden text-xs max-h-60 overflow-y-auto scrollbar-thin"
                            >
                                <table class="w-full text-left border-collapse">
                                    <thead
                                        class="bg-zinc-50 dark:bg-zinc-900 font-semibold border-b border-zinc-200 dark:border-zinc-800 text-[10px] uppercase tracking-wider text-muted-foreground"
                                    >
                                        <tr>
                                            <th class="p-2 w-[40%]"
                                                >Nama Barang/Jasa & Spesifikasi</th
                                            >
                                            <th class="p-2 w-[15%] text-center"
                                                >Vol</th
                                            >
                                            <th class="p-2 w-[15%] text-center"
                                                >Satuan</th
                                            >
                                            <th class="p-2 w-[20%] text-right"
                                                >Harga Satuan</th
                                            >
                                            <th class="p-2 w-[10%] text-center"
                                                >Aksi</th
                                            >
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each form.items as item, index}
                                            <tr
                                                class="border-b border-zinc-100 dark:border-zinc-900/50 last:border-0"
                                            >
                                                <td class="p-1.5">
                                                    <input
                                                        type="text"
                                                        bind:value={item.name}
                                                        placeholder="Nama barang..."
                                                        class="w-full px-2 py-1 bg-background border border-zinc-200 dark:border-zinc-800 rounded outline-none focus:border-primary text-xs"
                                                        required
                                                    />
                                                    <input
                                                        type="text"
                                                        bind:value={
                                                            item.remarks
                                                        }
                                                        placeholder="Keterangan / spesifikasi (opsional)..."
                                                        class="w-full px-2 py-0.5 mt-1 bg-background border border-transparent rounded text-[10px] text-muted-foreground outline-none focus:border-zinc-200 dark:focus:border-zinc-700"
                                                    />
                                                </td>
                                                <td class="p-1.5">
                                                    <input
                                                        type="number"
                                                        bind:value={item.volume}
                                                        oninput={calculateTotal}
                                                        min="0.01"
                                                        step="any"
                                                        class="w-full px-2 py-1 bg-background border border-zinc-200 dark:border-zinc-800 rounded outline-none focus:border-primary text-xs text-center"
                                                        required
                                                    />
                                                </td>
                                                <td class="p-1.5">
                                                    <input
                                                        type="text"
                                                        bind:value={item.unit}
                                                        placeholder="Pcs"
                                                        class="w-full px-2 py-1 bg-background border border-zinc-200 dark:border-zinc-800 rounded outline-none focus:border-primary text-xs text-center"
                                                        required
                                                    />
                                                </td>
                                                <td class="p-1.5">
                                                    <input
                                                        type="number"
                                                        bind:value={
                                                            item.unit_price
                                                        }
                                                        oninput={calculateTotal}
                                                        min="0"
                                                        class="w-full px-2 py-1 bg-background border border-zinc-200 dark:border-zinc-800 rounded outline-none focus:border-primary text-xs text-right"
                                                        required
                                                    />
                                                </td>
                                                <td class="p-1.5 text-center">
                                                    <button
                                                        type="button"
                                                        disabled={form.items
                                                            .length <= 1}
                                                        onclick={() =>
                                                            removeItem(index)}
                                                        class="text-rose-500 hover:text-rose-600 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer p-1 transition-colors"
                                                    >
                                                        🗑️
                                                    </button>
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>

                            <!-- SUB-TOTAL AUTO-SUM CARD -->
                            <div
                                class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg border border-zinc-150 dark:border-zinc-850 text-xs font-semibold gap-2"
                            >
                                <div class="text-muted-foreground">
                                    Total Nilai Rincian (Dihitung Otomatis):
                                </div>
                                <div
                                    class="text-base font-extrabold text-foreground"
                                >
                                    {formatRupiah(form.amount)}
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- TAB 2: KONTRAK (SPK) -->
                {#if activeModalTab === 'kontrak'}
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tipe Dokumen</label
                                >
                                <select
                                    bind:value={form.procurement_type}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
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
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nama Paket Pekerjaan</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.procurement_title}
                                    placeholder="Nama paket pengadaan..."
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    required
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor SP / SPK</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.procurement_number}
                                    placeholder="PL.107/67/7/POLTEKPEL..."
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    required
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tanggal SP / SPK</label
                                >
                                <input
                                    type="date"
                                    bind:value={form.procurement_date}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    required
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Waktu Pelaksanaan Pekerjaan</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.work_duration}
                                    placeholder="E.g., 5 (lima) Hari Kalender"
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor Nota Dinas PPK</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.nota_dinas_number}
                                    placeholder="ND/PPK/..."
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tanggal Nota Dinas</label
                                >
                                <input
                                    type="date"
                                    bind:value={form.nota_dinas_date}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor BA Pengadaan Langsung (PL)</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.ba_pl_number}
                                    placeholder="BA-PL/..."
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tanggal BA Pengadaan Langsung</label
                                >
                                <input
                                    type="date"
                                    bind:value={form.ba_pl_date}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- TAB 3: SERAH TERIMA -->
                {#if activeModalTab === 'serah_terima'}
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor BAST Barang</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.bast_number}
                                    placeholder="PL.109/57/22/POLTEKPEL..."
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tanggal BAST Barang</label
                                >
                                <input
                                    type="date"
                                    bind:value={form.bast_date}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor Berita Acara Pembayaran (BAP)</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.bap_number}
                                    placeholder="BAP/XXX/..."
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tanggal BAP</label
                                >
                                <input
                                    type="date"
                                    bind:value={form.bap_date}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor BA Penyerahan</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.ba_penyerahan_number}
                                    placeholder="BA-PENYERAHAN/..."
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tanggal BA Penyerahan</label
                                >
                                <input
                                    type="date"
                                    bind:value={form.ba_penyerahan_date}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor SP2D</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.sp2d_number}
                                    placeholder="SP2D/XXX/..."
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Tanggal SP2D</label
                                >
                                <input
                                    type="date"
                                    bind:value={form.sp2d_date}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- TAB 4: PEJABAT & VENDOR -->
                {#if activeModalTab === 'pejabat'}
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Kuasa Pengguna Anggaran (KPA)</label
                                >
                                <select
                                    bind:value={form.kpa_id}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
                                >
                                    <option value="">-- Pilih KPA --</option>
                                    {#each officers as off}
                                        <option value={off.id.toString()}
                                            >{off.name} (NIP: {off.employee_id})</option
                                        >
                                    {/each}
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Pejabat Pembuat Komitmen (PPK)</label
                                >
                                <select
                                    bind:value={form.ppk_id}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
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

                        <div
                            class="p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg border border-zinc-150 dark:border-zinc-800/80 space-y-3"
                        >
                            <p class="text-xs font-bold text-primary">
                                Informasi Penyedia (Vendor) & Perbankan
                            </p>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Pilih Vendor Terdaftar (Auto-fill)</label
                                >
                                <select
                                    onchange={handleVendorSelect}
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
                                >
                                    <option value=""
                                        >-- Tulis Baru / Edit Manual --</option
                                    >
                                    {#each vendors as v}
                                        <option value={v.id}>{v.name}</option>
                                    {/each}
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold"
                                        >Nama Resmi Vendor</label
                                    >
                                    <input
                                        type="text"
                                        bind:value={form.vendor_name}
                                        placeholder="E.g., CV. Media Utama"
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                        required
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold"
                                        >NPWP Vendor</label
                                    >
                                    <input
                                        type="text"
                                        bind:value={form.vendor_npwp}
                                        placeholder="00.000.000.0-000.000"
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold"
                                        >Nama Bank</label
                                    >
                                    <input
                                        type="text"
                                        bind:value={form.bank_name}
                                        placeholder="BTN / Mandiri / BRI"
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold"
                                        >Nomor Rekening</label
                                    >
                                    <input
                                        type="text"
                                        bind:value={form.bank_account_number}
                                        placeholder="0021-01-..."
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold"
                                        >Pemilik Rekening (Direktur)</label
                                    >
                                    <input
                                        type="text"
                                        bind:value={form.bank_account_name}
                                        placeholder="Nama direktur..."
                                        class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    />
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Alamat Resmi Vendor</label
                                >
                                <textarea
                                    bind:value={form.vendor_address}
                                    placeholder="Jalan, RT/RW, Kota..."
                                    rows="2"
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary resize-none"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- CEILING BUDGET WARNING ALERT -->
                {#if form.amount > remainingBudget}
                    <div
                        class="p-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 rounded-lg text-rose-700 dark:text-rose-350 text-xs flex items-start gap-2"
                    >
                        <span>⚠️</span>
                        <div>
                            <strong class="font-bold"
                                >Over-Budget Warning:</strong
                            >
                            Total realisasi belanja (<strong
                                >{formatRupiah(form.amount)}</strong
                            >) melampaui sisa pagu anggaran (<strong
                                >{formatRupiah(remainingBudget)}</strong
                            >). Silakan kurangi volume atau harga item sebelum
                            menyimpan.
                        </div>
                    </div>
                {/if}

                <!-- FOOTER BUTTONS -->
                <div
                    class="flex justify-end gap-2 pt-3 border-t border-sidebar-border/20 mt-4"
                >
                    <button
                        type="button"
                        onclick={() => {
                            realizationModalOpen = false;
                            form.reset();
                        }}
                        class="inline-flex h-8 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background px-3 text-xs font-medium hover:bg-accent cursor-pointer transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={form.processing || form.amount <= 0}
                        class="inline-flex h-8 items-center justify-center rounded-md bg-emerald-600 hover:bg-emerald-500 text-white px-3 text-xs font-semibold cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                    >
                        {form.processing
                            ? 'Menyimpan...'
                            : 'Simpan Realisasi Belanja'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

{#if editBudgetModalOpen}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/30 backdrop-blur-sm"
    >
        <div
            class="bg-card/95 border border-sidebar-border/50 p-6 rounded-xl shadow-xl w-full max-w-md space-y-4 text-foreground"
        >
            <h3 class="text-lg font-bold">Edit Pagu Anggaran</h3>
            <p class="text-xs text-muted-foreground">
                Kegiatan: {selectedBudgetToEdit?.activity?.name || '-'}
            </p>

            <form onsubmit={handleEditBudgetSubmit} class="space-y-3">
                <div class="space-y-1">
                    <label class="text-xs font-semibold"
                        >Kategori Anggaran</label
                    >
                    <select
                        bind:value={editForm.budget_category}
                        class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
                        required
                    >
                        <option value="personnel">Personnel</option>
                        <option value="goods_services">Goods & Services</option>
                        <option value="capital">Capital</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold">Kode Akun</label>
                        <input
                            type="text"
                            bind:value={editForm.account_code}
                            placeholder="Contoh: 521811"
                            class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold">Nama Akun</label>
                        <input
                            type="text"
                            bind:value={editForm.account_name}
                            placeholder="Contoh: Belanja Barang"
                            class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                        />
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold">Deskripsi Pagu</label>
                    <input
                        type="text"
                        bind:value={editForm.description}
                        class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                        required
                    />
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold"
                        >Jumlah Pagu (IDR)</label
                    >
                    <input
                        type="number"
                        bind:value={editForm.amount}
                        class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                        min="0"
                        required
                    />
                </div>

                <div
                    class="flex justify-end gap-3 pt-3 border-t border-sidebar-border/20 mt-4"
                >
                    <button
                        type="button"
                        onclick={() => {
                            editBudgetModalOpen = false;
                            editForm.reset();
                        }}
                        class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background px-4 py-2 text-sm font-medium hover:bg-accent cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={editForm.processing}
                        class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/90 text-white px-4 py-2 text-sm font-medium cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
