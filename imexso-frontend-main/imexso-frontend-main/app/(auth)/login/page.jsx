"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";
import { trackEvent, EventTypes } from "@/lib/tracking";

export default function LoginPage() {
    const router = useRouter();
    const { login, isAuthenticated, isValidated, loading: authLoading } = useAuth();
    const { t } = useLocale();

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [errors, setErrors] = useState({});
    const [generalError, setGeneralError] = useState("");
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        if (!authLoading && isAuthenticated) {
            if (isValidated) {
                router.replace("/");
            } else {
                router.replace("/pending-approval");
            }
        }
    }, [authLoading, isAuthenticated, isValidated, router]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setGeneralError("");
        setSubmitting(true);

        try {
            const result = await login(email, password);
            if (result.success) {
                trackEvent(EventTypes.LOGIN, {});
                router.push("/");
            } else {
                if (result.errors) {
                    setErrors(result.errors);
                }
                if (result.error) {
                    setGeneralError(result.error);
                }
            }
        } catch {
            setGeneralError(t("auth.unexpected_error"));
        } finally {
            setSubmitting(false);
        }
    };

    if (authLoading) {
        return (
            <>
                <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
                <section className="login-section layout-radius" style={{ padding: "80px 0" }}>
                    <div className="inner-container">
                        <div className="right-box">
                            <div className="form-sec text-center py-5">
                                <div className="spinner-border" role="status">
                                    <span className="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <Footer1 parentClass="boxcar-footer footer-style-one v1 cus-st-1" />
            </>
        );
    }

    if (isAuthenticated) {
        return null;
    }

    return (
        <>
            <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
            <section className="login-section layout-radius" style={{ padding: "80px 0" }}>
                <div className="inner-container">
                    <div className="right-box">
                        <div className="form-sec">
                            <nav>
                                <div className="nav nav-tabs" id="nav-tab" role="tablist">
                                    <button
                                        className="nav-link active"
                                        id="nav-signin-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#nav-signin"
                                        type="button"
                                        role="tab"
                                        aria-controls="nav-signin"
                                        aria-selected="true"
                                    >
                                        {t("auth.login")}
                                    </button>
                                    <button
                                        className="nav-link"
                                        id="nav-register-tab"
                                        type="button"
                                        role="tab"
                                        onClick={() => router.push("/register")}
                                    >
                                        {t("auth.register")}
                                    </button>
                                </div>
                            </nav>
                            <div className="tab-content" id="nav-tabContent">
                                <div
                                    className="tab-pane fade show active"
                                    id="nav-signin"
                                    role="tabpanel"
                                    aria-labelledby="nav-signin-tab"
                                >
                                    <div className="form-box">
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
                                                {errors.email && (
                                                    <small className="text-danger d-block mt-1">
                                                        {errors.email[0]}
                                                    </small>
                                                )}
                                            </div>
                                            <div className="form_boxes">
                                                <label>{t("auth.password")}</label>
                                                <input
                                                    required
                                                    type="password"
                                                    name="password"
                                                    placeholder="********"
                                                    value={password}
                                                    onChange={(e) => setPassword(e.target.value)}
                                                    disabled={submitting}
                                                />
                                                {errors.password && (
                                                    <small className="text-danger d-block mt-1">
                                                        {errors.password[0]}
                                                    </small>
                                                )}
                                            </div>
                                            <div className="btn-box">
                                                <Link href="/forgot-password" className="pasword-btn">
                                                    {t("auth.forgotten_password")}
                                                </Link>
                                            </div>
                                            {generalError && (
                                                <div className="alert alert-danger mb-3">
                                                    {generalError}
                                                </div>
                                            )}
                                            <div className="form-submit">
                                                <button type="submit" className="theme-btn" disabled={submitting}>
                                                    {submitting ? t("auth.signing_in") : t("auth.login")}{" "}
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
                                        </form>
                                        <div className="btn-box-two">
                                            <span>{t("auth.no_account")}</span>
                                            <Link href="/register" className="register-link">
                                                {t("auth.register_here")}
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <Footer1 parentClass="boxcar-footer footer-style-one v1 cus-st-1" />
        </>
    );
}
