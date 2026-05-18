import { Link } from '@inertiajs/react';
import { BookOpen, Car, FolderGit2, Heart, History, LayoutGrid, Megaphone, Search, Users, Activity, FileText, Mail } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import admin from '@/routes/admin';
import type { NavItem } from '@/types';

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: admin.dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Users',
        href: admin.users.index(),
        icon: Users,
    },
    {
        title: 'Cars',
        href: admin.cars.index(),
        icon: Car,
    },
    {
        title: 'Car History',
        href: admin.cars.history.index(),
        icon: History,
    },
    {
        title: 'Favourites',
        href: admin.favourites.index(),
        icon: Heart,
    },
    {
        title: 'Offers',
        href: admin.offers.index(),
        icon: Mail,
    },
    {
        title: 'Saved Searches',
        href: admin.savedSearches.index(),
        icon: Search,
    },
    {
        title: 'Events',
        href: admin.events.index(),
        icon: Activity,
    },
    {
        title: 'Site pages',
        href: admin.sitePages.index(),
        icon: FileText,
    },
    {
        title: 'Announcements',
        href: admin.announcements.index(),
        icon: Megaphone,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={admin.dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={adminNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
