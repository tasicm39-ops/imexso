import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

type SitePageRow = {
    id: number;
    slug: string;
    published_at: string | null;
};

type Props = {
    pages: SitePageRow[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Site pages', href: admin.sitePages.index() },
];

export default function SitePagesIndex({ pages }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Site pages" />
            <div className="flex flex-col gap-6 p-6">
                <h1 className="text-2xl font-semibold">Marketing site pages</h1>
                <p className="text-sm text-muted-foreground">
                    JSON payload per locale is merged on the Next.js site when a page is published (set published date). Empty payload keeps static
                    copy from the app bundle.
                </p>
                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Slug</th>
                                <th className="px-4 py-3 text-left font-medium">Published</th>
                                <th className="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {pages.map((p) => (
                                <tr key={p.id} className="border-b last:border-0">
                                    <td className="px-4 py-3 font-mono">{p.slug}</td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {p.published_at ? new Date(p.published_at).toLocaleString() : '— (API returns 404)'}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Link
                                            href={admin.sitePages.edit.url({ site_page: p.slug })}
                                            className="text-primary underline-offset-4 hover:underline"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
