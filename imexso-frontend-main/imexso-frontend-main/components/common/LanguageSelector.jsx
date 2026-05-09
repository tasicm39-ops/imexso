"use client";

import React, { useState, useRef, useEffect } from "react";
import { useLocale } from "@/context/LocaleContext";

const FLAG_EMOJI = {
    fr: "🇫🇷",
    nl: "🇳🇱",
    de: "🇩🇪",
    it: "🇮🇹",
    en: "🇬🇧",
};

export default function LanguageSelector({ variant = "desktop" }) {
    const { locale, changeLocale, supportedLocales, localeLabels } = useLocale();
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        function handleClickOutside(event) {
            if (ref.current && !ref.current.contains(event.target)) {
                setOpen(false);
            }
        }
        document.addEventListener("click", handleClickOutside);
        return () => document.removeEventListener("click", handleClickOutside);
    }, []);

    const handleSelect = (newLocale) => {
        changeLocale(newLocale);
        setOpen(false);
    };

    if (variant === "mobile") {
        return (
            <div style={{ padding: "10px 20px" }}>
                <div style={{ display: "flex", gap: "8px", flexWrap: "wrap" }}>
                    {supportedLocales.map((loc) => (
                        <button
                            key={loc}
                            type="button"
                            onClick={() => handleSelect(loc)}
                            style={{
                                padding: "6px 14px",
                                fontSize: "13px",
                                fontWeight: locale === loc ? 700 : 400,
                                borderRadius: "6px",
                                border: locale === loc ? "2px solid #405FF2" : "1px solid rgba(255,255,255,0.2)",
                                background: locale === loc ? "rgba(64, 95, 242, 0.15)" : "transparent",
                                color: locale === loc ? "#405FF2" : "rgba(255,255,255,0.8)",
                                cursor: "pointer",
                                transition: "all 0.2s",
                            }}
                        >
                            {FLAG_EMOJI[loc]} {loc.toUpperCase()}
                        </button>
                    ))}
                </div>
            </div>
        );
    }

    return (
        <div ref={ref} style={{ position: "relative", marginRight: "12px" }}>
            <button
                type="button"
                onClick={() => setOpen((prev) => !prev)}
                style={{
                    display: "flex",
                    alignItems: "center",
                    gap: "4px",
                    padding: "6px 12px",
                    fontSize: "13px",
                    fontWeight: 600,
                    borderRadius: "6px",
                    border: "1px solid rgba(255,255,255,0.35)",
                    background: "rgba(255,255,255,0.2)",
                    color: "white",
                    cursor: "pointer",
                    transition: "all 0.2s",
                    backdropFilter: "blur(4px)",
                }}
                onMouseEnter={(e) => {
                    e.currentTarget.style.background = "rgba(255,255,255,0.2)";
                    e.currentTarget.style.borderColor = "rgba(255,255,255,0.5)";
                }}
                onMouseLeave={(e) => {
                    e.currentTarget.style.background = "rgba(255,255,255,0.1)";
                    e.currentTarget.style.borderColor = "rgba(255,255,255,0.35)";
                }}
            >
                {FLAG_EMOJI[locale]} {locale.toUpperCase()}
                <i
                    className="fa-solid fa-angle-down"
                    style={{ fontSize: "9px", marginLeft: "2px" }}
                />
            </button>
            {open && (
                <div
                    style={{
                        position: "absolute",
                        top: "100%",
                        right: 0,
                        marginTop: "6px",
                        background: "#fff",
                        borderRadius: "8px",
                        boxShadow: "0 4px 20px rgba(0,0,0,0.12)",
                        minWidth: "150px",
                        zIndex: 1000,
                        overflow: "hidden",
                    }}
                >
                    {supportedLocales.map((loc) => (
                        <button
                            key={loc}
                            type="button"
                            onClick={() => handleSelect(loc)}
                            style={{
                                display: "flex",
                                alignItems: "center",
                                gap: "8px",
                                width: "100%",
                                padding: "10px 16px",
                                fontSize: "14px",
                                fontWeight: locale === loc ? 700 : 400,
                                background: locale === loc ? "#f0f4ff" : "transparent",
                                color: locale === loc ? "#405FF2" : "#333",
                                border: "none",
                                cursor: "pointer",
                                textAlign: "left",
                                transition: "background 0.2s",
                            }}
                            onMouseEnter={(e) => {
                                if (locale !== loc) e.currentTarget.style.background = "#f7f7f7";
                            }}
                            onMouseLeave={(e) => {
                                e.currentTarget.style.background = locale === loc ? "#f0f4ff" : "transparent";
                            }}
                        >
                            <span style={{ fontSize: "16px" }}>{FLAG_EMOJI[loc]}</span>
                            {localeLabels[loc]}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
