"use client";

import { useState, Suspense } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";

function ResetPasswordForm() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const { resetPassword } = useAuth();
    const { t } = useLocale();

    const token = searchParams.get("token") || "";
    const emailParam = searchParams.get("email") || "";

    const [formData, setFormData] = useState({
        email: emailParam,
        password: "",
        password_confirmation: "",
    });
    const [errors, setErrors] = useState({});
    const [generalError, setGeneralError] = useState("");
    const [submitting, setSubmitting] = useState(false);

    const handleChange = (e) => {
        setFormData((prev) => ({ ...prev, [e.target.name]: e.target.value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setGeneralError("");
        setSubmitting(true);

        try {
            const ok = await resetPassword({
                ...formData,
                token,
            });
            if (ok) {
                router.push("/login");
            } else {
                setGeneralError(t("auth.reset_error"));
            }
        } catch {
            setGeneralError(t("auth.unexpected_error"));
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="form-box">
            <form onSubmit={handleSubmit}>
                <div className="form_boxes">
                    <label>{t("auth.email")}</label>
                    <input
                        required
                        type="email"
                        name="email"
                        value={formData.email}
                        onChange={handleChange}
                        disabled={submitting}
                    />
                    {errors.email && (
                        <small className="text-danger">{errors.email[0]}</small>
                    )}
                </div>
                <div className="form_boxes">
                    <label>{t("auth.new_password")}</label>
                    <input
                        required
                        type="password"
                        name="password"
                        placeholder="********"
                        value={formData.password}
                        onChange={handleChange}
                        disabled={submitting}
                    />
                    {errors.password && (
                        <small className="text-danger">{errors.password[0]}</small>
                    )}
                </div>
                <div className="form_boxes">
                    <label>{t("auth.confirm_new_password")}</label>
                    <input
                        required
                        type="password"
                        name="password_confirmation"
                        placeholder="********"
                        value={formData.password_confirmation}
                        onChange={handleChange}
                        disabled={submitting}
                    />
                </div>
                {generalError && (
                    <div className="alert alert-danger mb-3">
                        {generalError}
                    </div>
                )}
                <div className="form-submit">
                    <button type="submit" className="theme-btn" disabled={submitting}>
                        {submitting ? t("auth.resetting") : t("auth.reset_password")}{" "}
                        {!submitting && (
                            <Image
                                alt=""
                                src="/images/arrow.svg"
                                width={14}
                                height={14}
                            />
                        )}
                    </button>
                </div>
                <div className="text-center mt-4">
                    <Link href="/login">{t("auth.back_to_login")}</Link>
                </div>
            </form>
        </div>
    );
}

export default function ResetPasswordPage() {
    const { t } = useLocale();

    return (
        <>
            <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
            <section className="login-section layout-radius">
                <div className="inner-container">
                    <div className="right-box">
                        <div className="form-sec">
                            <h3 className="mb-4">{t("auth.reset_password")}</h3>
                            <Suspense fallback={<p>{t("general.loading")}</p>}>
                                <ResetPasswordForm />
                            </Suspense>
                        </div>
                    </div>
                </div>
            </section>
            <Footer1 parentClass="boxcar-footer footer-style-one v1 cus-st-1" />
        </>
    );
}
