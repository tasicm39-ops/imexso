import { Head, Link, router, usePage } from '@inertiajs/react';
import { AdminFilterSelect } from '@/components/admin-filter-select';
import CarStatsDialog from '@/components/car-stats-dialog';
import SparkBar from '@/components/spark-bar';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

type DayStat = { date: string; count: number };

type CarPhoto = {
    id: number;
    url: string;
    position: number;
};

type CarMarketing = {
    id: number;
    limited_stock_enabled: boolean;
    new_price_enabled: boolean;
    promotion_enabled: boolean;
    is_active: boolean;
} | null;

type Car = {
    id: number;
    id_produit: string;
    make: string;
    model: string;
    trim_level: string;
    fuel_type: string;
    horsepower: number;
    manufacturing_year: number | null;
    registration_date: string | null;
    color: string;
    mileage: number;
    professional_price: string;
    sync_status: string;
    condition_type: string;
    photos: CarPhoto[];
    marketing: CarMarketing;
    created_at: string;
};

type PaginatedCars = {
    data: Car[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    cars: PaginatedCars;
    filters: {
        sync_status?: string;
        make?: string;
        search?: string;
    };
    makes: string[];
    counts: {
        active: number;
        sold: number;
        total: number;
    };
    offerStats: Record<number, DayStat[]>;
    viewStats: Record<number, DayStat[]>;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Cars', href: admin.cars.index() },
];

const EMPTY_STATS: DayStat[] = [];

function formatPrice(price: string | number): string {
    return new Intl.NumberFormat('en-EU', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(price));
}

function formatMileage(km: number): string {
    return new Intl.NumberFormat('en-EU').format(km) + ' km';
}

function displayYear(car: Car): string | number {
    if (car.manufacturing_year) {
        return car.manufacturing_year;
    }

    if (car.registration_date) {
        const year = new Date(car.registration_date).getFullYear();

        return Number.isNaN(year) ? '—' : year;
    }

    return '—';
}

function statusBadge(status: string) {
    const styles: Record<string, string> = {
        active: 'bg-green-100 text-green-700',
        sold: 'bg-red-100 text-red-700',
    };

    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${styles[status] ?? 'bg-gray-100 text-gray-700'}`}>
            {status}
        </span>
    );
}

export default function CarsIndex({ cars, filters, makes, counts, offerStats, viewStats }: Props) {
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    function handleFilter(key: string, value: string) {
        router.get(admin.cars.index.url(), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Cars" />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Cars</h1>
                    <div className="flex items-center gap-3">
                        <span className="text-sm text-muted-foreground">
                            {counts.active} active · {counts.sold} sold · {counts.total} total
                            {filters.sync_status && filters.sync_status !== 'all'
                                ? ` · showing ${cars.total} filtered`
                                : ''}
                        </span>
                        <Link
                            href={admin.cars.history.index.url()}
                            className="rounded-md border px-4 py-2 text-sm font-medium hover:bg-muted"
                        >
                            Car History
                        </Link>
                        <Link
                            href={admin.cars.import.url()}
                            className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Import XML
                        </Link>
                    </div>
                </div>

                {flash?.success && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {flash.error}
                    </div>
                )}

                <div className="flex gap-4">
                    <input
                        type="text"
                        placeholder="Search by make, model, VIN..."
                        defaultValue={filters.search || ''}
                        onChange={(e) => handleFilter('search', e.target.value)}
                        className="w-full max-w-sm rounded-md border px-3 py-2 text-sm"
                    />
                    <AdminFilterSelect
                        value={filters.sync_status || 'active'}
                        onValueChange={(value) => handleFilter('sync_status', value)}
                        placeholder="All Status"
                        options={[
                            { value: 'active', label: 'Active (stock)' },
                            { value: 'sold', label: 'Sold' },
                            { value: 'all', label: 'All statuses' },
                        ]}
                    />
                    <AdminFilterSelect
                        value={filters.make || ''}
                        onValueChange={(value) => handleFilter('make', value)}
                        placeholder="All Makes"
                        options={makes.map((make) => ({ value: make, label: make }))}
                        triggerClassName="min-w-[180px]"
                    />
                </div>

                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Photo</th>
                                <th className="px-4 py-3 text-left font-medium">Reference</th>
                                <th className="px-4 py-3 text-left font-medium">Make / Model</th>
                                <th className="px-4 py-3 text-left font-medium">Year</th>
                                <th className="px-4 py-3 text-left font-medium">Fuel</th>
                                <th className="px-4 py-3 text-left font-medium">HP</th>
                                <th className="px-4 py-3 text-left font-medium">Mileage</th>
                                <th className="px-4 py-3 text-left font-medium">Price</th>
                                <th className="px-4 py-3 text-left font-medium">Offers</th>
                                <th className="px-4 py-3 text-left font-medium">Views</th>
                                <th className="px-4 py-3 text-left font-medium">Status</th>
                                <th className="px-4 py-3 text-left font-medium">Marketing</th>
                            </tr>
                        </thead>
                        <tbody>
                            {cars.data.length === 0 && (
                                <tr>
                                    <td colSpan={12} className="px-4 py-8 text-center text-muted-foreground">
                                        No cars found.
                                    </td>
                                </tr>
                            )}
                            {cars.data.map((car) => {
                                const carOffers = offerStats[car.id] ?? EMPTY_STATS;
                                const carViews = viewStats[car.id] ?? EMPTY_STATS;

                                return (
                                    <tr key={car.id} className="border-b last:border-0">
                                        <td className="px-4 py-3">
                                            {car.photos?.[0] ? (
                                                <img
                                                    src={car.photos[0].url}
                                                    alt={`${car.make} ${car.model}`}
                                                    className="h-10 w-14 rounded object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-10 w-14 items-center justify-center rounded bg-muted text-xs text-muted-foreground">
                                                    N/A
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className="font-mono text-xs text-muted-foreground">{car.id_produit}</span>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="font-medium">{car.make} {car.model}</div>
                                            <div className="text-xs text-muted-foreground">{car.trim_level}</div>
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">{displayYear(car)}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{car.fuel_type}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{car.horsepower}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{formatMileage(car.mileage)}</td>
                                        <td className="px-4 py-3 font-medium">{formatPrice(car.professional_price)}</td>
                                        <td className="px-4 py-3">
                                            {carOffers.length > 0 ? (
                                                <CarStatsDialog
                                                    title={`Offers — ${car.make} ${car.model}`}
                                                    description="Sent offers over the last 5 days"
                                                    data={carOffers}
                                                    color="#22c55e"
                                                >
                                                    <button type="button" className="cursor-pointer rounded p-1 hover:bg-muted">
                                                        <SparkBar data={carOffers} color="#22c55e" />
                                                    </button>
                                                </CarStatsDialog>
                                            ) : (
                                                <span className="text-xs text-muted-foreground">—</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            {carViews.length > 0 ? (
                                                <CarStatsDialog
                                                    title={`Views — ${car.make} ${car.model}`}
                                                    description="Page views over the last 5 days"
                                                    data={carViews}
                                                    color="#3b82f6"
                                                >
                                                    <button type="button" className="cursor-pointer rounded p-1 hover:bg-muted">
                                                        <SparkBar data={carViews} color="#3b82f6" />
                                                    </button>
                                                </CarStatsDialog>
                                            ) : (
                                                <span className="text-xs text-muted-foreground">—</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">{statusBadge(car.sync_status)}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center gap-2">
                                                <Link
                                                    href={admin.cars.history.index.url({
                                                        query: { car_id: String(car.id) },
                                                    })}
                                                    className="rounded border px-2 py-1 text-xs hover:bg-muted"
                                                >
                                                    History
                                                </Link>
                                                <Link
                                                    href={admin.cars.marketing(car.id)}
                                                    className="rounded border px-2 py-1 text-xs hover:bg-muted"
                                                >
                                                    {car.marketing?.is_active ? 'Edit' : 'Setup'}
                                                </Link>
                                                {car.marketing?.is_active && (
                                                    <span className="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                                        Active
                                                    </span>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>

                {cars.last_page > 1 && (
                    <div className="flex justify-center gap-1">
                        {cars.links.map((link, i) => (
                            <button
                                key={i}
                                disabled={!link.url}
                                onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                className={`rounded px-3 py-1 text-sm ${link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'} ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
