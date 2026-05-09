"use client";

import { useState } from "react";
import Image from "next/image";
import Link from "next/link";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";

export default function ForgotPasswordPage() {
    const { forgotPassword } = useAuth();
    const { t } = useLocale();

    const [email, setEmail] = useState("");
    const [submitting, setSubmitting] = useState(false);
    const [success, setSuccess] = useState(false);
    const [error, setError] = useState("");

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");
        setSuccess(false);
        setSubmitting(true);

        try {
            const ok = await forgotPassword(email);
            if (ok) {
                setSuccess(true);
            } else {
                setError(t("auth.reset_link_error"));
            }
        } catch {
            setError(t("auth.unexpected_error"));
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <>
        <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
        <section className="login-section layout-radius">
            <div className="inner-container">
                <div className="right-box">
                    <div className="form-sec">
                        <h3 className="mb-2">{t("auth.forgot_password_title")}</h3>
                        <p className="mb-4">
                            {t("auth.forgot_password_subtitle")}
                        </p>
                        <div className="form-box">
                            {success ? (
                                <div>
                                    <div className="alert alert-success">
                                        {t("auth.reset_link_sent")}
                                    </div>
                                    <div className="text-center mt-4">
                                        <Link href="/login" className="theme-btn">
                                            {t("auth.back_to_login")}{" "}
                                            <Image
                                                alt=""
                                                src="/images/arrow.svg"
                                                width={14}
                                                height={14}
                                            />
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <form onSubmit={handleSubmit}>
                                    <div className="form_boxes">
                                        <label>{t("auth.email")}</label>
                                        <input
                                            required
                                            type="email"
                                            name="email"
                                            placeholder="you@example.com"
                                            value={email}
                                            onChange={(e) => setEmail(e.target.value)}
                                            disabled={submitting}
                                        />
                                    </div>
                                    {error && (
                                        <div className="alert alert-danger mb-3">
                                            {error}
                                        </div>
                                    )}
                                    <div className="form-submit">
                                        <button type="submit" className="theme-btn" disabled={submitting}>
                                            {submitting ? t("auth.sending") : t("auth.send_reset_link")}{" "}
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
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <Footer1 parentClass="boxcar-footer footer-style-one v1 cus-st-1" />
        </>
    );
}
