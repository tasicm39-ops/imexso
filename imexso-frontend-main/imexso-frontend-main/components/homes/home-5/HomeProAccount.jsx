"use client";

import Image from "next/image";
import Link from "next/link";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";

const ADVANTAGE_KEYS = ["1", "2", "3", "4", "5", "6", "7"];
const COMMITMENT_KEYS = ["1", "2", "3", "4", "5", "6"];

const PRO_CARS_IMAGE =
  "https://www.imexso.com/ui/images/contents/_CD20122.jpg";

function renderProLine(text) {
  const parts = text.split(/(\*\*[^*]+\*\*)/g).filter(Boolean);

  return parts.map((part, index) => {
    if (part.startsWith("**") && part.endsWith("**")) {
      return <strong key={index}>{part.slice(2, -2)}</strong>;
    }

    return <span key={index}>{part}</span>;
  });
}

export default function HomeProAccount() {
  const { isAuthenticated, isValidated } = useAuth();
  const { t } = useLocale();
  const canBrowse = isAuthenticated && isValidated;
  const stockHref = canBrowse ? "/inventory" : "/login";

  return (
    <section className="home-pro-account">
      <div className="home-pro-account__banner">
        <div className="boxcar-container home-pro-account__content home-pro-account__content--banner">
          <p className="home-pro-account__banner-intro">{t("home.pro_intro")}</p>
          <p className="home-pro-account__banner-highlight">
            <span>{t("home.pro_highlight")}</span>
            <Link href={stockHref} className="home-pro-account__vehicles-btn">
              <i className="flaticon-car-3" aria-hidden="true" />
              {t("home.pro_vehicles_btn")}
            </Link>
          </p>
          <p className="home-pro-account__banner-footer">{t("home.pro_footer")}</p>
        </div>
      </div>

      <div className="boxcar-container home-pro-account__content">
        <div className="home-pro-account__grid">
          <div className="home-pro-account__panel wow fadeInUp">
            <h3 className="home-pro-account__panel-title">
              {t("home.pro_advantages_title")}
            </h3>
            <ul className="home-pro-account__list">
              {ADVANTAGE_KEYS.map((key) => (
                <li key={key}>{renderProLine(t(`home.pro_advantage_${key}`))}</li>
              ))}
            </ul>
          </div>

          <div className="home-pro-account__center wow fadeInUp" data-wow-delay="100ms">
            <div className="home-pro-account__image-wrap">
              <Image
                src={PRO_CARS_IMAGE}
                alt=""
                width={320}
                height={420}
                className="home-pro-account__image"
                unoptimized
              />
            </div>
            <div className="home-pro-account__btn-group">
              <Link href="/engagements" className="home-pro-account__btn home-pro-account__btn--accent">
                {t("home.pro_learn_more")}
              </Link>
              <Link href="/register" className="home-pro-account__btn home-pro-account__btn--primary">
                {t("home.pro_create_account")}
              </Link>
            </div>
          </div>

          <div className="home-pro-account__panel wow fadeInUp" data-wow-delay="200ms">
            <h3 className="home-pro-account__panel-title">
              {t("home.pro_commitments_title")}
            </h3>
            <ul className="home-pro-account__list">
              {COMMITMENT_KEYS.map((key) => (
                <li key={key}>{renderProLine(t(`home.pro_commitment_${key}`))}</li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </section>
  );
}
