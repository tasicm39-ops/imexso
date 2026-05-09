import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useRef } from 'react';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: admin.dashboard() },
    { title: 'Cars', href: admin.cars.index() },
    { title: 'Import XML', href: admin.cars.import() },
];

export default function CarImport() {
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, processing, errors, progress, reset } = useForm<{
        file: File | null;
    }>({
        file: null,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(admin.cars.import.store.url(), {
            forceFormData: true,
            onSuccess: () => {
                reset();
                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Import Cars XML" />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Import Cars from XML</h1>
                    <Link
                        href={admin.cars.index.url()}
                        className="rounded-md border px-4 py-2 text-sm font-medium hover:bg-muted"
                    >
                        Back to Cars
                    </Link>
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

                <div className="max-w-xl rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <form onSubmit={submit} className="flex flex-col gap-4">
                        <div>
                            <label htmlFor="xml-file" className="mb-1 block text-sm font-medium">
                                XML File
                            </label>
                            <input
                                id="xml-file"
                                ref={fileInputRef}
                                type="file"
                                accept=".xml"
                                onChange={(e) => setData('file', e.target.files?.[0] ?? null)}
                                className="w-full rounded-md border px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-primary file:px-3 file:py-1 file:text-sm file:font-medium file:text-primary-foreground"
                            />
                            {errors.file && (
                                <p className="mt-1 text-sm text-red-600">{errors.file}</p>
                            )}
                            <p className="mt-1 text-xs text-muted-foreground">
                                Upload an XML file containing car data. Maximum size: 20MB.
                            </p>
                        </div>

                        {progress && (
                            <div className="w-full rounded-full bg-muted">
                                <div
                                    className="rounded-full bg-primary px-3 py-1 text-center text-xs text-primary-foreground"
                                    style={{ width: `${progress.percentage}%` }}
                                >
                                    {progress.percentage}%
                                </div>
                            </div>
                        )}

                        <button
                            type="submit"
                            disabled={processing || !data.file}
                            className="w-fit rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {processing ? 'Importing...' : 'Upload & Import'}
                        </button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
