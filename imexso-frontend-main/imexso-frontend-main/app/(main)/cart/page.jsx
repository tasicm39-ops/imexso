"use client";

import React, { useState, useEffect, useCallback } from "react";
import Link from "next/link";
import CarPhoto from "@/components/common/CarPhoto";
import { getCarImageUrl } from "@/lib/carDisplay";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import { apiDelete, apiGet, apiPost } from "@/lib/api";
import { useLocale } from "@/context/LocaleContext";
import { useCart } from "@/context/CartContext";
import { useAuth } from "@/context/AuthContext";

function formatPrice(price) {
  if (!price && price !== 0) return "N/A";
  return "€" + Math.round(price).toLocaleString();
}

function formatMileage(km) {
  if (!km && km !== 0) return "N/A";
  return Number(km).toLocaleString() + " km";
}

function getCarTitle(car) {
  return [car.make, car.model].filter(Boolean).join(" ") || "Unknown Vehicle";
}

export default function CartPage() {
  const { t } = useLocale();
  const { refreshCart } = useCart();
  const { user } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [pagination, setPagination] = useState(null);
  const [removingId, setRemovingId] = useState(null);
  const [ordering, setOrdering] = useState(false);
  const [orderResult, setOrderResult] = useState(null);
  const [orderError, setOrderError] = useState(null);

  const hasClientId = !!(user?.legacy_client_id);

  async function handleConfirmOrder() {
    if (!hasClientId || items.length === 0) return;
    setOrdering(true);
    setOrderError(null);
    setOrderResult(null);
    try {
      const carIds = items.map((row) => row.car?.id).filter(Boolean);
      const res = await apiPost("/api/orders", { car_ids: carIds });
      const json = await res.json();
      if (res.ok) {
        setOrderResult(json);
        setItems([]);
        refreshCart();
      } else {
        setOrderError(json.message || t("general.error"));
      }
    } catch {
      setOrderError(t("general.error"));
    } finally {
      setOrdering(false);
    }
  }

  const fetchCart = useCallback(async () => {
    setLoading(true);
    try {
      const res = await apiGet(`/api/cart?per_page=20&page=${page}`);
      if (res.ok) {
        const json = await res.json();
        setItems(json.data || []);
        setPagination(json.meta || null);
      } else {
        setItems([]);
        setPagination(null);
      }
    } catch {
      setItems([]);
      setPagination(null);
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

  async function handleRemove(carId) {
    setRemovingId(carId);
    try {
      const res = await apiDelete(`/api/cart/${carId}`);
      if (res.ok) {
        setItems((prev) => prev.filter((row) => row.car_id !== carId));
        refreshCart();
      }
    } catch {
      // ignore
    } finally {
      setRemovingId(null);
    }
  }

  const total = pagination?.total ?? items.length;
  const lastPage = pagination?.last_page || 1;

  return (
    <>
      <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
      <section className="inventory-section layout-radius">
        <div className="boxcar-container">
          <div className="boxcar-title-three">
            <ul className="breadcrumb">
              <li>
                <Link href="/">{t("nav.home")}</Link>
              </li>
              <li>
                <span>{t("nav.cart")}</span>
              </li>
            </ul>
            <h2>{t("cart_page.title")}</h2>
            <p className="mt-2" style={{ color: "#666" }}>
              {total > 0 ? t("cart_page.count", { count: total }) : ""}
            </p>
          </div>

          {loading ? (
            <div
              className="d-flex justify-content-center align-items-center"
              style={{ minHeight: "300px" }}
            >
              <div className="spinner-border" role="status">
                <span className="visually-hidden">Loading...</span>
              </div>
            </div>
          ) : items.length === 0 ? (
            <div
              className="d-flex flex-column justify-content-center align-items-center"
              style={{ minHeight: "300px" }}
            >
              <div style={{ fontSize: 48, marginBottom: 16, opacity: 0.3 }}>
                <svg xmlns="http://www.w3.org/2000/svg" width={64} height={64} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.5}>
                  <circle cx="9" cy="21" r="1" />
                  <circle cx="20" cy="21" r="1" />
                  <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>
              </div>
              <h4>{t("cart_page.empty_title")}</h4>
              <p className="mt-2 text-center px-3" style={{ color: "#888", maxWidth: 480 }}>
                {t("cart_page.empty_description")}
              </p>
              <Link href="/inventory" className="theme-btn btn-style-one mt-4">
                <span className="btn-title">{t("car.view_all_inventory")}</span>
              </Link>
            </div>
          ) : (
            <>
              <div className="row">
                {items.map((row) => {
                  const car = row.car;
                  if (!car) return null;
                  const imageUrl = getCarImageUrl(car);
                  const title = getCarTitle(car);

                  return (
                    <div key={row.id} className="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
                      <div className="favourite-card">
                        <div className="favourite-card__image">
                          <Link href={`/car/${car.id_produit}`}>
                            <CarPhoto
                              src={imageUrl}
                              alt={title}
                              width={400}
                              height={240}
                              variant="card"
                            />
                          </Link>
                        </div>
                        <div className="favourite-card__body">
                          <h6 className="favourite-card__title">
                            <Link href={`/car/${car.id_produit}`}>{title}</Link>
                          </h6>
                          {car.trim_level && (
                            <div className="favourite-card__subtitle">{car.trim_level}</div>
                          )}
                          <div className="favourite-card__specs">
                            {car.manufacturing_year && (
                              <span>{car.manufacturing_year}</span>
                            )}
                            {car.mileage != null && (
                              <span>{formatMileage(car.mileage)}</span>
                            )}
                            {car.fuel_type && (
                              <span>{car.fuel_type}</span>
                            )}
                          </div>
                          <div className="favourite-card__footer">
                            <div className="favourite-card__price">
                              {formatPrice(car.professional_price)}
                              {car.vat_type && (
                                <small className="favourite-card__vat">{car.vat_type}</small>
                              )}
                            </div>
                            <Link href={`/car/${car.id_produit}`} className="favourite-card__link">
                              {t("general.view_details")}
                            </Link>
                          </div>
                          <button
                            type="button"
                            className="btn btn-outline-secondary btn-sm w-100 mt-3"
                            disabled={removingId === car.id}
                            onClick={() => handleRemove(car.id)}
                          >
                            {removingId === car.id ? t("general.loading") : t("cart_page.remove")}
                          </button>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>

              {orderResult && (
                <div className="alert alert-success mt-4" role="alert">
                  <strong>{orderResult.message}</strong>
                  {orderResult.references?.length > 0 && (
                    <div className="mt-2">
                      {t("cart_page.ordered_refs")}: {orderResult.references.join(", ")}
                    </div>
                  )}
                </div>
              )}

              {orderError && (
                <div className="alert alert-danger mt-4" role="alert">
                  {orderError}
                </div>
              )}

              {items.length > 0 && !orderResult && (
                <div className="mt-4 mb-4 p-4" style={{ border: "1px solid #e0e0e0", borderRadius: "12px", background: "#fafafa" }}>
                  {hasClientId ? (
                    <div className="d-flex flex-column align-items-center gap-3">
                      <p className="text-center mb-0" style={{ fontSize: "15px" }}>
                        {t("cart_page.client_id_label")}: <strong>{user.legacy_client_id}</strong>
                      </p>
                      <button
                        type="button"
                        className="theme-btn btn-style-one"
                        disabled={ordering}
                        onClick={handleConfirmOrder}
                        style={{ minWidth: "220px" }}
                      >
                        <span className="btn-title">
                          {ordering ? t("general.loading") : t("cart_page.confirm_order")}
                        </span>
                      </button>
                    </div>
                  ) : (
                    <div className="d-flex flex-column align-items-center gap-3 text-center">
                      <svg xmlns="http://www.w3.org/2000/svg" width={32} height={32} viewBox="0 0 24 24" fill="none" stroke="#e67e22" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                      </svg>
                      <p className="mb-0" style={{ fontSize: "15px", maxWidth: 500 }}>
                        {t("cart_page.no_client_id_message")}
                      </p>
                      <Link href="/contact" className="theme-btn btn-style-one mt-2">
                        <span className="btn-title">{t("car.contact_us")}</span>
                      </Link>
                    </div>
                  )}
                </div>
              )}

              {lastPage > 1 && (
                <div className="d-flex justify-content-center gap-2 mt-4 mb-4">
                  <button
                    type="button"
                    className="btn btn-outline-secondary btn-sm"
                    disabled={page <= 1}
                    onClick={() => {
                      setPage((p) => p - 1);
                      window.scrollTo({ top: 0, behavior: "smooth" });
                    }}
                  >
                    {t("general.previous")}
                  </button>
                  <span className="d-flex align-items-center px-3" style={{ fontSize: 14 }}>
                    {page} / {lastPage}
                  </span>
                  <button
                    type="button"
                    className="btn btn-outline-secondary btn-sm"
                    disabled={page >= lastPage}
                    onClick={() => {
                      setPage((p) => p + 1);
                      window.scrollTo({ top: 0, behavior: "smooth" });
                    }}
                  >
                    {t("general.next")}
                  </button>
                </div>
              )}
            </>
          )}
        </div>
      </section>
      <Footer1 parentClass="boxcar-footer footer-style-one v1 cus-st-1" />
    </>
  );
}
