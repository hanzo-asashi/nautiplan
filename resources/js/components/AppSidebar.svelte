<script lang="ts">
    import type { NavItem } from '@/types';
    import type { Snippet } from 'svelte';
    import { Link, page } from '@inertiajs/svelte';
    import Activity from 'lucide-svelte/icons/activity';
    import BarChart3 from 'lucide-svelte/icons/bar-chart-3';
    import Building from 'lucide-svelte/icons/building';
    import Calendar from 'lucide-svelte/icons/calendar';
    import CalendarDays from 'lucide-svelte/icons/calendar-days';
    import ChartPie from 'lucide-svelte/icons/chart-pie';
    import ClipboardCheck from 'lucide-svelte/icons/clipboard-check';
    import Coins from 'lucide-svelte/icons/coins';
    import FileCheck from 'lucide-svelte/icons/file-check';
    import Folder from 'lucide-svelte/icons/folder';
    import History from 'lucide-svelte/icons/history';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Target from 'lucide-svelte/icons/target';
    import Users from 'lucide-svelte/icons/users';

    import AppLogo from '@/components/AppLogo.svelte';
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import { index as activityIndex } from '@/routes/activities';
    import { index as approvalIndex } from '@/routes/approvals';
    import { index as auditLogIndex } from '@/routes/audit-logs';
    import { index as budgetIndex } from '@/routes/budgets';
    import { index as fiscalYearIndex } from '@/routes/fiscal-years';
    import { kpi as kpiIndex } from '@/routes/monitoring';
    import { index as reportIndex } from '@/routes/monitoring/reports';
    import { index as programIndex } from '@/routes/programs';
    import { index as renjaIndex } from '@/routes/renja';
    import { index as renstraIndex } from '@/routes/renstra';
    import {
        gantt as ganttIndex,
        analytics as analyticsIndex,
        calendar as calendarIndex,
    } from '@/routes/reports';
    import { index as unitIndex } from '@/routes/units';
    import { index as userIndex } from '@/routes/users';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const user = $derived(page.props.auth.user as any);
    const isAdmin = $derived(
        user?.is_super_admin || user?.roles?.includes('admin'),
    );

    const dashboardItems = $derived.by((): NavItem[] => [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ]);

    const planningItems = $derived.by((): NavItem[] => [
        {
            title: 'Renstra (Strategis)',
            href: renstraIndex(),
            icon: Target,
        },
        {
            title: 'Renja (Tahunan)',
            href: renjaIndex(),
            icon: Calendar,
        },
        {
            title: 'Program Kerja',
            href: programIndex(),
            icon: Folder,
        },
        {
            title: 'Kegiatan Utama',
            href: activityIndex(),
            icon: Activity,
        },
        {
            title: 'Anggaran & Realisasi',
            href: budgetIndex(),
            icon: Coins,
        },
        {
            title: 'Persetujuan Rencana',
            href: approvalIndex(),
            icon: FileCheck,
        },
    ]);

    const monitoringItems = $derived.by((): NavItem[] => [
        {
            title: 'Capaian KPI',
            href: kpiIndex(),
            icon: ChartPie,
        },
        {
            title: 'Laporan & Monev',
            href: reportIndex(),
            icon: ClipboardCheck,
        },
        {
            title: 'Jadwal Kalender',
            href: calendarIndex(),
            icon: Calendar,
        },
        {
            title: 'Linimasa Gantt',
            href: ganttIndex(),
            icon: CalendarDays,
        },
        {
            title: 'Analisis & Realisasi',
            href: analyticsIndex(),
            icon: BarChart3,
        },
    ]);

    const masterItems = $derived.by((): NavItem[] => [
        {
            title: 'Unit Organisasi',
            href: unitIndex(),
            icon: Building,
        },
        {
            title: 'Tahun Anggaran',
            href: fiscalYearIndex(),
            icon: CalendarDays,
        },
    ]);

    const systemItems = $derived.by((): NavItem[] => {
        const items: NavItem[] = [];
        if (isAdmin) {
            items.push(
                {
                    title: 'Manajemen Pengguna',
                    href: userIndex(),
                    icon: Users,
                },
                {
                    title: 'Audit Logs',
                    href: auditLogIndex(),
                    icon: History,
                },
            );
        }
        return items;
    });

    const footerNavItems: NavItem[] = [];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl(dashboard())}
                            class={props.class}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={dashboardItems} label="Ringkasan" />
        <NavMain items={planningItems} label="Perencanaan & Anggaran" />
        <NavMain items={monitoringItems} label="Monitoring & Pelaporan" />
        <NavMain items={masterItems} label="Data Master" />
        <NavMain items={systemItems} label="Pengaturan Sistem" />
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} />
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
