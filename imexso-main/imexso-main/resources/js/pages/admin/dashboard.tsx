import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

type Stats = {
    total_users: number;
    pending_users: number;
    active_cars: number;
    sold_cars: number;
    recent_events: number;
};

type CarPhoto = {
    id: number;
    url: string;
    position: number;
};

type RecentCar = {
    id: number;
    make: string;
    model: string;
    trim_level: string;
    manufacturing_year: number | null;
    professional_price: string;
    sync_status: string;
    photos: CarPhoto[];
    created_at: string;
};

type EventUser = {
    id: number;
    name: string;
    email: string;
};

type RecentEvent = {
    id: number;
    event_type: string;
    payload: Record<string, unknown> | null;
    ip_address: string | null;
    user: EventUser | null;
    created_at: string;
};

type Props = {
    stats: Stats;
    recentCars?: RecentCar[];
    recentEvents?: RecentEvent[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Dashboard', href: admin.dashboard() },
];

function formatPrice(price: string | number): string {
    return new Intl.NumberFormat('en-EU', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(price));
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function SkeletonRows({ cols, rows = 5 }: { cols: number; rows?: number }) {
    return (
        <>
            {Array.from({ length: rows }).map((_, i) => (
                <tr key={i} className="border-b last:border-0">
                    {Array.from({ length: cols }).map((_, j) => (
                        <td key={j} className="px-4 py-3">
                            <div className="h-4 animate-pulse rounded bg-muted" />
                        </td>
                    ))}
                </tr>
            ))}
        </>
    );
}

export default function AdminDashboard({ stats, recentCars, recentEvents }: Props) {
    const cards = [
        { label: 'Total Users', value: stats.total_users, color: 'text-blue-600' },
        { label: 'Pending Approval', value: stats.pending_users, color: 'text-amber-600' },
        { label: 'Active Cars', value: stats.active_cars, color: 'text-green-600' },
        { label: 'Sold Cars', value: stats.sold_cars, color: 'text-gray-600' },
        { label: 'Events (24h)', value: stats.recent_events, color: 'text-purple-600' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />
            <div className="flex flex-col gap-6 p-6">
                <h1 className="text-2xl font-semibold">Admin Dashboard</h1>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {cards.map((card) => (
                        <div key={card.label} className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <p className="text-sm text-muted-foreground">{card.label}</p>
                            <p className={`text-3xl font-bold ${card.color}`}>{card.value}</p>
                        </div>
                    ))}
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Recent Cars */}
                    <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <div className="flex items-center justify-between border-b px-4 py-3">
                            <h2 className="font-semibold">Recent Cars</h2>
                            <Link href={admin.cars.index.url()} className="text-sm text-primary hover:underline">
                                View all
                            </Link>
                        </div>
                        <table className="w-full text-sm">
                            <thead className="border-b bg-muted/50">
                                <tr>
                                    <th className="px-4 py-2 text-left font-medium">Car</th>
                                    <th className="px-4 py-2 text-left font-medium">Year</th>
                                    <th className="px-4 py-2 text-left font-medium">Price</th>
                                    <th className="px-4 py-2 text-left font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {!recentCars ? (
                                    <SkeletonRows cols={4} />
                                ) : recentCars.length === 0 ? (
                                    <tr>
                                        <td colSpan={4} className="px-4 py-6 text-center text-muted-foreground">No cars yet.</td>
                                    </tr>
                                ) : (
                                    recentCars.map((car) => (
                                        <tr key={car.id} className="border-b last:border-0">
                                            <td className="px-4 py-2">
                                                <span className="font-medium">{car.make} {car.model}</span>
                                            </td>
                                            <td className="px-4 py-2 text-muted-foreground">{car.manufacturing_year ?? '—'}</td>
                                            <td className="px-4 py-2 font-medium">{formatPrice(car.professional_price)}</td>
                                            <td className="px-4 py-2">
                                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${car.sync_status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                                    {car.sync_status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Recent Events */}
                    <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <div className="flex items-center justify-between border-b px-4 py-3">
                            <h2 className="font-semibold">Recent Events</h2>
                            <Link href={admin.events.index.url()} className="text-sm text-primary hover:underline">
                                View all
                            </Link>
                        </div>
                        <table className="w-full text-sm">
                            <thead className="border-b bg-muted/50">
                                <tr>
                                    <th className="px-4 py-2 text-left font-medium">Event</th>
                                    <th className="px-4 py-2 text-left font-medium">User</th>
                                    <th className="px-4 py-2 text-left font-medium">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {!recentEvents ? (
                                    <SkeletonRows cols={3} rows={5} />
                                ) : recentEvents.length === 0 ? (
                                    <tr>
                                        <td colSpan={3} className="px-4 py-6 text-center text-muted-foreground">No events yet.</td>
                                    </tr>
                                ) : (
                                    recentEvents.map((event) => (
                                        <tr key={event.id} className="border-b last:border-0">
                                            <td className="px-4 py-2">
                                                <span className="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700">
                                                    {event.event_type}
                                                </span>
                                            </td>
                                            <td className="px-4 py-2 text-muted-foreground">
                                                {event.user?.name ?? '—'}
                                            </td>
                                            <td className="px-4 py-2 text-muted-foreground">
                                                {formatDate(event.created_at)}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
