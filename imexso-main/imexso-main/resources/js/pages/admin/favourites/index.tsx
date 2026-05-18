import { Head, router } from '@inertiajs/react';
import { AdminFilterSelect } from '@/components/admin-filter-select';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

type UserSummary = {
    id: number;
    name: string;
    email: string;
};

type CarPhoto = {
    id: number;
    url: string;
    position: number;
};

type Car = {
    id: number;
    make: string;
    model: string;
    trim_level: string;
    professional_price: string;
    manufacturing_year: number | null;
    photos: CarPhoto[];
};

type Favourite = {
    id: number;
    user_id: number;
    car_id: number;
    user: UserSummary;
    car: Car;
    created_at: string;
};

type PaginatedFavourites = {
    data: Favourite[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    favourites: PaginatedFavourites;
    filters: {
        user_id?: string;
        search?: string;
    };
    users: UserSummary[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Favourites', href: admin.favourites.index() },
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
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function FavouritesIndex({ favourites, filters, users }: Props) {
    function handleFilter(key: string, value: string) {
        router.get(admin.favourites.index.url(), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Client Favourites" />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Client Favourites</h1>
                    <span className="text-sm text-muted-foreground">{favourites.total} favourites total</span>
                </div>

                <div className="flex gap-4">
                    <input
                        type="text"
                        placeholder="Search by name, email, or company..."
                        defaultValue={filters.search || ''}
                        onChange={(e) => handleFilter('search', e.target.value)}
                        className="w-full max-w-sm rounded-md border px-3 py-2 text-sm"
                    />
                    <AdminFilterSelect
                        value={filters.user_id || ''}
                        onValueChange={(value) => handleFilter('user_id', value)}
                        placeholder="All Users"
                        options={users.map((user) => ({
                            value: String(user.id),
                            label: `${user.name} (${user.email})`,
                        }))}
                        triggerClassName="min-w-[220px]"
                    />
                </div>

                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Photo</th>
                                <th className="px-4 py-3 text-left font-medium">Car</th>
                                <th className="px-4 py-3 text-left font-medium">Price</th>
                                <th className="px-4 py-3 text-left font-medium">Client</th>
                                <th className="px-4 py-3 text-left font-medium">Email</th>
                                <th className="px-4 py-3 text-left font-medium">Favourited At</th>
                            </tr>
                        </thead>
                        <tbody>
                            {favourites.data.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                        No favourites found.
                                    </td>
                                </tr>
                            )}
                            {favourites.data.map((fav) => (
                                <tr key={fav.id} className="border-b last:border-0">
                                    <td className="px-4 py-3">
                                        {fav.car?.photos?.[0] ? (
                                            <img
                                                src={fav.car.photos[0].url}
                                                alt={`${fav.car.make} ${fav.car.model}`}
                                                className="h-10 w-14 rounded object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-10 w-14 items-center justify-center rounded bg-muted text-xs text-muted-foreground">
                                                N/A
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">
                                            {fav.car?.make} {fav.car?.model}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {fav.car?.manufacturing_year ?? '—'}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 font-medium">
                                        {fav.car ? formatPrice(fav.car.professional_price) : '—'}
                                    </td>
                                    <td className="px-4 py-3">{fav.user?.name ?? '—'}</td>
                                    <td className="px-4 py-3 text-muted-foreground">{fav.user?.email ?? '—'}</td>
                                    <td className="px-4 py-3 text-muted-foreground">{formatDate(fav.created_at)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {favourites.last_page > 1 && (
                    <div className="flex justify-center gap-1">
                        {favourites.links.map((link, i) => (
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
