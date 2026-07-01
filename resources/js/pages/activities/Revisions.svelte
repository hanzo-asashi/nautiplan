<script module lang="ts">
    import { dashboard } from '@/routes';
    import { show as showActivity } from '@/routes/activities';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Kegiatan',
                href: '#',
            },
            {
                title: 'Riwayat Revisi',
                href: '',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import Clock from 'lucide-svelte/icons/clock';
    import Info from 'lucide-svelte/icons/info';
    import Laptop from 'lucide-svelte/icons/laptop';
    import Network from 'lucide-svelte/icons/network';
    import User from 'lucide-svelte/icons/user';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';

    let {
        activity,
        revisions = { data: [], links: [] },
    }: {
        activity: {
            id: number;
            code: string;
            name: string;
            unit: { name: string };
            fiscal_year: { year: number };
        };
        revisions: {
            data: Array<{
                id: number;
                user_id: number | null;
                auditable_type: string;
                auditable_id: number;
                event: 'created' | 'updated' | 'deleted';
                old_values: Record<string, any> | null;
                new_values: Record<string, any> | null;
                ip_address: string | null;
                user_agent: string | null;
                created_at: string;
                user?: { name: string } | null;
            }>;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
    } = $props();

    function formatAuditableType(type: string): string {
        if (type.includes('ActivityBudget')) {
            return 'Anggaran';
        }

        if (type.includes('SubActivity')) {
            return 'Sub-Kegiatan';
        }

        if (type.includes('Activity')) {
            return 'Kegiatan Utama';
        }

        return type;
    }

    function formatFieldName(name: string): string {
        const labels: Record<string, string> = {
            code: 'Kode',
            name: 'Nama',
            description: 'Deskripsi',
            status: 'Status',
            priority: 'Prioritas',
            start_date: 'Tanggal Mulai',
            end_date: 'Tanggal Selesai',
            progress_percentage: 'Progres (%)',
            location: 'Lokasi',
            amount: 'Pagu Anggaran',
            assigned_to: 'Penanggung Jawab Tugas',
            responsible_user_id: 'Penanggung Jawab Kegiatan',
        };

        return labels[name] || name;
    }

    function formatValue(key: string, val: any): string {
        if (val === null || val === undefined) {
            return 'Kosong';
        }

        if (typeof val === 'boolean') {
            return val ? 'Ya' : 'Tidak';
        }

        if (key === 'amount') {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(val);
        }

        if (typeof val === 'object') {
            return JSON.stringify(val);
        }

        return String(val);
    }

    function formatDateTime(dateStr: string): string {
        return new Date(dateStr).toLocaleString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }
</script>

<AppHead title={`Riwayat Perubahan - ${activity.name}`} />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <!-- Header Area -->
    <div class="flex flex-col gap-3">
        <Link
            href={toUrl(showActivity({ activity: activity.id }))}
            class="flex items-center gap-1.5 text-xs text-muted-foreground hover:text-primary transition-colors w-fit"
        >
            <ArrowLeft class="size-3.5" />
            Kembali ke Detail Kegiatan
        </Link>
        <PageHeader
            title="Riwayat Perubahan & Revisi"
            description={`Log audit terperinci rencana kegiatan: [${activity.code}] ${activity.name}`}
        />
    </div>

    <!-- Timeline Layout -->
    <div
        class="rounded-xl border border-sidebar-border/30 bg-card/40 backdrop-blur-md p-6 shadow-sm flex-1 space-y-6"
    >
        {#if revisions.data.length === 0}
            <div
                class="p-12 text-center text-sm text-muted-foreground/60 italic space-y-2"
            >
                <Info class="size-8 text-muted-foreground/30 mx-auto" />
                <p>
                    Belum ada riwayat perubahan yang dicatat untuk kegiatan ini.
                </p>
            </div>
        {:else}
            <div
                class="relative border-l-2 border-sidebar-border/50 pl-6 space-y-8"
            >
                {#each revisions.data as revision (revision.id)}
                    <div class="relative">
                        <!-- Timeline circle node -->
                        <div
                            class="absolute -left-[31px] top-1.5 size-4 rounded-full border bg-background flex items-center justify-center
                            {revision.event === 'created'
                                ? 'border-emerald-500 text-emerald-500 bg-emerald-50/50'
                                : ''}
                            {revision.event === 'updated'
                                ? 'border-blue-500 text-blue-500 bg-blue-50/50'
                                : ''}
                            {revision.event === 'deleted'
                                ? 'border-rose-500 text-rose-500 bg-rose-50/50'
                                : ''}"
                        >
                            <span
                                class="size-1.5 rounded-full
                                {revision.event === 'created'
                                    ? 'bg-emerald-500'
                                    : ''}
                                {revision.event === 'updated'
                                    ? 'bg-blue-500'
                                    : ''}
                                {revision.event === 'deleted'
                                    ? 'bg-rose-500'
                                    : ''}"
                            ></span>
                        </div>

                        <!-- Card Body -->
                        <div
                            class="bg-background border border-sidebar-border/20 rounded-xl p-4 space-y-3 hover:border-sidebar-border/40 hover:shadow-sm transition-all"
                        >
                            <!-- Title & Metadata -->
                            <div
                                class="flex flex-wrap items-center justify-between gap-3 border-b border-sidebar-border/10 pb-2"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                        {revision.event === 'created'
                                            ? 'bg-emerald-500/10 text-emerald-600'
                                            : ''}
                                        {revision.event === 'updated'
                                            ? 'bg-blue-500/10 text-blue-600'
                                            : ''}
                                        {revision.event === 'deleted'
                                            ? 'bg-rose-500/10 text-rose-600'
                                            : ''}"
                                    >
                                        {revision.event === 'created'
                                            ? 'Baru'
                                            : ''}
                                        {revision.event === 'updated'
                                            ? 'Revisi'
                                            : ''}
                                        {revision.event === 'deleted'
                                            ? 'Hapus'
                                            : ''}
                                    </span>
                                    <span
                                        class="text-xs font-bold text-foreground"
                                    >
                                        Perubahan {formatAuditableType(
                                            revision.auditable_type,
                                        )}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-3 text-[10px] text-muted-foreground"
                                >
                                    <span class="flex items-center gap-1">
                                        <Clock class="size-3" />
                                        {formatDateTime(revision.created_at)}
                                    </span>
                                    {#if revision.ip_address}
                                        <span
                                            class="flex items-center gap-1 hidden sm:inline-flex"
                                        >
                                            <Network class="size-3" />
                                            {revision.ip_address}
                                        </span>
                                    {/if}
                                </div>
                            </div>

                            <!-- User Info -->
                            <div
                                class="flex items-center gap-2 text-[10px] text-muted-foreground"
                            >
                                <User class="size-3.5 text-primary" />
                                <span
                                    >Diubah oleh: <strong
                                        class="text-foreground"
                                        >{revision.user?.name ||
                                            'Sistem'}</strong
                                    ></span
                                >
                                {#if revision.user_agent}
                                    <span
                                        class="flex items-center gap-1 hidden md:inline-flex ml-2"
                                    >
                                        <Laptop class="size-3" />
                                        <span
                                            class="truncate max-w-[200px]"
                                            title={revision.user_agent}
                                            >{revision.user_agent}</span
                                        >
                                    </span>
                                {/if}
                            </div>

                            <!-- Comparison Values Block -->
                            {#if revision.event === 'updated' && revision.new_values}
                                <div
                                    class="overflow-x-auto border border-sidebar-border/10 rounded-lg"
                                >
                                    <table
                                        class="w-full text-left text-xs border-collapse divide-y divide-sidebar-border/10"
                                    >
                                        <thead>
                                            <tr
                                                class="bg-zinc-50/50 dark:bg-zinc-900/50 text-[10px] font-bold text-muted-foreground uppercase tracking-wider"
                                            >
                                                <th class="px-4 py-2"
                                                    >Parameter</th
                                                >
                                                <th class="px-4 py-2"
                                                    >Nilai Awal</th
                                                >
                                                <th class="px-4 py-2"
                                                    >Nilai Baru</th
                                                >
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-sidebar-border/10"
                                        >
                                            {#each Object.keys(revision.new_values) as key}
                                                <tr>
                                                    <td
                                                        class="px-4 py-2.5 font-semibold text-foreground/80"
                                                        >{formatFieldName(
                                                            key,
                                                        )}</td
                                                    >
                                                    <td
                                                        class="px-4 py-2.5 text-rose-600 dark:text-rose-400 bg-rose-50/10 line-through"
                                                    >
                                                        {formatValue(
                                                            key,
                                                            revision
                                                                .old_values?.[
                                                                key
                                                            ],
                                                        )}
                                                    </td>
                                                    <td
                                                        class="px-4 py-2.5 text-emerald-600 dark:text-emerald-400 bg-emerald-50/10 font-bold"
                                                    >
                                                        {formatValue(
                                                            key,
                                                            revision.new_values[
                                                                key
                                                            ],
                                                        )}
                                                    </td>
                                                </tr>
                                            {/each}
                                        </tbody>
                                    </table>
                                </div>
                            {:else if revision.event === 'created' && revision.new_values}
                                <div
                                    class="p-3 bg-zinc-50/50 dark:bg-zinc-900/20 border border-sidebar-border/10 rounded-lg space-y-1.5 text-xs"
                                >
                                    <span
                                        class="text-[10px] font-bold text-muted-foreground uppercase block mb-1"
                                        >Detail Entri Baru:</span
                                    >
                                    {#each Object.keys(revision.new_values).slice(0, 5) as key}
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground"
                                                >{formatFieldName(key)}:</span
                                            >
                                            <span
                                                class="font-semibold text-foreground"
                                                >{formatValue(
                                                    key,
                                                    revision.new_values[key],
                                                )}</span
                                            >
                                        </div>
                                    {/each}
                                    {#if Object.keys(revision.new_values).length > 5}
                                        <span
                                            class="text-[9px] text-muted-foreground/60 block italic"
                                            >dan beberapa detail teknis
                                            lainnya...</span
                                        >
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>
                {/each}
            </div>

            <!-- Pagination -->
            {#if revisions.links && revisions.links.length > 3}
                <div
                    class="flex items-center justify-center gap-1.5 pt-6 border-t border-sidebar-border/20"
                >
                    {#each revisions.links as link}
                        {#if link.url}
                            <Link
                                href={link.url}
                                class="px-3 py-1.5 text-xs rounded-md border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 transition-colors
                                    {link.active
                                    ? 'bg-primary text-white hover:bg-primary/95 border-primary'
                                    : 'bg-background'}"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span
                                class="px-3 py-1.5 text-xs rounded-md border border-zinc-200/50 dark:border-zinc-800/50 text-muted-foreground/45 bg-zinc-50/20"
                            >
                                {@html link.label}
                            </span>
                        {/if}
                    {/each}
                </div>
            {/if}
        {/if}
    </div>
</div>
