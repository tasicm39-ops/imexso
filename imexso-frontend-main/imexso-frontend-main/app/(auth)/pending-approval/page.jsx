"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";

export default function PendingApprovalPage() {
    const router = useRouter();
    const { logout, fetchUser, isAuthenticated, isValidated, loading: authLoading } = useAuth();
    const { t } = useLocale();

    const [checking, setChecking] = useState(false);

    const handleCheckStatus = async () => {
        setChecking(true);
        try {
            await fetchUser();
        } finally {
            setChecking(false);
        }
    };

    if (!authLoading && isAuthenticated && isValidated) {
        router.replace("/");
        return null;
    }

    const handleLogout = async () => {
        await logout();
        router.push("/login");
    };

    return (
        <>
            <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
            <section className="login-section layout-radius">
                <div className="inner-container">
                    <div className="right-box">
                        <div className="form-sec">
                            <div className="form-box" style={{ textAlign: "center" }}>
                                <div style={{ marginBottom: "24px" }}>
                                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="40" cy="40" r="38" stroke="#405FF2" strokeWidth="3" fill="none" />
                                        <path d="M40 22V44" stroke="#405FF2" strokeWidth="3" strokeLinecap="round" />
                                        <circle cx="40" cy="54" r="2.5" fill="#405FF2" />
                                    </svg>
                                </div>
                                <h4 className="title" style={{ marginBottom: "16px", fontSize: "24px" }}>
                                    {t("auth.pending_approval")}
                                </h4>
                                <p style={{ color: "#666", maxWidth: "420px", margin: "0 auto 32px", lineHeight: "1.7" }}>
                                    {t("auth.pending_approval_message")}
                                </p>
                                <div style={{ display: "flex", gap: "12px", justifyContent: "center", flexWrap: "wrap" }}>
                                    <button
                                        type="button"
                                        onClick={handleCheckStatus}
                                        disabled={checking}
                                        style={{
                                            padding: "12px 28px",
                                            fontSize: "14px",
                                            fontWeight: 600,
                                            borderRadius: "8px",
                                            border: "none",
                                            cursor: checking ? "not-allowed" : "pointer",
                                            background: "#405FF2",
                                            color: "#fff",
                                            transition: "all 0.2s",
                                            opacity: checking ? 0.7 : 1,
                                        }}
                                    >
                                        {checking ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm" style={{ marginRight: "8px" }} />
                                                {t("auth.checking")}
                                            </>
                                        ) : (
                                            <>
                                                <i className="fa-solid fa-rotate-right" style={{ marginRight: "8px" }} />
                                                {t("auth.check_status")}
                                            </>
                                        )}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={handleLogout}
                                        style={{
                                            padding: "12px 28px",
                                            fontSize: "14px",
                                            fontWeight: 600,
                                            borderRadius: "8px",
                                            border: "2px solid #e2e2e2",
                                            cursor: "pointer",
                                            background: "#fff",
                                            color: "#333",
                                            transition: "all 0.2s",
                                        }}
                                    >
                                        <i className="fa-solid fa-right-from-bracket" style={{ marginRight: "8px" }} />
                                        {t("nav.logout")}
                                    </button>
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
