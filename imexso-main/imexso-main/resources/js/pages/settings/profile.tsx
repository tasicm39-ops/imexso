import { Transition } from '@headlessui/react';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useRef, useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit(),
    },
];

export default function Profile({
    mustVerifyEmail,
    status,
    logoUrl,
}: {
    mustVerifyEmail: boolean;
    status?: string;
    logoUrl: string | null;
}) {
    const { auth } = usePage().props;
    const logoInputRef = useRef<HTMLInputElement>(null);
    const [logoPreview, setLogoPreview] = useState<string | null>(logoUrl);

    function handleLogoChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (file) {
            setLogoPreview(URL.createObjectURL(file));
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <h1 className="sr-only">Profile settings</h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title="Profile information"
                        description="Update your name and email address"
                    />

                    <Form
                        {...ProfileController.update.form()}
                        options={{
                            preserveScroll: true,
                            forceFormData: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, recentlySuccessful, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder="Full name"
                                    />
                                    <InputError className="mt-2" message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.email}
                                        name="email"
                                        required
                                        autoComplete="username"
                                        placeholder="Email address"
                                    />
                                    <InputError className="mt-2" message={errors.email} />
                                </div>

                                {mustVerifyEmail && auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            Your email address is unverified.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                Click here to resend the verification email.
                                            </Link>
                                        </p>

                                        {status === 'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                A new verification link has been sent to your email address.
                                            </div>
                                        )}
                                    </div>
                                )}

                                <div className="border-t pt-6">
                                    <Heading
                                        variant="small"
                                        title="Company branding"
                                        description="Used in offers, PDFs and emails sent to clients"
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="company_name">Company name</Label>
                                    <Input
                                        id="company_name"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.company_name ?? ''}
                                        name="company_name"
                                        placeholder="Your company name"
                                    />
                                    <InputError className="mt-2" message={errors.company_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="slogan">Slogan</Label>
                                    <Input
                                        id="slogan"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.slogan ?? ''}
                                        name="slogan"
                                        placeholder="Company slogan or tagline"
                                    />
                                    <InputError className="mt-2" message={errors.slogan} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="logo">Logo</Label>
                                    <div className="flex items-center gap-4">
                                        {logoPreview && (
                                            <img
                                                src={logoPreview}
                                                alt="Company logo"
                                                className="h-16 w-auto rounded border object-contain"
                                            />
                                        )}
                                        <div>
                                            <input
                                                ref={logoInputRef}
                                                id="logo"
                                                type="file"
                                                name="logo"
                                                accept="image/jpeg,image/png,image/gif,image/webp"
                                                className="hidden"
                                                onChange={handleLogoChange}
                                            />
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => logoInputRef.current?.click()}
                                            >
                                                {logoPreview ? 'Change logo' : 'Upload logo'}
                                            </Button>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                JPEG, PNG, GIF or WebP. Max 2MB.
                                            </p>
                                        </div>
                                    </div>
                                    <InputError className="mt-2" message={errors.logo} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing} data-test="update-profile-button">
                                        Save
                                    </Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">Saved</p>
                                    </Transition>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
