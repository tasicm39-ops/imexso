import { Head, Link, router } from '@inertiajs/react';
import { AdminFilterSelect } from '@/components/admin-filter-select';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

type CarPhoto = {
    id: number;
    url: string;
    position: number;
};

type CarSummary = {
    id: number;
    id_produit: string;
    make: string;
    model: string;
    stock_status: string;
    sync_status: string;
    photos?: CarPhoto[];
};

type HistoryEntry = {
    id: number;
    car_id: number;
    status: string;
    buyer_info: Record<string, string> | null;
    created_at: string;
    car?: CarSummary;
};

type PaginatedHistories = {
    data: HistoryEntry[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    histories: PaginatedHistories;
    filters: {
        status?: string;
        search?: string;
        car_id?: string;
    };
    statuses: string[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Cars', href: admin.cars.index() },
    { title: 'History', href: admin.cars.history.index() },
];

const STATUS_STYLES: Record<string, string> = {
    IMPORTED: 'bg-blue-100 text-blue-800',
    AVAILABLE: 'bg-green-100 text-green-800',
    SOLD: 'bg-red-100 text-red-800',
};

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatVehicleLabel(car: CarSummary | undefined): string {
    if (!car) {
        return '—';
    }

    if (car.make === 'UNKNOWN') {
        return `Sold archive · ${car.id_produit}`;
    }

    const model =
        car.model === '—' || car.model === car.id_produit ? '' : car.model;

    return [car.make, model].filter(Boolean).join(' ');
}

function formatBuyerInfo(info: Record<string, string> | null): string {
    if (!info || Object.keys(info).length === 0) {
        return '—';
    }

    return Object.entries(info)
        .map(([key, value]) => `${key}: ${value}`)
        .join(' · ');
}

export default function CarHistoryIndex({ histories, filters, statuses }: Props) {
    function handleFilter(key: string, value: string) {
        router.get(
            admin.cars.history.index.url(),
            { ...filters, [key]: value || undefined },
            { preserveState: true, replace: true },
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Car History" />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">Car History</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Status changes and buyer info from stock and vendu imports (XML car id).
                        </p>
                    </div>
                    <span className="text-sm text-muted-foreground">{histories.total} entries</span>
                </div>

                <div className="flex flex-wrap gap-4">
                    <input
                        type="text"
                        placeholder="Search by XML id, ref, make, model, VIN..."
                        defaultValue={filters.search || ''}
                        onChange={(e) => handleFilter('search', e.target.value)}
                        className="w-full max-w-md rounded-md border px-3 py-2 text-sm"
                    />
                    <AdminFilterSelect
                        value={filters.status || ''}
                        onValueChange={(value) => handleFilter('status', value)}
                        placeholder="All statuses"
                        options={statuses.map((status) => ({
                            value: status,
                            label: status,
                        }))}
                    />
                    {filters.car_id && (
                        <button
                            type="button"
                            onClick={() => handleFilter('car_id', '')}
                            className="rounded-md border px-3 py-2 text-sm hover:bg-muted"
                        >
                            Clear car filter
                        </button>
                    )}
                </div>

                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full min-w-[800px] text-sm">
                        <thead className="border-b bg-muted/50 text-left text-xs uppercase text-muted-foreground">
                            <tr>
                                <th className="px-4 py-3">Date</th>
                                <th className="px-4 py-3">Photo</th>
                                <th className="px-4 py-3">XML ID</th>
                                <th className="px-4 py-3">Reference</th>
                                <th className="px-4 py-3">Vehicle</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3">Buyer / details</th>
                                <th className="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody>
                            {histories.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-muted-foreground">
                                        No history entries yet. Import cars.xml and vendu.xml to populate this table.
                                    </td>
                                </tr>
                            ) : (
                                histories.data.map((entry) => (
                                    <tr key={entry.id} className="border-b last:border-0">
                                        <td className="whitespace-nowrap px-4 py-3">{formatDate(entry.created_at)}</td>
                                        <td className="px-4 py-3">
                                            {entry.car?.photos?.[0]?.url ? (
                                                <img
                                                    src={entry.car.photos[0].url}
                                                    alt=""
                                                    className="h-10 w-14 rounded object-cover"
                                                />
                                            ) : (
                                                <span className="text-xs text-muted-foreground">—</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 font-mono text-xs">{entry.car_id}</td>
                                        <td className="px-4 py-3 font-mono text-xs">{entry.car?.id_produit ?? '—'}</td>
                                        <td className="px-4 py-3">{formatVehicleLabel(entry.car)}</td>
                                        <td className="px-4 py-3">
                                            <span
                                                className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${STATUS_STYLES[entry.status] ?? 'bg-gray-100 text-gray-800'}`}
                                            >
                                                {entry.status}
                                            </span>
                                        </td>
                                        <td className="max-w-md px-4 py-3 text-xs text-muted-foreground">
                                            {formatBuyerInfo(entry.buyer_info)}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {entry.car && (
                                                <Link
                                                    href={admin.cars.marketing(entry.car_id)}
                                                    className="text-primary hover:underline"
                                                >
                                                    Car
                                                </Link>
                                            )}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {histories.last_page > 1 && (
                    <div className="flex flex-wrap gap-2">
                        {histories.links.map((link, i) =>
                            link.url ? (
                                <Link
                                    key={i}
                                    href={link.url}
                                    className={`rounded-md border px-3 py-1 text-sm ${link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={i}
                                    className="px-3 py-1 text-sm text-muted-foreground"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ),
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
