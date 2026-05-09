"use client";

import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import { useLocale } from "@/context/LocaleContext";

export default function PrivacyPolicyPage() {
    const { t } = useLocale();

    const sections = [
        { title: t("privacy.data_collection_title"), text: t("privacy.data_collection_text") },
        { title: t("privacy.data_usage_title"), text: t("privacy.data_usage_text") },
        { title: t("privacy.no_sell_title"), text: t("privacy.no_sell_text") },
        { title: t("privacy.cookies_title"), text: t("privacy.cookies_text") },
        {
            title: t("privacy.rights_title"),
            text: t("privacy.rights_text"),
            extra: (
                <a href={`mailto:${t("privacy.contact_email")}`} style={{ fontWeight: 600 }}>
                    {t("privacy.contact_email")}
                </a>
            ),
        },
        { title: t("privacy.data_retention_title"), text: t("privacy.data_retention_text") },
    ];

    return (
        <>
            <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
            <section className="login-section layout-radius">
                <div className="inner-container" style={{ maxWidth: 800 }}>
                    <div className="right-box" style={{ width: "100%" }}>
                        <div className="form-sec" style={{ padding: "40px 30px" }}>
                            <h1 style={{ fontSize: 28, fontWeight: 700, marginBottom: 16 }}>
                                {t("privacy.title")}
                            </h1>
                            <p style={{ color: "#666", marginBottom: 30, fontSize: 15, lineHeight: 1.6 }}>
                                {t("privacy.intro")}
                            </p>

                            {sections.map((section, idx) => (
                                <div key={idx} style={{ marginBottom: 24 }}>
                                    <h2 style={{ fontSize: 18, fontWeight: 600, marginBottom: 8 }}>
                                        {section.title}
                                    </h2>
                                    <p style={{ color: "#555", fontSize: 14, lineHeight: 1.7, margin: 0 }}>
                                        {section.text}
                                        {section.extra && (
                                            <>
                                                {" "}
                                                {section.extra}
                                            </>
                                        )}
                                    </p>
                                </div>
                            ))}

                            <p style={{ color: "#999", fontSize: 12, marginTop: 30 }}>
                                {t("privacy.last_updated")}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <Footer1 parentClass="boxcar-footer footer-style-one v1 cus-st-1" />
        </>
    );
}
