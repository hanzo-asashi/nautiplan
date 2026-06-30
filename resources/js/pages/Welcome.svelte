<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Activity from 'lucide-svelte/icons/activity';
    import ArrowRight from 'lucide-svelte/icons/arrow-right';
    import BarChart3 from 'lucide-svelte/icons/bar-chart-3';
    import Calendar from 'lucide-svelte/icons/calendar';
    import ClipboardCheck from 'lucide-svelte/icons/clipboard-check';
    import Moon from 'lucide-svelte/icons/moon';
    import Shield from 'lucide-svelte/icons/shield';
    import Sun from 'lucide-svelte/icons/sun';
    import Target from 'lucide-svelte/icons/target';
    import AppHead from '@/components/AppHead.svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { themeState } from '@/lib/theme.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard, login } from '@/routes';

    const auth = $derived(page.props.auth);
    const theme = themeState();
</script>

<AppHead title="NautiPlan - Poltekpel Barombong" />

<div
    class="min-h-screen flex flex-col bg-zinc-50 dark:bg-[#030712] text-zinc-900 dark:text-zinc-100 transition-colors duration-300 selection:bg-cyan-500/30 mesh-gradient-bg"
>
    <!-- Global Decorative Background Glows -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <!-- Top Left Cyan Glow -->
        <div
            class="absolute -top-[10%] -left-[10%] w-[50vw] h-[50vw] rounded-full bg-cyan-500/5 dark:bg-cyan-500/10 blur-[120px] animate-drift-1"
        ></div>
        <!-- Top Right Indigo/Purple Glow -->
        <div
            class="absolute top-[15%] -right-[10%] w-[45vw] h-[45vw] rounded-full bg-indigo-500/5 dark:bg-indigo-500/8 blur-[120px] animate-drift-2"
        ></div>
        <!-- Middle Left Fuchsia Glow -->
        <div
            class="absolute top-[45%] -left-[15%] w-[40vw] h-[40vw] rounded-full bg-fuchsia-500/4 dark:bg-fuchsia-500/6 blur-[120px] animate-drift-1"
        ></div>
        <!-- Bottom Right Cyan Glow -->
        <div
            class="absolute bottom-[-10%] right-[-10%] w-[55vw] h-[55vw] rounded-full bg-cyan-500/4 dark:bg-cyan-500/8 blur-[120px] animate-drift-2"
        ></div>
    </div>
    <!-- Navbar -->
    <header
        class="sticky top-0 z-50 w-full border-b border-zinc-200/50 dark:border-cyan-950/40 bg-white/75 dark:bg-[#030712]/75 backdrop-blur-md"
    >
        <div
            class="container mx-auto px-4 h-16 flex items-center justify-between"
        >
            <div class="flex items-center gap-2">
                <div
                    class="flex aspect-square size-10 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-400 to-indigo-600 text-white shadow-lg shadow-cyan-500/20"
                >
                    <AppLogoIcon class="size-6 fill-current text-white" />
                </div>
                <div class="grid text-left text-sm">
                    <span
                        class="font-extrabold tracking-tight text-zinc-900 dark:text-white text-base leading-none"
                        >NautiPlan</span
                    >
                    <span
                        class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold leading-tight"
                        >Poltekpel Barombong</span
                    >
                </div>
            </div>

            <nav class="flex items-center gap-4">
                <!-- Theme Toggle -->
                <button
                    onclick={() =>
                        theme.updateAppearance(
                            theme.resolvedAppearance() === 'dark'
                                ? 'light'
                                : 'dark',
                        )}
                    class="border-beam-button relative flex aspect-square size-9 items-center justify-center rounded-lg text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 dark:hover:text-white transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer"
                    aria-label="Toggle Theme"
                >
                    {#if theme.resolvedAppearance() === 'dark'}
                        <Sun class="size-4" />
                    {:else}
                        <Moon class="size-4" />
                    {/if}
                </button>

                {#if auth?.user}
                    <Link
                        href={toUrl(dashboard())}
                        class="border-beam-button relative flex h-9 items-center justify-center rounded-lg px-5 text-sm font-semibold text-zinc-800 dark:text-white transition-all duration-300 hover:scale-[1.02] cursor-pointer"
                    >
                        <span>Ke Dashboard</span>
                    </Link>
                {:else}
                    <Link
                        href={toUrl(login())}
                        class="border-beam-button relative flex h-9 items-center justify-center rounded-lg px-5 text-sm font-semibold text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-white transition-all duration-300 hover:scale-[1.02] cursor-pointer"
                    >
                        <span>Masuk Sistem</span>
                    </Link>
                {/if}
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section
        class="relative overflow-hidden pt-20 pb-20 lg:pt-28 lg:pb-28 flex-1"
    >
        <!-- Decorative glowing orbs -->
        <div
            class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-cyan-500/10 rounded-full blur-3xl pointer-events-none animate-pulse-slow"
        ></div>
        <div
            class="absolute top-1/3 left-1/3 w-[400px] h-[400px] bg-fuchsia-500/10 rounded-full blur-3xl pointer-events-none animate-pulse-slow-2"
        ></div>
        <div
            class="absolute top-1/2 right-1/4 w-[300px] h-[300px] bg-indigo-500/10 rounded-full blur-3xl pointer-events-none animate-pulse-slow-2"
        ></div>

        <div class="container mx-auto px-4 text-center relative z-10 max-w-5xl">
            <span
                class="inline-flex items-center rounded-full bg-cyan-500/10 px-3.5 py-1 text-xs font-semibold text-cyan-600 dark:text-cyan-400 mb-6 border border-cyan-500/20 animate-fade-in-up"
            >
                Politeknik Pelayaran Barombong Makassar
            </span>
            <h1
                class="text-4xl font-extrabold tracking-tight sm:text-6xl text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 via-indigo-600 to-fuchsia-600 dark:from-cyan-400 dark:via-indigo-250 dark:to-fuchsia-400 leading-[1.1] sm:leading-[1.1] animate-fade-in-delay-1 drop-shadow-[0_0_30px_rgba(6,182,212,0.15)]"
            >
                Sistem Terintegrasi Pengelolaan Program & Kegiatan
            </h1>
            <p
                class="mt-6 text-lg sm:text-xl text-zinc-600 dark:text-zinc-400 leading-relaxed max-w-3xl mx-auto animate-fade-in-delay-2"
            >
                Manajemen rencana strategis (Renstra), rencana kerja tahunan
                (Renja), anggaran, indikator kinerja utama, hingga evaluasi
                program kerja secara digital, transparan, dan akuntabel.
            </p>
            <div
                class="mt-10 flex items-center justify-center gap-4 animate-fade-in-delay-3"
            >
                {#if auth?.user}
                    <Link
                        href={toUrl(dashboard())}
                        class="border-beam-button relative flex h-11 items-center justify-center rounded-lg px-8 text-base font-semibold text-zinc-950 dark:text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] cursor-pointer shadow-lg shadow-cyan-500/10 hover:shadow-cyan-500/30 gap-2"
                    >
                        <span>Masuk Dashboard</span>
                        <ArrowRight
                            class="size-4 transition-transform duration-200 group-hover:translate-x-1"
                        />
                    </Link>
                {:else}
                    <Link
                        href={toUrl(login())}
                        class="border-beam-button relative flex h-11 items-center justify-center rounded-lg px-8 text-base font-semibold text-zinc-950 dark:text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] cursor-pointer shadow-lg shadow-cyan-500/10 hover:shadow-cyan-500/30 gap-2"
                    >
                        <span>Masuk ke Aplikasi</span>
                        <ArrowRight
                            class="size-4 transition-transform duration-200 group-hover:translate-x-1"
                        />
                    </Link>
                {/if}
            </div>

            <!-- Interactive UI Mockup Wrapper -->
            <div class="animate-fade-in-delay-3 w-full">
                <div
                    class="mt-12 relative mx-auto max-w-4xl rounded-2xl border border-zinc-200/80 dark:border-cyan-950/40 solid-card backdrop-blur-lg p-4 sm:p-6 glow-cyan transition-all duration-300 animate-float"
                >
                    <div
                        class="flex items-center justify-between border-b border-zinc-200 dark:border-cyan-950/40 pb-3 mb-4"
                    >
                        <div class="flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <span
                                    class="size-2.5 rounded-full bg-rose-500/80"
                                ></span>
                                <span
                                    class="size-2.5 rounded-full bg-amber-500/80"
                                ></span>
                                <span
                                    class="size-2.5 rounded-full bg-emerald-500/80"
                                ></span>
                            </div>
                            <span
                                class="text-xs text-zinc-400 dark:text-zinc-500 font-mono ml-2"
                                >nautiplan.test/budgets</span
                            >
                        </div>
                        <div
                            class="flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-cyan-500/10 border border-cyan-500/20 text-[10px] font-semibold text-cyan-600 dark:text-cyan-400 select-none shadow-[0_0_10px_rgba(6,182,212,0.1)]"
                        >
                            <span
                                class="size-1.5 rounded-full bg-cyan-500 dark:bg-cyan-400 animate-pulse"
                            ></span>
                            Koneksi Terhubung
                        </div>
                    </div>

                    <!-- Mockup Grid -->
                    <div class="grid gap-4 sm:grid-cols-3 text-left">
                        <div
                            class="p-4 rounded-xl border border-zinc-200/80 dark:border-zinc-800/80 solid-subcard"
                        >
                            <span
                                class="text-[10px] text-zinc-500 uppercase font-bold tracking-wider"
                                >Total Pagu</span
                            >
                            <h4
                                class="text-lg font-extrabold text-zinc-900 dark:text-white mt-1"
                            >
                                Rp 1.881.841.000
                            </h4>
                            <div
                                class="w-full bg-zinc-200 dark:bg-zinc-800 h-1.5 rounded-full mt-3 overflow-hidden"
                            >
                                <div
                                    class="bg-cyan-500 dark:bg-cyan-400 h-full rounded-full shadow-[0_0_10px_rgba(6,182,212,0.5)]"
                                    style="width: 45%"
                                ></div>
                            </div>
                        </div>
                        <div
                            class="p-4 rounded-xl border border-zinc-200/80 dark:border-zinc-800/80 solid-subcard"
                        >
                            <span
                                class="text-[10px] text-zinc-500 uppercase font-bold tracking-wider"
                                >Realisasi</span
                            >
                            <h4
                                class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 drop-shadow-[0_0_10px_rgba(52,211,153,0.2)]"
                            >
                                Rp 850.000.000
                            </h4>
                            <span
                                class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-2.5 block"
                                >45.17% Penyerapan Anggaran</span
                            >
                        </div>
                        <div
                            class="p-4 rounded-xl border border-zinc-200/80 dark:border-zinc-800/80 solid-subcard"
                        >
                            <span
                                class="text-[10px] text-zinc-500 uppercase font-bold tracking-wider"
                                >Status Kegiatan</span
                            >
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    class="text-lg font-extrabold text-zinc-900 dark:text-white"
                                    >Diklat Pelaut</span
                                >
                                <span
                                    class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 shadow-[0_0_10px_rgba(6,182,212,0.1)]"
                                    >APPROVED</span
                                >
                            </div>
                            <span
                                class="text-[10px] text-cyan-600 dark:text-cyan-400 font-semibold mt-2.5 block"
                                >TA 2026 - Unit Diklat</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section
        class="py-24 bg-transparent border-y border-zinc-200/50 dark:border-zinc-800/50 relative overflow-hidden"
    >
        <!-- Background Radial Glow -->
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-500/5 dark:bg-cyan-500/5 rounded-full blur-[120px] pointer-events-none"
        ></div>

        <div class="container mx-auto px-4 max-w-6xl relative z-10">
            <div
                class="text-center max-w-2xl mx-auto mb-16 flex flex-col items-center"
            >
                <!-- Glowing Neon Badge -->
                <span
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-500/10 dark:bg-cyan-500/10 text-indigo-600 dark:text-cyan-400 border border-indigo-500/20 dark:border-cyan-500/20 mb-4 tracking-wider uppercase shadow-[0_0_15px_rgba(99,102,241,0.1)]"
                >
                    <span
                        class="size-1.5 rounded-full bg-indigo-600 dark:bg-cyan-400 animate-pulse"
                    ></span>
                    Platform Sistem
                </span>

                <!-- Gradient Glow Title -->
                <h2
                    class="text-3xl font-extrabold tracking-tight sm:text-5xl text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 via-indigo-600 to-fuchsia-600 dark:from-cyan-400 dark:via-indigo-300 dark:to-fuchsia-400 leading-tight drop-shadow-[0_0_20px_rgba(99,102,241,0.12)]"
                >
                    Modul Unggulan
                </h2>

                <!-- Modern Description with Highlighted Accents -->
                <p
                    class="mt-4 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed"
                >
                    Arsitektur <span
                        class="font-semibold text-zinc-900 dark:text-zinc-200"
                        >NautiPlan</span
                    >
                    dirancang khusus untuk memenuhi standar
                    <span
                        class="text-indigo-600 dark:text-cyan-400 font-semibold"
                        >administrasi digital</span
                    >
                    dan
                    <span
                        class="text-fuchsia-600 dark:text-fuchsia-400 font-semibold"
                        >akuntabilitas penuh</span
                    > Poltekpel Barombong.
                </p>

                <!-- Premium Neon Divider -->
                <div class="mt-6 flex justify-center items-center gap-1.5">
                    <span
                        class="w-12 h-1 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-full"
                    ></span>
                    <span
                        class="size-1.5 bg-fuchsia-500 rounded-full animate-pulse"
                    ></span>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Card 1 -->
                <div
                    class="rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card p-6 shadow-sm hover:scale-[1.02] hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-blue transition-all duration-300 flex flex-col justify-between group relative overflow-hidden"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -top-12 -right-12 size-36 rounded-full bg-blue-500/10 blur-3xl group-hover:bg-blue-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <div class="relative z-10">
                        <div
                            class="rounded-xl p-3 bg-blue-500/10 text-blue-600 dark:text-blue-400 w-fit shadow-inner transition-transform duration-300 group-hover:scale-110"
                        >
                            <Target class="size-6" />
                        </div>
                        <h3
                            class="mt-4 font-bold text-lg text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300"
                        >
                            Perencanaan Strategis
                        </h3>
                        <p
                            class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed"
                        >
                            Formulasi Renstra multi-tahun lengkap dengan sasaran
                            program, indikator kinerja utama (IKU), dan target
                            pencapaian instansi secara detail.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div
                    class="rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card p-6 shadow-sm hover:scale-[1.02] hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-emerald transition-all duration-300 flex flex-col justify-between group relative overflow-hidden"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -top-12 -right-12 size-36 rounded-full bg-emerald-500/10 blur-3xl group-hover:bg-emerald-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <div class="relative z-10">
                        <div
                            class="rounded-xl p-3 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 w-fit shadow-inner transition-transform duration-300 group-hover:scale-110"
                        >
                            <Calendar class="size-6" />
                        </div>
                        <h3
                            class="mt-4 font-bold text-lg text-zinc-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors duration-300"
                        >
                            Rencana Kerja Tahunan
                        </h3>
                        <p
                            class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed"
                        >
                            Penyusunan Rencana Kerja (Renja) setiap tahun
                            anggaran yang diturunkan langsung dari sasaran
                            strategis program kerja unit.
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div
                    class="rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card p-6 shadow-sm hover:scale-[1.02] hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-cyan transition-all duration-300 flex flex-col justify-between group relative overflow-hidden"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -top-12 -right-12 size-36 rounded-full bg-cyan-500/10 blur-3xl group-hover:bg-cyan-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <div class="relative z-10">
                        <div
                            class="rounded-xl p-3 bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 w-fit shadow-inner transition-transform duration-300 group-hover:scale-110"
                        >
                            <Activity class="size-6" />
                        </div>
                        <h3
                            class="mt-4 font-bold text-lg text-zinc-900 dark:text-white group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors duration-300"
                        >
                            Manajemen Kegiatan
                        </h3>
                        <p
                            class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed"
                        >
                            Organisasi kegiatan dan sub-kegiatan di bawah
                            naungan departemen pelaksana lengkap dengan status
                            dan progres rincian tahapan.
                        </p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div
                    class="rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card p-6 shadow-sm hover:scale-[1.02] hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-amber transition-all duration-300 flex flex-col justify-between group relative overflow-hidden"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -top-12 -right-12 size-36 rounded-full bg-amber-500/10 blur-3xl group-hover:bg-amber-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <div class="relative z-10">
                        <div
                            class="rounded-xl p-3 bg-amber-500/10 text-amber-600 dark:text-amber-400 w-fit shadow-inner transition-transform duration-300 group-hover:scale-110"
                        >
                            <BarChart3 class="size-6" />
                        </div>
                        <h3
                            class="mt-4 font-bold text-lg text-zinc-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors duration-300"
                        >
                            Anggaran & Realisasi
                        </h3>
                        <p
                            class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed"
                        >
                            Perencanaan pagu belanja program dan pemantauan
                            realisasi anggaran secara real-time berdasarkan data
                            POK BLU Poltekpel.
                        </p>
                    </div>
                </div>

                <!-- Card 5 -->
                <div
                    class="rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card p-6 shadow-sm hover:scale-[1.02] hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-purple transition-all duration-300 flex flex-col justify-between group relative overflow-hidden"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -top-12 -right-12 size-36 rounded-full bg-purple-500/10 blur-3xl group-hover:bg-purple-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <div class="relative z-10">
                        <div
                            class="rounded-xl p-3 bg-purple-500/10 text-purple-600 dark:text-purple-400 w-fit shadow-inner transition-transform duration-300 group-hover:scale-110"
                        >
                            <ClipboardCheck class="size-6" />
                        </div>
                        <h3
                            class="mt-4 font-bold text-lg text-zinc-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300"
                        >
                            Persetujuan & Usulan
                        </h3>
                        <p
                            class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed"
                        >
                            Sistem alur kerja pengajuan proposal kegiatan,
                            verifikasi, hingga persetujuan bertahap dari jajaran
                            pimpinan.
                        </p>
                    </div>
                </div>

                <!-- Card 6 -->
                <div
                    class="rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card p-6 shadow-sm hover:scale-[1.02] hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-rose transition-all duration-300 flex flex-col justify-between group relative overflow-hidden"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -top-12 -right-12 size-36 rounded-full bg-rose-500/10 blur-3xl group-hover:bg-rose-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <div class="relative z-10">
                        <div
                            class="rounded-xl p-3 bg-rose-500/10 text-rose-600 dark:text-rose-400 w-fit shadow-inner transition-transform duration-300 group-hover:scale-110"
                        >
                            <Shield class="size-6" />
                        </div>
                        <h3
                            class="mt-4 font-bold text-lg text-zinc-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors duration-300"
                        >
                            Audit Trail & Riwayat
                        </h3>
                        <p
                            class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed"
                        >
                            Pencatatan riwayat perubahan data (audit logs) yang
                            lengkap untuk menjamin akuntabilitas setiap proses
                            di dalam sistem.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section
        class="py-24 bg-transparent border-t border-zinc-200/50 dark:border-cyan-950/20 relative overflow-hidden"
    >
        <div class="container mx-auto px-4 max-w-5xl relative z-10">
            <div class="grid gap-8 grid-cols-2 md:grid-cols-4 text-center">
                <!-- Stat Card 1 -->
                <div
                    class="p-6 rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card shadow-sm hover:scale-105 hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-blue transition-all duration-300 relative overflow-hidden group"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -bottom-10 -left-10 size-28 rounded-full bg-blue-500/10 blur-2xl group-hover:bg-blue-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <span
                        class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-450 block group-hover:scale-110 transition-transform duration-300 origin-center select-none"
                        >2</span
                    >
                    <p
                        class="mt-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-bold group-hover:text-zinc-900 dark:group-hover:text-zinc-200 transition-colors duration-300"
                    >
                        Program Utama (POK)
                    </p>
                </div>

                <!-- Stat Card 2 -->
                <div
                    class="p-6 rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card shadow-sm hover:scale-105 hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-cyan transition-all duration-300 relative overflow-hidden group"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -bottom-10 -left-10 size-28 rounded-full bg-cyan-500/10 blur-2xl group-hover:bg-cyan-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <span
                        class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 to-blue-600 dark:from-cyan-400 dark:to-blue-400 whitespace-nowrap block group-hover:scale-110 transition-transform duration-300 origin-center select-none"
                        >Rp 1.88M+</span
                    >
                    <p
                        class="mt-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-bold group-hover:text-zinc-900 dark:group-hover:text-zinc-200 transition-colors duration-300"
                    >
                        Pagu Anggaran
                    </p>
                </div>

                <!-- Stat Card 3 -->
                <div
                    class="p-6 rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card shadow-sm hover:scale-105 hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-emerald transition-all duration-300 relative overflow-hidden group"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -bottom-10 -left-10 size-28 rounded-full bg-emerald-500/10 blur-2xl group-hover:bg-emerald-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <span
                        class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400 block group-hover:scale-110 transition-transform duration-300 origin-center select-none"
                        >12+</span
                    >
                    <p
                        class="mt-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-bold group-hover:text-zinc-900 dark:group-hover:text-zinc-200 transition-colors duration-300"
                    >
                        Unit / Departemen
                    </p>
                </div>

                <!-- Stat Card 4 -->
                <div
                    class="p-6 rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 solid-card shadow-sm hover:scale-105 hover:bg-zinc-50 dark:hover:bg-zinc-900 glow-purple transition-all duration-300 relative overflow-hidden group"
                >
                    <!-- Blurred Inner Glow -->
                    <div
                        class="absolute -bottom-10 -left-10 size-28 rounded-full bg-purple-500/10 blur-2xl group-hover:bg-purple-500/20 group-hover:scale-125 transition-all duration-500 pointer-events-none"
                    ></div>

                    <span
                        class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-indigo-600 dark:from-purple-400 dark:to-indigo-400 block group-hover:scale-110 transition-transform duration-300 origin-center select-none"
                        >100%</span
                    >
                    <p
                        class="mt-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-bold group-hover:text-zinc-900 dark:group-hover:text-zinc-200 transition-colors duration-300"
                    >
                        Akuntabilitas
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer
        class="mt-auto border-t border-cyan-950/30 py-8 bg-[#020617]/90 text-center"
    >
        <div class="container mx-auto px-4 text-sm text-zinc-500">
            <p>
                &copy; 2026 Politeknik Pelayaran Barombong. Hak Cipta
                Dilindungi.
            </p>
            <p class="mt-1 text-xs text-zinc-600">
                Dikembangkan untuk Sistem Pengelolaan Program & Kegiatan
                Terintegrasi (NautiPlan).
            </p>
        </div>
    </footer>
</div>

<style>
    @keyframes -global-pulse-slow {
        0%,
        100% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 0.15;
        }
        50% {
            transform: translate(-50%, -50%) scale(1.15);
            opacity: 0.25;
        }
    }

    @keyframes -global-pulse-slow-2 {
        0%,
        100% {
            transform: scale(1);
            opacity: 0.12;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.18;
        }
    }

    @keyframes -global-fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes -global-float {
        0%,
        100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-8px);
        }
    }

    @keyframes -global-spin-translate {
        from {
            transform: translate(-50%, -50%) rotate(0deg);
        }
        to {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

    :global(.animate-pulse-slow) {
        animation: pulse-slow 8s ease-in-out infinite;
    }

    :global(.animate-pulse-slow-2) {
        animation: pulse-slow-2 10s ease-in-out infinite;
    }

    :global(.animate-fade-in-up) {
        animation: fade-in-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    :global(.animate-fade-in-delay-1) {
        animation: fade-in-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
        opacity: 0;
    }

    :global(.animate-fade-in-delay-2) {
        animation: fade-in-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
        opacity: 0;
    }

    :global(.animate-fade-in-delay-3) {
        animation: fade-in-up 1s cubic-bezier(0.16, 1, 0.3, 1) 0.45s forwards;
        opacity: 0;
    }

    :global(.animate-float) {
        animation: float 6s ease-in-out infinite;
    }

    :global(.animate-spin-slow) {
        animation: spin-translate 3s linear infinite;
    }

    :global(.bg-conic-glow) {
        background: conic-gradient(
            from 0deg,
            transparent 30%,
            #22d3ee 70%,
            #4f46e5 100%
        ) !important;
    }

    :global(.border-beam-button) {
        position: relative !important;
        overflow: hidden !important;
        background: rgba(6, 182, 212, 0.05) !important;
        border: 1px solid rgba(6, 182, 212, 0.15) !important;
    }

    :global(.border-beam-button::before) {
        content: '' !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        width: 300px !important;
        height: 300px !important;
        background: conic-gradient(
            from 0deg,
            transparent 40%,
            #22d3ee 70%,
            #4f46e5 100%
        ) !important;
        transform: translate(-50%, -50%) rotate(0deg);
        animation: spin-translate 2s linear infinite !important;
        opacity: 0 !important;
        transition: opacity 0.3s ease-in-out !important;
        pointer-events: none !important;
        z-index: 1 !important;
    }

    :global(.border-beam-button:hover::before) {
        opacity: 1 !important;
    }

    :global(.border-beam-button::after) {
        content: '' !important;
        position: absolute !important;
        inset: 2px !important;
        border-radius: 6px !important;
        transition: background-color 0.3s ease !important;
        z-index: 2 !important;
        background: #ffffff !important;
    }

    :global(.dark .border-beam-button::after) {
        background: #030712 !important;
    }

    :global(.border-beam-button > *) {
        position: relative !important;
        z-index: 3 !important;
    }

    :global(.glow-cyan) {
        border-color: rgba(6, 182, 212, 0.35) !important;
        box-shadow:
            0 0 25px rgba(6, 182, 212, 0.12),
            inset 0 0 15px rgba(6, 182, 212, 0.05) !important;
    }
    :global(.glow-cyan:hover) {
        border-color: rgba(6, 182, 212, 0.85) !important;
        box-shadow:
            0 0 35px rgba(6, 182, 212, 0.45),
            0 0 15px rgba(6, 182, 212, 0.25),
            inset 0 0 20px rgba(6, 182, 212, 0.15) !important;
    }

    :global(.glow-emerald) {
        border-color: rgba(16, 185, 129, 0.35) !important;
        box-shadow:
            0 0 25px rgba(16, 185, 129, 0.12),
            inset 0 0 15px rgba(16, 185, 129, 0.05) !important;
    }
    :global(.glow-emerald:hover) {
        border-color: rgba(16, 185, 129, 0.85) !important;
        box-shadow:
            0 0 35px rgba(16, 185, 129, 0.45),
            0 0 15px rgba(16, 185, 129, 0.25),
            inset 0 0 20px rgba(16, 185, 129, 0.15) !important;
    }

    :global(.glow-blue) {
        border-color: rgba(59, 130, 246, 0.35) !important;
        box-shadow:
            0 0 25px rgba(59, 130, 246, 0.12),
            inset 0 0 15px rgba(59, 130, 246, 0.05) !important;
    }
    :global(.glow-blue:hover) {
        border-color: rgba(59, 130, 246, 0.85) !important;
        box-shadow:
            0 0 35px rgba(59, 130, 246, 0.45),
            0 0 15px rgba(59, 130, 246, 0.25),
            inset 0 0 20px rgba(59, 130, 246, 0.15) !important;
    }

    :global(.glow-amber) {
        border-color: rgba(245, 158, 11, 0.35) !important;
        box-shadow:
            0 0 25px rgba(245, 158, 11, 0.12),
            inset 0 0 15px rgba(245, 158, 11, 0.05) !important;
    }
    :global(.glow-amber:hover) {
        border-color: rgba(245, 158, 11, 0.85) !important;
        box-shadow:
            0 0 35px rgba(245, 158, 11, 0.45),
            0 0 15px rgba(245, 158, 11, 0.25),
            inset 0 0 20px rgba(245, 158, 11, 0.15) !important;
    }

    :global(.glow-purple) {
        border-color: rgba(139, 92, 246, 0.35) !important;
        box-shadow:
            0 0 25px rgba(139, 92, 246, 0.12),
            inset 0 0 15px rgba(139, 92, 246, 0.05) !important;
    }
    :global(.glow-purple:hover) {
        border-color: rgba(139, 92, 246, 0.85) !important;
        box-shadow:
            0 0 35px rgba(139, 92, 246, 0.45),
            0 0 15px rgba(139, 92, 246, 0.25),
            inset 0 0 20px rgba(139, 92, 246, 0.15) !important;
    }

    :global(.glow-rose) {
        border-color: rgba(244, 63, 94, 0.35) !important;
        box-shadow:
            0 0 25px rgba(244, 63, 94, 0.12),
            inset 0 0 15px rgba(244, 63, 94, 0.05) !important;
    }
    :global(.glow-rose:hover) {
        border-color: rgba(244, 63, 94, 0.85) !important;
        box-shadow:
            0 0 35px rgba(244, 63, 94, 0.45),
            0 0 15px rgba(244, 63, 94, 0.25),
            inset 0 0 20px rgba(244, 63, 94, 0.15) !important;
    }

    :global(.solid-card) {
        background-color: #ffffff !important;
    }
    :global(.dark .solid-card) {
        background-color: #070b14 !important;
    }

    :global(.solid-subcard) {
        background-color: #f4f4f5 !important;
    }
    :global(.dark .solid-subcard) {
        background-color: #111827 !important;
    }

    :global(.mesh-gradient-bg) {
        background-color: #f8fafc !important;
        background-image:
            radial-gradient(
                at 10% 20%,
                rgba(6, 182, 212, 0.05) 0px,
                transparent 50%
            ),
            radial-gradient(
                at 90% 10%,
                rgba(99, 102, 241, 0.05) 0px,
                transparent 50%
            ),
            radial-gradient(
                at 50% 80%,
                rgba(217, 70, 239, 0.04) 0px,
                transparent 50%
            ),
            radial-gradient(
                at 80% 90%,
                rgba(6, 182, 212, 0.05) 0px,
                transparent 50%
            ),
            radial-gradient(
                at 20% 80%,
                rgba(99, 102, 241, 0.05) 0px,
                transparent 50%
            ) !important;
        background-attachment: fixed !important;
    }
    :global(.dark .mesh-gradient-bg) {
        background-color: #030712 !important;
        background-image:
            radial-gradient(
                at 10% 20%,
                rgba(6, 182, 212, 0.06) 0px,
                transparent 50%
            ),
            radial-gradient(
                at 90% 10%,
                rgba(99, 102, 241, 0.06) 0px,
                transparent 50%
            ),
            radial-gradient(
                at 50% 80%,
                rgba(217, 70, 239, 0.04) 0px,
                transparent 50%
            ),
            radial-gradient(
                at 80% 90%,
                rgba(6, 182, 212, 0.06) 0px,
                transparent 50%
            ),
            radial-gradient(
                at 20% 80%,
                rgba(99, 102, 241, 0.06) 0px,
                transparent 50%
            ) !important;
    }

    @keyframes -global-drift-1 {
        0%,
        100% {
            transform: translate(0, 0) scale(1);
        }
        50% {
            transform: translate(60px, -40px) scale(1.15);
        }
    }
    @keyframes -global-drift-2 {
        0%,
        100% {
            transform: translate(0, 0) scale(1.1);
        }
        50% {
            transform: translate(-50px, 50px) scale(0.9);
        }
    }
    :global(.animate-drift-1) {
        animation: drift-1 15s ease-in-out infinite;
    }
    :global(.animate-drift-2) {
        animation: drift-2 18s ease-in-out infinite;
    }
</style>
