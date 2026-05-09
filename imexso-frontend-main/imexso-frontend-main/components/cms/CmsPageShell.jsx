"use client";

import Link from "next/link";
import { useLocale } from "@/context/LocaleContext";

/**
 * Boxcar inner-page shell: optional hero banner, then white card with breadcrumb + title.
 * With `banner`, uses `layout-radius` overlap like the old CMS hero; without it, matches
 * flat inner pages (no hero image).
 */
export default function CmsPageShell({ banner = null, titleKey, children }) {
  const { t } = useLocale();
  const pageTitle = t(titleKey);
  const showBanner = Boolean(banner);

  return (
    <>
      {showBanner ? banner : null}
      <section
        className={`inventory-section cms-page-shell${
          showBanner ? " layout-radius" : " cms-page-shell--no-hero"
        }`}
      >
        <div className="boxcar-container">
          <div className="boxcar-title-three">
            <ul className="breadcrumb">
              <li>
                <Link href="/">{t("nav.home")}</Link>
              </li>
              <li>
                <span>{pageTitle}</span>
              </li>
            </ul>
            <h2>{pageTitle}</h2>
          </div>
        </div>
        <div className="cms-page cms-page--boxed">{children}</div>
      </section>
    </>
  );
}
