<script lang="ts">
    import type { NavItem } from '@/types';
    import type { Snippet } from 'svelte';
    import { Link, page } from '@inertiajs/svelte';
    import Activity from 'lucide-svelte/icons/activity';
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

    const mainNavItems = $derived.by((): NavItem[] => {
        const items: NavItem[] = [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: 'Renstra (Strategic)',
                href: renstraIndex(),
                icon: Target,
            },
            {
                title: 'Renja (Annual)',
                href: renjaIndex(),
                icon: Calendar,
            },
            {
                title: 'Programs',
                href: programIndex(),
                icon: Folder,
            },
            {
                title: 'Activities',
                href: activityIndex(),
                icon: Activity,
            },
            {
                title: 'Persetujuan',
                href: approvalIndex(),
                icon: FileCheck,
            },
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
                title: 'Budget & Realization',
                href: budgetIndex(),
                icon: Coins,
            },
            {
                title: 'Organizational Units',
                href: unitIndex(),
                icon: Building,
            },
            {
                title: 'Fiscal Years',
                href: fiscalYearIndex(),
                icon: CalendarDays,
            },
        ];

        if (isAdmin) {
            items.push(
                {
                    title: 'User Management',
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
        <NavMain items={mainNavItems} />
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} />
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
