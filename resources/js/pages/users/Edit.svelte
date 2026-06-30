<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as usersIndex } from '@/routes/users';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'User Management',
                href: usersIndex(),
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
    import { update } from '@/routes/users';

    let {
        user,
        units = [],
        roles = [],
    }: {
        user: {
            id: number;
            name: string;
            email: string;
            employee_id: string | null;
            phone: string | null;
            unit_id: number | null;
            is_active: boolean;
            roles: Array<{ id: number }>;
        };
        units: Array<{ id: number; name: string; code: string }>;
        roles: Array<{ id: number; display_name: string }>;
    } = $props();

    const form = useForm({
        name: user.name,
        email: user.email,
        employee_id: user.employee_id || '',
        phone: user.phone || '',
        unit_id: (user.unit_id === null ? '' : user.unit_id) as any,
        is_active: user.is_active,
        role_ids: user.roles ? user.roles.map((r) => r.id) : [],
        password: '',
        password_confirmation: '',
    });

    function handleRoleToggle(id: number) {
        if (form.role_ids.includes(id)) {
            form.role_ids = form.role_ids.filter((r) => r !== id);
        } else {
            form.role_ids = [...form.role_ids, id];
        }
    }

    function handleSubmit(e: Event) {
        e.preventDefault();
        form.unit_id = form.unit_id === '' ? null : form.unit_id;
        form.put(toUrl(update({ user: user.id })));
    }
</script>

<AppHead title={`Edit Pengguna - ${user.name}`} />

<div class="p-6 space-y-6 w-full">
    <PageHeader
        title={`Edit Pengguna: ${user.name}`}
        description="Perbarui biodata, hak akses peranan, status aktif, atau ganti password pengguna"
    >
        {#snippet actions()}
            <Link
                href={toUrl(usersIndex())}
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
            <!-- Left Side: Profil & Kontak -->
            <div class="space-y-4">
                <h3
                    class="text-sm font-bold text-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Profil & Kontak
                </h3>

                <div class="space-y-1">
                    <label
                        for="name"
                        class="text-sm font-semibold text-foreground"
                        >Nama Lengkap</label
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

                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            for="email"
                            class="text-sm font-semibold text-foreground"
                            >Email</label
                        >
                        <input
                            type="email"
                            id="email"
                            bind:value={form.email}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            required
                        />
                        {#if form.errors.email}
                            <p class="text-xs text-rose-500 font-medium">
                                {form.errors.email}
                            </p>
                        {/if}
                    </div>

                    <div class="space-y-1">
                        <label
                            for="employee_id"
                            class="text-sm font-semibold text-foreground"
                            >NIP / Kode Pegawai</label
                        >
                        <input
                            type="text"
                            id="employee_id"
                            bind:value={form.employee_id}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        />
                        {#if form.errors.employee_id}
                            <p class="text-xs text-rose-500 font-medium">
                                {form.errors.employee_id}
                            </p>
                        {/if}
                    </div>
                </div>

                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            for="phone"
                            class="text-sm font-semibold text-foreground"
                            >No Telepon</label
                        >
                        <input
                            type="text"
                            id="phone"
                            bind:value={form.phone}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            for="unit_id"
                            class="text-sm font-semibold text-foreground"
                            >Unit Kerja</label
                        >
                        <select
                            id="unit_id"
                            bind:value={form.unit_id}
                            class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                        >
                            <option value=""
                                >-- Tanpa Unit (Admin Global) --</option
                            >
                            {#each units as unit}
                                <option value={unit.id}
                                    >[{unit.code}] {unit.name}</option
                                >
                            {/each}
                        </select>
                        {#if form.errors.unit_id}
                            <p class="text-xs text-rose-500 font-medium">
                                {form.errors.unit_id}
                            </p>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- Right Side: Hak Akses & Kredensial -->
            <div class="space-y-4">
                <h3
                    class="text-sm font-bold text-foreground border-b border-sidebar-border/30 pb-2"
                >
                    Akses & Keamanan
                </h3>

                <!-- Role Assignment Checkboxes -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-foreground"
                        >Hak Akses (Roles)</label
                    >
                    <div
                        class="grid gap-2 sm:grid-cols-2 p-3 rounded-lg border border-sidebar-border bg-background/50"
                    >
                        {#each roles as role}
                            <label
                                class="flex items-center gap-2 text-xs font-semibold cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    checked={form.role_ids.includes(role.id)}
                                    onchange={() => handleRoleToggle(role.id)}
                                    class="rounded border-zinc-300 text-primary focus:ring-primary size-4 cursor-pointer"
                                />
                                <span>{role.display_name}</span>
                            </label>
                        {/each}
                    </div>
                    {#if form.errors.role_ids}
                        <p class="text-xs text-rose-500 font-medium">
                            {form.errors.role_ids}
                        </p>
                    {/if}
                </div>

                <div class="border-t border-sidebar-border/20 pt-4">
                    <p class="text-xs text-muted-foreground mb-3 font-semibold">
                        Kosongkan kolom password di bawah jika tidak ingin
                        mengganti password.
                    </p>
                    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                        <div class="space-y-1">
                            <label
                                for="password"
                                class="text-sm font-semibold text-foreground"
                                >Password Baru</label
                            >
                            <input
                                type="password"
                                id="password"
                                bind:value={form.password}
                                class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            />
                            {#if form.errors.password}
                                <p class="text-xs text-rose-500 font-medium">
                                    {form.errors.password}
                                </p>
                            {/if}
                        </div>

                        <div class="space-y-1">
                            <label
                                for="password_confirmation"
                                class="text-sm font-semibold text-foreground"
                                >Konfirmasi Password</label
                            >
                            <input
                                type="password"
                                id="password_confirmation"
                                bind:value={form.password_confirmation}
                                class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            />
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label
                        for="is_active"
                        class="text-sm font-semibold text-foreground"
                        >Status Akun</label
                    >
                    <select
                        id="is_active"
                        bind:value={form.is_active}
                        class="w-full px-3 py-2 text-sm bg-background border border-zinc-200 dark:border-zinc-800 rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer transition-all"
                    >
                        <option value={true}>Aktif</option>
                        <option value={false}>Suspended</option>
                    </select>
                </div>
            </div>
        </div>

        <div
            class="flex justify-end gap-3 pt-4 border-t border-sidebar-border/30"
        >
            <Link
                href={toUrl(usersIndex())}
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
