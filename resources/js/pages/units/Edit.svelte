<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as unitsIndex } from '@/routes/units';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Unit Kerja',
                href: unitsIndex(),
            },
            {
                title: 'Edit',
                href: '',
            },
        ],
    };
</script>

<script lang="ts">
    import { useForm, Link } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import AppHead from '@/components/AppHead.svelte';
    import PageHeader from '@/components/PageHeader.svelte';
    import { toUrl } from '@/lib/utils';
    import { update } from '@/routes/units';

    let {
        unit,
        parentUnits = [],
        users = [],
    }: {
        unit: {
            id: number;
            code: string;
            name: string;
            parent_id: number | null;
            head_user_id: number | null;
            description: string | null;
            is_active: boolean;
        };
        parentUnits: Array<{ id: number; name: string; code: string }>;
        users: Array<{ id: number; name: string }>;
    } = $props();

    const form = useForm({
        code: unit.code,
        name: unit.name,
        parent_id: (unit.parent_id === null ? '' : unit.parent_id) as any,
        head_user_id: (unit.head_user_id === null
            ? ''
            : unit.head_user_id) as any,
        description: unit.description || '',
        is_active: unit.is_active,
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        form.parent_id = form.parent_id === '' ? null : form.parent_id;
        form.head_user_id = form.head_user_id === '' ? null : form.head_user_id;
        form.put(toUrl(update({ unit: unit.id })));
    }
</script>

<AppHead title={`Edit Unit Kerja - ${unit.name}`} />

<div class="p-6 space-y-6 w-full">
    <PageHeader
        title={`Edit Unit Kerja: ${unit.name}`}
        description="Perbarui kode unit, struktur induk, dan pimpinan unit"
    >
        {#snippet actions()}
            <Link
                href={toUrl(unitsIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background text-muted-foreground hover:text-foreground px-4 py-2 text-sm font-medium transition-colors cursor-pointer gap-1.5"
            >
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        {/snippet}
    </PageHeader>

    <form
        onsubmit={handleSubmit}
        class="space-y-6 bg-card/45 backdrop-blur-md p-6 rounded-xl border border-sidebar-border/50 shadow-sm"
    >
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Left Side: Identitas Unit -->
            <div class="space-y-4">
                <h3
                    class="text-sm font-bold text-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Identitas Unit Kerja
                </h3>

                <div class="grid gap-4 grid-cols-3">
                    <div class="space-y-1 col-span-1">
                        <label
                            for="code"
                            class="text-sm font-semibold text-foreground"
                            >Kode Unit</label
                        >
                        <input
                            type="text"
                            id="code"
                            bind:value={form.code}
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
                            >Nama Unit Kerja</label
                        >
                        <input
                            type="text"
                            id="name"
                            bind:value={form.name}
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
                        for="parent_id"
                        class="text-sm font-semibold text-foreground"
                        >Unit Induk (Parent)</label
                    >
                    <select
                        id="parent_id"
                        bind:value={form.parent_id}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                    >
                        <option value="">-- Tanpa Induk --</option>
                        {#each parentUnits as parent}
                            <option value={parent.id}
                                >[{parent.code}] {parent.name}</option
                            >
                        {/each}
                    </select>
                    {#if form.errors.parent_id}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.parent_id}
                        </p>
                    {/if}
                </div>
            </div>

            <!-- Right Side: Penanggung Jawab & Status -->
            <div class="space-y-4">
                <h3
                    class="text-sm font-bold text-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Manajemen & Status
                </h3>

                <div class="space-y-1">
                    <label
                        for="head_user_id"
                        class="text-sm font-semibold text-foreground"
                        >Kepala Unit (Head)</label
                    >
                    <select
                        id="head_user_id"
                        bind:value={form.head_user_id}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                    >
                        <option value="">-- Belum Ditentukan --</option>
                        {#each users as user}
                            <option value={user.id}>{user.name}</option>
                        {/each}
                    </select>
                    {#if form.errors.head_user_id}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.head_user_id}
                        </p>
                    {/if}
                </div>

                <div class="space-y-1">
                    <label
                        for="is_active"
                        class="text-sm font-semibold text-foreground"
                        >Status Aktif</label
                    >
                    <select
                        id="is_active"
                        bind:value={form.is_active}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                    >
                        <option value={true}>Aktif</option>
                        <option value={false}>Non-aktif</option>
                    </select>
                    {#if form.errors.is_active}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.is_active}
                        </p>
                    {/if}
                </div>
            </div>
        </div>

        <div class="space-y-1">
            <label
                for="description"
                class="text-sm font-semibold text-foreground">Deskripsi</label
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

        <div
            class="flex justify-end gap-3 pt-4 border-t border-sidebar-border/30"
        >
            <Link
                href={toUrl(unitsIndex())}
                class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 dark:border-zinc-800 bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground outline-none cursor-pointer"
            >
                Batal
            </Link>
            <button
                type="submit"
                disabled={form.processing}
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary hover:bg-primary/95 text-white px-5 py-2 text-sm font-medium shadow-md shadow-primary/20 transition-all cursor-pointer disabled:opacity-50"
            >
                {form.processing ? 'Menyimpan...' : 'Perbarui'}
            </button>
        </div>
    </form>
</div>
