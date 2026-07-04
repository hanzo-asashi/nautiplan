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
                title: 'Pagu & Realisasi Anggaran',
                href: budgetsIndex(),
            },
            {
                title: 'Revisi POK',
                href: '',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, useForm } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import History from 'lucide-svelte/icons/history';
    import Plus from 'lucide-svelte/icons/plus';
    import Save from 'lucide-svelte/icons/save';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { formatRupiah, toUrl } from '@/lib/utils';
    import { update } from '@/routes/budgets';

    let { budget }: { budget: any } = $props();

    const form = useForm({
        budget_category: budget.budget_category || '',
        account_code: budget.account_code || '',
        account_name: budget.account_name || '',
        description: budget.description || '',
        amount: budget.amount || 0,
        revision_description: '',
        items: (budget.budget_items || []).map((bi: any) => ({
            id: bi.id,
            name: bi.name,
            volume: Number(bi.volume),
            unit: bi.unit,
            unit_price: Number(bi.unit_price),
        })),
    });

    function addItem() {
        form.items = [
            ...form.items,
            {
                name: '',
                volume: 1,
                unit: 'Pcs',
                unit_price: 0,
            },
        ];
        calculateTotal();
    }

    function removeItem(index: number) {
        form.items = form.items.filter((_: any, i: number) => i !== index);
        calculateTotal();
    }

    function calculateTotal() {
        form.amount = form.items.reduce(
            (sum: number, item: any) =>
                sum + Number(item.volume) * Number(item.unit_price),
            0,
        );
    }

    function handleSubmit(e: Event) {
        e.preventDefault();

        if (form.items.length === 0) {
            alert('Harap masukkan minimal 1 item anggaran.');

            return;
        }

        if (!form.revision_description.trim()) {
            alert('Harap isi alasan/keterangan revisi.');

            return;
        }

        form.put(toUrl(update({ budget: budget.id })));
    }

    // Revision history panel toggle
    let showRevisionHistory = $state(false);
</script>

<AppHead title="Revisi POK — {budget.activity?.name || 'Anggaran'}" />

<div class="p-6 space-y-6">
    {#snippet actions()}
        <Link
            href={toUrl(budgetsIndex())}
            class="inline-flex h-9 items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-background px-4 text-xs font-semibold hover:bg-accent cursor-pointer gap-1.5"
        >
            <ArrowLeft class="size-4" />
            Kembali
        </Link>
    {/snippet}

    <PageHeader
        title="Revisi Pagu Anggaran (POK)"
        description="Kegiatan: {budget.activity?.name || '-'} — {budget.activity
            ?.unit?.name || ''}"
        {actions}
    />

    <form onsubmit={handleSubmit} class="space-y-6">
        <!-- Metadata Section -->
        <div
            class="bg-card border border-sidebar-border/50 rounded-xl p-6 space-y-5"
        >
            <h3
                class="text-sm font-bold text-foreground border-b border-sidebar-border/20 pb-3"
            >
                Informasi Pagu Anggaran
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label
                        for="budget_category"
                        class="text-xs font-bold text-muted-foreground"
                        >Kategori Anggaran</label
                    >
                    <select
                        id="budget_category"
                        bind:value={form.budget_category}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary cursor-pointer font-semibold"
                        required
                    >
                        <option value="personnel">Personnel</option>
                        <option value="goods_services">Goods & Services</option>
                        <option value="capital">Capital</option>
                        <option value="other">Other</option>
                    </select>
                    {#if form.errors.budget_category}
                        <p class="text-xs text-rose-500">
                            {form.errors.budget_category}
                        </p>
                    {/if}
                </div>
                <div class="space-y-1.5">
                    <label
                        for="account_code"
                        class="text-xs font-bold text-muted-foreground"
                        >Kode Akun</label
                    >
                    <input
                        id="account_code"
                        type="text"
                        bind:value={form.account_code}
                        placeholder="E.g., 521811"
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-semibold"
                    />
                    {#if form.errors.account_code}
                        <p class="text-xs text-rose-500">
                            {form.errors.account_code}
                        </p>
                    {/if}
                </div>
                <div class="space-y-1.5">
                    <label
                        for="account_name"
                        class="text-xs font-bold text-muted-foreground"
                        >Nama Akun</label
                    >
                    <input
                        id="account_name"
                        type="text"
                        bind:value={form.account_name}
                        placeholder="E.g., Belanja Barang"
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-semibold"
                    />
                    {#if form.errors.account_name}
                        <p class="text-xs text-rose-500">
                            {form.errors.account_name}
                        </p>
                    {/if}
                </div>
                <div class="space-y-1.5">
                    <label
                        for="description"
                        class="text-xs font-bold text-muted-foreground"
                        >Deskripsi Pagu</label
                    >
                    <input
                        id="description"
                        type="text"
                        bind:value={form.description}
                        placeholder="Deskripsi pagu anggaran"
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-semibold"
                        required
                    />
                    {#if form.errors.description}
                        <p class="text-xs text-rose-500">
                            {form.errors.description}
                        </p>
                    {/if}
                </div>
            </div>
        </div>

        <!-- Items Section -->
        <div
            class="bg-card border border-sidebar-border/50 rounded-xl p-6 space-y-4"
        >
            <div
                class="flex flex-wrap justify-between items-center border-b border-sidebar-border/20 pb-3 gap-3"
            >
                <h3 class="text-sm font-bold text-primary">
                    Rincian Rencana Belanja (POK)
                </h3>
                <button
                    type="button"
                    onclick={addItem}
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors cursor-pointer"
                >
                    <Plus class="size-3.5" /> Tambah Item
                </button>
            </div>

            {#if form.items.length === 0}
                <div class="text-center py-12 text-sm text-muted-foreground">
                    Belum ada rincian item anggaran. Klik <strong
                        >Tambah Item</strong
                    > untuk menambahkan.
                </div>
            {:else}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-zinc-200 dark:border-zinc-800 text-muted-foreground font-semibold"
                            >
                                <th class="pb-2 pr-3">Nama Barang / Jasa</th>
                                <th class="pb-2 text-center w-24">Volume</th>
                                <th class="pb-2 text-center w-28">Satuan</th>
                                <th class="pb-2 text-right w-44"
                                    >Harga Satuan</th
                                >
                                <th class="pb-2 text-right w-44">Total</th>
                                <th class="pb-2 text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-zinc-200/50 dark:divide-zinc-800/30"
                        >
                            {#each form.items as item, idx}
                                <tr>
                                    <td class="py-3 pr-3">
                                        <input
                                            type="text"
                                            bind:value={item.name}
                                            placeholder="Nama item"
                                            class="w-full px-3 py-2 bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium text-sm"
                                            required
                                        />
                                    </td>
                                    <td class="py-3 pr-3">
                                        <input
                                            type="number"
                                            bind:value={item.volume}
                                            oninput={calculateTotal}
                                            min="0.01"
                                            step="any"
                                            class="w-full px-3 py-2 bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary text-center font-medium text-sm"
                                            required
                                        />
                                    </td>
                                    <td class="py-3 pr-3">
                                        <input
                                            type="text"
                                            bind:value={item.unit}
                                            placeholder="Satuan"
                                            class="w-full px-3 py-2 bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary text-center font-medium text-sm"
                                            required
                                        />
                                    </td>
                                    <td class="py-3 pr-3">
                                        <input
                                            type="number"
                                            bind:value={item.unit_price}
                                            oninput={calculateTotal}
                                            min="0"
                                            step="any"
                                            class="w-full px-3 py-2 bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary text-right font-medium text-sm"
                                            required
                                        />
                                    </td>
                                    <td
                                        class="py-3 pr-3 text-right font-bold text-sm whitespace-nowrap"
                                    >
                                        {formatRupiah(
                                            Number(item.volume) *
                                                Number(item.unit_price),
                                        )}
                                    </td>
                                    <td class="py-3 text-center">
                                        <button
                                            type="button"
                                            onclick={() => removeItem(idx)}
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-rose-500 hover:bg-rose-500/10 cursor-pointer"
                                            title="Hapus item"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            {/if}

            <!-- Computed Total Pagu Display -->
            <div
                class="flex justify-between items-center p-4 bg-zinc-100 dark:bg-zinc-900 rounded-lg font-bold text-sm mt-3"
            >
                <span class="text-muted-foreground"
                    >Total Pagu Baru (Dihitung Otomatis):</span
                >
                <span class="text-lg font-extrabold text-foreground"
                    >{formatRupiah(form.amount)}</span
                >
            </div>
        </div>

        <!-- Revision Description -->
        <div
            class="bg-card border border-sidebar-border/50 rounded-xl p-6 space-y-3"
        >
            <label
                for="revision_description"
                class="text-sm font-bold text-rose-500"
                >Alasan / Keterangan Revisi *</label
            >
            <textarea
                id="revision_description"
                bind:value={form.revision_description}
                placeholder="E.g., Pergeseran anggaran belanja barang untuk mendukung diklat simulator semester I"
                rows="3"
                class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary font-medium"
                required
            ></textarea>
            {#if form.errors.revision_description}
                <p class="text-xs text-rose-500">
                    {form.errors.revision_description}
                </p>
            {/if}
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap justify-between items-center gap-3">
            <button
                type="button"
                onclick={() => (showRevisionHistory = !showRevisionHistory)}
                class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-background px-4 text-xs font-semibold hover:bg-accent cursor-pointer gap-1.5"
            >
                <History class="size-4" />
                {showRevisionHistory ? 'Sembunyikan' : 'Lihat'} Histori Revisi
            </button>

            <div class="flex gap-3">
                <Link
                    href={toUrl(budgetsIndex())}
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-800 bg-background px-5 text-sm font-semibold hover:bg-accent cursor-pointer"
                >
                    Batal
                </Link>
                <button
                    type="submit"
                    disabled={form.processing}
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-primary hover:bg-primary/95 text-white px-5 text-sm font-bold cursor-pointer transition-colors gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <Save class="size-4" />
                    {form.processing ? 'Menyimpan...' : 'Simpan & Revisi POK'}
                </button>
            </div>
        </div>
    </form>

    <!-- Revision History Section (inline, not modal) -->
    {#if showRevisionHistory}
        <div
            class="bg-card border border-sidebar-border/50 rounded-xl p-6 space-y-4"
        >
            <h3
                class="text-sm font-bold text-foreground border-b border-sidebar-border/20 pb-3"
            >
                Histori Revisi Anggaran (POK)
            </h3>

            {#if !budget.revisions || budget.revisions.length === 0}
                <div class="text-center py-12 text-sm text-muted-foreground">
                    Belum ada histori revisi untuk pagu anggaran ini.
                </div>
            {:else}
                <div class="space-y-6">
                    {#each budget.revisions as revision (revision.id)}
                        <div
                            class="border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden bg-zinc-50/50 dark:bg-zinc-900/10"
                        >
                            <!-- Revision Header -->
                            <div
                                class="p-4 bg-zinc-100/50 dark:bg-zinc-900/40 border-b border-zinc-200 dark:border-zinc-800 flex flex-wrap justify-between items-center gap-3"
                            >
                                <div>
                                    <div
                                        class="flex items-center gap-2 flex-wrap"
                                    >
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
                </div>
            {/if}
        </div>
    {/if}
</div>
