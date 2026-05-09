"use client";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import React, { useEffect, useState } from "react";
import { useAuth } from "@/context/AuthContext";
import { useCart } from "@/context/CartContext";
import { useLocale } from "@/context/LocaleContext";
import LanguageSelector from "@/components/common/LanguageSelector";
import {
  WHO_WE_ARE_NAV_ITEMS,
  isWhoWeAreNavActive,
} from "@/data/whoWeAreNav";

export default function MobileMenu() {
  const pathname = usePathname();
  const router = useRouter();
  const { isAuthenticated, isValidated, user, logout } = useAuth();
  const { cartCount } = useCart();
  const { t } = useLocale();
  const [showMenu, setShowMenu] = useState(false);
  const [whoWeAreOpen, setWhoWeAreOpen] = useState(false);

  useEffect(() => {
    setWhoWeAreOpen(false);
    setShowMenu(true);
    const mobileNavigation = document.querySelectorAll('[href="#nav-mobile"]');
    const mobileMenu = document.getElementById("nav-mobile");
    const mobileMenuOverlay = document.getElementById("mobileOverlay");
    mobileNavigation.forEach((elm) => elm?.classList.remove("active"));
    mobileMenu?.classList.remove("mm-menu_opened");
    mobileMenuOverlay?.classList.remove("active");

    const toggleActiveClass = (e) => {
      e?.preventDefault();
      mobileNavigation.forEach((elm) => elm?.classList.toggle("active"));
      mobileMenu?.classList.toggle("mm-menu_opened");
      mobileMenuOverlay?.classList.toggle("active");
    };

    mobileNavigation.forEach((elm) =>
      elm?.addEventListener("click", toggleActiveClass),
    );

    return () => {
      mobileNavigation.forEach((elm) =>
        elm?.removeEventListener("click", toggleActiveClass),
      );
    };
  }, [pathname]);

  const closeMenu = () => {
    setWhoWeAreOpen(false);
    const mobileNavigation = document.querySelector(".mobile-navigation");
    const mobileMenu = document.getElementById("nav-mobile");
    const mobileMenuOverlay = document.getElementById("mobileOverlay");
    mobileNavigation?.classList.remove("active");
    mobileMenu?.classList.remove("mm-menu_opened");
    mobileMenuOverlay?.classList.remove("active");
  };

  const handleLogout = async () => {
    closeMenu();
    await logout();
    router.push("/login");
  };

  return (
    <>
      <div
        id="nav-mobile"
        className="mm-menu mm-menu_offcanvas mm-menu_position-left mm-menu_ mm-menu_theme-black"
        style={{
          zIndex: 101,
          display: "block",
          transition: "0.5s",
          opacity: 0.5,
          visibility: "hidden",
          left: "-100%",
        }}
      >
        {showMenu && (
          <div className="mm-panels">
            <div
              id="navbar"
              className={`mm-panel ${
                whoWeAreOpen
                  ? "mm-panel_opened-parent mm-hidden"
                  : "mm-panel_opened"
              }`}
            >
              <div className="mm-navbar mm-navbar_sticky">
                <a className="mm-navbar__title">
                  <span>{t("nav.menu")}</span>
                </a>
              </div>
              <ul className="navigation mm-listview">
                <li
                  className={`mm-listitem ${pathname === "/" ? "current" : ""}`}
                >
                  <Link
                    href="/"
                    className="mm-listitem__text"
                    onClick={closeMenu}
                  >
                    {t("nav.home")}
                  </Link>
                </li>
                <li
                  className={`current-dropdown mm-listitem ${
                    isWhoWeAreNavActive(pathname) ? "current" : ""
                  }`}
                >
                  <a
                    className="mm-btn mm-btn_next mm-listitem__btn mm-listitem__text"
                    href="#navbar"
                    onClick={(e) => {
                      e.preventDefault();
                      setWhoWeAreOpen((v) => !v);
                    }}
                  >
                    {t("nav.who_we_are")}{" "}
                    <i className="fa-solid fa-angle-down" />
                    <span className="mm-sronly">Open submenu</span>
                  </a>
                </li>
                <li
                  className={`mm-listitem ${pathname === "/inventory" ? "current" : ""}`}
                >
                  <Link
                    href="/inventory"
                    className="mm-listitem__text"
                    onClick={closeMenu}
                  >
                    {t("nav.inventory")}
                  </Link>
                </li>
                {isAuthenticated && isValidated && (
                  <li
                    className={`mm-listitem ${pathname === "/cart" ? "current" : ""}`}
                  >
                    <Link
                      href="/cart"
                      className="mm-listitem__text"
                      onClick={closeMenu}
                    >
                      {t("nav.cart")}{cartCount > 0 ? ` (${cartCount})` : ""}
                    </Link>
                  </li>
                )}
                <li
                  className={`mm-listitem ${pathname === "/contact" ? "current" : ""}`}
                >
                  <Link
                    href="/contact"
                    className="mm-listitem__text"
                    onClick={closeMenu}
                  >
                    {t("nav.contact")}
                  </Link>
                </li>
                {isAuthenticated && (
                  <>
                    <li
                      style={{
                        borderTop: "1px solid rgba(255,255,255,0.1)",
                        marginTop: "10px",
                        paddingTop: "10px",
                      }}
                      className="mm-listitem"
                    >
                      <Link
                        href="/profile"
                        className="mm-listitem__text"
                        onClick={closeMenu}
                      >
                        <i
                          className="fa-regular fa-user"
                          style={{ marginRight: "8px" }}
                        />
                        {t("general.profile_settings")}
                      </Link>
                    </li>
                    <li className="mm-listitem">
                      <a
                        className="mm-listitem__text"
                        onClick={handleLogout}
                        style={{ cursor: "pointer", color: "#e53e3e" }}
                      >
                        <i
                          className="fa-solid fa-right-from-bracket"
                          style={{ marginRight: "8px" }}
                        />
                        {t("nav.logout")}
                      </a>
                    </li>
                  </>
                )}
                {!isAuthenticated && (
                  <li className="mm-listitem">
                    <Link
                      href="/login"
                      className="mm-listitem__text"
                      onClick={closeMenu}
                    >
                      {t("general.sign_in")}
                    </Link>
                  </li>
                )}
                <li className="mm-listitem" style={{ borderTop: "1px solid rgba(255,255,255,0.1)", marginTop: "10px", paddingTop: "10px" }}>
                  <LanguageSelector variant="mobile" />
                </li>
              </ul>
            </div>
            <div
              id="mm-who-we-are"
              className={`mm-panel ${
                whoWeAreOpen ? "mm-panel_opened" : "mm-hidden"
              }`}
            >
              <div className="mm-navbar mm-navbar_sticky">
                <a
                  className="mm-btn mm-btn_prev mm-navbar__btn"
                  href="#navbar"
                  onClick={(e) => {
                    e.preventDefault();
                    setWhoWeAreOpen(false);
                  }}
                >
                  <span className="mm-sronly">Close submenu</span>
                </a>
                <a
                  className="mm-navbar__title"
                  href="#navbar"
                  onClick={(e) => {
                    e.preventDefault();
                    setWhoWeAreOpen(false);
                  }}
                >
                  <span>{t("nav.who_we_are")}</span>
                </a>
              </div>
              <ul className="dropdown mm-listview">
                {WHO_WE_ARE_NAV_ITEMS.map((item) => (
                  <li key={item.href} className="mm-listitem">
                    <Link
                      href={item.href}
                      className={`mm-listitem__text ${
                        pathname === item.href ? "current" : ""
                      }`}
                      onClick={closeMenu}
                    >
                      {t(item.labelKey)}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        )}
      </div>
      <div
        className="overlay-mobile"
        id="mobileOverlay"
        onClick={closeMenu}
      ></div>
    </>
  );
}
