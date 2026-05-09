"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import React from "react";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";
import {
  WHO_WE_ARE_NAV_ITEMS,
  isWhoWeAreNavActive,
} from "@/data/whoWeAreNav";

export default function Nav() {
  const pathname = usePathname();
  const { isAuthenticated, isValidated } = useAuth();
  const { t } = useLocale();
  const whoWeAreActive = isWhoWeAreNavActive(pathname);

  return (
    <>
      <li>
        <Link
          className={pathname === "/" ? "menuActive" : ""}
          href="/"
        >
          {t("nav.home")}
        </Link>
      </li>
      {isAuthenticated && isValidated && (
        <>
          <li className="current-dropdown">
            <span className={whoWeAreActive ? "menuActive" : ""}>
              {t("nav.who_we_are")}{" "}
              <i className="fa-solid fa-angle-down" />
            </span>
            <ul className="dropdown">
              {WHO_WE_ARE_NAV_ITEMS.map((item) => (
                <li key={item.href}>
                  <Link
                    className={pathname === item.href ? "menuActive" : ""}
                    href={item.href}
                  >
                    {t(item.labelKey)}
                  </Link>
                </li>
              ))}
            </ul>
          </li>
          <li>
            <Link
              className={pathname === "/inventory" ? "menuActive" : ""}
              href="/inventory"
            >
              {t("nav.inventory")}
            </Link>
          </li>
        </>
      )}
      <li>
        <Link
          className={pathname === "/contact" ? "menuActive" : ""}
          href="/contact"
        >
          {t("nav.contact")}
        </Link>
      </li>
    </>
  );
}
