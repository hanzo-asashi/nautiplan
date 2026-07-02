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
                realizations: Array<{
                    id: number;
                    amount: number;
                    realization_date: string;
                    description: string;
                    receipt_number: string | null;
                    realization_type: string;
                    vendor_name: string | null;
                    vendor_address: string | null;
                    vendor_npwp: string | null;
                    procurement_number: string | null;
                    procurement_date: string | null;
                    sp2d_number: string | null;
                    sp2d_date: string | null;
                    verified_at: string | null;
                    verified_by: number | null;
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

    const form = useForm({
        activity_budget_id: '',
        realization_type: 'non_pengadaan',
        amount: 0,
        realization_date: new Date().toISOString().split('T')[0],
        description: '',
        receipt_number: '',
        vendor_name: '',
        vendor_address: '',
        vendor_npwp: '',
        procurement_number: '',
        procurement_date: '',
        sp2d_number: '',
        sp2d_date: '',
    });

    function openRealizationModal(budget: any) {
        selectedBudget = budget;
        form.activity_budget_id = budget.id.toString();
        realizationModalOpen = true;
    }

    function handleRealizationSubmit(e: Event) {
        e.preventDefault();
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
                                                <a
                                                    href={`/reports/realization/${real.id}/pdf`}
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 text-[10px] font-semibold text-primary hover:text-white bg-primary/10 hover:bg-primary px-2 py-1 rounded transition-colors cursor-pointer"
                                                >
                                                    Cetak SP
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
    <!-- dialog block -->
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/30 backdrop-blur-sm"
    >
        <div
            class="bg-card/95 border border-sidebar-border/50 p-6 rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto scrollbar-thin space-y-4 text-foreground"
        >
            <h3 class="text-lg font-bold">Catat Realisasi Belanja</h3>
            <p class="text-xs text-muted-foreground">
                Pagu: {selectedBudget?.description} ({formatRupiah(
                    selectedBudget?.amount,
                )})
            </p>

            <form onsubmit={handleRealizationSubmit} class="space-y-3">
                <div class="space-y-1">
                    <label class="text-xs font-semibold">Tipe Realisasi</label>
                    <select
                        bind:value={form.realization_type}
                        class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer"
                        required
                    >
                        <option value="non_pengadaan">Non-Pengadaan</option>
                        <option value="surat_pesanan"
                            >Surat Pesanan (Pihak Ketiga/Vendor)</option
                        >
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold"
                        >Jumlah Realisasi (IDR)</label
                    >
                    <input
                        type="number"
                        bind:value={form.amount}
                        class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                        min="1"
                        required
                    />
                </div>

                {#if form.realization_type === 'surat_pesanan'}
                    <div
                        class="space-y-3 p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg border border-zinc-150 dark:border-zinc-800/80"
                    >
                        <p class="text-xs font-bold text-primary">
                            Informasi Vendor & Surat Pesanan
                        </p>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold"
                                >Nama Penyedia / Vendor</label
                            >
                            <input
                                type="text"
                                bind:value={form.vendor_name}
                                placeholder="E.g., CV. Media Pratama"
                                class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                required
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
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
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor SP / SPK</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.procurement_number}
                                    placeholder="SP/XXXX/2026"
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                    required
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
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
                            <div class="space-y-1">
                                <label class="text-xs font-semibold"
                                    >Nomor SP2D</label
                                >
                                <input
                                    type="text"
                                    bind:value={form.sp2d_number}
                                    placeholder="SP2D/XXX/2026"
                                    class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
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

                        <div class="space-y-1">
                            <label class="text-xs font-semibold"
                                >Alamat Vendor</label
                            >
                            <textarea
                                bind:value={form.vendor_address}
                                placeholder="Alamat lengkap vendor..."
                                rows="2"
                                class="w-full px-3 py-1.5 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary resize-none"
                            ></textarea>
                        </div>
                    </div>
                {/if}

                <div class="space-y-1">
                    <label class="text-xs font-semibold"
                        >Tanggal Realisasi</label
                    >
                    <input
                        type="date"
                        bind:value={form.realization_date}
                        class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                        required
                    />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold"
                        >Nomor Bukti Kuitansi / Receipt</label
                    >
                    <input
                        type="text"
                        bind:value={form.receipt_number}
                        placeholder="E.g., KUITANSI-0012, SP2D-0044"
                        class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                    />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold"
                        >Deskripsi Belanja</label
                    >
                    <input
                        type="text"
                        bind:value={form.description}
                        placeholder="E.g., Pembelian alat tulis dan fotokopi materi diklat..."
                        class="w-full px-3 py-1.5 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary"
                        required
                    />
                </div>

                <div
                    class="flex justify-end gap-3 pt-3 border-t border-sidebar-border/20 mt-4"
                >
                    <button
                        type="button"
                        onclick={() => {
                            realizationModalOpen = false;
                            form.reset();
                        }}
                        class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background px-4 py-2 text-sm font-medium hover:bg-accent cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={form.processing}
                        class="inline-flex h-9 items-center justify-center rounded-md bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 text-sm font-medium cursor-pointer"
                    >
                        Simpan Belanja
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
