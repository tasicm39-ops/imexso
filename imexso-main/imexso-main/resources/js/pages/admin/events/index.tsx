import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

type EventUser = {
    id: number;
    name: string;
    email: string;
    company_name: string | null;
    phone: string | null;
    country: string | null;
    legacy_client_id: string | null;
};

type SegmentEvent = {
    id: number;
    user_id: number | null;
    event_type: string;
    payload: Record<string, unknown> | null;
    ip_address: string | null;
    user_agent: string | null;
    user: EventUser | null;
    created_at: string;
};

type PaginatedEvents = {
    data: SegmentEvent[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    events: PaginatedEvents;
    filters: {
        event_type?: string;
        search?: string;
        date_from?: string;
        date_to?: string;
        payload_make?: string;
        payload_model?: string;
        country?: string;
        per_page?: string;
    };
    eventTypes: string[];
    payloadMakes: string[];
    payloadModels: string[];
    countries: string[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Events', href: admin.events.index() },
];

const EVENT_TYPE_STYLES: Record<string, { bg: string; text: string; label: string }> = {
    view_car: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-700 dark:text-blue-300', label: 'View Car' },
    filter: { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-700 dark:text-amber-300', label: 'Filter' },
    search: { bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-700 dark:text-emerald-300', label: 'Search' },
    page_view: { bg: 'bg-gray-100 dark:bg-gray-800', text: 'text-gray-600 dark:text-gray-400', label: 'Page View' },
    login: { bg: 'bg-purple-100 dark:bg-purple-900/30', text: 'text-purple-700 dark:text-purple-300', label: 'Login' },
    register: { bg: 'bg-teal-100 dark:bg-teal-900/30', text: 'text-teal-700 dark:text-teal-300', label: 'Register' },
};

function eventTypeBadge(type: string) {
    const style = EVENT_TYPE_STYLES[type] ?? { bg: 'bg-gray-100 dark:bg-gray-800', text: 'text-gray-600 dark:text-gray-400', label: type };
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ${style.bg} ${style.text}`}>
            {style.label}
        </span>
    );
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function PayloadDisplay({ event }: { event: SegmentEvent }) {
    const { event_type, payload } = event;
    if (!payload) return <span className="text-muted-foreground">—</span>;

    if (event_type === 'view_car') {
        const carId = payload.car_id as number | undefined;
        const make = payload.make as string | undefined;
        const model = payload.model as string | undefined;

        return (
            <div className="flex flex-col gap-1">
                <div className="flex items-center gap-2">
                    <span className="font-medium">{make} {model}</span>
                </div>
                {carId && (
                    <a
                        href={`/admin/cars?search=${carId}`}
                        className="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        View car #{carId}
                    </a>
                )}
            </div>
        );
    }

    if (event_type === 'filter' || event_type === 'search') {
        return <FilterPayloadDisplay payload={payload} />;
    }

    if (event_type === 'page_view') {
        const path = payload.path as string | undefined;
        return (
            <span className="text-sm text-muted-foreground">
                {path || '—'}
            </span>
        );
    }

    return <span className="text-muted-foreground">—</span>;
}

function FilterPayloadDisplay({ payload }: { payload: Record<string, unknown> }) {
    const [expanded, setExpanded] = useState(false);

    const activeFilters: { label: string; value: string }[] = [];

    if (payload.make) activeFilters.push({ label: 'Make', value: String(payload.make) });
    if (payload.model) activeFilters.push({ label: 'Model', value: String(payload.model) });
    if (payload.condition_type) activeFilters.push({ label: 'Condition', value: String(payload.condition_type) === 'VN' ? 'New' : String(payload.condition_type) === 'VO' ? 'Used' : String(payload.condition_type) });
    if (payload.search) activeFilters.push({ label: 'Search', value: String(payload.search) });

    const fuelType = payload.fuel_type;
    if (Array.isArray(fuelType) && fuelType.length > 0) {
        activeFilters.push({ label: 'Fuel', value: fuelType.join(', ') });
    }

    const gearbox = payload.gearbox;
    if (Array.isArray(gearbox) && gearbox.length > 0) {
        activeFilters.push({ label: 'Transmission', value: gearbox.join(', ') });
    }

    const color = payload.color;
    if (Array.isArray(color) && color.length > 0) {
        activeFilters.push({ label: 'Color', value: color.join(', ') });
    }

    const priceMin = payload.price_min as number | undefined;
    const priceMax = payload.price_max as number | undefined;
    if (priceMin && priceMin > 0) activeFilters.push({ label: 'Price min', value: `€${Number(priceMin).toLocaleString()}` });
    if (priceMax && priceMax < 200000) activeFilters.push({ label: 'Price max', value: `€${Number(priceMax).toLocaleString()}` });

    const hpMin = payload.horsepower_min as number | undefined;
    const hpMax = payload.horsepower_max as number | undefined;
    if (hpMin && hpMin > 0) activeFilters.push({ label: 'HP min', value: String(hpMin) });
    if (hpMax && hpMax < 1000) activeFilters.push({ label: 'HP max', value: String(hpMax) });

    const yearMin = payload.year_min as number | undefined;
    const yearMax = payload.year_max as number | undefined;
    if (yearMin && yearMin > 2000) activeFilters.push({ label: 'Year from', value: String(yearMin) });
    if (yearMax && yearMax < new Date().getFullYear()) activeFilters.push({ label: 'Year to', value: String(yearMax) });

    const mileageMin = payload.mileage_min as number | undefined;
    const mileageMax = payload.mileage_max as number | undefined;
    if (mileageMin && mileageMin > 0) activeFilters.push({ label: 'Mileage min', value: `${Number(mileageMin).toLocaleString()} km` });
    if (mileageMax && mileageMax < 500000) activeFilters.push({ label: 'Mileage max', value: `${Number(mileageMax).toLocaleString()} km` });

    if (activeFilters.length === 0) {
        return <span className="text-sm text-muted-foreground italic">No active filters</span>;
    }

    const visibleFilters = expanded ? activeFilters : activeFilters.slice(0, 3);
    const hasMore = activeFilters.length > 3;

    return (
        <div className="flex flex-col gap-1">
            <div className="flex flex-wrap gap-1">
                {visibleFilters.map((f, i) => (
                    <span key={i} className="inline-flex items-center rounded-md border border-border bg-muted/50 px-2 py-0.5 text-xs">
                        <span className="font-medium text-muted-foreground">{f.label}:</span>
                        <span className="ml-1">{f.value}</span>
                    </span>
                ))}
            </div>
            {hasMore && (
                <button
                    onClick={() => setExpanded(!expanded)}
                    className="self-start text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                >
                    {expanded ? 'Show less' : `+${activeFilters.length - 3} more`}
                </button>
            )}
        </div>
    );
}

function ClientDisplay({ user }: { user: EventUser | null }) {
    if (!user) return <span className="text-muted-foreground">—</span>;

    return (
        <div className="flex flex-col gap-0.5">
            {user.company_name && (
                <div className="text-sm font-semibold">{user.company_name}</div>
            )}
            <div className={user.company_name ? 'text-xs text-muted-foreground' : 'text-sm font-medium'}>{user.name}</div>
            <div className="text-xs text-muted-foreground">{user.email}</div>
            {user.phone && <div className="text-xs text-muted-foreground">{user.phone}</div>}
        </div>
    );
}

export default function EventsIndex({ events, filters, eventTypes, payloadMakes, payloadModels, countries }: Props) {
    const hasActiveFilters = Object.values(filters).some((v) => v !== undefined && v !== '');

    function handleFilter(key: string, value: string) {
        router.get(
            admin.events.index.url(),
            { ...filters, [key]: value || undefined, page: undefined },
            { preserveState: true, replace: true },
        );
    }

    function handleReset() {
        router.get(admin.events.index.url(), {}, { preserveState: true, replace: true });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Events" />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Event Log</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Track user activity: searches, car views, filters, and page visits
                        </p>
                    </div>
                    <span className="rounded-md border border-border bg-muted/50 px-3 py-1.5 text-sm font-medium tabular-nums">
                        {events.total.toLocaleString()} events
                    </span>
                </div>

                {/* Filters */}
                <div className="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-sm font-medium text-muted-foreground">Filters</h2>
                        {hasActiveFilters && (
                            <button
                                onClick={handleReset}
                                className="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Reset all
                            </button>
                        )}
                    </div>
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">Client</label>
                            <input
                                type="text"
                                placeholder="Name, email, company, ID..."
                                defaultValue={filters.search || ''}
                                onChange={(e) => handleFilter('search', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">Event Type</label>
                            <select
                                value={filters.event_type || ''}
                                onChange={(e) => handleFilter('event_type', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="">All Types</option>
                                {eventTypes.map((type) => (
                                    <option key={type} value={type}>{EVENT_TYPE_STYLES[type]?.label ?? type}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">Make</label>
                            <select
                                value={filters.payload_make || ''}
                                onChange={(e) => handleFilter('payload_make', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="">All Makes</option>
                                {payloadMakes.map((make) => (
                                    <option key={make} value={make}>{make}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">Model</label>
                            <select
                                value={filters.payload_model || ''}
                                onChange={(e) => handleFilter('payload_model', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="">All Models</option>
                                {payloadModels.map((model) => (
                                    <option key={model} value={model}>{model}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">Country</label>
                            <select
                                value={filters.country || ''}
                                onChange={(e) => handleFilter('country', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="">All Countries</option>
                                {countries.map((c) => (
                                    <option key={c} value={c}>{c}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">From</label>
                            <input
                                type="date"
                                value={filters.date_from || ''}
                                onChange={(e) => handleFilter('date_from', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">To</label>
                            <input
                                type="date"
                                value={filters.date_to || ''}
                                onChange={(e) => handleFilter('date_to', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">Per Page</label>
                            <select
                                value={filters.per_page || '20'}
                                onChange={(e) => handleFilter('per_page', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* Table */}
                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Date</th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Client</th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">ID Client</th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Country</th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Event</th>
                                <th className="min-w-[240px] px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Details</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {events.data.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-4 py-12 text-center text-muted-foreground">
                                        <div className="flex flex-col items-center gap-2">
                                            <svg className="h-8 w-8 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                            </svg>
                                            <span>No events found matching your filters.</span>
                                        </div>
                                    </td>
                                </tr>
                            )}
                            {events.data.map((event) => (
                                <tr key={event.id} className="transition-colors hover:bg-muted/30">
                                    <td className="whitespace-nowrap px-4 py-3 text-sm text-muted-foreground">
                                        {formatDate(event.created_at)}
                                    </td>
                                    <td className="px-4 py-3">
                                        <ClientDisplay user={event.user} />
                                    </td>
                                    <td className="px-4 py-3">
                                        {event.user?.legacy_client_id ? (
                                            <span className="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">{event.user.legacy_client_id}</span>
                                        ) : (
                                            <span className="text-muted-foreground">—</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3 text-sm text-muted-foreground">
                                        {event.user?.country || '—'}
                                    </td>
                                    <td className="px-4 py-3">
                                        {eventTypeBadge(event.event_type)}
                                    </td>
                                    <td className="px-4 py-3">
                                        <PayloadDisplay event={event} />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {events.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <span className="text-sm text-muted-foreground">
                            Page {events.current_page} of {events.last_page}
                        </span>
                        <div className="flex gap-1">
                            {events.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                    className={`rounded-md px-3 py-1.5 text-sm transition-colors ${
                                        link.active
                                            ? 'bg-primary text-primary-foreground'
                                            : 'hover:bg-muted'
                                    } ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
