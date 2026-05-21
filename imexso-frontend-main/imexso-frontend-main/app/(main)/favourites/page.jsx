"use client";

import React, { useState, useEffect, useCallback } from "react";
import Link from "next/link";
import CarPhoto from "@/components/common/CarPhoto";
import { getCarImageUrl } from "@/lib/carDisplay";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import FavouriteButton from "@/components/common/FavouriteButton";
import { apiGet } from "@/lib/api";
import { useLocale } from "@/context/LocaleContext";

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

export default function FavouritesPage() {
  const { t } = useLocale();
  const [favourites, setFavourites] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [pagination, setPagination] = useState(null);

  const fetchFavourites = useCallback(async () => {
    setLoading(true);
    try {
      const res = await apiGet(`/api/favourites?per_page=20&page=${page}`);
      if (res.ok) {
        const json = await res.json();
        setFavourites(json.data || []);
        setPagination(json.meta || null);
      }
    } catch {
      setFavourites([]);
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    fetchFavourites();
  }, [fetchFavourites]);

  function handleRemoved(carId) {
    setFavourites((prev) => prev.filter((f) => f.car_id !== carId));
  }

  const total = pagination?.total || favourites.length;
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
                <span>{t("favourites.my_favourites")}</span>
              </li>
            </ul>
            <h2>{t("favourites.my_favourites")}</h2>
            <p className="mt-2" style={{ color: "#666" }}>
              {total > 0
                ? t("favourites.count", { count: total })
                : ""}
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
          ) : favourites.length === 0 ? (
            <div
              className="d-flex flex-column justify-content-center align-items-center"
              style={{ minHeight: "300px" }}
            >
              <div style={{ fontSize: 48, marginBottom: 16, opacity: 0.3 }}>
                <svg xmlns="http://www.w3.org/2000/svg" width={64} height={64} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.5}>
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                </svg>
              </div>
              <h4>{t("favourites.empty_title")}</h4>
              <p className="mt-2" style={{ color: "#888" }}>
                {t("favourites.empty_description")}
              </p>
              <Link
                href="/inventory"
                className="theme-btn btn-style-one mt-4"
              >
                <span className="btn-title">{t("car.view_all_inventory")}</span>
              </Link>
            </div>
          ) : (
            <>
              <div className="row">
                {favourites.map((fav) => {
                  const car = fav.car;
                  if (!car) return null;
                  const imageUrl = getCarImageUrl(car);
                  const title = getCarTitle(car);

                  return (
                    <div key={fav.id} className="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
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
                          <div className="favourite-card__fav-btn">
                            <FavouriteButton
                              carId={car.id}
                              initialFavourited={true}
                              size="small"
                              onToggle={(isFav) => {
                                if (!isFav) handleRemoved(car.id);
                              }}
                            />
                          </div>
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
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>

              {lastPage > 1 && (
                <div className="d-flex justify-content-center gap-2 mt-4 mb-4">
                  <button
                    className="btn btn-outline-secondary btn-sm"
                    disabled={page <= 1}
                    onClick={() => { setPage((p) => p - 1); window.scrollTo({ top: 0, behavior: "smooth" }); }}
                  >
                    {t("general.previous")}
                  </button>
                  <span className="d-flex align-items-center px-3" style={{ fontSize: 14 }}>
                    {page} / {lastPage}
                  </span>
                  <button
                    className="btn btn-outline-secondary btn-sm"
                    disabled={page >= lastPage}
                    onClick={() => { setPage((p) => p + 1); window.scrollTo({ top: 0, behavior: "smooth" }); }}
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
