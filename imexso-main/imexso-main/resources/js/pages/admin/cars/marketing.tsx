import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

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
    photos: CarPhoto[];
};

type Marketing = {
    id?: number;
    car_id?: number;
    limited_stock_enabled: boolean;
    limited_stock_count: number | null;
    new_price_enabled: boolean;
    new_price_amount: string | null;
    promotion_enabled: boolean;
    promotion_label: string | null;
    badge_text: string | null;
    sold_enabled: boolean;
    sold_visible_days: number;
    is_active: boolean;
};

type Props = {
    car: Car;
    marketing: Marketing;
};

export default function CarMarketing({ car, marketing }: Props) {
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: admin.dashboard() },
        { title: 'Cars', href: admin.cars.index() },
        { title: `${car.make} ${car.model}`, href: admin.cars.marketing(car.id) },
        { title: 'Marketing', href: admin.cars.marketing(car.id) },
    ];

    const [form, setForm] = useState({
        limited_stock_enabled: marketing.limited_stock_enabled,
        limited_stock_count: marketing.limited_stock_count ?? '',
        new_price_enabled: marketing.new_price_enabled,
        new_price_amount: marketing.new_price_amount ?? '',
        promotion_enabled: marketing.promotion_enabled,
        promotion_label: marketing.promotion_label ?? '',
        badge_text: marketing.badge_text ?? '',
        sold_enabled: marketing.sold_enabled,
        sold_visible_days: marketing.sold_visible_days ?? 5,
        is_active: marketing.is_active,
    });

    const [processing, setProcessing] = useState(false);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setProcessing(true);
        router.post(
            admin.cars.marketing.update(car.id),
            {
                ...form,
                limited_stock_count: form.limited_stock_count === '' ? null : Number(form.limited_stock_count),
                new_price_amount: form.new_price_amount === '' ? null : Number(form.new_price_amount),
                promotion_label: form.promotion_label || null,
                badge_text: form.badge_text || null,
                sold_visible_days: Number(form.sold_visible_days),
            },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Marketing - ${car.make} ${car.model}`} />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Marketing Settings</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {car.make} {car.model} {car.trim_level && `- ${car.trim_level}`}
                        </p>
                    </div>
                    <button
                        onClick={() => router.get(admin.cars.index())}
                        className="rounded-md border px-4 py-2 text-sm hover:bg-muted"
                    >
                        Back to Cars
                    </button>
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

                <form onSubmit={handleSubmit} className="max-w-2xl space-y-6">
                    <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <h2 className="mb-4 text-lg font-semibold">Global</h2>
                        <label className="flex items-center gap-3">
                            <input
                                type="checkbox"
                                checked={form.is_active}
                                onChange={(e) => setForm({ ...form, is_active: e.target.checked })}
                                className="h-4 w-4 rounded border"
                            />
                            <span className="text-sm font-medium">Marketing active for this car</span>
                        </label>
                    </div>

                    <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <h2 className="mb-4 text-lg font-semibold">Limited Stock</h2>
                        <div className="space-y-3">
                            <label className="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    checked={form.limited_stock_enabled}
                                    onChange={(e) => setForm({ ...form, limited_stock_enabled: e.target.checked })}
                                    className="h-4 w-4 rounded border"
                                />
                                <span className="text-sm font-medium">Show &quot;Only X remaining&quot;</span>
                            </label>
                            {form.limited_stock_enabled && (
                                <div className="ml-7">
                                    <label className="block text-sm text-muted-foreground">Number of cars remaining</label>
                                    <input
                                        type="number"
                                        min={1}
                                        max={999}
                                        value={form.limited_stock_count}
                                        onChange={(e) => setForm({ ...form, limited_stock_count: e.target.value })}
                                        className="mt-1 w-32 rounded-md border px-3 py-2 text-sm"
                                        placeholder="e.g. 3"
                                    />
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <h2 className="mb-4 text-lg font-semibold">New Price</h2>
                        <div className="space-y-3">
                            <label className="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    checked={form.new_price_enabled}
                                    onChange={(e) => setForm({ ...form, new_price_enabled: e.target.checked })}
                                    className="h-4 w-4 rounded border"
                                />
                                <span className="text-sm font-medium">Show &quot;New Price&quot; badge</span>
                            </label>
                            {form.new_price_enabled && (
                                <div className="ml-7">
                                    <label className="block text-sm text-muted-foreground">New price amount (€)</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min={0}
                                        value={form.new_price_amount}
                                        onChange={(e) => setForm({ ...form, new_price_amount: e.target.value })}
                                        className="mt-1 w-48 rounded-md border px-3 py-2 text-sm"
                                        placeholder="e.g. 18500.00"
                                    />
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <h2 className="mb-4 text-lg font-semibold">Promotion</h2>
                        <div className="space-y-3">
                            <label className="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    checked={form.promotion_enabled}
                                    onChange={(e) => setForm({ ...form, promotion_enabled: e.target.checked })}
                                    className="h-4 w-4 rounded border"
                                />
                                <span className="text-sm font-medium">Show promotion badge</span>
                            </label>
                            {form.promotion_enabled && (
                                <div className="ml-7">
                                    <label className="block text-sm text-muted-foreground">Promotion label</label>
                                    <input
                                        type="text"
                                        maxLength={255}
                                        value={form.promotion_label}
                                        onChange={(e) => setForm({ ...form, promotion_label: e.target.value })}
                                        className="mt-1 w-full rounded-md border px-3 py-2 text-sm"
                                        placeholder="e.g. Big Promotion! -20%"
                                    />
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <h2 className="mb-4 text-lg font-semibold">Custom Badge</h2>
                        <div>
                            <label className="block text-sm text-muted-foreground">Custom badge text (optional)</label>
                            <input
                                type="text"
                                maxLength={100}
                                value={form.badge_text}
                                onChange={(e) => setForm({ ...form, badge_text: e.target.value })}
                                className="mt-1 w-full rounded-md border px-3 py-2 text-sm"
                                placeholder="e.g. Best Seller, Editor's Choice"
                            />
                        </div>
                    </div>

                    <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <h2 className="mb-4 text-lg font-semibold">Sold Badge</h2>
                        <div className="space-y-3">
                            <label className="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    checked={form.sold_enabled}
                                    onChange={(e) => setForm({ ...form, sold_enabled: e.target.checked })}
                                    className="h-4 w-4 rounded border"
                                />
                                <span className="text-sm font-medium">Mark this car as sold in inventory</span>
                            </label>
                            <div className="ml-7">
                                <label className="block text-sm text-muted-foreground">Visible for (days)</label>
                                <input
                                    type="number"
                                    min={1}
                                    max={365}
                                    value={form.sold_visible_days}
                                    onChange={(e) => setForm({ ...form, sold_visible_days: Number(e.target.value || 5) })}
                                    className="mt-1 w-32 rounded-md border px-3 py-2 text-sm"
                                />
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Default is 5 days. Increase this to keep sold badge visible longer.
                                </p>
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Save Marketing Settings'}
                    </button>
                </form>
            </div>
        </AppLayout>
    );
}
