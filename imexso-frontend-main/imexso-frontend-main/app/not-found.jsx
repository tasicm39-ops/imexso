"use client";
import Link from "next/link";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import { useLocale } from "@/context/LocaleContext";

export default function NotFound() {
    const { t } = useLocale();
    return (
        <>
            <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
            <section className="error-section layout-radius" style={{ minHeight: "60vh" }}>
                <div className="boxcar-container">
                    <div className="d-flex flex-column justify-content-center align-items-center" style={{ minHeight: "50vh" }}>
                        <h2>{t("not_found.title")}</h2>
                        <p className="mt-3">
                            {t("not_found.description")}
                        </p>
                        <Link href="/" className="theme-btn btn-style-one mt-4">
                            <span className="btn-title">{t("not_found.go_home")}</span>
                        </Link>
                    </div>
                </div>
            </section>
            <Footer1 />
        </>
    );
}
