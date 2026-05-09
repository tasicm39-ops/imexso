import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

type Translation = {
    id: number;
    locale: string;
    title: string;
};

type AnnouncementRow = {
    id: number;
    starts_at: string;
    ends_at: string;
    is_active: boolean;
    show_to_validated_only: boolean;
    translations: Translation[];
};

type Props = {
    announcements: AnnouncementRow[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Announcements', href: admin.announcements.index() },
];

export default function AnnouncementsIndex({ announcements }: Props) {
    function remove(id: number) {
        if (!confirm('Delete this announcement?')) {
            return;
        }
        router.delete(admin.announcements.destroy.url({ announcement: id }));
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Announcements" />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Announcements</h1>
                    <Button asChild>
                        <Link href={admin.announcements.create.url()}>New announcement</Link>
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">ID</th>
                                <th className="px-4 py-3 text-left font-medium">FR title</th>
                                <th className="px-4 py-3 text-left font-medium">Starts</th>
                                <th className="px-4 py-3 text-left font-medium">Ends</th>
                                <th className="px-4 py-3 text-left font-medium">Active</th>
                                <th className="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {announcements.map((a) => {
                                const fr = a.translations.find((t) => t.locale === 'fr');
                                return (
                                    <tr key={a.id} className="border-b last:border-0">
                                        <td className="px-4 py-3">{a.id}</td>
                                        <td className="px-4 py-3">{fr?.title ?? '—'}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{a.starts_at}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{a.ends_at}</td>
                                        <td className="px-4 py-3">{a.is_active ? 'Yes' : 'No'}</td>
                                        <td className="px-4 py-3">
                                            <Link
                                                href={admin.announcements.edit.url({ announcement: a.id })}
                                                className="mr-3 text-primary underline-offset-4 hover:underline"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                type="button"
                                                onClick={() => remove(a.id)}
                                                className="text-destructive hover:underline"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
