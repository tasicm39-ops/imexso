"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { useLocale } from "@/context/LocaleContext";

const COOKIE_NAME = "cookie_consent";

export default function CookieConsent() {
    const { t } = useLocale();
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const consent = document.cookie
            .split("; ")
            .find((row) => row.startsWith(`${COOKIE_NAME}=`));
        if (!consent) {
            setVisible(true);
        }
    }, []);

    const handleAccept = () => {
        const maxAge = 365 * 24 * 60 * 60;
        document.cookie = `${COOKIE_NAME}=accepted; path=/; max-age=${maxAge}; SameSite=Lax`;
        setVisible(false);
    };

    if (!visible) return null;

    return (
        <div
            style={{
                position: "fixed",
                bottom: 0,
                left: 0,
                right: 0,
                zIndex: 9999,
                background: "#050B20",
                color: "#fff",
                padding: "16px 24px",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                gap: 20,
                flexWrap: "wrap",
                fontSize: 14,
                boxShadow: "0 -2px 12px rgba(0,0,0,0.15)",
            }}
        >
            <span>{t("cookie_consent.message")}</span>
            <div style={{ display: "flex", gap: 12, alignItems: "center" }}>
                <Link
                    href="/privacy-policy"
                    style={{ color: "#ccc", textDecoration: "underline", fontSize: 13 }}
                >
                    {t("cookie_consent.learn_more")}
                </Link>
                <button
                    onClick={handleAccept}
                    style={{
                        background: "var(--theme-color1, #E8A427)",
                        color: "#fff",
                        border: "none",
                        borderRadius: 6,
                        padding: "8px 24px",
                        fontWeight: 600,
                        cursor: "pointer",
                        fontSize: 14,
                    }}
                >
                    {t("cookie_consent.accept")}
                </button>
            </div>
        </div>
    );
}
