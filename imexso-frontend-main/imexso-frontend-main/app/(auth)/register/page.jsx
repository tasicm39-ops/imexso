"use client";

import { useState, useEffect, useCallback } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import Script from "next/script";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import { useAuth } from "@/context/AuthContext";
import { trackEvent, EventTypes } from "@/lib/tracking";
import { useLocale } from "@/context/LocaleContext";
import { VAT_COUNTRIES, USER_COUNTRIES } from "@/lib/countries";

const RECAPTCHA_SITE_KEY = process.env.NEXT_PUBLIC_RECAPTCHA_SITE_KEY || "";

function parseVatInput(raw) {
    const cleaned = raw.replace(/[\.\-\s]/g, "").toUpperCase();
    const prefix = cleaned.substring(0, 2);
    const found = VAT_COUNTRIES.find((c) => c.code === prefix);
    if (found && cleaned.length > 2) {
        return { country: prefix, number: cleaned.substring(2) };
    }
    return { country: null, number: cleaned };
}

export default function RegisterPage() {
    const router = useRouter();
    const { register, validateVat, isAuthenticated, isValidated, loading: authLoading } = useAuth();
    const { t, locale } = useLocale();

    const [vatData, setVatData] = useState({ country: "", number: "" });
    const [vatStatus, setVatStatus] = useState(null);
    const [vatLoading, setVatLoading] = useState(false);

    const [formData, setFormData] = useState({
        company_name: "",
        vat_number: "",
        last_name: "",
        first_name: "",
        email: "",
        phone: "",
        fax: "",
        address: "",
        postal_code: "",
        city: "",
        country: "",
        password: "",
        is_professional: false,
        website: "",
        gdpr_accepted: false,
        website_url: "",
    });

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

    const handleVatNumberChange = (e) => {
        const raw = e.target.value;
        setVatData((prev) => ({ ...prev, number: raw }));

        const parsed = parseVatInput(raw);
        if (parsed.country && !vatData.country) {
            setVatData({ country: parsed.country, number: raw });
        }
    };

    const handleVatValidate = useCallback(async () => {
        if (!vatData.number) return;

        const parsed = parseVatInput(vatData.number);
        const countryCode = vatData.country || parsed.country;
        const vatNumber = parsed.number;

        if (!countryCode) return;

        setVatLoading(true);
        setVatStatus(null);

        try {
            const result = await validateVat(countryCode, vatNumber);

            if (result.valid) {
                setVatStatus("valid");

                const countryObj = USER_COUNTRIES.find((c) => c.code === result.country_code);
                setFormData((prev) => ({
                    ...prev,
                    company_name: result.company_name || prev.company_name,
                    vat_number: result.vat_number || vatNumber,
                    address: result.address || prev.address,
                    postal_code: result.postal_code || prev.postal_code,
                    city: result.city || prev.city,
                    country: countryObj ? countryObj.name : prev.country,
                }));
            } else {
                setVatStatus("invalid");
            }
        } catch {
            setVatStatus("error");
        } finally {
            setVatLoading(false);
        }
    }, [vatData, validateVat]);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: type === "checkbox" ? checked : value,
        }));
    };

    const getRecaptchaToken = () => {
        return new Promise((resolve) => {
            if (!RECAPTCHA_SITE_KEY || typeof window === "undefined" || !window.grecaptcha) {
                resolve("");
                return;
            }
            window.grecaptcha.ready(() => {
                window.grecaptcha
                    .execute(RECAPTCHA_SITE_KEY, { action: "register" })
                    .then(resolve)
                    .catch(() => resolve(""));
            });
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setGeneralError("");
        setSubmitting(true);

        try {
            const recaptchaToken = await getRecaptchaToken();

            const payload = {
                ...formData,
                language: locale,
                recaptcha_token: recaptchaToken,
            };

            const result = await register(payload);
            if (result.success) {
                trackEvent(EventTypes.REGISTER, {});
                router.push("/pending-approval");
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
                <section className="login-section layout-radius">
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

    if (isAuthenticated) return null;

    const selectStyle = {
        height: 48,
        borderRadius: 6,
        padding: "8px 12px",
        fontSize: 14,
        lineHeight: "1.4",
        appearance: "auto",
        WebkitAppearance: "auto",
    };

    return (
        <>
            <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />

            {RECAPTCHA_SITE_KEY && (
                <Script
                    src={`https://www.google.com/recaptcha/api.js?render=${RECAPTCHA_SITE_KEY}`}
                    strategy="afterInteractive"
                />
            )}

            <section className="login-section layout-radius" style={{ padding: "80px 0" }}>
                <div className="inner-container" style={{ maxWidth: 960 }}>
                    <div className="right-box" style={{ width: "100%" }}>
                        <div className="form-sec">
                            <nav>
                                <div className="nav nav-tabs" role="tablist">
                                    <button
                                        className="nav-link"
                                        type="button"
                                        role="tab"
                                        onClick={() => router.push("/login")}
                                    >
                                        {t("auth.login")}
                                    </button>
                                    <button
                                        className="nav-link active"
                                        type="button"
                                        role="tab"
                                    >
                                        {t("auth.register")}
                                    </button>
                                </div>
                            </nav>

                            <h2 style={{ fontSize: 24, fontWeight: 700, marginBottom: 30, marginTop: 24 }}>
                                {t("auth.register_pro_title")}
                            </h2>

                            {/* Step 1: VAT Validation */}
                            <div style={{ marginBottom: 30, border: "1px solid #eee", borderRadius: 8, overflow: "hidden" }}>
                                <div style={{ display: "flex", alignItems: "center", gap: 12, padding: "16px 20px", background: "#f8f9fa", borderBottom: "1px solid #eee" }}>
                                    <span style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: 28, height: 28, borderRadius: "50%", background: "var(--theme-color1, #050B20)", color: "#fff", fontWeight: 700, fontSize: 14 }}>
                                        1
                                    </span>
                                    <h3 style={{ fontSize: 16, fontWeight: 600, margin: 0 }}>
                                        {t("auth.step1_title")}
                                    </h3>
                                </div>
                                <div style={{ padding: 20 }}>
                                    <p style={{ fontSize: 13, color: "#666", marginBottom: 16 }}>
                                        <strong>{t("auth.step1_country_not_listed")}</strong>{" "}
                                        {t("auth.step1_skip_to_step2")}
                                    </p>
                                    <div className="row" style={{ alignItems: "flex-end" }}>
                                        <div className="col-md-4 mb-3">
                                            <select
                                                className="form-control"
                                                value={vatData.country}
                                                onChange={(e) => setVatData((prev) => ({ ...prev, country: e.target.value }))}
                                                style={selectStyle}
                                            >
                                                <option value="">{t("auth.select_country")}</option>
                                                {VAT_COUNTRIES.map((c) => (
                                                    <option key={c.code} value={c.code}>
                                                        {c.name}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                        <div className="col-md-5 mb-3">
                                            <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                                                {vatData.country && (
                                                    <span style={{ fontWeight: 600, fontSize: 14, minWidth: 28, color: "#333" }}>
                                                        {vatData.country}
                                                    </span>
                                                )}
                                                <input
                                                    type="text"
                                                    className="form-control"
                                                    placeholder={t("auth.vat_number")}
                                                    value={vatData.number}
                                                    onChange={handleVatNumberChange}
                                                    style={{ height: 48, borderRadius: 6 }}
                                                />
                                            </div>
                                        </div>
                                        <div className="col-md-3 mb-3">
                                            <button
                                                type="button"
                                                className="theme-btn"
                                                onClick={handleVatValidate}
                                                disabled={vatLoading || !vatData.number}
                                                style={{ width: "100%", padding: "12px 16px", fontSize: 13 }}
                                            >
                                                {vatLoading ? t("auth.validating_vat") : t("auth.validate")}
                                            </button>
                                        </div>
                                    </div>
                                    {vatStatus === "valid" && (
                                        <div className="alert alert-success mb-0" style={{ fontSize: 13 }}>
                                            {t("auth.vat_valid")}
                                        </div>
                                    )}
                                    {vatStatus === "invalid" && (
                                        <div className="alert alert-danger mb-0" style={{ fontSize: 13 }}>
                                            {t("auth.vat_invalid")}
                                        </div>
                                    )}
                                    {vatStatus === "error" && (
                                        <div className="alert alert-warning mb-0" style={{ fontSize: 13 }}>
                                            {t("auth.vat_error")}
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Step 2: Registration Form */}
                            <div style={{ border: "1px solid #eee", borderRadius: 8, overflow: "hidden" }}>
                                <div style={{ display: "flex", alignItems: "center", gap: 12, padding: "16px 20px", background: "#f8f9fa", borderBottom: "1px solid #eee" }}>
                                    <span style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: 28, height: 28, borderRadius: "50%", background: "var(--theme-color1, #050B20)", color: "#fff", fontWeight: 700, fontSize: 14 }}>
                                        2
                                    </span>
                                    <h3 style={{ fontSize: 16, fontWeight: 600, margin: 0 }}>
                                        {t("auth.step2_title")}
                                    </h3>
                                </div>
                                <div style={{ padding: 20 }}>
                                    <form onSubmit={handleSubmit}>
                                        <div className="row">
                                            {/* Company Name */}
                                            <div className="col-md-12 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.company_name")}</label>
                                                    <input
                                                        type="text"
                                                        name="company_name"
                                                        value={formData.company_name}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                    {errors.company_name && (
                                                        <small className="text-danger d-block mt-1">{errors.company_name[0]}</small>
                                                    )}
                                                </div>
                                            </div>

                                            {/* VAT Number */}
                                            <div className="col-md-12 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.vat_number")}</label>
                                                    <input
                                                        type="text"
                                                        name="vat_number"
                                                        value={formData.vat_number}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                    {errors.vat_number && (
                                                        <small className="text-danger d-block mt-1">{errors.vat_number[0]}</small>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Last Name / First Name */}
                                            <div className="col-md-6 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.last_name")} *</label>
                                                    <input
                                                        required
                                                        type="text"
                                                        name="last_name"
                                                        value={formData.last_name}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                    {errors.last_name && (
                                                        <small className="text-danger d-block mt-1">{errors.last_name[0]}</small>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="col-md-6 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.first_name")}</label>
                                                    <input
                                                        type="text"
                                                        name="first_name"
                                                        value={formData.first_name}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                </div>
                                            </div>

                                            {/* Email */}
                                            <div className="col-md-12 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.email")} *</label>
                                                    <input
                                                        required
                                                        type="email"
                                                        name="email"
                                                        placeholder="you@example.com"
                                                        value={formData.email}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                    {errors.email && (
                                                        <small className="text-danger d-block mt-1">{errors.email[0]}</small>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Phone / Fax */}
                                            <div className="col-md-6 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.phone")}</label>
                                                    <input
                                                        type="text"
                                                        name="phone"
                                                        value={formData.phone}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                    {errors.phone && (
                                                        <small className="text-danger d-block mt-1">{errors.phone[0]}</small>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="col-md-6 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.fax")}</label>
                                                    <input
                                                        type="text"
                                                        name="fax"
                                                        value={formData.fax}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                </div>
                                            </div>

                                            {/* Address */}
                                            <div className="col-md-12 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.address")}</label>
                                                    <input
                                                        type="text"
                                                        name="address"
                                                        value={formData.address}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                </div>
                                            </div>

                                            {/* Postal Code / City / Country -- aligned row */}
                                            <div className="col-md-3 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.postal_code")}</label>
                                                    <input
                                                        type="text"
                                                        name="postal_code"
                                                        value={formData.postal_code}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                </div>
                                            </div>
                                            <div className="col-md-4 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.city")}</label>
                                                    <input
                                                        type="text"
                                                        name="city"
                                                        value={formData.city}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                </div>
                                            </div>
                                            <div className="col-md-5 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.country")}</label>
                                                    <select
                                                        name="country"
                                                        className="form-control"
                                                        value={formData.country}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                        style={selectStyle}
                                                    >
                                                        <option value="">{t("auth.select_country")}</option>
                                                        {USER_COUNTRIES.map((c) => (
                                                            <option key={c.code} value={c.name}>
                                                                {c.name}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    {errors.country && (
                                                        <small className="text-danger d-block mt-1">{errors.country[0]}</small>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Password */}
                                            <div className="col-md-12 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.password")} *</label>
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
                                                        <small className="text-danger d-block mt-1">{errors.password[0]}</small>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Professional Checkbox */}
                                            <div className="col-md-6 mb-3">
                                                <div className="form-check">
                                                    <input
                                                        className="form-check-input"
                                                        type="checkbox"
                                                        id="is_professional"
                                                        name="is_professional"
                                                        checked={formData.is_professional}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                    <label className="form-check-label" htmlFor="is_professional">
                                                        {t("auth.is_professional")}
                                                    </label>
                                                </div>
                                            </div>

                                            {/* Website */}
                                            <div className="col-md-6 mb-3">
                                                <div className="form_boxes">
                                                    <label>{t("auth.website")}</label>
                                                    <input
                                                        type="text"
                                                        name="website"
                                                        value={formData.website}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                    />
                                                </div>
                                            </div>

                                            {/* GDPR Checkbox */}
                                            <div className="col-md-12 mb-3">
                                                <div className="form-check">
                                                    <input
                                                        className="form-check-input"
                                                        type="checkbox"
                                                        id="gdpr_accepted"
                                                        name="gdpr_accepted"
                                                        checked={formData.gdpr_accepted}
                                                        onChange={handleChange}
                                                        disabled={submitting}
                                                        required
                                                    />
                                                    <label className="form-check-label" htmlFor="gdpr_accepted">
                                                        {t("auth.gdpr_accept")}{" "}
                                                        <Link href="/privacy-policy" target="_blank" style={{ fontWeight: 600 }}>
                                                            {t("auth.gdpr_terms_link")}
                                                        </Link>.
                                                    </label>
                                                    {errors.gdpr_accepted && (
                                                        <small className="text-danger d-block mt-1">{errors.gdpr_accepted[0]}</small>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Honeypot */}
                                            <div style={{ position: "absolute", left: -9999, opacity: 0, height: 0, overflow: "hidden" }} aria-hidden="true">
                                                <input
                                                    type="text"
                                                    name="website_url"
                                                    tabIndex={-1}
                                                    autoComplete="off"
                                                    value={formData.website_url}
                                                    onChange={handleChange}
                                                />
                                            </div>

                                            {/* Error display */}
                                            {generalError && (
                                                <div className="col-md-12 mb-3">
                                                    <div className="alert alert-danger">{generalError}</div>
                                                </div>
                                            )}

                                            {errors.recaptcha_token && (
                                                <div className="col-md-12 mb-3">
                                                    <div className="alert alert-danger">{errors.recaptcha_token[0]}</div>
                                                </div>
                                            )}

                                            {/* Submit */}
                                            <div className="col-md-4 mb-3">
                                                <button
                                                    type="submit"
                                                    className="theme-btn"
                                                    disabled={submitting}
                                                    style={{ width: "100%", padding: "12px 20px" }}
                                                >
                                                    {submitting ? t("auth.submitting") : t("auth.submit")}{" "}
                                                    {!submitting && (
                                                        <Image alt="" src="/images/arrow.svg" width={14} height={14} />
                                                    )}
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <div style={{ textAlign: "center", marginTop: 16 }}>
                                        <span>
                                            {t("auth.have_account")}{" "}
                                            <Link href="/login">{t("auth.sign_in_here")}</Link>
                                        </span>
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
