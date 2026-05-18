import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { AdminFilterSelect } from '@/components/admin-filter-select';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, User } from '@/types';

type PaginatedUsers = {
    data: (User & {
        is_validated: boolean;
        is_active: boolean;
        is_admin: boolean;
        company_name: string | null;
        legacy_client_id: string | null;
    })[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    users: PaginatedUsers;
    filters: {
        status?: string;
        search?: string;
    };
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Users', href: '/admin/users' },
];

function ClientIdCell({ user }: { user: PaginatedUsers['data'][number] }) {
    const [editing, setEditing] = useState(false);
    const [value, setValue] = useState(user.legacy_client_id || '');
    const [saving, setSaving] = useState(false);

    function handleSave() {
        if (!value.trim()) return;
        setSaving(true);
        router.post(
            `/admin/users/${user.id}/client-id`,
            { legacy_client_id: value.trim() },
            {
                preserveScroll: true,
                onFinish: () => {
                    setSaving(false);
                    setEditing(false);
                },
            },
        );
    }

    if (editing) {
        return (
            <div className="flex items-center gap-1">
                <input
                    type="text"
                    value={value}
                    onChange={(e) => setValue(e.target.value)}
                    placeholder="C1234"
                    className="w-24 rounded border px-2 py-1 text-xs"
                    autoFocus
                    onKeyDown={(e) => {
                        if (e.key === 'Enter') handleSave();
                        if (e.key === 'Escape') setEditing(false);
                    }}
                />
                <button
                    onClick={handleSave}
                    disabled={saving || !value.trim()}
                    className="rounded bg-primary px-2 py-1 text-xs text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                >
                    {saving ? '...' : 'Save'}
                </button>
                <button
                    onClick={() => { setEditing(false); setValue(user.legacy_client_id || ''); }}
                    className="rounded border px-2 py-1 text-xs hover:bg-muted"
                >
                    Cancel
                </button>
            </div>
        );
    }

    return (
        <div className="flex items-center gap-2">
            {user.legacy_client_id ? (
                <span className="font-mono text-xs">{user.legacy_client_id}</span>
            ) : (
                <span className="text-xs text-muted-foreground">—</span>
            )}
            <button
                onClick={() => setEditing(true)}
                className="rounded border px-1.5 py-0.5 text-xs hover:bg-muted"
                title="Edit Client ID"
            >
                {user.legacy_client_id ? 'Edit' : 'Set'}
            </button>
        </div>
    );
}

export default function UsersIndex({ users, filters }: Props) {
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    function handleFilter(key: string, value: string) {
        router.get('/admin/users', { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    function handleAction(userId: number, action: 'approve' | 'reject' | 'toggle-admin') {
        router.post(`/admin/users/${userId}/${action}`, {}, { preserveScroll: true });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Users" />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">User Management</h1>
                    <span className="text-sm text-muted-foreground">{users.total} users total</span>
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
                        placeholder="Search by name, email, or company..."
                        defaultValue={filters.search || ''}
                        onChange={(e) => handleFilter('search', e.target.value)}
                        className="w-full max-w-sm rounded-md border px-3 py-2 text-sm"
                    />
                    <AdminFilterSelect
                        value={filters.status || ''}
                        onValueChange={(value) => handleFilter('status', value)}
                        placeholder="All Users"
                        options={[
                            { value: 'pending', label: 'Pending Approval' },
                            { value: 'validated', label: 'Validated' },
                            { value: 'inactive', label: 'Inactive' },
                        ]}
                    />
                </div>

                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Name</th>
                                <th className="px-4 py-3 text-left font-medium">Email</th>
                                <th className="px-4 py-3 text-left font-medium">Company</th>
                                <th className="px-4 py-3 text-left font-medium">Client ID</th>
                                <th className="px-4 py-3 text-left font-medium">Status</th>
                                <th className="px-4 py-3 text-left font-medium">Admin</th>
                                <th className="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.map((user) => (
                                <tr key={user.id} className="border-b last:border-0">
                                    <td className="px-4 py-3">{user.name}</td>
                                    <td className="px-4 py-3 text-muted-foreground">{user.email}</td>
                                    <td className="px-4 py-3 text-muted-foreground">{user.company_name || '—'}</td>
                                    <td className="px-4 py-3">
                                        <ClientIdCell user={user} />
                                    </td>
                                    <td className="px-4 py-3">
                                        {user.is_validated ? (
                                            <span className="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                                Validated
                                            </span>
                                        ) : (
                                            <span className="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                                Pending
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        {user.is_admin && (
                                            <span className="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700">
                                                Admin
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            {!user.is_validated && (
                                                <button
                                                    onClick={() => handleAction(user.id, 'approve')}
                                                    className="rounded bg-green-600 px-2 py-1 text-xs text-white hover:bg-green-700"
                                                >
                                                    Approve
                                                </button>
                                            )}
                                            {user.is_validated && user.is_active && (
                                                <button
                                                    onClick={() => handleAction(user.id, 'reject')}
                                                    className="rounded bg-red-600 px-2 py-1 text-xs text-white hover:bg-red-700"
                                                >
                                                    Deactivate
                                                </button>
                                            )}
                                            <button
                                                onClick={() => handleAction(user.id, 'toggle-admin')}
                                                className="rounded border px-2 py-1 text-xs hover:bg-muted"
                                            >
                                                {user.is_admin ? 'Remove Admin' : 'Make Admin'}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {users.last_page > 1 && (
                    <div className="flex justify-center gap-1">
                        {users.links.map((link, i) => (
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
