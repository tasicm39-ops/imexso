"use client";

import Link from "next/link";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";
import HomeStatCardIcon from "@/components/homes/home-5/HomeStatCardIcon";

const CARD_KEYS = ["team", "stock", "history", "installations"];

const CARD_HREFS = {
  team: "/myimexso/equipe",
  stock: "/inventory",
  history: "/historique",
  installations: "/installations",
};

export default function HomeStatsCards() {
  const { isAuthenticated, isValidated } = useAuth();
  const { t } = useLocale();
  const canBrowse = isAuthenticated && isValidated;

  return (
    <section className="home-stats-cards">
      <div className="boxcar-container">
        <div className="home-stats-cards__grid">
          {CARD_KEYS.map((key, index) => {
            const href = canBrowse ? CARD_HREFS[key] : "/login";

            return (
              <Link
                key={key}
                href={href}
                className="home-stat-card wow fadeInUp"
                data-wow-delay={`${index * 80}ms`}
              >
                <div className="home-stat-card__head">
                  <span className="home-stat-card__value">
                    {t(`home.stats_${key}_value`)}
                  </span>
                </div>
                <div className="home-stat-card__body">
                  <p className="home-stat-card__desc">
                    {t(`home.stats_${key}_desc`)}
                  </p>
                  <div className="home-stat-card__icon" aria-hidden="true">
                    <HomeStatCardIcon type={key} />
                  </div>
                </div>
                <div className="home-stat-card__foot">
                  {t(`home.stats_${key}_btn`)}
                </div>
              </Link>
            );
          })}
        </div>
      </div>
    </section>
  );
}
