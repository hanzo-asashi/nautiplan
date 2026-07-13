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
    import { Link, router, page } from '@inertiajs/svelte';
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
    import { toUrl, formatRupiah, formatDateIndonesian } from '@/lib/utils';
    import { deleteMethod, edit } from '@/routes/budgets';
    import {
        verify as verifyReal,
        deleteMethod as deleteReal,
        create as realizationCreate,
    } from '@/routes/budgets/realizations';

    let {
        budgets,
        units = [],
        fiscalYears = [],
        vendors: _vendors = [],
        officers: _officers = [],
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
                budgetItems?: Array<{
                    id: number;
                    name: string;
                    volume: number;
                    unit: string;
                    unit_price: number;
                    total: number;
                }>;
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

    // Realization dropdown state
    let activeDropdownRealId = $state<number | null>(null);

    // Collapsible realizations state
    let expandedBudgets = $state<Record<number, boolean>>({});

    function toggleBudgetRealizations(budgetId: number) {
        expandedBudgets[budgetId] = !expandedBudgets[budgetId];
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

    // Revision history modal state
    let revisionHistoryModalOpen = $state(false);
    let selectedBudgetForHistory = $state<any>(null);

    function openRevisionHistoryModal(budget: any) {
        selectedBudgetForHistory = budget;
        revisionHistoryModalOpen = true;
    }

    // Quick Budget Revision/Transfer Wizard state
    let transferWizardModalOpen = $state(false);
    let transferStep = $state(1); // 1 = Select Source/Dest, 2 = Enter Amount/Reason, 3 = Confirmation

    // Form fields
    let selectedSourceBudgetId = $state('');
    let selectedSourceItemId = $state('');
    let selectedDestBudgetId = $state('');
    let selectedDestItemId = $state('');
    let transferAmount = $state<number>(0);
    let transferReason = $state('');

    // Derived helpers
    const selectedSourceBudget = $derived(
        budgets.data.find((b) => b.id.toString() === selectedSourceBudgetId),
    );
    const selectedDestBudget = $derived(
        budgets.data.find((b) => b.id.toString() === selectedDestBudgetId),
    );

    const sourceItems = $derived(selectedSourceBudget?.budgetItems || []);
    const destItems = $derived(selectedDestBudget?.budgetItems || []);

    const selectedSourceItem = $derived(
        sourceItems.find((i: any) => i.id.toString() === selectedSourceItemId),
    );
    const selectedDestItem = $derived(
        destItems.find((i: any) => i.id.toString() === selectedDestItemId),
    );

    // Calculate source item's available amount
    const sourceItemAvailableAmount = $derived.by(() => {
        if (!selectedSourceItem) {
            return 0;
        }

        // Realized total is calculated from realizations
        const realizations = selectedSourceBudget?.realizations || [];
        const realizedTotal = realizations.reduce((sum: number, r: any) => {
            const itemTotal =
                r.items
                    ?.filter(
                        (ri: any) =>
                            ri.budget_item_id === selectedSourceItem.id,
                    )
                    .reduce(
                        (s: number, ri: any) => s + ri.volume * ri.unit_price,
                        0,
                    ) || 0;

            return sum + itemTotal;
        }, 0);

        return Math.max(0, selectedSourceItem.total - realizedTotal);
    });

    function resetWizard() {
        transferWizardModalOpen = false;
        transferStep = 1;
        selectedSourceBudgetId = '';
        selectedSourceItemId = '';
        selectedDestBudgetId = '';
        selectedDestItemId = '';
        transferAmount = 0;
        transferReason = '';
    }

    function handleTransferSubmit() {
        if (transferAmount <= 0 || transferAmount > sourceItemAvailableAmount) {
            return;
        }

        router.post(
            '/budgets/transfer',
            {
                source_budget_item_id: selectedSourceItemId,
                destination_budget_item_id: selectedDestItemId,
                amount: transferAmount,
                reason: transferReason,
            },
            {
                onSuccess: () => {
                    resetWizard();
                },
            },
        );
    }
</script>

<svelte:window onclick={() => (activeDropdownRealId = null)} />

<AppHead title="Pagu & Realisasi" />

{#snippet actions()}
    <div class="flex flex-wrap items-center gap-2">
        <button
            type="button"
            onclick={() => (transferWizardModalOpen = true)}
            class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/90 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm cursor-pointer transition-colors"
        >
            🔄 Transfer Anggaran
        </button>
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

<div class="p-6 space-y-6">
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
                    (sum, r) => sum + Number(r.amount),
                    0,
                )}
                {@const remaining = Number(bud.amount) - totalSpent}

                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/45 backdrop-blur-md p-6 shadow-sm space-y-4 transition-all duration-200 {bud.realizations.some(
                        (r) => r.id === activeDropdownRealId,
                    )
                        ? 'relative z-50'
                        : ''}"
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

                            <div class="flex gap-2 items-center">
                                <button
                                    onclick={() =>
                                        openRevisionHistoryModal(bud)}
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-zinc-550 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                                    title="Histori Revisi POK"
                                >
                                    <History class="size-4" />
                                </button>

                                {#if isFinanceOrAdmin}
                                    <div
                                        class="flex gap-2 pl-2 border-l border-sidebar-border/30"
                                    >
                                        <Link
                                            href={toUrl(
                                                realizationCreate({
                                                    budget: bud.id,
                                                }),
                                            )}
                                            class="inline-flex h-8 items-center justify-center rounded-md bg-emerald-600 hover:bg-emerald-500 text-white px-3 text-xs font-semibold shadow-sm cursor-pointer gap-1"
                                        >
                                            <Plus class="size-3.5" />
                                            Realisasi
                                        </Link>
                                        <Link
                                            href={toUrl(
                                                edit({
                                                    budget: bud.id,
                                                }),
                                            )}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-primary hover:bg-primary/10 cursor-pointer"
                                            title="Revisi POK"
                                        >
                                            <Pen class="size-4" />
                                        </Link>
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
                            <button
                                type="button"
                                onclick={() => toggleBudgetRealizations(bud.id)}
                                class="flex items-center justify-between w-full px-3 py-2 text-xs font-semibold text-muted-foreground hover:text-foreground bg-zinc-55 dark:bg-zinc-900/35 rounded-lg border border-zinc-200/50 dark:border-zinc-800 transition-all cursor-pointer group"
                            >
                                <span class="flex items-center gap-1.5">
                                    <History
                                        class="size-3.5 text-emerald-600 dark:text-emerald-500"
                                    />
                                    Riwayat Realisasi Anggaran ({bud
                                        .realizations.length} Transaksi)
                                </span>
                                <span
                                    class="text-[10px] text-muted-foreground group-hover:text-primary transition-colors font-medium"
                                >
                                    {expandedBudgets[bud.id]
                                        ? 'Sembunyikan ▲'
                                        : 'Tampilkan Rincian ▼'}
                                </span>
                            </button>

                            {#if expandedBudgets[bud.id]}
                                <div
                                    class="pl-4 border-l-2 border-emerald-500/20 space-y-3 pt-2 mt-1 animate-in fade-in slide-in-from-top-1 duration-200"
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
                                                    Tanggal Transaksi: {formatDateIndonesian(
                                                        real.realization_date,
                                                    )}
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
                                                {#if real.items && real.items.length > 0}
                                                    <div
                                                        class="mt-1.5 pl-3 border-l border-zinc-200 dark:border-zinc-800 text-[10px] text-muted-foreground/80 space-y-0.5"
                                                    >
                                                        {#each real.items as item}
                                                            <div>
                                                                • {item.name} ({item.volume}
                                                                {item.unit} @ {formatRupiah(
                                                                    item.unit_price,
                                                                )})
                                                            </div>
                                                        {/each}
                                                    </div>
                                                {/if}
                                            </div>

                                            <div
                                                class="flex items-center gap-4 shrink-0"
                                            >
                                                {#if real.realization_type === 'surat_pesanan'}
                                                    <div
                                                        class="relative inline-block text-left {activeDropdownRealId ===
                                                        real.id
                                                            ? 'z-50'
                                                            : ''}"
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
                                                                class="absolute right-0 top-full mt-1 w-64 rounded-xl shadow-xl bg-card border border-zinc-200 dark:border-zinc-800 z-50 py-1.5 text-xs text-foreground divide-y divide-zinc-100 dark:divide-zinc-800/60 transition-all duration-150 origin-top-right select-none animate-in fade-in zoom-in-95"
                                                            >
                                                                <!-- Group 1: Pengadaan -->
                                                                <div
                                                                    class="py-1"
                                                                >
                                                                    <div
                                                                        class="px-3 py-1 text-[9px] uppercase tracking-wider font-extrabold text-muted-foreground/80"
                                                                    >
                                                                        Dokumen
                                                                        Pengadaan
                                                                    </div>
                                                                    <a
                                                                        href={`/reports/realization/${real.id}/pdf`}
                                                                        target="_blank"
                                                                        onclick={() =>
                                                                            (activeDropdownRealId =
                                                                                null)}
                                                                        class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                    >
                                                                        <span
                                                                            >📄</span
                                                                        > Surat Pesanan
                                                                        (SP)
                                                                    </a>
                                                                    <a
                                                                        href={`/reports/realization/${real.id}/spk`}
                                                                        target="_blank"
                                                                        onclick={() =>
                                                                            (activeDropdownRealId =
                                                                                null)}
                                                                        class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                    >
                                                                        <span
                                                                            >🛠️</span
                                                                        > Surat Perintah
                                                                        Kerja (SPK)
                                                                    </a>
                                                                    <a
                                                                        href={`/reports/realization/${real.id}/bast`}
                                                                        target="_blank"
                                                                        onclick={() =>
                                                                            (activeDropdownRealId =
                                                                                null)}
                                                                        class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                    >
                                                                        <span
                                                                            >🤝</span
                                                                        > BA Serah
                                                                        Terima (BAST)
                                                                    </a>
                                                                    <a
                                                                        href={`/reports/realization/${real.id}/bap`}
                                                                        target="_blank"
                                                                        onclick={() =>
                                                                            (activeDropdownRealId =
                                                                                null)}
                                                                        class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                    >
                                                                        <span
                                                                            >💰</span
                                                                        > BA Pembayaran
                                                                        (BAP)
                                                                    </a>
                                                                    <a
                                                                        href={`/reports/realization/${real.id}/kwitansi`}
                                                                        target="_blank"
                                                                        onclick={() =>
                                                                            (activeDropdownRealId =
                                                                                null)}
                                                                        class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                    >
                                                                        <span
                                                                            >🏷️</span
                                                                        > Kwitansi
                                                                        Resmi
                                                                    </a>
                                                                </div>

                                                                <!-- Group 2: Pencairan -->
                                                                <div
                                                                    class="py-1"
                                                                >
                                                                    <div
                                                                        class="px-3 py-1 text-[9px] uppercase tracking-wider font-extrabold text-muted-foreground/80"
                                                                    >
                                                                        Pencairan
                                                                        & Pajak
                                                                    </div>
                                                                    <a
                                                                        href={`/reports/realization/${real.id}/spp`}
                                                                        target="_blank"
                                                                        onclick={() =>
                                                                            (activeDropdownRealId =
                                                                                null)}
                                                                        class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                    >
                                                                        <span
                                                                            >📄</span
                                                                        > Cetak SPP
                                                                    </a>
                                                                    <a
                                                                        href={`/reports/realization/${real.id}/spm`}
                                                                        target="_blank"
                                                                        onclick={() =>
                                                                            (activeDropdownRealId =
                                                                                null)}
                                                                        class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                    >
                                                                        <span
                                                                            >🏛️</span
                                                                        > Cetak SPM
                                                                    </a>
                                                                    <a
                                                                        href={`/reports/realization/${real.id}/sptjb`}
                                                                        target="_blank"
                                                                        onclick={() =>
                                                                            (activeDropdownRealId =
                                                                                null)}
                                                                        class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                    >
                                                                        <span
                                                                            >🤝</span
                                                                        > Cetak SPTJB
                                                                    </a>
                                                                    {#if real.items && real.items.some((i) => Number(i.tax_ppn) > 0)}
                                                                        <a
                                                                            href={`/reports/realization/${real.id}/ssp?type=ppn`}
                                                                            target="_blank"
                                                                            onclick={() =>
                                                                                (activeDropdownRealId =
                                                                                    null)}
                                                                            class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                        >
                                                                            <span
                                                                                >🧾</span
                                                                            > SSP
                                                                            (PPN)
                                                                        </a>
                                                                    {/if}
                                                                    {#if real.items && real.items.some((i) => Number(i.tax_pph22) > 0)}
                                                                        <a
                                                                            href={`/reports/realization/${real.id}/ssp?type=pph22`}
                                                                            target="_blank"
                                                                            onclick={() =>
                                                                                (activeDropdownRealId =
                                                                                    null)}
                                                                            class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                        >
                                                                            <span
                                                                                >🧾</span
                                                                            > SSP
                                                                            (PPh 22)
                                                                        </a>
                                                                    {/if}
                                                                    {#if real.items && real.items.some((i) => Number(i.tax_pph23) > 0)}
                                                                        <a
                                                                            href={`/reports/realization/${real.id}/ssp?type=pph23`}
                                                                            target="_blank"
                                                                            onclick={() =>
                                                                                (activeDropdownRealId =
                                                                                    null)}
                                                                            class="flex items-center gap-2 px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground transition-colors font-medium"
                                                                        >
                                                                            <span
                                                                                >🧾</span
                                                                            > SSP
                                                                            (PPh 23)
                                                                        </a>
                                                                    {/if}
                                                                </div>
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
                                                        <Trash2
                                                            class="size-3.5"
                                                        />
                                                    </button>
                                                {/if}
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
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

{#if revisionHistoryModalOpen}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/30 backdrop-blur-sm p-4 overflow-y-auto"
    >
        <div
            class="bg-card/95 border border-sidebar-border/50 p-6 rounded-xl shadow-xl w-full max-w-4xl space-y-4 text-foreground max-h-[90vh] overflow-y-auto"
        >
            <div
                class="flex justify-between items-start border-b border-sidebar-border/20 pb-3"
            >
                <div>
                    <h3 class="text-lg font-bold">
                        Histori Revisi Anggaran (POK)
                    </h3>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Kegiatan: {selectedBudgetForHistory?.activity?.name ||
                            '-'} | Deskripsi: {selectedBudgetForHistory?.description}
                    </p>
                </div>
                <button
                    type="button"
                    onclick={() => (revisionHistoryModalOpen = false)}
                    class="text-zinc-450 hover:text-zinc-600 dark:text-zinc-550 dark:hover:text-zinc-400 p-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <div class="space-y-6 overflow-y-auto max-h-[65vh] pr-2 pt-2">
                {#if !selectedBudgetForHistory?.revisions || selectedBudgetForHistory.revisions.length === 0}
                    <div
                        class="text-center py-12 text-sm text-muted-foreground"
                    >
                        Belum ada histori revisi untuk pagu anggaran ini.
                    </div>
                {:else}
                    {#each selectedBudgetForHistory.revisions as revision (revision.id)}
                        <div
                            class="border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden bg-zinc-50/50 dark:bg-zinc-900/10"
                        >
                            <!-- Revision Header -->
                            <div
                                class="p-4 bg-zinc-100/50 dark:bg-zinc-900/40 border-b border-zinc-200 dark:border-zinc-800 flex flex-wrap justify-between items-center gap-3"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-bold bg-primary/10 text-primary px-2 py-0.5 rounded"
                                        >
                                            Revisi #{revision.revision_number}
                                        </span>
                                        <span
                                            class="text-xs text-muted-foreground font-medium"
                                        >
                                            Oleh: <strong
                                                class="text-foreground"
                                                >{revision.revised_by?.name ||
                                                    'Sistem'}</strong
                                            >
                                        </span>
                                        <span
                                            class="text-xs text-muted-foreground font-medium"
                                        >
                                            | Tanggal: {new Date(
                                                revision.created_at,
                                            ).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </span>
                                    </div>
                                    <p
                                        class="text-xs font-semibold text-foreground mt-2"
                                    >
                                        Alasan: <span
                                            class="italic font-medium text-muted-foreground"
                                            >"{revision.description}"</span
                                        >
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <span
                                            class="text-[10px] text-muted-foreground block font-semibold"
                                            >Total Pagu Semula &rarr; Menjadi</span
                                        >
                                        <span
                                            class="text-xs font-bold text-foreground"
                                        >
                                            {formatRupiah(
                                                revision.amount_semula,
                                            )} &rarr; {formatRupiah(
                                                revision.amount_menjadi,
                                            )}
                                        </span>
                                    </div>
                                    <a
                                        href={`/reports/revision/${revision.id}/pdf`}
                                        target="_blank"
                                        class="inline-flex h-8 items-center justify-center rounded-lg bg-zinc-200 dark:bg-zinc-800 text-foreground px-3 text-xs font-bold hover:bg-zinc-300 dark:hover:bg-zinc-700 cursor-pointer gap-1 transition-colors"
                                    >
                                        📄 PDF
                                    </a>
                                </div>
                            </div>

                            <!-- Revision Details Table -->
                            <div class="p-4 overflow-x-auto">
                                <table
                                    class="w-full text-xs text-left border-collapse"
                                >
                                    <thead>
                                        <tr
                                            class="border-b border-zinc-200 dark:border-zinc-800 text-muted-foreground font-semibold"
                                        >
                                            <th class="pb-2">Rincian Item</th>
                                            <th class="pb-2 text-right w-48"
                                                >Semula (Pagu Lama)</th
                                            >
                                            <th class="pb-2 text-right w-48"
                                                >Menjadi (Pagu Baru)</th
                                            >
                                            <th class="pb-2 text-right w-36"
                                                >Selisih (Perubahan)</th
                                            >
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-zinc-200/50 dark:divide-zinc-800/30"
                                    >
                                        {#each revision.details || [] as detail}
                                            {@const isNew =
                                                !detail.total_semula ||
                                                Number(detail.total_semula) ===
                                                    0}
                                            {@const isDeleted =
                                                !detail.total_menjadi ||
                                                Number(detail.total_menjadi) ===
                                                    0}
                                            {@const delta =
                                                Number(detail.total_menjadi) -
                                                Number(detail.total_semula)}

                                            <tr>
                                                <td class="py-2.5 font-medium">
                                                    {detail.name_menjadi ||
                                                        detail.name_semula}
                                                    {#if isNew}
                                                        <span
                                                            class="ml-1 text-[9px] bg-emerald-500/10 text-emerald-600 px-1 py-0.5 rounded font-bold"
                                                            >Baru</span
                                                        >
                                                    {:else if isDeleted}
                                                        <span
                                                            class="ml-1 text-[9px] bg-rose-500/10 text-rose-600 px-1 py-0.5 rounded font-bold"
                                                            >Dihapus</span
                                                        >
                                                    {/if}
                                                </td>
                                                <td
                                                    class="py-2.5 text-right font-medium text-muted-foreground"
                                                >
                                                    {#if isNew}
                                                        -
                                                    {:else}
                                                        {detail.volume_semula}
                                                        {detail.unit_semula} @ {formatRupiah(
                                                            detail.unit_price_semula,
                                                        )}
                                                        <span
                                                            class="block text-[10px] font-bold mt-0.5 text-foreground"
                                                            >{formatRupiah(
                                                                detail.total_semula,
                                                            )}</span
                                                        >
                                                    {/if}
                                                </td>
                                                <td
                                                    class="py-2.5 text-right font-medium"
                                                >
                                                    {#if isDeleted}
                                                        -
                                                    {:else}
                                                        {detail.volume_menjadi}
                                                        {detail.unit_menjadi} @ {formatRupiah(
                                                            detail.unit_price_menjadi,
                                                        )}
                                                        <span
                                                            class="block text-[10px] font-extrabold mt-0.5"
                                                            >{formatRupiah(
                                                                detail.total_menjadi,
                                                            )}</span
                                                        >
                                                    {/if}
                                                </td>
                                                <td
                                                    class="py-2.5 text-right font-bold"
                                                >
                                                    {#if delta > 0}
                                                        <span
                                                            class="text-emerald-600 dark:text-emerald-400"
                                                            >+{formatRupiah(
                                                                delta,
                                                            )}</span
                                                        >
                                                    {:else if delta < 0}
                                                        <span
                                                            class="text-rose-500"
                                                            >-{formatRupiah(
                                                                Math.abs(delta),
                                                            )}</span
                                                        >
                                                    {:else}
                                                        <span
                                                            class="text-zinc-400"
                                                            >0</span
                                                        >
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {/each}
                {/if}
            </div>

            <div
                class="flex justify-end pt-3 border-t border-sidebar-border/20"
            >
                <button
                    type="button"
                    onclick={() => (revisionHistoryModalOpen = false)}
                    class="inline-flex h-9 items-center justify-center rounded-lg bg-primary hover:bg-primary/95 text-white px-5 py-2 text-xs font-bold cursor-pointer transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
{/if}

{#if transferWizardModalOpen}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/30 backdrop-blur-sm p-4 overflow-y-auto"
    >
        <div
            class="bg-card/95 border border-sidebar-border/50 p-6 rounded-xl shadow-xl w-full max-w-2xl space-y-4 text-foreground max-h-[90vh] overflow-y-auto flex flex-col"
        >
            <div
                class="flex justify-between items-start border-b border-sidebar-border/20 pb-3"
            >
                <div>
                    <h3 class="text-lg font-bold">
                        Wizard Transfer / Revisi Anggaran POK
                    </h3>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Pindahkan sisa alokasi pagu antar kegiatan dengan aman.
                    </p>
                </div>
                <button
                    type="button"
                    onclick={resetWizard}
                    class="text-zinc-450 hover:text-zinc-600 dark:text-zinc-550 dark:hover:text-zinc-400 p-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Wizard Steps Progress Bar -->
            <div
                class="flex items-center justify-between text-xs px-2 py-2 bg-zinc-50 dark:bg-zinc-900/35 rounded-lg border border-sidebar-border/30"
            >
                <span
                    class="font-bold {transferStep === 1
                        ? 'text-primary'
                        : 'text-muted-foreground'}">1. Sumber & Tujuan</span
                >
                <span class="text-muted-foreground">&rarr;</span>
                <span
                    class="font-bold {transferStep === 2
                        ? 'text-primary'
                        : 'text-muted-foreground'}">2. Jumlah & Alasan</span
                >
                <span class="text-muted-foreground">&rarr;</span>
                <span
                    class="font-bold {transferStep === 3
                        ? 'text-primary'
                        : 'text-muted-foreground'}">3. Konfirmasi</span
                >
            </div>

            <div class="flex-1 space-y-4 pt-2">
                {#if transferStep === 1}
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-foreground"
                                >1. Kegiatan Sumber (Didebet)</label
                            >
                            <select
                                bind:value={selectedSourceBudgetId}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200/80 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
                            >
                                <option value=""
                                    >-- Pilih Kegiatan Sumber --</option
                                >
                                {#each budgets.data as b}
                                    <option value={b.id.toString()}>
                                        [{b.account_code}] {b.activity?.name ||
                                            b.description} ({formatRupiah(
                                            b.amount,
                                            true,
                                        )})
                                    </option>
                                {/each}
                            </select>
                        </div>

                        {#if selectedSourceBudgetId}
                            <div
                                class="space-y-1.5 animate-in fade-in duration-200"
                            >
                                <label class="text-xs font-bold text-foreground"
                                    >Detail Item Sumber</label
                                >
                                <select
                                    bind:value={selectedSourceItemId}
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200/80 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
                                >
                                    <option value=""
                                        >-- Pilih Rincian Belanja Sumber --</option
                                    >
                                    {#each sourceItems as item}
                                        <option value={item.id.toString()}>
                                            {item.name} ({item.volume}
                                            {item.unit} @ {formatRupiah(
                                                item.unit_price,
                                            )}) - Total: {formatRupiah(
                                                item.total,
                                            )}
                                        </option>
                                    {/each}
                                </select>
                            </div>
                        {/if}

                        <hr class="border-sidebar-border/20 my-4" />

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-foreground"
                                >2. Kegiatan Tujuan (Dikredit)</label
                            >
                            <select
                                bind:value={selectedDestBudgetId}
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200/80 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
                            >
                                <option value=""
                                    >-- Pilih Kegiatan Tujuan --</option
                                >
                                {#each budgets.data.filter((b) => b.id.toString() !== selectedSourceBudgetId) as b}
                                    <option value={b.id.toString()}>
                                        [{b.account_code}] {b.activity?.name ||
                                            b.description} ({formatRupiah(
                                            b.amount,
                                            true,
                                        )})
                                    </option>
                                {/each}
                            </select>
                        </div>

                        {#if selectedDestBudgetId}
                            <div
                                class="space-y-1.5 animate-in fade-in duration-200"
                            >
                                <label class="text-xs font-bold text-foreground"
                                    >Detail Item Tujuan</label
                                >
                                <select
                                    bind:value={selectedDestItemId}
                                    class="w-full px-3 py-2 text-xs bg-background border border-zinc-200/80 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
                                >
                                    <option value=""
                                        >-- Pilih Rincian Belanja Tujuan --</option
                                    >
                                    {#each destItems as item}
                                        <option value={item.id.toString()}>
                                            {item.name} ({item.volume}
                                            {item.unit} @ {formatRupiah(
                                                item.unit_price,
                                            )}) - Total: {formatRupiah(
                                                item.total,
                                            )}
                                        </option>
                                    {/each}
                                </select>
                            </div>
                        {/if}
                    </div>
                {:else if transferStep === 2}
                    <div class="space-y-4 animate-in fade-in duration-200">
                        <div
                            class="p-3 bg-zinc-50 dark:bg-zinc-900/35 rounded-lg border border-sidebar-border/30 text-xs space-y-1"
                        >
                            <p class="font-medium text-foreground">
                                Dana Tersedia untuk Ditransfer:
                            </p>
                            <p class="text-base font-extrabold text-primary">
                                {formatRupiah(sourceItemAvailableAmount, true)}
                            </p>
                            <p class="text-[10px] text-muted-foreground">
                                Berdasarkan total pagu rincian dikurangi
                                realisasi transaksi belanja.
                            </p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-foreground"
                                >Jumlah Transfer (IDR)</label
                            >
                            <input
                                type="number"
                                bind:value={transferAmount}
                                min="1000"
                                max={sourceItemAvailableAmount}
                                placeholder="Masukkan nominal dana..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200/80 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-foreground"
                                >Alasan Perubahan / Pemindahan Pagu</label
                            >
                            <textarea
                                bind:value={transferReason}
                                rows="3"
                                placeholder="Contoh: Pergeseran sisa pagu operasional rapat ke belanja perjalanan dinas..."
                                class="w-full px-3 py-2 text-xs bg-background border border-zinc-200/80 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                            ></textarea>
                        </div>
                    </div>
                {:else if transferStep === 3}
                    <div
                        class="space-y-4 animate-in fade-in duration-200 text-xs"
                    >
                        <div
                            class="p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-xl space-y-2"
                        >
                            <h4
                                class="font-bold text-amber-800 dark:text-amber-300"
                            >
                                Mohon periksa kembali detail pemindahan dana
                                berikut:
                            </h4>

                            <div
                                class="grid grid-cols-2 gap-4 mt-2 text-foreground"
                            >
                                <div>
                                    <span
                                        class="text-muted-foreground block text-[10px]"
                                        >Dari Item Belanja (Didebet):</span
                                    >
                                    <strong
                                        class="text-rose-600 dark:text-rose-400"
                                        >{selectedSourceItem?.name}</strong
                                    >
                                    <span
                                        class="block text-[10px] text-muted-foreground"
                                        >Pagu Awal: {formatRupiah(
                                            selectedSourceItem?.total,
                                        )}</span
                                    >
                                    <span class="block text-[10px] font-bold"
                                        >Pagu Baru: {formatRupiah(
                                            (selectedSourceItem?.total || 0) -
                                                transferAmount,
                                        )}</span
                                    >
                                </div>
                                <div>
                                    <span
                                        class="text-muted-foreground block text-[10px]"
                                        >Ke Item Belanja (Dikredit):</span
                                    >
                                    <strong
                                        class="text-emerald-600 dark:text-emerald-400"
                                        >{selectedDestItem?.name}</strong
                                    >
                                    <span
                                        class="block text-[10px] text-muted-foreground"
                                        >Pagu Awal: {formatRupiah(
                                            selectedDestItem?.total,
                                        )}</span
                                    >
                                    <span class="block text-[10px] font-bold"
                                        >Pagu Baru: {formatRupiah(
                                            (selectedDestItem?.total || 0) +
                                                transferAmount,
                                        )}</span
                                    >
                                </div>
                            </div>

                            <div class="border-t border-amber-200/40 pt-2 mt-2">
                                <span
                                    class="text-muted-foreground block text-[10px]"
                                    >Nominal Pemindahan:</span
                                >
                                <strong class="text-lg text-primary"
                                    >{formatRupiah(
                                        transferAmount,
                                        true,
                                    )}</strong
                                >
                            </div>

                            {#if transferReason}
                                <div class="mt-2 text-muted-foreground italic">
                                    " {transferReason} "
                                </div>
                            {/if}
                        </div>
                    </div>
                {/if}
            </div>

            <div
                class="flex justify-between items-center pt-4 border-t border-sidebar-border/20 mt-4"
            >
                <button
                    type="button"
                    onclick={resetWizard}
                    class="px-4 py-2 text-xs font-semibold rounded-lg border border-zinc-200/60 dark:border-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground cursor-pointer transition-colors"
                >
                    Batalkan
                </button>

                <div class="flex gap-2">
                    {#if transferStep > 1}
                        <button
                            type="button"
                            onclick={() => transferStep--}
                            class="px-4 py-2 text-xs font-semibold rounded-lg border border-zinc-200/60 dark:border-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-foreground cursor-pointer transition-colors"
                        >
                            Sebelumnya
                        </button>
                    {/if}

                    {#if transferStep < 3}
                        <button
                            type="button"
                            disabled={(transferStep === 1 &&
                                (!selectedSourceItemId ||
                                    !selectedDestItemId)) ||
                                (transferStep === 2 &&
                                    (transferAmount <= 0 ||
                                        transferAmount >
                                            sourceItemAvailableAmount ||
                                        !transferReason))}
                            onclick={() => transferStep++}
                            class="px-5 py-2 text-xs font-bold rounded-lg bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-zinc-200 text-background cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Berikutnya
                        </button>
                    {:else}
                        <button
                            type="button"
                            onclick={handleTransferSubmit}
                            class="px-6 py-2 text-xs font-extrabold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow cursor-pointer transition-colors"
                        >
                            Proses Transfer Anggaran
                        </button>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}
