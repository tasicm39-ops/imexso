import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

type UserSummary = {
    id: number;
    name: string;
    email: string;
};

type CarSummary = {
    id: number;
    id_produit: string | null;
    make: string | null;
    model: string | null;
    professional_price: string | null;
};

type Offer = {
    id: number;
    client_name: string;
    client_email: string;
    price_excl_vat: string;
    price_incl_vat: string;
    margin_type: string | null;
    margin_amount: string | null;
    vat_rate: string;
    validity_days: number | null;
    delivery_type: string;
    message: string | null;
    setup_fees: string;
    registration_fees: string;
    admin_fees: string;
    bonus_malus: string;
    ww_fees: string;
    created_at: string;
    user: UserSummary | null;
    car: CarSummary | null;
};

type PaginatedOffers = {
    data: Offer[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    offers: PaginatedOffers;
    filters: {
        user_id?: string;
        delivery_type?: string;
        search?: string;
    };
    users: UserSummary[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Offers', href: admin.offers.index() },
];

function formatMoney(value: string | number | null): string {
    if (value === null) return '—';
    return new Intl.NumberFormat('en-EU', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value));
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

function formatMargin(offer: Offer): string {
    if (!offer.margin_type || offer.margin_amount === null) return '—';
    const amount = Number(offer.margin_amount);
    if (offer.margin_type === 'percentage') return `${amount}%`;
    return formatMoney(amount);
}

export default function OffersIndex({ offers, filters, users }: Props) {
    function handleFilter(key: string, value: string) {
        router.get(admin.offers.index.url(), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Offers" />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Offers</h1>
                    <span className="text-sm text-muted-foreground">{offers.total} offers total</span>
                </div>

                <div className="flex gap-4">
                    <input
                        type="text"
                        placeholder="Search by client, email, reference, make, model..."
                        defaultValue={filters.search || ''}
                        onChange={(e) => handleFilter('search', e.target.value)}
                        className="w-full max-w-sm rounded-md border px-3 py-2 text-sm"
                    />
                    <select
                        value={filters.user_id || ''}
                        onChange={(e) => handleFilter('user_id', e.target.value)}
                        className="rounded-md border px-3 py-2 text-sm"
                    >
                        <option value="">All Sales Users</option>
                        {users.map((user) => (
                            <option key={user.id} value={user.id}>
                                {user.name} ({user.email})
                            </option>
                        ))}
                    </select>
                    <select
                        value={filters.delivery_type || ''}
                        onChange={(e) => handleFilter('delivery_type', e.target.value)}
                        className="rounded-md border px-3 py-2 text-sm"
                    >
                        <option value="">All Delivery Types</option>
                        <option value="email">Email</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>

                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Date</th>
                                <th className="px-4 py-3 text-left font-medium">Car</th>
                                <th className="px-4 py-3 text-left font-medium">Client</th>
                                <th className="px-4 py-3 text-left font-medium">Sales user</th>
                                <th className="px-4 py-3 text-left font-medium">Price excl. VAT</th>
                                <th className="px-4 py-3 text-left font-medium">Price incl. VAT</th>
                                <th className="px-4 py-3 text-left font-medium">Offer details</th>
                                <th className="px-4 py-3 text-left font-medium">Delivery</th>
                                <th className="px-4 py-3 text-left font-medium">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            {offers.data.length === 0 && (
                                <tr>
                                    <td colSpan={9} className="px-4 py-8 text-center text-muted-foreground">
                                        No offers found.
                                    </td>
                                </tr>
                            )}
                            {offers.data.map((offer) => (
                                <tr key={offer.id} className="border-b last:border-0 align-top">
                                    <td className="px-4 py-3 whitespace-nowrap text-muted-foreground">{formatDate(offer.created_at)}</td>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">{offer.car?.make} {offer.car?.model}</div>
                                        <div className="text-xs text-muted-foreground">
                                            Ref: {offer.car?.id_produit ?? '—'}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">{offer.client_name}</div>
                                        <div className="text-xs text-muted-foreground">{offer.client_email}</div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">{offer.user?.name ?? '—'}</div>
                                        <div className="text-xs text-muted-foreground">{offer.user?.email ?? '—'}</div>
                                    </td>
                                    <td className="px-4 py-3 font-medium">{formatMoney(offer.price_excl_vat)}</td>
                                    <td className="px-4 py-3 font-medium">{formatMoney(offer.price_incl_vat)}</td>
                                    <td className="px-4 py-3">
                                        <div className="space-y-1 text-xs text-muted-foreground">
                                            <div><span className="font-medium text-foreground">Margin:</span> {formatMargin(offer)}</div>
                                            <div><span className="font-medium text-foreground">VAT:</span> {offer.vat_rate}%</div>
                                            <div><span className="font-medium text-foreground">Validity:</span> {offer.validity_days ? `${offer.validity_days} days` : '—'}</div>
                                            <div><span className="font-medium text-foreground">Setup:</span> {formatMoney(offer.setup_fees)}</div>
                                            <div><span className="font-medium text-foreground">Registration:</span> {formatMoney(offer.registration_fees)}</div>
                                            <div><span className="font-medium text-foreground">Admin:</span> {formatMoney(offer.admin_fees)}</div>
                                            <div><span className="font-medium text-foreground">Bonus/Malus:</span> {formatMoney(offer.bonus_malus)}</div>
                                            <div><span className="font-medium text-foreground">WW Fees:</span> {formatMoney(offer.ww_fees)}</div>
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 capitalize">{offer.delivery_type}</td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        <span className="line-clamp-2">{offer.message || '—'}</span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {offers.last_page > 1 && (
                    <div className="flex justify-center gap-1">
                        {offers.links.map((link, i) => (
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
