"use client";
import React, { useState } from "react";
import RelatedCars from "./RelatedCars";
import Carlab360Modal from "./Carlab360Modal";
import { buildCarlab360ViewerUrl } from "@/lib/carlab360";
import Image from "next/image";
import Overview from "./sections/Overview";
import Description from "./sections/Description";
import Features from "./sections/Features";
import { Gallery, Item } from "react-photoswipe-gallery";
import Link from "next/link";
import { useLocale } from "@/context/LocaleContext";
import FavouriteButton from "@/components/common/FavouriteButton";
import AddToCartButton from "@/components/common/AddToCartButton";
import SendOfferForm from "./sections/SendOfferForm";
import { DEFAULT_CAR_IMAGE, getCarImageUrl } from "@/lib/carDisplay";

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

export default function Single1({
  carItem,
  viewerCount = 1,
  isFavourited = false,
  cartOtherUsersCount = 0,
  isInCart = false,
  onCartChange,
}) {
  const { t } = useLocale();
  const [carlab360Open, setCarlab360Open] = useState(false);

  const isAvailable = carItem.sync_status === "active";
  const photos = carItem.photos || [];
  const mainPhoto = getCarImageUrl(carItem);
  const galleryPhotos = photos.slice(1, 5);
  const title = getCarTitle(carItem);
  const marketing = carItem.marketing;
  const carlab360Url = carItem.carlab
    ? buildCarlab360ViewerUrl(carItem.id_produit)
    : null;

  return (
    <>
      <section className="inventory-section pb-0 layout-radius">
        <div className="boxcar-container">
          <div className="boxcar-title-three">
            <ul className="breadcrumb">
              <li>
                <Link href={`/`}>{t("nav.home")}</Link>
              </li>
              <li>
                <Link href={`/inventory`}>{t("car.cars_for_sale")}</Link>
              </li>
              <li>
                <span>{title}</span>
              </li>
            </ul>
            <div className="car-detail-hero d-flex flex-wrap gap-4 justify-content-between align-items-start">
              <div className="car-detail-hero-main flex-grow-1 min-w-0">
                <h2 className="mb-0">{title}</h2>
                <ul className="spectes-list mt-3 mb-0">
              {carItem.manufacturing_year && (
                <li>
                  <span>
                    <Image
                      src="/images/resource/spec1-1.svg"
                      width={18}
                      height={18}
                      alt=""
                    />
                    {carItem.manufacturing_year}
                  </span>
                </li>
              )}
              <li>
                <span>
                  <Image
                    src="/images/resource/spec1-2.svg"
                    width={18}
                    height={18}
                    alt=""
                  />
                  {formatMileage(carItem.mileage)}
                </span>
              </li>
              <li>
                <span>
                  <Image
                    src="/images/resource/spec1-3.svg"
                    width={18}
                    height={18}
                    alt=""
                  />
                  {carItem.gearbox || "N/A"}
                </span>
              </li>
              <li>
                <span>
                  <Image
                    src="/images/resource/spec1-4.svg"
                    width={18}
                    height={18}
                    alt=""
                  />
                  {carItem.fuel_type || "N/A"}
                </span>
              </li>
                </ul>
              </div>
              <div className="car-detail-hero-aside d-flex flex-column align-items-end gap-2 flex-shrink-0">
                <div className="d-flex align-items-center gap-2 car-detail-hero-aside__buttons">
                  {isAvailable && (
                    <AddToCartButton
                      carId={carItem.id}
                      initialInCart={isInCart}
                      onChange={onCartChange ?? undefined}
                    />
                  )}
                  <FavouriteButton
                    carId={carItem.id}
                    initialFavourited={isFavourited}
                  />
                </div>
                {carItem.id_produit && (
                  <span className="car-detail-ref">
                    {t("car.ref_label")}: {carItem.id_produit}
                  </span>
                )}
                <div className="car-detail-price-box text-end">
                  {carItem.condition_type && (
                    <div className="btn-box d-flex justify-content-end mb-2">
                      <div className="share-btn">
                        <span>{carItem.condition_type}</span>
                      </div>
                    </div>
                  )}
                  <div>
                    <h3 className="title mb-1">
                      {marketing?.new_price_enabled && marketing?.new_price_amount ? (
                        <>
                          <span className="price-old">
                            {formatPrice(carItem.professional_price)}
                          </span>{" "}
                          {formatPrice(marketing.new_price_amount)}
                        </>
                      ) : (
                        formatPrice(carItem.professional_price)
                      )}
                    </h3>
                    {carItem.vat_type && <span className="d-block small text-muted">{carItem.vat_type}</span>}
                  </div>
                </div>
              </div>
            </div>
            {!isAvailable && (
              <div className="car-unavailable-banner mt-2 mb-2" role="status">
                {t("car.already_reserved")}
              </div>
            )}
            {marketing?.is_active && (marketing.promotion_enabled || marketing.new_price_enabled || marketing.limited_stock_enabled || marketing.badge_text) && (
              <div className="marketing-badges d-flex gap-2 flex-wrap">
                {marketing.promotion_enabled && marketing.promotion_label && (
                  <span className="badge-marketing badge-promotion">
                    {marketing.promotion_label}
                  </span>
                )}
                {marketing.new_price_enabled && (
                  <span className="badge-marketing badge-new-price">
                    {t("car.new_price")}
                  </span>
                )}
                {marketing.limited_stock_enabled && marketing.limited_stock_count && (
                  <span className="badge-marketing badge-limited-stock">
                    {t("car.only_x_remaining").replace("{count}", marketing.limited_stock_count)}
                  </span>
                )}
                {marketing.badge_text && (
                  <span className="badge-marketing badge-custom">
                    {marketing.badge_text}
                  </span>
                )}
              </div>
            )}
            <div className="viewer-count d-flex align-items-center gap-2 mt-2">
              <svg xmlns="http://www.w3.org/2000/svg" width={16} height={16} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              <span className="viewer-count-text">
                {viewerCount} {viewerCount === 1 ? t("car.person_viewing") : t("car.people_viewing")}
              </span>
            </div>
            {isAvailable && cartOtherUsersCount > 0 && (
              <div className="cart-interest-hint d-flex align-items-center gap-2 mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" width={16} height={16} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden>
                  <circle cx="9" cy="21" r="1" />
                  <circle cx="20" cy="21" r="1" />
                  <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>
                <span className="cart-interest-hint-text">
                  {cartOtherUsersCount === 1
                    ? t("car.one_other_user_in_cart")
                    : t("car.n_other_users_in_cart", { count: cartOtherUsersCount })}
                </span>
              </div>
            )}
          </div>

          <Gallery>
            <div className="gallery-sec">
              <div className="row">
                <div className="image-column item1 col-lg-7 col-md-12 col-sm-12">
                  <div className="inner-column">
                    <div className="image-box" style={{ position: "relative" }}>
                      {carlab360Url && (
                        <button
                          type="button"
                          className="theme-btn btn-style-one"
                          onClick={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            setCarlab360Open(true);
                          }}
                          style={{
                            position: "absolute",
                            top: 14,
                            left: 14,
                            zIndex: 3,
                            padding: "8px 14px",
                            fontSize: 13,
                            lineHeight: 1.2,
                            boxShadow: "0 2px 8px rgba(0,0,0,0.2)",
                          }}
                        >
                          <span className="btn-title">{t("car.view_360")}</span>
                        </button>
                      )}
                      <figure className="image">
                        <Item
                          original={mainPhoto}
                          thumbnail={mainPhoto}
                          width={1920}
                          height={1280}
                        >
                          {({ ref, open }) => (
                            <a onClick={open}>
                            <img
                                      alt={title}
                                      src={mainPhoto}
                                      ref={ref}
                                      style={{ width: "100%", height: "100%", objectFit: "cover" }}
                                onError={(e) => {
                                  e.target.onerror = null;
                                  e.target.src = DEFAULT_CAR_IMAGE;
                                }}
                              />
                            </a>
                          )}
                        </Item>
                      </figure>
                      {photos.length > 0 && (
                        <div className="content-box">
                          <ul className="video-list">
                            <li>
                              <span>
                                {photos.length}{" "}
                                {photos.length !== 1
                                  ? t("car.photo_plural")
                                  : t("car.photo")}
                              </span>
                            </li>
                          </ul>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
                <div className="col-lg-5 col-md-12 col-sm-12">
                  <div className="row">
                    {galleryPhotos.map((photo, index) => (
                      <div
                        key={photo.id || index}
                        className={`image-column-two item${index + 2} col-6`}
                      >
                        <div className="inner-column">
                          <div className="image-box">
                            <figure className="image">
                              <Item
                                original={photo.url}
                                thumbnail={photo.url}
                                width={1920}
                                height={1280}
                              >
                                {({ ref, open }) => (
                                  <a onClick={open}>
                                    <img
                                      ref={ref}
                                      alt={`${title} - photo ${index + 2}`}
                                      src={photo.url}
                                      style={{ width: "100%", height: "100%", objectFit: "cover" }}
                                      onError={(e) => {
                                        e.target.onerror = null;
                                        e.target.src = DEFAULT_CAR_IMAGE;
                                      }}
                                    />
                                  </a>
                                )}
                              </Item>
                            </figure>
                          </div>
                        </div>
                      </div>
                    ))}
                    {photos.length > 0 &&
                      galleryPhotos.length < 4 &&
                      Array.from({ length: 4 - galleryPhotos.length }).map(
                        (_, i) => (
                          <div
                            key={`placeholder-${i}`}
                            className={`image-column-two item${galleryPhotos.length + i + 2} col-6`}
                          >
                            <div className="inner-column">
                              <div className="image-box">
                                <figure className="image">
                                  <div style={{ display: "flex", alignItems: "center", justifyContent: "center", height: "100%", minHeight: "130px", background: "#e9ecef", color: "#888", fontSize: "13px" }}>
                                    No Preview
                                  </div>
                                </figure>
                              </div>
                            </div>
                          </div>
                        ),
                      )}
                  </div>
                </div>
                {/* Hidden items so PhotoSwipe lightbox includes ALL photos */}
                {photos.slice(5).map((photo, index) => (
                  <div key={`hidden-${photo.id || index}`} style={{ display: "none" }}>
                    <Item
                      original={photo.url}
                      thumbnail={photo.url}
                      width={1920}
                      height={1280}
                    >
                      {({ ref }) => (
                        <img ref={ref} alt={`${title} - photo ${index + 6}`} src={photo.url} />
                      )}
                    </Item>
                  </div>
                ))}
              </div>
            </div>
          </Gallery>

          <div className="row">
            <div className="inspection-column col-lg-8 col-md-12 col-sm-12">
              <div className="inner-column">
                <div className="overview-sec">
                  <Overview carItem={carItem} />
                </div>
                <div className="description-sec">
                  <Description carItem={carItem} />
                </div>
                <div className="features-sec">
                  <Features carItem={carItem} />
                </div>
              </div>
            </div>
            <div className="side-bar-column style-1 col-lg-4 col-md-12 col-sm-12">
              <div className="inner-column">
                <div className="contact-box">
                  <div className="icon-box">
                    <Image
                      src="/images/logo.svg"
                      width={55}
                      height={54}
                      alt="Imexso"
                    />
                  </div>
                  <div className="content-box">
                    <h6 className="title">Imexso</h6>
                    <div className="text">
                      {t("car.professional_sourcing")}
                    </div>
                    <div className="btn-box">
                      <Link href="/contact" className="side-btn">
                        {t("car.contact_us")}
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width={14}
                          height={14}
                          viewBox="0 0 14 14"
                          fill="none"
                        >
                          <g clipPath="url(#clip_contact)">
                            <path
                              d="M13.6111 0H5.05558C4.84062 0 4.66668 0.173943 4.66668 0.388901C4.66668 0.603859 4.84062 0.777802 5.05558 0.777802H12.6723L0.113941 13.3362C-0.0379805 13.4881 -0.0379805 13.7342 0.113941 13.8861C0.189884 13.962 0.289415 14 0.38891 14C0.488405 14 0.5879 13.962 0.663879 13.8861L13.2222 1.3277V8.94447C13.2222 9.15943 13.3962 9.33337 13.6111 9.33337C13.8261 9.33337 14 9.15943 14 8.94447V0.388901C14 0.173943 13.8261 0 13.6111 0Z"
                              fill="white"
                            />
                          </g>
                          <defs>
                            <clipPath id="clip_contact">
                              <rect width={14} height={14} fill="white" />
                            </clipPath>
                          </defs>
                        </svg>
                      </Link>
                      <Link href="/inventory" className="side-btn-three">
                        {t("car.view_all_inventory")}
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width={14}
                          height={14}
                          viewBox="0 0 14 14"
                          fill="none"
                        >
                          <g clipPath="url(#clip_inventory)">
                            <path
                              d="M13.6111 0H5.05558C4.84062 0 4.66668 0.173943 4.66668 0.388901C4.66668 0.603859 4.84062 0.777802 5.05558 0.777802H12.6723L0.113941 13.3362C-0.0379805 13.4881 -0.0379805 13.7342 0.113941 13.8861C0.189884 13.962 0.289415 14 0.38891 14C0.488405 14 0.5879 13.962 0.663879 13.8861L13.2222 1.3277V8.94447C13.2222 9.15943 13.3962 9.33337 13.6111 9.33337C13.8261 9.33337 14 9.15943 14 8.94447V0.388901C14 0.173943 13.8261 0 13.6111 0Z"
                              fill="#050B20"
                            />
                          </g>
                          <defs>
                            <clipPath id="clip_inventory">
                              <rect width={14} height={14} fill="white" />
                            </clipPath>
                          </defs>
                        </svg>
                      </Link>
                    </div>
                  </div>
                </div>
                <SendOfferForm carItem={carItem} />
              </div>
            </div>
          </div>
        </div>
        <RelatedCars make={carItem.make} currentCarId={carItem.id} />
      </section>
      <Carlab360Modal
        isOpen={carlab360Open}
        onClose={() => setCarlab360Open(false)}
        viewerUrl={carlab360Url}
        viewerTitle={t("car.view_360")}
        closeLabel={t("car.close_360")}
      />
    </>
  );
}
