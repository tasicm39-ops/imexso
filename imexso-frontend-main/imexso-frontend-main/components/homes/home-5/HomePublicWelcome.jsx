"use client";

import Image from "next/image";
import Link from "next/link";
import { useLocale } from "@/context/LocaleContext";

export default function HomePublicWelcome() {
  const { t } = useLocale();

  return (
    <div>
      <div
        style={{
          height: 200,
          width: "100%",
          marginTop: -2,
          backgroundColor: "#050b20",
          position: "absolute",
        }}
      />
      <section
        className="boxcar-banner-section-five section-radius-top home-public-welcome"
        style={{ marginTop: 0 }}
      >
        <div className="banner-content-three">
          <div className="boxcar-container">
            <div className="home-public-welcome__inner text-center wow fadeInUp">
              <h2 className="home-public-welcome__title">
                {t("home.professional_title")}
              </h2>
              <p className="home-public-welcome__subtitle">
                {t("home.professional_subtitle")}
              </p>
              <div className="home-public-welcome__actions">
                <Link href="/login" className="home-public-welcome__btn home-public-welcome__btn--primary">
                  {t("general.log_in")}
                  <Image alt="" src="/images/arrow.svg" width={14} height={14} />
                </Link>
                <Link href="/register" className="home-public-welcome__btn home-public-welcome__btn--outline">
                  {t("nav.register")}
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
