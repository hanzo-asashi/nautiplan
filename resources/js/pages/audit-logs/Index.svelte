<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as auditLogIndex } from '@/routes/audit-logs';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Audit Logs',
                href: auditLogIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Clock from 'lucide-svelte/icons/clock';
    import Eye from 'lucide-svelte/icons/eye';
    import Filter from 'lucide-svelte/icons/filter';
    import Globe from 'lucide-svelte/icons/globe';
    import History from 'lucide-svelte/icons/history';
    import Monitor from 'lucide-svelte/icons/monitor';
    import Search from 'lucide-svelte/icons/search';
    import UserIcon from 'lucide-svelte/icons/user';
    import X from 'lucide-svelte/icons/x';
    import AppHead from '@/components/AppHead.svelte';
    import EmptyState from '@/components/EmptyState.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import StatusBadge from '@/components/StatusBadge.svelte';
    import { toUrl, formatRupiah } from '@/lib/utils';

    interface AuditLogItem {
        id: number;
        user_id: number | null;
        user: { name: string } | null;
        auditable_type: string;
        auditable_id: number;
        event: 'created' | 'updated' | 'deleted';
        old_values: Record<string, any> | null;
        new_values: Record<string, any> | null;
        ip_address: string | null;
        user_agent: string | null;
        created_at: string;
    }

    let {
        logs,
        users,
        filters,
    }: {
        logs: {
            data: AuditLogItem[];
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
            current_page: number;
            total: number;
        };
        users: Array<{ id: number; name: string }>;
        filters: { search?: string; user_id?: string; event?: string };
    } = $props();

    // Filter states
    let search = $state(filters.search || '');
    let selectedUser = $state(filters.user_id || '');
    let selectedEvent = $state(filters.event || '');

    // Modal details states
    let detailOpen = $state(false);
    let selectedLog = $state<AuditLogItem | null>(null);
    let detailDialogEl: HTMLDialogElement;

    // Friendly names for models
    function getFriendlyModelName(type: string): string {
        const parts = type.split('\\');
        const modelName = parts[parts.length - 1];

        const mapping: Record<string, string> = {
            Renstra: 'Rencana Strategis (Renstra)',
            RenstraIndicator: 'Indikator Renstra',
            Renja: 'Rencana Kerja (Renja)',
            Program: 'Program',
            ProgramIndicator: 'Indikator Program',
            Activity: 'Kegiatan',
            SubActivity: 'Sub Kegiatan',
            ActivityBudget: 'Anggaran Kegiatan',
            BudgetRealization: 'Realisasi Anggaran',
            ActivityIndicator: 'Indikator Kegiatan',
            ApprovalRequest: 'Pengajuan Persetujuan',
            ApprovalStep: 'Tahap Persetujuan',
            ActivityDocument: 'Dokumen Kegiatan',
            AuditLog: 'Log Audit',
            User: 'Pengguna/User',
            Role: 'Peran/Role',
            Permission: 'Izin/Permission',
            Unit: 'Unit Kerja',
            FiscalYear: 'Tahun Anggaran',
        };

        return mapping[modelName] || modelName;
    }

    // Format fields for displaying in human-readable way
    function formatFieldName(field: string): string {
        const mapping: Record<string, string> = {
            name: 'Nama',
            code: 'Kode',
            title: 'Judul',
            description: 'Deskripsi',
            objective: 'Sasaran / Tujuan',
            status: 'Status',
            priority: 'Prioritas',
            start_date: 'Tanggal Mulai',
            end_date: 'Tanggal Selesai',
            amount: 'Jumlah Anggaran / Realisasi',
            total_budget: 'Total Anggaran',
            progress_percentage: 'Persentase Kemajuan (%)',
            location: 'Lokasi',
            email: 'Email',
            phone: 'Nomor Telepon',
            employee_id: 'NIP / NIDN',
            is_active: 'Status Aktif',
            is_locked: 'Status Terkunci',
            year: 'Tahun',
            budget_category: 'Kategori Anggaran',
            notes: 'Catatan / Alasan',
            current_step: 'Tahap Pengajuan Saat Ini',
            unit_id: 'ID Unit Kerja',
            fiscal_year_id: 'ID Tahun Anggaran',
            renstra_id: 'ID Renstra',
            renja_id: 'ID Renja',
            program_id: 'ID Program',
            activity_id: 'ID Kegiatan',
            activity_budget_id: 'ID Kategori Anggaran',
            responsible_user_id: 'ID Penanggung Jawab',
            assigned_to: 'ID Petugas ditunjuk',
            verified_by: 'ID Verifikator',
            verified_at: 'Waktu Verifikasi',
            receipt_number: 'Nomor Kuitansi / Bukti',
        };

        if (mapping[field]) {
            return mapping[field];
        }

        // Fallback: convert snake_case to Title Case
        return field
            .split('_')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    // Value formatter helper
    function formatValue(key: string, value: any): string {
        if (value === null || value === undefined) {
            return '-';
        }

        if (typeof value === 'boolean') {
            return value ? 'Ya / Aktif' : 'Tidak / Non-aktif';
        }

        // Format rupiah for money fields
        if (
            ['amount', 'total_budget', 'budget'].includes(key) ||
            key.endsWith('_budget') ||
            key.endsWith('_amount')
        ) {
            const num = parseFloat(value);

            return isNaN(num) ? String(value) : formatRupiah(num);
        }

        // Format dates
        if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return formatDate(value);
        }

        if (typeof value === 'object') {
            return JSON.stringify(value, null, 2);
        }

        return String(value);
    }

    // Format date Indonesian style
    function formatDate(dateStr: string, includeTime = false): string {
        if (!dateStr) {
            return '-';
        }

        try {
            const d = new Date(dateStr);

            if (isNaN(d.getTime())) {
                return dateStr;
            }

            const months = [
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember',
            ];

            const day = d.getDate();
            const month = months[d.getMonth()];
            const year = d.getFullYear();

            let result = `${day} ${month} ${year}`;

            if (includeTime) {
                const hours = String(d.getHours()).padStart(2, '0');
                const minutes = String(d.getMinutes()).padStart(2, '0');
                result += ` ${hours}:${minutes}`;
            }

            return result;
        } catch {
            return dateStr;
        }
    }

    function handleFilter(e: Event) {
        e.preventDefault();
        router.get(
            toUrl(auditLogIndex()),
            {
                search,
                user_id: selectedUser,
                event: selectedEvent,
            },
            { preserveState: true },
        );
    }

    function resetFilters() {
        search = '';
        selectedUser = '';
        selectedEvent = '';
        router.get(toUrl(auditLogIndex()), {}, { preserveState: true });
    }

    function showDetails(log: AuditLogItem) {
        selectedLog = log;
        detailOpen = true;
    }

    $effect(() => {
        if (!detailDialogEl) {
            return;
        }

        if (detailOpen) {
            if (!detailDialogEl.open) {
                detailDialogEl.showModal();
            }
        } else {
            if (detailDialogEl.open) {
                detailDialogEl.close();
            }
        }
    });

    function closeDetail() {
        detailOpen = false;
        selectedLog = null;
    }

    // Calculate diffs for updated logs
    const diffKeys = $derived.by(() => {
        if (!selectedLog || selectedLog.event !== 'updated') {
            return [];
        }

        const oldVals = selectedLog.old_values || {};
        const newVals = selectedLog.new_values || {};

        // Get all unique keys from old and new values
        const allKeys = new Set([
            ...Object.keys(oldVals),
            ...Object.keys(newVals),
        ]);

        // Return only keys where the values actually differ
        return Array.from(allKeys).filter((key) => {
            const oldVal = oldVals[key];
            const newVal = newVals[key];

            // Compare primitive values or string representation
            if (typeof oldVal === 'object' || typeof newVal === 'object') {
                return JSON.stringify(oldVal) !== JSON.stringify(newVal);
            }

            return String(oldVal) !== String(newVal);
        });
    });
</script>

<AppHead title="Audit Logs" />

<div class="p-6 space-y-6 font-sans">
    <PageHeader
        title="Audit Logs"
        description="Pantau riwayat aktivitas perubahan data pada sistem NautiPlan"
    />

    <!-- Filters Section -->
    <div
        class="rounded-xl border border-sidebar-border/50 bg-card/45 backdrop-blur-md p-5 shadow-sm space-y-4"
    >
        <form
            onsubmit={handleFilter}
            class="grid grid-cols-1 gap-4 md:grid-cols-4 items-end"
        >
            <!-- Search Input -->
            <div class="space-y-1.5">
                <label
                    for="search"
                    class="text-xs font-semibold text-muted-foreground uppercase tracking-wider"
                    >Cari Kata Kunci</label
                >
                <div class="relative">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground"
                    />
                    <input
                        type="text"
                        id="search"
                        placeholder="IP, tipe model, dsb..."
                        bind:value={search}
                        class="w-full pl-9 pr-4 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary transition-colors text-foreground"
                    />
                </div>
            </div>

            <!-- User Select -->
            <div class="space-y-1.5">
                <label
                    for="user"
                    class="text-xs font-semibold text-muted-foreground uppercase tracking-wider"
                    >Pengguna</label
                >
                <select
                    id="user"
                    bind:value={selectedUser}
                    class="w-full px-3 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary transition-colors text-foreground"
                >
                    <option value="">Semua Pengguna</option>
                    {#each users as u}
                        <option value={String(u.id)}>{u.name}</option>
                    {/each}
                </select>
            </div>

            <!-- Event Select -->
            <div class="space-y-1.5">
                <label
                    for="event"
                    class="text-xs font-semibold text-muted-foreground uppercase tracking-wider"
                    >Jenis Perubahan</label
                >
                <select
                    id="event"
                    bind:value={selectedEvent}
                    class="w-full px-3 py-2 text-sm bg-background border border-zinc-200/60 dark:border-zinc-800 rounded-lg outline-none focus:border-primary transition-colors text-foreground"
                >
                    <option value="">Semua Perubahan</option>
                    <option value="created">Dibuat (Created)</option>
                    <option value="updated">Diperbarui (Updated)</option>
                    <option value="deleted">Dihapus (Deleted)</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="flex-1 inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white text-sm font-semibold shadow-md shadow-primary/25 transition-colors cursor-pointer gap-1.5"
                >
                    <Filter class="size-4" />
                    Cari
                </button>
                {#if search || selectedUser || selectedEvent}
                    <button
                        type="button"
                        onclick={resetFilters}
                        class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background px-3 text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
                        title="Reset Filter"
                    >
                        <X class="size-4" />
                    </button>
                {/if}
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    {#if logs.data.length === 0}
        <EmptyState
            title="Tidak ada Log Audit ditemukan"
            description="Tidak ada riwayat perubahan yang sesuai dengan filter Anda."
        />
    {:else}
        <div
            class="rounded-xl border border-sidebar-border/50 bg-card/45 backdrop-blur-md p-6 shadow-sm overflow-hidden space-y-4 animate-in fade-in duration-300"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr
                            class="border-b border-sidebar-border/50 text-muted-foreground font-semibold"
                        >
                            <th class="pb-3 pr-4">Waktu</th>
                            <th class="pb-3 pr-4">Pengguna</th>
                            <th class="pb-3 pr-4">Aksi</th>
                            <th class="pb-3 pr-4">Modul / Objek</th>
                            <th class="pb-3 pr-4">IP Address</th>
                            <th class="pb-3 text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/40">
                        {#each logs.data as log (log.id)}
                            <tr
                                class="hover:bg-zinc-100/40 dark:hover:bg-zinc-900/40 transition-colors"
                            >
                                <td
                                    class="py-3.5 pr-4 text-muted-foreground font-medium text-xs whitespace-nowrap"
                                >
                                    {formatDate(log.created_at, true)}
                                </td>
                                <td
                                    class="py-3.5 pr-4 font-semibold text-foreground"
                                >
                                    {#if log.user}
                                        <span
                                            class="inline-flex items-center gap-1"
                                        >
                                            <UserIcon
                                                class="size-3.5 text-muted-foreground"
                                            />
                                            {log.user.name}
                                        </span>
                                    {:else}
                                        <span
                                            class="text-muted-foreground italic font-normal"
                                            >Sistem</span
                                        >
                                    {/if}
                                </td>
                                <td class="py-3.5 pr-4">
                                    <StatusBadge status={log.event} />
                                </td>
                                <td class="py-3.5 pr-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-foreground text-sm"
                                        >
                                            {getFriendlyModelName(
                                                log.auditable_type,
                                            )}
                                        </span>
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >ID: {log.auditable_id}</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="py-3.5 pr-4 font-mono text-xs text-muted-foreground"
                                >
                                    {log.ip_address || '-'}
                                </td>
                                <td class="py-3.5 text-right">
                                    <button
                                        onclick={() => showDetails(log)}
                                        class="inline-flex h-8 px-3 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground transition-colors cursor-pointer gap-1.5 text-xs font-semibold"
                                        title="Lihat Detail Perubahan"
                                    >
                                        <Eye class="size-3.5" />
                                        Lihat
                                    </button>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {#if logs.links.length > 3}
                <div
                    class="flex items-center justify-between pt-4 border-t border-sidebar-border/30"
                >
                    <span class="text-xs text-muted-foreground font-medium">
                        Menampilkan halaman <span
                            class="font-bold text-foreground"
                            >{logs.current_page}</span
                        >
                        dari total
                        <span class="font-bold text-foreground"
                            >{logs.total}</span
                        > logs
                    </span>
                    <div class="flex items-center gap-1.5">
                        {#each logs.links as link}
                            {#if link.url}
                                <Link
                                    href={link.url}
                                    class="px-2.5 py-1 text-xs font-semibold rounded-md border border-zinc-200/50 dark:border-zinc-800 transition-colors 
                                        {link.active
                                        ? 'bg-primary text-white border-primary shadow-sm'
                                        : 'bg-background hover:bg-accent'}"
                                >
                                    {@html link.label}
                                </Link>
                            {:else}
                                <span
                                    class="px-2.5 py-1 text-xs font-semibold text-muted-foreground/50 cursor-not-allowed"
                                >
                                    {@html link.label}
                                </span>
                            {/if}
                        {/each}
                    </div>
                </div>
            {/if}
        </div>
    {/if}
</div>

<!-- Log Diff Detail Modal -->
<dialog
    bind:this={detailDialogEl}
    onclose={closeDetail}
    onclick={(e) => {
        if (e.target === detailDialogEl) {
            closeDetail();
        }
    }}
    class="rounded-2xl border border-sidebar-border/50 bg-card/95 backdrop-blur-md p-6 shadow-2xl max-w-2xl w-full text-foreground backdrop:bg-zinc-950/40 backdrop:backdrop-blur-sm outline-none overflow-hidden flex flex-col max-h-[85vh] font-sans"
>
    {#if selectedLog}
        <!-- Header -->
        <div
            class="flex items-start justify-between pb-4 border-b border-sidebar-border/50 mb-4 flex-shrink-0"
        >
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <History class="size-5 text-primary" />
                    <h3 class="text-lg font-bold text-foreground">
                        Detail Perubahan Data
                    </h3>
                </div>
                <p class="text-xs text-muted-foreground">
                    Perubahan pada {getFriendlyModelName(
                        selectedLog.auditable_type,
                    )} (ID: {selectedLog.auditable_id})
                </p>
            </div>
            <button
                onclick={closeDetail}
                class="rounded-full p-1 hover:bg-accent text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Metadata Grid -->
        <div
            class="grid grid-cols-2 gap-4 bg-muted/30 p-4 rounded-xl border border-sidebar-border/30 text-xs mb-4 flex-shrink-0"
        >
            <div class="flex items-center gap-2">
                <Clock class="size-4 text-muted-foreground" />
                <div>
                    <span
                        class="block text-[10px] text-muted-foreground uppercase font-bold"
                        >Waktu Transaksi</span
                    >
                    <span class="font-semibold text-foreground"
                        >{formatDate(selectedLog.created_at, true)}</span
                    >
                </div>
            </div>
            <div class="flex items-center gap-2">
                <UserIcon class="size-4 text-muted-foreground" />
                <div>
                    <span
                        class="block text-[10px] text-muted-foreground uppercase font-bold"
                        >Dilakukan Oleh</span
                    >
                    <span class="font-semibold text-foreground"
                        >{selectedLog.user?.name || 'Sistem'}</span
                    >
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Globe class="size-4 text-muted-foreground" />
                <div>
                    <span
                        class="block text-[10px] text-muted-foreground uppercase font-bold"
                        >IP Address</span
                    >
                    <span class="font-semibold text-foreground font-mono"
                        >{selectedLog.ip_address || '-'}</span
                    >
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Monitor class="size-4 text-muted-foreground" />
                <div>
                    <span
                        class="block text-[10px] text-muted-foreground uppercase font-bold"
                        >Event Aksi</span
                    >
                    <StatusBadge status={selectedLog.event} />
                </div>
            </div>
        </div>

        <!-- User Agent Display -->
        {#if selectedLog.user_agent}
            <div
                class="px-4 py-2 bg-muted/20 border border-sidebar-border/30 rounded-lg text-[11px] text-muted-foreground font-mono truncate mb-4 flex-shrink-0"
                title={selectedLog.user_agent}
            >
                <span class="font-bold text-foreground">User Agent:</span>
                {selectedLog.user_agent}
            </div>
        {/if}

        <!-- Values/Diff display -->
        <div class="flex-1 overflow-y-auto pr-1">
            {#if selectedLog.event === 'created'}
                <!-- Created display: show new values -->
                <div class="space-y-3">
                    <h4
                        class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400"
                    >
                        Data Baru Yang Dibuat
                    </h4>
                    <div
                        class="border border-sidebar-border/50 rounded-xl overflow-hidden bg-emerald-500/[0.02]"
                    >
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr
                                    class="bg-emerald-500/10 border-b border-sidebar-border/50 text-emerald-800 dark:text-emerald-300 font-semibold"
                                >
                                    <th class="p-3 w-1/3">Kolom / Atribut</th>
                                    <th class="p-3">Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/30">
                                {#each Object.entries(selectedLog.new_values || {}) as [key, val]}
                                    <tr class="hover:bg-emerald-500/[0.04]">
                                        <td
                                            class="p-3 font-semibold text-foreground"
                                            >{formatFieldName(key)}</td
                                        >
                                        <td
                                            class="p-3 text-emerald-600 dark:text-emerald-300 font-medium whitespace-pre-wrap"
                                            >{formatValue(key, val)}</td
                                        >
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </div>
            {:else if selectedLog.event === 'deleted'}
                <!-- Deleted display: show old values -->
                <div class="space-y-3">
                    <h4
                        class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400"
                    >
                        Data Yang Dihapus
                    </h4>
                    <div
                        class="border border-sidebar-border/50 rounded-xl overflow-hidden bg-rose-500/[0.02]"
                    >
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr
                                    class="bg-rose-500/10 border-b border-sidebar-border/50 text-rose-800 dark:text-rose-300 font-semibold"
                                >
                                    <th class="p-3 w-1/3">Kolom / Atribut</th>
                                    <th class="p-3">Nilai Sebelum Dihapus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/30">
                                {#each Object.entries(selectedLog.old_values || {}) as [key, val]}
                                    <tr class="hover:bg-rose-500/[0.04]">
                                        <td
                                            class="p-3 font-semibold text-foreground"
                                            >{formatFieldName(key)}</td
                                        >
                                        <td
                                            class="p-3 text-rose-600 dark:text-rose-300 font-medium line-through whitespace-pre-wrap"
                                            >{formatValue(key, val)}</td
                                        >
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </div>
            {:else if selectedLog.event === 'updated'}
                <!-- Updated display: show only diffs -->
                <div class="space-y-3">
                    <h4
                        class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400"
                    >
                        Perbandingan Perubahan Data
                    </h4>

                    {#if diffKeys.length === 0}
                        <div
                            class="p-4 rounded-xl border border-sidebar-border/30 text-center text-xs text-muted-foreground italic bg-muted/20"
                        >
                            Tidak ada perubahan signifikan yang dicatat (hanya
                            perubahan timestamp/kolom internal).
                        </div>
                    {:else}
                        <div
                            class="border border-sidebar-border/50 rounded-xl overflow-hidden bg-amber-500/[0.01]"
                        >
                            <table
                                class="w-full text-left text-xs border-collapse"
                            >
                                <thead>
                                    <tr
                                        class="bg-amber-500/10 border-b border-sidebar-border/50 text-amber-800 dark:text-amber-300 font-semibold"
                                    >
                                        <th class="p-3 w-1/4"
                                            >Kolom / Atribut</th
                                        >
                                        <th
                                            class="p-3 w-3/8 text-rose-700 dark:text-rose-400"
                                            >Sebelum (Lama)</th
                                        >
                                        <th
                                            class="p-3 w-3/8 text-emerald-700 dark:text-emerald-400"
                                            >Sesudah (Baru)</th
                                        >
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-sidebar-border/30"
                                >
                                    {#each diffKeys as key}
                                        <tr class="hover:bg-amber-500/[0.03]">
                                            <td
                                                class="p-3 font-semibold text-foreground"
                                                >{formatFieldName(key)}</td
                                            >
                                            <td
                                                class="p-3 text-rose-600 dark:text-rose-400/90 line-through bg-rose-500/[0.02] whitespace-pre-wrap"
                                            >
                                                {formatValue(
                                                    key,
                                                    selectedLog.old_values?.[
                                                        key
                                                    ],
                                                )}
                                            </td>
                                            <td
                                                class="p-3 text-emerald-600 dark:text-emerald-400 bg-emerald-500/[0.02] font-semibold whitespace-pre-wrap"
                                            >
                                                {formatValue(
                                                    key,
                                                    selectedLog.new_values?.[
                                                        key
                                                    ],
                                                )}
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    {/if}
                </div>
            {/if}
        </div>

        <!-- Footer actions -->
        <div
            class="flex justify-end pt-4 border-t border-sidebar-border/50 mt-4 flex-shrink-0"
        >
            <button
                type="button"
                onclick={closeDetail}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200/50 dark:border-zinc-800 bg-background px-5 py-2 text-sm font-semibold transition-colors hover:bg-accent hover:text-accent-foreground outline-none cursor-pointer text-foreground"
            >
                Tutup
            </button>
        </div>
    {/if}
</dialog>
