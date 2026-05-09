import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import admin from '@/routes/admin';
import { isAnnouncementLocaleOptional, supportedLocales } from '@/lib/supported-locales';
import type { BreadcrumbItem } from '@/types';

type Announcement = {
    id: number;
    starts_at: string;
    ends_at: string;
    is_active: boolean;
    show_to_validated_only: boolean;
};

type Props = {
    announcement: Announcement;
    titles: Record<string, string>;
    bodies: Record<string, string>;
};

export default function AnnouncementEdit({ announcement, titles, bodies }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: admin.dashboard() },
        { title: 'Announcements', href: admin.announcements.index() },
        { title: `Edit #${announcement.id}`, href: admin.announcements.edit.url({ announcement: announcement.id }) },
    ];

    const form = useForm({
        starts_at: announcement.starts_at.slice(0, 16),
        ends_at: announcement.ends_at.slice(0, 16),
        is_active: announcement.is_active,
        show_to_validated_only: announcement.show_to_validated_only,
        title_fr: titles.fr ?? '',
        title_nl: titles.nl ?? '',
        title_de: titles.de ?? '',
        title_it: titles.it ?? '',
        title_en: titles.en ?? '',
        body_fr: bodies.fr ?? '',
        body_nl: bodies.nl ?? '',
        body_de: bodies.de ?? '',
        body_it: bodies.it ?? '',
        body_en: bodies.en ?? '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.put(admin.announcements.update.url({ announcement: announcement.id }));
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Announcement ${announcement.id}`} />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Edit announcement #{announcement.id}</h1>
                    <Link href={admin.announcements.index.url()} className="text-sm text-muted-foreground hover:underline">
                        Back
                    </Link>
                </div>

                <form onSubmit={submit} className="flex max-w-2xl flex-col gap-4">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="starts_at">Starts at</Label>
                            <Input
                                id="starts_at"
                                type="datetime-local"
                                value={form.data.starts_at}
                                onChange={(e) => form.setData('starts_at', e.target.value)}
                                required
                            />
                            {form.errors.starts_at && <p className="text-sm text-destructive">{form.errors.starts_at}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="ends_at">Ends at</Label>
                            <Input
                                id="ends_at"
                                type="datetime-local"
                                value={form.data.ends_at}
                                onChange={(e) => form.setData('ends_at', e.target.value)}
                                required
                            />
                            {form.errors.ends_at && <p className="text-sm text-destructive">{form.errors.ends_at}</p>}
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-6">
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={form.data.is_active}
                                onChange={(e) => form.setData('is_active', e.target.checked)}
                            />
                            Active
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={form.data.show_to_validated_only}
                                onChange={(e) => form.setData('show_to_validated_only', e.target.checked)}
                            />
                            Validated users only (informational)
                        </label>
                    </div>

                    <p className="text-sm text-muted-foreground">
                        Provide a title and body in French or in English. Other languages are optional; if you leave them
                        empty, the French text is used (or English when French is empty).
                    </p>

                    {supportedLocales.map((locale) => (
                        <div key={locale} className="space-y-2">
                            <Label htmlFor={`title_${locale}`}>
                                Title ({locale})
                                {isAnnouncementLocaleOptional(locale) ? (
                                    <span className="font-normal text-muted-foreground"> — optional</span>
                                ) : null}
                            </Label>
                            <Input
                                id={`title_${locale}`}
                                value={form.data[`title_${locale}` as keyof typeof form.data] as string}
                                onChange={(e) => form.setData(`title_${locale}` as keyof typeof form.data, e.target.value)}
                            />
                            {form.errors[`title_${locale}`] && (
                                <p className="text-sm text-destructive">{form.errors[`title_${locale}`]}</p>
                            )}
                            <Label htmlFor={`body_${locale}`}>
                                Body ({locale})
                                {isAnnouncementLocaleOptional(locale) ? (
                                    <span className="font-normal text-muted-foreground"> — optional</span>
                                ) : null}
                            </Label>
                            <textarea
                                id={`body_${locale}`}
                                className="min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                                value={form.data[`body_${locale}` as keyof typeof form.data] as string}
                                onChange={(e) => form.setData(`body_${locale}` as keyof typeof form.data, e.target.value)}
                            />
                            {form.errors[`body_${locale}`] && (
                                <p className="text-sm text-destructive">{form.errors[`body_${locale}`]}</p>
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
