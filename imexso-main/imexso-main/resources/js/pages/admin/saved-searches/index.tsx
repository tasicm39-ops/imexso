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

type SavedSearch = {
    id: number;
    user_id: number;
    name: string | null;
    filters: Record<string, unknown>;
    user: UserSummary;
    created_at: string;
};

type PaginatedSavedSearches = {
    data: SavedSearch[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    savedSearches: PaginatedSavedSearches;
    filters: {
        user_id?: string;
        search?: string;
    };
    users: UserSummary[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Saved Searches', href: admin.savedSearches.index() },
];

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatFilters(filters: Record<string, unknown>): string {
    return Object.entries(filters)
        .filter(([, v]) => v !== null && v !== '' && v !== undefined)
        .map(([k, v]) => `${k}: ${v}`)
        .join(', ');
}

export default function SavedSearchesIndex({ savedSearches, filters, users }: Props) {
    function handleFilter(key: string, value: string) {
        router.get(admin.savedSearches.index.url(), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Saved Searches" />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Saved Searches</h1>
                    <span className="text-sm text-muted-foreground">{savedSearches.total} saved searches total</span>
                </div>

                <div className="flex gap-4">
                    <input
                        type="text"
                        placeholder="Search by name or user..."
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
                                <th className="px-4 py-3 text-left font-medium">Name</th>
                                <th className="px-4 py-3 text-left font-medium">Filters</th>
                                <th className="px-4 py-3 text-left font-medium">Client</th>
                                <th className="px-4 py-3 text-left font-medium">Email</th>
                                <th className="px-4 py-3 text-left font-medium">Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            {savedSearches.data.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">
                                        No saved searches found.
                                    </td>
                                </tr>
                            )}
                            {savedSearches.data.map((search) => (
                                <tr key={search.id} className="border-b last:border-0">
                                    <td className="px-4 py-3 font-medium">{search.name || 'Unnamed'}</td>
                                    <td className="max-w-xs truncate px-4 py-3 text-muted-foreground">
                                        {formatFilters(search.filters)}
                                    </td>
                                    <td className="px-4 py-3">{search.user?.name ?? '—'}</td>
                                    <td className="px-4 py-3 text-muted-foreground">{search.user?.email ?? '—'}</td>
                                    <td className="px-4 py-3 text-muted-foreground">{formatDate(search.created_at)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {savedSearches.last_page > 1 && (
                    <div className="flex justify-center gap-1">
                        {savedSearches.links.map((link, i) => (
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
