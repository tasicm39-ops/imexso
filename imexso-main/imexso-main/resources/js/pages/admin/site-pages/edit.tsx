import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import admin from '@/routes/admin';
import { supportedLocales } from '@/lib/supported-locales';
import type { BreadcrumbItem } from '@/types';

type SitePage = {
    id: number;
    slug: string;
    published_at: string | null;
};

type Props = {
    page: SitePage;
    payloadJson: Record<string, string>;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Site pages', href: admin.sitePages.index() },
    { title: 'Edit', href: '#' },
];

export default function SitePageEdit({ page, payloadJson }: Props) {
    const form = useForm({
        published_at: page.published_at ? page.published_at.slice(0, 16) : '',
        ...Object.fromEntries(supportedLocales.map((loc) => [`payload_${loc}`, payloadJson[loc] ?? '{}'])),
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.put(admin.sitePages.update.url({ site_page: page.slug }));
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${page.slug}`} />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Edit “{page.slug}”</h1>
                    <Link href={admin.sitePages.index.url()} className="text-sm text-muted-foreground hover:underline">
                        Back
                    </Link>
                </div>

                <form onSubmit={submit} className="flex max-w-4xl flex-col gap-6">
                    <div className="space-y-2">
                        <Label htmlFor="published_at">Published at (leave empty to hide from public API)</Label>
                        <Input
                            id="published_at"
                            type="datetime-local"
                            value={form.data.published_at}
                            onChange={(e) => form.setData('published_at', e.target.value)}
                        />
                        {form.errors.published_at && (
                            <p className="text-sm text-destructive">{form.errors.published_at}</p>
                        )}
                    </div>

                    {supportedLocales.map((locale) => (
                        <div key={locale} className="space-y-2">
                            <Label htmlFor={`payload_${locale}`}>Payload JSON ({locale})</Label>
                            <textarea
                                id={`payload_${locale}`}
                                className="min-h-[200px] w-full rounded-md border border-input bg-transparent px-3 py-2 font-mono text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                value={form.data[`payload_${locale}` as keyof typeof form.data] as string}
                                onChange={(e) => form.setData(`payload_${locale}` as keyof typeof form.data, e.target.value)}
                            />
                            {form.errors[`payload_${locale}` as keyof typeof form.errors] && (
                                <p className="text-sm text-destructive">
                                    {form.errors[`payload_${locale}` as keyof typeof form.errors]}
                                </p>
                            )}
                        </div>
                    ))}

                    <Button type="submit" disabled={form.processing}>
                        Save
                    </Button>
                </form>
            </div>
        </AppLayout>
    );
}
