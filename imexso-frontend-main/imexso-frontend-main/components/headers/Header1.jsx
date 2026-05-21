"use client";
import React, { useState, useRef, useCallback, useEffect } from "react";
import Nav from "./Nav";
import Link from "next/link";
import Image from "next/image";
import { useAuth } from "@/context/AuthContext";
import { useCart } from "@/context/CartContext";
import { useLocale } from "@/context/LocaleContext";
import { useRouter } from "next/navigation";
import { apiGet } from "@/lib/api";
import { getCarImageUrl } from "@/lib/carDisplay";
import LanguageSelector from "@/components/common/LanguageSelector";

export default function Header1({
  headerClass = "header-style-v1 header-default",
  white = false,
}) {
  const { isAuthenticated, isValidated, user, logout } = useAuth();
  const { cartCount } = useCart();
  const { t } = useLocale();
  const router = useRouter();
  const [searchQuery, setSearchQuery] = useState("");
  const [searchResults, setSearchResults] = useState([]);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const searchTimeout = useRef(null);
  const userMenuRef = useRef(null);

  const performSearch = useCallback(
    async (query) => {
      if (!query || query.length < 2 || !isAuthenticated || !isValidated) {
        setSearchResults([]);
        return;
      }
      try {
        const res = await apiGet(
          `/api/cars?search=${encodeURIComponent(query)}&per_page=5`,
        );
        if (res.ok) {
          const json = await res.json();
          setSearchResults(json.data || []);
        }
      } catch {
        setSearchResults([]);
      }
    },
    [isAuthenticated, isValidated],
  );

  useEffect(() => {
    return () => {
      if (searchTimeout.current) clearTimeout(searchTimeout.current);
    };
  }, []);

  useEffect(() => {
    function handleClickOutside(event) {
      if (userMenuRef.current && !userMenuRef.current.contains(event.target)) {
        setUserMenuOpen(false);
      }
    }
    document.addEventListener("click", handleClickOutside);
    return () => document.removeEventListener("click", handleClickOutside);
  }, []);

  const handleFocus = () => {
    document.getElementById("box-content-search")?.classList.add("active");
    document
      .getElementById("box-content-search")
      ?.closest(".layout-search")
      ?.classList.add("active");
  };

  const handleBlur = () => {
    document.getElementById("box-content-search")?.classList.remove("active");
    document
      .getElementById("box-content-search")
      ?.closest(".layout-search")
      ?.classList.remove("active");
  };

  const handleLogout = async () => {
    setUserMenuOpen(false);
    await logout();
    router.push("/login");
  };

  return (
    <header className={`boxcar-header ${headerClass}`}>
      <div className="header-inner">
        <div className="inner-container">
          <div className="c-box">
            <div className="logo-inner">
              <div className="logo">
                <Link href="/">
                  {white ? (
                    <Image
                      alt="Imexso"
                      title="Imexso"
                      src="/images/logo.png"
                      width={108}
                      height={26}
                    />
                  ) : (
                    <Image
                      alt="Imexso"
                      title="Imexso"
                      src="/images/logo2.png"
                      width={108}
                      height={26}
                    />
                  )}
                </Link>
              </div>

              <div className="layout-search">
                <form
                  className="search-box"
                  onSubmit={(e) => {
                    e.preventDefault();
                    if (searchQuery.trim()) {
                      router.push(`/inventory?search=${encodeURIComponent(searchQuery.trim())}`);
                    }
                  }}
                >
                  <button type="submit" style={{ background: "none", border: "none", padding: 0, cursor: "pointer" }}>
                    <svg
                      className="icon"
                      width={16}
                      height={16}
                      viewBox="0 0 16 16"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M7.29301 1.2876C3.9872 1.2876 1.29431 3.98048 1.29431 7.28631C1.29431 10.5921 3.9872 13.2902 7.29301 13.2902C8.70502 13.2902 10.0036 12.7954 11.03 11.9738L13.5287 14.4712C13.6548 14.5921 13.8232 14.6588 13.9979 14.657C14.1725 14.6552 14.3395 14.5851 14.4631 14.4617C14.5867 14.3382 14.6571 14.1713 14.6591 13.9967C14.6611 13.822 14.5947 13.6535 14.474 13.5272L11.9753 11.0285C12.7976 10.0006 13.293 8.69995 13.293 7.28631C13.293 3.98048 10.5988 1.2876 7.29301 1.2876ZM7.29301 2.62095C9.87824 2.62095 11.9584 4.70108 11.9584 7.28631C11.9584 9.87153 9.87824 11.9569 7.29301 11.9569C4.70778 11.9569 2.62764 9.87153 2.62764 7.28631C2.62764 4.70108 4.70778 2.62095 7.29301 2.62095Z"
                        fill="white"
                      />
                    </svg>
                  </button>
                  <input
                    type="search"
                    placeholder={t("general.search")}
                    className="show-search"
                    name="name"
                    tabIndex={2}
                    defaultValue=""
                    aria-required="true"
                    required
                    onFocus={handleFocus}
                    onBlur={handleBlur}
                    onChange={(e) => {
                      const val = e.target.value;
                      setSearchQuery(val);
                      if (searchTimeout.current)
                        clearTimeout(searchTimeout.current);
                      searchTimeout.current = setTimeout(
                        () => performSearch(val),
                        400,
                      );
                    }}
                  />
                </form>
                <div className="box-content-search" id="box-content-search">
                  <ul className="box-car-search">
                    {searchResults.map((car) => {
                      const imageUrl =
                        getCarImageUrl(car);
                      const title =
                        [car.make, car.model].filter(Boolean).join(" ") ||
                        "Vehicle";
                      const price = car.professional_price
                        ? "€" +
                          Math.round(car.professional_price).toLocaleString()
                        : "";
                      return (
                        <li key={car.id}>
                          <Link
                            href={`/car/${car.id_produit}`}
                            className="car-search-item"
                          >
                            <div className="box-img">
                              <Image
                                alt={title}
                                src={imageUrl}
                                width={70}
                                height={70}
                                style={{ objectFit: "cover" }}
                                unoptimized={imageUrl.startsWith("http")}
                              />
                            </div>
                            <div className="info">
                              <p className="name">{title}</p>
                              <span className="price">{price}</span>
                            </div>
                          </Link>
                        </li>
                      );
                    })}
                  </ul>
                  <Link href={searchQuery.trim() ? `/inventory?search=${encodeURIComponent(searchQuery.trim())}` : "/inventory"} className="btn-view-search">
                    {t("general.view_all_results")}
                    <svg
                      width={14}
                      height={14}
                      viewBox="0 0 14 14"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <g clipPath="url(#clip0_3114_6864)">
                        <path
                          d="M13.6109 0H5.05533C4.84037 0 4.66643 0.173943 4.66643 0.388901C4.66643 0.603859 4.84037 0.777802 5.05533 0.777802H12.6721L0.113697 13.3362C-0.0382246 13.4881 -0.0382246 13.7342 0.113697 13.8861C0.18964 13.962 0.289171 14 0.388666 14C0.488161 14 0.587656 13.962 0.663635 13.8861L13.222 1.3277V8.94447C13.222 9.15943 13.3959 9.33337 13.6109 9.33337C13.8259 9.33337 13.9998 9.15943 13.9998 8.94447V0.388901C13.9998 0.173943 13.8258 0 13.6109 0Z"
                          fill="#405FF2"
                        />
                      </g>
                      <defs>
                        <clipPath id="clip0_3114_6864">
                          <rect width={14} height={14} fill="white" />
                        </clipPath>
                      </defs>
                    </svg>
                  </Link>
                </div>
              </div>
            </div>
            <div className="nav-out-bar">
              <nav className="nav main-menu">
                <ul className="navigation" id="navbar">
                  <Nav />
                </ul>
              </nav>
            </div>
            <div className="right-box">
              <LanguageSelector />
              {isAuthenticated ? (
                <div
                  ref={userMenuRef}
                  style={{ position: "relative" }}
                >
                  <a
                    className="box-account"
                    onClick={(e) => {
                      e.preventDefault();
                      setUserMenuOpen((prev) => !prev);
                    }}
                    style={{ cursor: "pointer" }}
                  >
                    <span className="icon">
                      <svg
                        width={16}
                        height={16}
                        viewBox="0 0 16 16"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <g clipPath="url(#clip0_147_6490)">
                          <path
                            d="M7.99998 9.01221C3.19258 9.01221 0.544983 11.2865 0.544983 15.4161C0.544983 15.7386 0.806389 16.0001 1.12892 16.0001H14.871C15.1935 16.0001 15.455 15.7386 15.455 15.4161C15.455 11.2867 12.8074 9.01221 7.99998 9.01221ZM1.73411 14.8322C1.9638 11.7445 4.06889 10.1801 7.99998 10.1801C11.9311 10.1801 14.0362 11.7445 14.2661 14.8322H1.73411Z"
                            fill="white"
                          />
                          <path
                            d="M7.99999 0C5.79171 0 4.12653 1.69869 4.12653 3.95116C4.12653 6.26959 5.86415 8.15553 7.99999 8.15553C10.1358 8.15553 11.8735 6.26959 11.8735 3.95134C11.8735 1.69869 10.2083 0 7.99999 0ZM7.99999 6.98784C6.50803 6.98784 5.2944 5.62569 5.2944 3.95134C5.2944 2.3385 6.43231 1.16788 7.99999 1.16788C9.54259 1.16788 10.7056 2.36438 10.7056 3.95134C10.7056 5.62569 9.49196 6.98784 7.99999 6.98784Z"
                            fill="white"
                          />
                        </g>
                        <defs>
                          <clipPath id="clip0_147_6490">
                            <rect width={16} height={16} fill="white" />
                          </clipPath>
                        </defs>
                      </svg>
                    </span>
                    {user?.name || "Account"}
                    <i
                      className="fa-solid fa-angle-down"
                      style={{ marginLeft: "5px", fontSize: "10px" }}
                    />
                  </a>
                  {userMenuOpen && (
                    <div
                      style={{
                        position: "absolute",
                        top: "100%",
                        right: 0,
                        marginTop: "8px",
                        background: "#fff",
                        borderRadius: "8px",
                        boxShadow: "0 4px 20px rgba(0,0,0,0.12)",
                        minWidth: "200px",
                        zIndex: 1000,
                        overflow: "hidden",
                      }}
                    >
                      <div
                        style={{
                          padding: "14px 18px",
                          borderBottom: "1px solid #f0f0f0",
                        }}
                      >
                        <div
                          style={{
                            fontWeight: 600,
                            fontSize: "14px",
                            color: "#050B20",
                          }}
                        >
                          {user?.name}
                        </div>
                        <div
                          style={{
                            fontSize: "12px",
                            color: "#888",
                            marginTop: "2px",
                          }}
                        >
                          {user?.email}
                        </div>
                      </div>
                      <ul style={{ listStyle: "none", padding: 0, margin: 0 }}>
                        <li>
                          <Link
                            href="/profile"
                            onClick={() => setUserMenuOpen(false)}
                            style={{
                              display: "block",
                              padding: "12px 18px",
                              fontSize: "14px",
                              color: "#333",
                              textDecoration: "none",
                              transition: "background 0.2s",
                            }}
                            onMouseEnter={(e) =>
                              (e.currentTarget.style.background = "#f7f7f7")
                            }
                            onMouseLeave={(e) =>
                              (e.currentTarget.style.background = "transparent")
                            }
                          >
                            <i
                              className="fa-regular fa-user"
                              style={{ marginRight: "10px", width: "16px" }}
                            />
                            {t("general.profile_settings")}
                          </Link>
                        </li>
                        <li>
                          <Link
                            href="/sale-history"
                            onClick={() => setUserMenuOpen(false)}
                            style={{
                              display: "block",
                              padding: "12px 18px",
                              fontSize: "14px",
                              color: "#333",
                              textDecoration: "none",
                              transition: "background 0.2s",
                            }}
                            onMouseEnter={(e) =>
                              (e.currentTarget.style.background = "#f7f7f7")
                            }
                            onMouseLeave={(e) =>
                              (e.currentTarget.style.background = "transparent")
                            }
                          >
                            <i
                              className="fa-regular fa-clock-rotate-left"
                              style={{ marginRight: "10px", width: "16px" }}
                            />
                            {t("sale_history.title")}
                          </Link>
                        </li>
                        <li>
                          <Link
                            href="/favourites"
                            onClick={() => setUserMenuOpen(false)}
                            style={{
                              display: "block",
                              padding: "12px 18px",
                              fontSize: "14px",
                              color: "#333",
                              textDecoration: "none",
                              transition: "background 0.2s",
                            }}
                            onMouseEnter={(e) =>
                              (e.currentTarget.style.background = "#f7f7f7")
                            }
                            onMouseLeave={(e) =>
                              (e.currentTarget.style.background = "transparent")
                            }
                          >
                            <i
                              className="fa-regular fa-heart"
                              style={{ marginRight: "10px", width: "16px" }}
                            />
                            {t("favourites.my_favourites")}
                          </Link>
                        </li>
                        <li>
                          <a
                            onClick={handleLogout}
                            style={{
                              display: "block",
                              padding: "12px 18px",
                              fontSize: "14px",
                              color: "#e53e3e",
                              textDecoration: "none",
                              cursor: "pointer",
                              borderTop: "1px solid #f0f0f0",
                              transition: "background 0.2s",
                            }}
                            onMouseEnter={(e) =>
                              (e.currentTarget.style.background = "#fff5f5")
                            }
                            onMouseLeave={(e) =>
                              (e.currentTarget.style.background = "transparent")
                            }
                          >
                            <i
                              className="fa-solid fa-right-from-bracket"
                              style={{ marginRight: "10px", width: "16px" }}
                            />
                            Logout
                          </a>
                        </li>
                      </ul>
                    </div>
                  )}
                </div>
              ) : (
                <Link href="/login" className="box-account">
                  <span className="icon">
                    <svg
                      width={16}
                      height={16}
                      viewBox="0 0 16 16"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <g clipPath="url(#clip0_147_6490b)">
                        <path
                          d="M7.99998 9.01221C3.19258 9.01221 0.544983 11.2865 0.544983 15.4161C0.544983 15.7386 0.806389 16.0001 1.12892 16.0001H14.871C15.1935 16.0001 15.455 15.7386 15.455 15.4161C15.455 11.2867 12.8074 9.01221 7.99998 9.01221ZM1.73411 14.8322C1.9638 11.7445 4.06889 10.1801 7.99998 10.1801C11.9311 10.1801 14.0362 11.7445 14.2661 14.8322H1.73411Z"
                          fill="white"
                        />
                        <path
                          d="M7.99999 0C5.79171 0 4.12653 1.69869 4.12653 3.95116C4.12653 6.26959 5.86415 8.15553 7.99999 8.15553C10.1358 8.15553 11.8735 6.26959 11.8735 3.95134C11.8735 1.69869 10.2083 0 7.99999 0ZM7.99999 6.98784C6.50803 6.98784 5.2944 5.62569 5.2944 3.95134C5.2944 2.3385 6.43231 1.16788 7.99999 1.16788C9.54259 1.16788 10.7056 2.36438 10.7056 3.95134C10.7056 5.62569 9.49196 6.98784 7.99999 6.98784Z"
                          fill="white"
                        />
                      </g>
                      <defs>
                        <clipPath id="clip0_147_6490b">
                          <rect width={16} height={16} fill="white" />
                        </clipPath>
                      </defs>
                    </svg>
                  </span>
                  {t("general.sign_in")}
                </Link>
              )}
              {isAuthenticated && isValidated && (
                <Link href="/cart" className="cart-header-btn" aria-label={t("nav.cart")}>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width={18}
                    height={18}
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth={2}
                    strokeLinecap="round"
                    strokeLinejoin="round"
                  >
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                  </svg>
                  {cartCount > 0 && (
                    <span className="cart-header-btn__badge">{cartCount}</span>
                  )}
                </Link>
              )}
              <div className="mobile-navigation">
                <a href="#nav-mobile" title="">
                  <svg
                    width={22}
                    height={11}
                    viewBox="0 0 22 11"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <rect
                      width={22}
                      height={2}
                      fill={white ? "#050B20" : "white"}
                    />
                    <rect
                      y={9}
                      width={22}
                      height={2}
                      fill={white ? "#050B20" : "white"}
                    />
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="nav-mobile" />
    </header>
  );
}
