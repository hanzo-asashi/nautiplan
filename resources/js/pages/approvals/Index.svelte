<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as approvalsIndex } from '@/routes/approvals';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Persetujuan Kegiatan',
                href: approvalsIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import AlertCircle from 'lucide-svelte/icons/alert-circle';
    import CheckCircle from 'lucide-svelte/icons/check-circle';
    import Clock from 'lucide-svelte/icons/clock';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';
    import { action as approvalAction } from '@/routes/approvals';

    let {
        pending_approvals = [],
        my_requests = [],
    }: {
        pending_approvals: Array<{
            id: number;
            type: string;
            name: string;
            requester: string;
            requested_at: string;
            current_step_order: number;
            current_step_id: number | null;
            role_name: string | null;
        }>;
        my_requests: Array<{
            id: number;
            type: string;
            name: string;
            status: string;
            notes: string | null;
            requested_at: string;
            steps: Array<{
                role_name: string;
                status: string;
                notes: string | null;
                acted_by: string | null;
                acted_at: string | null;
            }>;
        }>;
    } = $props();

    let activeTab = $state('pending'); // 'pending', 'my-requests'
    let selectedApproval = $state<any>(null); // For the action modal

    // Decision Form
    const actionForm = useForm({
        status: 'approved', // 'approved', 'rejected', 'revision'
        notes: '',
    });

    const openActionModal = (approval: any) => {
        selectedApproval = approval;
        actionForm.status = 'approved';
        actionForm.notes = '';
        actionForm.clearErrors();
    };

    const closeActionModal = () => {
        selectedApproval = null;
    };

    const submitDecision = () => {
        if (!selectedApproval) {
            return;
        }

        actionForm.post(
            toUrl(approvalAction({ approvalRequest: selectedApproval.id })),
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeActionModal();
                },
            },
        );
    };

    const getStatusBadgeClass = (status: string) => {
        switch (status) {
            case 'approved':
                return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
            case 'rejected':
                return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20';
            case 'revision':
                return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
            case 'pending':
            default:
                return 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20';
        }
    };

    const getStatusLabel = (status: string) => {
        switch (status) {
            case 'approved':
                return 'Disetujui';
            case 'rejected':
                return 'Ditolak';
            case 'revision':
                return 'Minta Revisi';
            case 'pending':
                return 'Menunggu';
            default:
                return status;
        }
    };
</script>

<AppHead title="Alur Persetujuan Kegiatan" />

<div class="flex h-full flex-1 flex-col gap-6 p-6 overflow-x-hidden">
    <PageHeader
        title="Persetujuan Kegiatan"
        description="Verifikasi proposal usulan kegiatan baru, berikan persetujuan, penolakan, atau permintaan revisi."
    />

    <!-- Tabs header -->
    <div class="flex gap-2 border-b border-sidebar-border/30 pb-px">
        <button
            onclick={() => (activeTab = 'pending')}
            class="px-4 py-2 text-xs font-semibold border-b-2 -mb-px transition-all
                {activeTab === 'pending'
                ? 'border-primary text-primary font-bold'
                : 'border-transparent text-muted-foreground hover:text-foreground'}"
        >
            Menunggu Tindakan Anda ({pending_approvals.length})
        </button>
        <button
            onclick={() => (activeTab = 'my-requests')}
            class="px-4 py-2 text-xs font-semibold border-b-2 -mb-px transition-all
                {activeTab === 'my-requests'
                ? 'border-primary text-primary font-bold'
                : 'border-transparent text-muted-foreground hover:text-foreground'}"
        >
            Riwayat Pengajuan Anda ({my_requests.length})
        </button>
    </div>

    <!-- Active Tab Panel -->
    <div class="space-y-4">
        {#if activeTab === 'pending'}
            <!-- PENDING ACTION TAB -->
            {#if pending_approvals.length === 0}
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 p-12 text-center text-muted-foreground/60 text-sm italic"
                >
                    Tidak ada usulan kegiatan yang menunggu persetujuan Anda
                    saat ini.
                </div>
            {:else}
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {#each pending_approvals as item (item.id)}
                        <div
                            class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-5 shadow-sm hover:shadow-md hover:border-primary/20 transition-all duration-300 flex flex-col justify-between"
                        >
                            <div class="space-y-3">
                                <div
                                    class="flex justify-between items-center text-[10px]"
                                >
                                    <span
                                        class="font-bold text-primary uppercase bg-primary/10 px-2 py-0.5 rounded border border-primary/20"
                                        >{item.type}</span
                                    >
                                    <span
                                        class="text-muted-foreground font-medium"
                                        >{item.requested_at}</span
                                    >
                                </div>
                                <h4
                                    class="text-sm font-bold text-foreground line-clamp-2"
                                >
                                    {item.name}
                                </h4>
                                <div class="text-xs text-muted-foreground">
                                    Diajukan oleh: <span
                                        class="font-bold text-foreground"
                                        >{item.requester}</span
                                    >
                                </div>
                                <div
                                    class="text-xs p-2 bg-zinc-50/50 dark:bg-zinc-950/40 border border-sidebar-border/20 rounded-md"
                                >
                                    <span
                                        class="text-muted-foreground font-medium"
                                        >Langkah Verifikasi:</span
                                    >
                                    <span
                                        class="font-bold text-foreground block mt-0.5"
                                        >{item.role_name} (Langkah {item.current_step_order}
                                        dari 3)</span
                                    >
                                </div>
                            </div>
                            <button
                                onclick={() => openActionModal(item)}
                                class="mt-4 w-full py-2 text-xs font-bold bg-primary hover:bg-primary/95 text-white rounded-md transition-all cursor-pointer text-center"
                            >
                                Tinjau & Verifikasi
                            </button>
                        </div>
                    {/each}
                </div>
            {/if}
        {:else}
            <!-- MY REQUESTS TAB -->
            {#if my_requests.length === 0}
                <div
                    class="rounded-xl border border-sidebar-border/50 bg-card/40 p-12 text-center text-muted-foreground/60 text-sm italic"
                >
                    Anda belum pernah mengajukan usulan persetujuan kegiatan.
                </div>
            {:else}
                <div class="space-y-4">
                    {#each my_requests as req (req.id)}
                        <div
                            class="rounded-xl border border-sidebar-border/50 bg-card/40 backdrop-blur-md p-6 shadow-sm space-y-4"
                        >
                            <div
                                class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-sidebar-border/30 pb-3"
                            >
                                <div>
                                    <div
                                        class="flex items-center gap-2 text-[10px]"
                                    >
                                        <span
                                            class="font-bold text-primary uppercase bg-primary/10 px-2 py-0.5 rounded border border-primary/20"
                                            >{req.type}</span
                                        >
                                        <span
                                            class="text-muted-foreground font-semibold"
                                            >Diajukan pada {req.requested_at}</span
                                        >
                                    </div>
                                    <h4
                                        class="text-sm font-bold text-foreground mt-1.5"
                                    >
                                        {req.name}
                                    </h4>
                                </div>
                                <span
                                    class="px-2.5 py-1 text-[10px] font-bold uppercase rounded border {getStatusBadgeClass(
                                        req.status,
                                    )}"
                                >
                                    {getStatusLabel(req.status)}
                                </span>
                            </div>

                            <!-- Stepper Timeline -->
                            <div class="grid gap-4 sm:grid-cols-3 relative">
                                {#each req.steps as step, idx}
                                    <div
                                        class="p-3 bg-zinc-50/50 dark:bg-zinc-950/20 border border-sidebar-border/20 rounded-lg space-y-2 text-xs"
                                    >
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <span
                                                class="font-bold text-foreground"
                                                >Langkah {idx + 1}: {step.role_name}</span
                                            >
                                            <span
                                                class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase border {getStatusBadgeClass(
                                                    step.status,
                                                )}"
                                            >
                                                {getStatusLabel(step.status)}
                                            </span>
                                        </div>
                                        <div
                                            class="text-[10px] text-muted-foreground space-y-0.5"
                                        >
                                            {#if step.acted_by}
                                                <div>
                                                    Verifikator: <span
                                                        class="font-semibold text-foreground"
                                                        >{step.acted_by}</span
                                                    >
                                                </div>
                                                <div>
                                                    Waktu: <span
                                                        class="font-semibold text-foreground"
                                                        >{step.acted_at}</span
                                                    >
                                                </div>
                                            {:else}
                                                <div
                                                    class="italic text-muted-foreground/60"
                                                >
                                                    Menunggu verifikasi...
                                                </div>
                                            {/if}
                                        </div>
                                        {#if step.notes}
                                            <div
                                                class="p-1.5 bg-background/50 border border-sidebar-border/30 rounded text-[10px] text-foreground italic whitespace-pre-wrap"
                                            >
                                                Catatan: "{step.notes}"
                                            </div>
                                        {/if}
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        {/if}
    </div>

    <!-- VERIFICATION MODAL / DIALOG -->
    {#if selectedApproval}
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        >
            <div
                class="w-full max-w-lg bg-card border border-sidebar-border/50 rounded-xl shadow-lg p-6 space-y-5"
            >
                <div
                    class="border-b border-sidebar-border/30 pb-3 flex justify-between items-center"
                >
                    <h3
                        class="text-sm font-bold text-foreground uppercase tracking-wider"
                    >
                        Verifikasi Usulan Kegiatan
                    </h3>
                    <button
                        onclick={closeActionModal}
                        class="text-muted-foreground hover:text-foreground text-xs font-semibold cursor-pointer"
                        >Tutup</button
                    >
                </div>

                <div class="space-y-2 text-xs">
                    <p class="text-muted-foreground">Kegiatan:</p>
                    <p class="font-bold text-foreground text-sm">
                        [{selectedApproval.type}] {selectedApproval.name}
                    </p>
                    <p class="text-muted-foreground">
                        Diajukan oleh: <span class="font-bold text-foreground"
                            >{selectedApproval.requester}</span
                        >
                        pada {selectedApproval.requested_at}
                    </p>
                    <p class="text-muted-foreground">
                        Langkah Anda: <span class="font-bold text-foreground"
                            >{selectedApproval.role_name}</span
                        >
                    </p>
                </div>

                <!-- Decision inputs -->
                <form
                    onsubmit={(e) => {
                        e.preventDefault();
                        submitDecision();
                    }}
                    class="space-y-4"
                >
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-foreground"
                            >Keputusan Verifikasi <span class="text-rose-500"
                                >*</span
                            ></label
                        >
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Approve card -->
                            <button
                                type="button"
                                onclick={() => (actionForm.status = 'approved')}
                                class="p-3 border rounded-lg flex flex-col items-center gap-1.5 text-xs transition-all cursor-pointer
                                    {actionForm.status === 'approved'
                                    ? 'bg-emerald-500/10 border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold'
                                    : 'bg-background border-zinc-200 dark:border-zinc-800 text-muted-foreground hover:text-foreground'}"
                            >
                                <CheckCircle class="size-4" />
                                Setujui
                            </button>

                            <!-- Revision card -->
                            <button
                                type="button"
                                onclick={() => (actionForm.status = 'revision')}
                                class="p-3 border rounded-lg flex flex-col items-center gap-1.5 text-xs transition-all cursor-pointer
                                    {actionForm.status === 'revision'
                                    ? 'bg-amber-500/10 border-amber-500 text-amber-600 dark:text-amber-400 font-bold'
                                    : 'bg-background border-zinc-200 dark:border-zinc-800 text-muted-foreground hover:text-foreground'}"
                            >
                                <Clock class="size-4" />
                                Minta Revisi
                            </button>

                            <!-- Reject card -->
                            <button
                                type="button"
                                onclick={() => (actionForm.status = 'rejected')}
                                class="p-3 border rounded-lg flex flex-col items-center gap-1.5 text-xs transition-all cursor-pointer
                                    {actionForm.status === 'rejected'
                                    ? 'bg-rose-500/10 border-rose-500 text-rose-600 dark:text-rose-400 font-bold'
                                    : 'bg-background border-zinc-200 dark:border-zinc-800 text-muted-foreground hover:text-foreground'}"
                            >
                                <AlertCircle class="size-4" />
                                Tolak
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="notes"
                            class="text-xs font-bold text-foreground"
                        >
                            Catatan Verifikator
                            {#if actionForm.status !== 'approved'}
                                <span class="text-rose-500"
                                    >* (Wajib diisi)</span
                                >
                            {/if}
                        </label>
                        <textarea
                            id="notes"
                            rows="4"
                            bind:value={actionForm.notes}
                            placeholder="Tuliskan ulasan persetujuan, arahan perbaikan untuk revisi, atau alasan penolakan..."
                            class="w-full px-3 py-2 text-xs bg-background border border-zinc-200 dark:border-zinc-800 rounded-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                        ></textarea>
                        {#if actionForm.errors.notes}
                            <p class="text-[10px] text-rose-500 font-semibold">
                                {actionForm.errors.notes}
                            </p>
                        {/if}
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button
                            type="button"
                            onclick={closeActionModal}
                            class="px-4 py-2 text-xs font-bold bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 rounded-md transition-all cursor-pointer"
                        >
                            Tutup
                        </button>
                        <button
                            type="submit"
                            disabled={actionForm.processing}
                            class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary/90 text-white rounded-md disabled:opacity-50 transition-all cursor-pointer"
                        >
                            Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    {/if}
</div>
