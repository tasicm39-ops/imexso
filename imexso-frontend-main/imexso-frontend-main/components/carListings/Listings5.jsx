"use client";
import React from "react";
import Link from "next/link";
import Pagination from "../common/Pagination";
import SelectComponent from "../common/SelectComponent";
import FilterForm from "./FilterForm";
import FavouriteButton from "@/components/common/FavouriteButton";
import AddToCartButton from "@/components/common/AddToCartButton";
import { useLocale } from "@/context/LocaleContext";
import CarPhoto from "@/components/common/CarPhoto";
import { getCarImageUrl } from "@/lib/carDisplay";

const SORT_LABELS = [
  "Newest",
  "Price: Low to High",
  "Price: High to Low",
  "Year: Newest",
  "Mileage: Lowest",
];

const SORT_MAP = {
  Newest: "newest",
  "Price: Low to High": "price_asc",
  "Price: High to Low": "price_desc",
  "Year: Newest": "year_desc",
  "Mileage: Lowest": "mileage_asc",
};

const REVERSE_SORT_MAP = Object.fromEntries(
  Object.entries(SORT_MAP).map(([k, v]) => [v, k]),
);

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

function getCarDescription(car) {
  return [car.manufacturing_year, car.trim_level].filter(Boolean).join(" ");
}

function isSoldVisible(car) {
  const marketing = car?.marketing;
  if (!marketing?.sold_enabled || !marketing?.sold_expires_at) return false;
  return new Date(marketing.sold_expires_at).getTime() > Date.now();
}

const PER_PAGE_OPTIONS = [10, 25, 50, 100];

export default function Listings5({
  cars = [],
  filterOptions,
  activeFilters,
  sort,
  perPage = 25,
  pagination,
  totalAll = 0,
  loading,
  viewMode = "list",
  onFilterChange,
  onSortChange,
  onPageChange,
  onPerPageChange,
  onResetFilters,
  onViewModeChange,
  favouriteCarIds = [],
  cartCarIds = [],
  onCartRefresh,
  saveSearchButton,
  savedSearchesList,
}) {
  const { t } = useLocale();
  const total = pagination?.total || 0;
  const from = pagination?.from || 0;
  const to = pagination?.to || 0;
  const currentPage = pagination?.current_page || 1;
  const lastPage = pagination?.last_page || 1;

  const currentSortLabel = REVERSE_SORT_MAP[sort] || "Newest";

  return (
    <section className="cars-section-thirteen layout-radius">
      <div className="boxcar-container">
        <div className="boxcar-title-three wow fadeInUp">
          <ul className="breadcrumb">
            <li>
              <Link href={`/`}>{t("nav.home")}</Link>
            </li>
            <li>
              <span>{t("listings.breadcrumb_cars")}</span>
            </li>
          </ul>
          <h2>{t("listings.browse_inventory")}</h2>
        </div>
        <div className="row">
          {/* Desktop Sidebar */}
          <div className="wrap-sidebar-dk side-bar col-xl-3 col-md-12 col-sm-12">
            <div className="sidebar-handle filter-popup">
              <svg
                width={24}
                height={24}
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fillRule="evenodd"
                  clipRule="evenodd"
                  d="M15.75 4.50903C13.9446 4.50903 12.4263 5.80309 12.0762 7.50903H2.25C1.83579 7.50903 1.5 7.84482 1.5 8.25903C1.5 8.67324 1.83579 9.00903 2.25 9.00903H12.0762C12.4263 10.715 13.9446 12.009 15.75 12.009C17.5554 12.009 19.0737 10.715 19.4238 9.00903H21.75C22.1642 9.00903 22.5 8.67324 22.5 8.25903C22.5 7.84482 22.1642 7.50903 21.75 7.50903H19.4238C19.0737 5.80309 17.5554 4.50903 15.75 4.50903ZM15.75 6.00903C17.0015 6.00903 18 7.00753 18 8.25903C18 9.51054 17.0015 10.509 15.75 10.509C14.4985 10.509 13.5 9.51054 13.5 8.25903C13.5 7.00753 14.4985 6.00903 15.75 6.00903Z"
                  fill="#050B20"
                />
                <path
                  fillRule="evenodd"
                  clipRule="evenodd"
                  d="M8.25 12.009C6.44461 12.009 4.92634 13.3031 4.57617 15.009H2.25C1.83579 15.009 1.5 15.3448 1.5 15.759C1.5 16.1732 1.83579 16.509 2.25 16.509H4.57617C4.92634 18.215 6.44461 19.509 8.25 19.509C10.0554 19.509 11.5737 18.215 11.9238 16.509H21.75C22.1642 16.509 22.5 16.1732 22.5 15.759C22.5 15.3448 22.1642 15.009 21.75 15.009H11.9238C11.5737 13.3031 10.0554 12.009 8.25 12.009ZM8.25 13.509C9.5015 13.509 10.5 14.5075 10.5 15.759C10.5 17.0105 9.5015 18.009 8.25 18.009C6.9985 18.009 6 17.0105 6 15.759C6 14.5075 6.9985 13.509 8.25 13.509Z"
                  fill="#050B20"
                />
              </svg>
              {t("listings.show_filter")}
            </div>
            <div className="inventory-sidebar">
              <FilterForm
                filterOptions={filterOptions}
                activeFilters={activeFilters}
                onFilterChange={onFilterChange}
                onResetFilters={onResetFilters}
              />
            </div>
          </div>

          {/* Car List */}
          <div className="col-xl-9 col-md-12 col-sm-12">
            <div className="right-box">
              <div className="text-box">
                <div className="text">
                  {loading
                    ? t("general.loading")
                    : total > 0
                      ? `${t("general.showing_results", {
                          from,
                          to,
                          total: total.toLocaleString(),
                        })}${totalAll > total ? ` (${totalAll.toLocaleString()} ${t("general.vehicles_in_stock")})` : ""}`
                      : t("general.no_results")}
                </div>
                <div className="d-flex align-items-center gap-3">
                  {onViewModeChange && (
                    <div className="d-flex align-items-center gap-1">
                      <button
                        type="button"
                        className={`view-toggle-btn${viewMode === "list" ? " active" : ""}`}
                        onClick={() => onViewModeChange("list")}
                        aria-label="List view"
                        title="List view"
                      >
                        <svg width={18} height={18} viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M2 4h14M2 9h14M2 14h14" stroke="currentColor" strokeWidth={1.8} strokeLinecap="round" />
                        </svg>
                      </button>
                      <button
                        type="button"
                        className={`view-toggle-btn${viewMode === "grid" ? " active" : ""}`}
                        onClick={() => onViewModeChange("grid")}
                        aria-label="Grid view"
                        title="Grid view"
                      >
                        <svg width={18} height={18} viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <rect x="1" y="1" width="6.5" height="6.5" rx="1" stroke="currentColor" strokeWidth={1.5} />
                          <rect x="10.5" y="1" width="6.5" height="6.5" rx="1" stroke="currentColor" strokeWidth={1.5} />
                          <rect x="1" y="10.5" width="6.5" height="6.5" rx="1" stroke="currentColor" strokeWidth={1.5} />
                          <rect x="10.5" y="10.5" width="6.5" height="6.5" rx="1" stroke="currentColor" strokeWidth={1.5} />
                        </svg>
                      </button>
                    </div>
                  )}
                  {onPerPageChange && (
                    <form onSubmit={(e) => e.preventDefault()}>
                      <div className="form_boxes v3">
                        <small>{t("listings.per_page")}</small>
                        <SelectComponent
                          options={PER_PAGE_OPTIONS.map(String)}
                          value={String(perPage)}
                          onChange={(val) => onPerPageChange(parseInt(val, 10))}
                        />
                      </div>
                    </form>
                  )}
                  <form onSubmit={(e) => e.preventDefault()}>
                    <div className="form_boxes v3">
                      <small>{t("filters.sort_by")}</small>
                      <SelectComponent
                        options={SORT_LABELS}
                        value={currentSortLabel}
                        onChange={(val) =>
                          onSortChange(SORT_MAP[val] || "newest")
                        }
                        placeholder="Newest"
                      />
                    </div>
                  </form>
                  {saveSearchButton}
                </div>
              </div>

              {savedSearchesList}

              {loading ? (
                <div
                  className="d-flex justify-content-center align-items-center"
                  style={{ minHeight: "300px" }}
                >
                  <div className="spinner-border" role="status">
                    <span className="visually-hidden">Loading...</span>
                  </div>
                </div>
              ) : cars.length === 0 ? (
                <div
                  className="d-flex flex-column justify-content-center align-items-center"
                  style={{ minHeight: "300px" }}
                >
                  <h4>{t("listings.no_vehicles")}</h4>
                  <p className="mt-2">
                    {t("listings.adjust_filters")}
                  </p>
                  <button
                    type="button"
                    className="mt-3"
                    onClick={onResetFilters}
                    style={{
                      display: "inline-flex",
                      alignItems: "center",
                      justifyContent: "center",
                      padding: "12px 36px",
                      background: "#405FF2",
                      color: "#fff",
                      fontSize: "15px",
                      fontWeight: 600,
                      borderRadius: "12px",
                      border: "none",
                      cursor: "pointer",
                      transition: "background 0.2s",
                    }}
                    onMouseEnter={(e) => (e.currentTarget.style.background = "#0146a6")}
                    onMouseLeave={(e) => (e.currentTarget.style.background = "#405FF2")}
                  >
                    {t("filters.reset_filters")}
                  </button>
                </div>
              ) : viewMode === "grid" ? (
                <div className="row">
                  {cars.map((car) => (
                    <CarGridItem
                      key={car.id}
                      car={car}
                      isFavourited={favouriteCarIds.includes(car.id)}
                      cartCarIds={cartCarIds}
                      onCartChange={onCartRefresh}
                    />
                  ))}
                </div>
              ) : (
                cars.map((car) => (
                  <CarListItem
                    key={car.id}
                    car={car}
                    isFavourited={favouriteCarIds.includes(car.id)}
                    cartCarIds={cartCarIds}
                    onCartChange={onCartRefresh}
                  />
                ))
              )}
            </div>

            {!loading && total > 0 && (
              <div className="pagination-sec">
                <nav aria-label="Page navigation">
                  <Pagination
                    currentPage={currentPage}
                    lastPage={lastPage}
                    onPageChange={onPageChange}
                  />
                  <div className="text">
                    {t("listings.showing_pagination", {
                      from,
                      to,
                      total: total.toLocaleString(),
                    })}
                    {totalAll > total && ` (${totalAll.toLocaleString()} ${t("general.vehicles_in_stock")})`}
                  </div>
                </nav>
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}

function CartPopularityIndicator({ count, t }) {
  if (!count || count <= 0) return null;
  const label = count === 1
    ? t("car.list_one_user_in_cart")
    : t("car.list_n_users_in_cart", { count });

  return (
    <span className="cart-popularity" title={label}>
      <svg xmlns="http://www.w3.org/2000/svg" width={14} height={14} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden>
        <circle cx="9" cy="21" r="1" />
        <circle cx="20" cy="21" r="1" />
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
      </svg>
      <span className="cart-popularity__dot" />
    </span>
  );
}

function MarketingBadges({ car, t }) {
  const marketing = car.marketing;
  const showMarketing = marketing?.is_active;
  const hasAnything = car.is_new ||
    (showMarketing && (marketing.promotion_enabled || marketing.new_price_enabled || marketing.limited_stock_enabled || marketing.badge_text));

  if (!hasAnything) return null;

  return (
    <div className="marketing-badges d-flex gap-2 flex-wrap align-items-center">
      {car.is_new && (
        <span className="badge-marketing badge-new">{t("car.badge_new")}</span>
      )}
      {showMarketing && marketing.promotion_enabled && marketing.promotion_label && (
        <span className="badge-marketing badge-promotion">{marketing.promotion_label}</span>
      )}
      {showMarketing && marketing.new_price_enabled && (
        <span className="badge-marketing badge-new-price">{t("car.new_price")}</span>
      )}
      {showMarketing && marketing.limited_stock_enabled && marketing.limited_stock_count && (
        <span className="badge-marketing badge-limited-stock">
          {t("car.only_x_remaining").replace("{count}", marketing.limited_stock_count)}
        </span>
      )}
      {showMarketing && marketing.badge_text && (
        <span className="badge-marketing badge-custom">{marketing.badge_text}</span>
      )}
    </div>
  );
}

function CarPrice({ car, className }) {
  const marketing = car.marketing;
  if (marketing?.is_active && marketing?.new_price_enabled && marketing?.new_price_amount) {
    return (
      <span className={className}>
        <span className="price-old">{formatPrice(car.professional_price)}</span>{" "}
        {formatPrice(marketing.new_price_amount)}
      </span>
    );
  }
  return <span className={className}>{formatPrice(car.professional_price)}</span>;
}

function CarListItem({ car, isFavourited = false, cartCarIds = [], onCartChange }) {
  const { t } = useLocale();
  const imageUrl = getCarImageUrl(car);
  const soldVisible = isSoldVisible(car);

  return (
    <div className={`service-block-thirteen${soldVisible ? " is-sold-card" : ""}`}>
      <div className="inner-box" style={{ position: "relative" }}>
        {soldVisible && <span className="sold-overlay-badge">SOLD</span>}
        <div className="image-box">
          <figure className="image" style={{ height: "100%" }}>
            <Link href={`/car/${car.id_produit}`}>
              <CarPhoto src={imageUrl} alt={getCarTitle(car)} width={340} height={320} variant="list" />
            </Link>
          </figure>
        </div>
        <div className="right-box">
          <div className="content-box">
            <h4 className="title d-flex align-items-center gap-2 flex-wrap">
              <Link href={`/car/${car.id_produit}`}>{getCarTitle(car)}</Link>
              <CartPopularityIndicator count={car.cart_users_count} t={t} />
              <FavouriteButton carId={car.id} initialFavourited={isFavourited} size="small" />
            </h4>
            {car.id_produit && (
              <span className="car-ref-badge">{t("car.ref_label")}: {car.id_produit}</span>
            )}
            <div className="text">{getCarDescription(car)}</div>
            <MarketingBadges car={car} t={t} />
            <div className="inspection-sec">
              <div className="inspection-box">
                <span className="icon">
                  <svg width={18} height={18} viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 10.6482C18 12.6227 17.3716 14.497 16.1827 16.0687C15.9482 16.379 15.5071 16.4391 15.1978 16.2052C14.8881 15.971 14.827 15.53 15.0612 15.2203C16.0638 13.895 16.5938 12.3139 16.5938 10.6482C16.5938 6.45117 13.1947 3.05859 9 3.05859C4.8024 3.05859 1.40625 6.45378 1.40625 10.6482C1.40625 12.3139 1.9362 13.895 2.93871 15.2203C3.17299 15.53 3.11188 15.971 2.8022 16.2052C2.49239 16.4395 2.05156 16.3784 1.81714 16.0687C0.628418 14.497 0 12.6227 0 10.6482C0 5.67361 4.02814 1.65234 9 1.65234C13.9746 1.65234 18 5.67636 18 10.6482Z" fill="#050B20" />
                    <path d="M9 4.21875C9.38867 4.21875 9.70312 4.53321 9.70312 4.92188V9.5625H12.6562C13.0449 9.5625 13.3594 9.87696 13.3594 10.2656C13.3594 10.6543 13.0449 10.9688 12.6562 10.9688H9C8.61133 10.9688 8.29688 10.6543 8.29688 10.2656V4.92188C8.29688 4.53321 8.61133 4.21875 9 4.21875Z" fill="#050B20" />
                  </svg>
                </span>
                <div className="info">
                  <span>{t("car.mileage")}</span>
                  <small>{formatMileage(car.mileage)}</small>
                </div>
              </div>
              <div className="inspection-box">
                <span className="icon">
                  <svg width={18} height={18} viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.6875 3.375C10.6875 3.06432 10.4357 2.8125 10.125 2.8125H4.5C4.18932 2.8125 3.9375 3.06432 3.9375 3.375V7.875C3.9375 8.18568 4.18932 8.4375 4.5 8.4375H10.125C10.4357 8.4375 10.6875 8.18568 10.6875 7.875V3.375ZM9.5625 7.3125H5.0625V3.9375H9.5625V7.3125Z" fill="#050B20" />
                    <path d="M17.14 3.98858L14.8967 2.86358C14.6182 2.72405 14.2843 2.8375 14.1453 3.11516C14.0064 3.39311 14.1206 3.73096 14.3986 3.86993L15.2529 4.29669C15.2301 4.35786 15.2005 4.42255 15.2005 4.49177C15.2005 5.22415 15.75 5.84269 16.3125 6.0756V12.9293C16.3125 13.2394 16.0601 13.4918 15.75 13.4918C15.4399 13.4918 15.1875 13.2394 15.1875 12.9293V8.42927C15.1875 7.07115 14.0625 5.93497 12.9375 5.67362V2.24177C12.9375 1.00114 11.955 0 10.7144 0H3.96436C2.72373 0 1.6875 1.00114 1.6875 2.24177V14.8315L0.873422 15.2386C0.682805 15.3339 0.5625 15.5286 0.5625 15.7418V17.4293C0.5625 17.7399 0.841219 18 1.15186 18H13.5269C13.8375 18 14.0625 17.7399 14.0625 17.4293V15.7418C14.0625 15.5286 13.9422 15.3339 13.7516 15.2386L12.9375 14.8315V6.84545C13.5 7.07836 14.0625 7.6969 14.0625 8.42927V12.9293C14.0625 13.8598 14.8194 14.6168 15.75 14.6168C16.6806 14.6168 17.4375 13.8598 17.4375 12.9293V4.49177C17.4375 4.27862 17.3306 4.08389 17.14 3.98858Z" fill="#050B20" />
                  </svg>
                </span>
                <div className="info">
                  <span>{t("car.fuel_type")}</span>
                  <small>{car.fuel_type || "N/A"}</small>
                </div>
              </div>
              <div className="inspection-box">
                <span className="icon">
                  <svg width={18} height={18} viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fillRule="evenodd" clipRule="evenodd" d="M3 1.6875C1.8615 1.6875 0.9375 2.6115 0.9375 3.75C0.9375 4.8885 1.8615 5.8125 3 5.8125C4.1385 5.8125 5.0625 4.8885 5.0625 3.75C5.0625 2.6115 4.1385 1.6875 3 1.6875ZM3 2.8125C3.5175 2.8125 3.9375 3.2325 3.9375 3.75C3.9375 4.2675 3.5175 4.6875 3 4.6875C2.4825 4.6875 2.0625 4.2675 2.0625 3.75C2.0625 3.2325 2.4825 2.8125 3 2.8125Z" fill="#050B20" />
                    <path fillRule="evenodd" clipRule="evenodd" d="M3 12.1875C1.8615 12.1875 0.9375 13.1115 0.9375 14.25C0.9375 15.3885 1.8615 16.3125 3 16.3125C4.1385 16.3125 5.0625 15.3885 5.0625 14.25C5.0625 13.1115 4.1385 12.1875 3 12.1875Z" fill="#050B20" />
                    <path fillRule="evenodd" clipRule="evenodd" d="M2.4375 5.25V12.75C2.4375 13.0605 2.6895 13.3125 3 13.3125C3.3105 13.3125 3.5625 13.0605 3.5625 12.75V5.25C3.5625 4.9395 3.3105 4.6875 3 4.6875C2.6895 4.6875 2.4375 4.9395 2.4375 5.25Z" fill="#050B20" />
                  </svg>
                </span>
                <div className="info">
                  <span>{t("car.transmission")}</span>
                  <small>{car.gearbox || "N/A"}</small>
                </div>
              </div>
            </div>
            {car.manufacturing_year && (
              <ul className="ul-cotent">
                <li><span>{car.manufacturing_year}</span></li>
                {car.condition_type && <li><span>{car.condition_type}</span></li>}
                {car.doors && <li><span>{t("listings.doors", { count: car.doors })}</span></li>}
              </ul>
            )}
          </div>
          <div className="content-box-two">
            <h4 className="title"><CarPrice car={car} /></h4>
            {car.vat_type && <span>{car.vat_type}</span>}
            <div className="content-box-two__actions">
              <Link href={`/car/${car.id_produit}`} className="button">
                {t("general.view_details")}
                <svg xmlns="http://www.w3.org/2000/svg" width={14} height={14} viewBox="0 0 14 14" fill="none">
                  <path d="M13.6106 0H5.05509C4.84013 0 4.66619 0.173943 4.66619 0.388901C4.66619 0.603859 4.84013 0.777802 5.05509 0.777802H12.6719L0.113453 13.3362C-0.0384687 13.4881 -0.0384687 13.7342 0.113453 13.8861C0.189396 13.962 0.288927 14 0.388422 14C0.487917 14 0.587411 13.962 0.663391 13.8861L13.2218 1.3277V8.94447C13.2218 9.15943 13.3957 9.33337 13.6107 9.33337C13.8256 9.33337 13.9996 9.15943 13.9996 8.94447V0.388901C13.9995 0.173943 13.8256 0 13.6106 0Z" fill="#405FF2" />
                </svg>
              </Link>
              {car.sync_status === "active" && !soldVisible && (
                <AddToCartButton
                  carId={car.id}
                  initialInCart={cartCarIds.includes(car.id)}
                  variant="button"
                  onChange={onCartChange}
                />
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function CarGridItem({ car, isFavourited = false, cartCarIds = [], onCartChange }) {
  const { t } = useLocale();
  const imageUrl = getCarImageUrl(car);
  const soldVisible = isSoldVisible(car);

  return (
    <div className="car-block-four col-xl-3 col-lg-4 col-md-6 col-sm-6">
      <div className={`inner-box${soldVisible ? " is-sold-card" : ""}`} style={{ position: "relative" }}>
        {soldVisible && <span className="sold-overlay-badge">SOLD</span>}
        <div className="image-box" style={{ position: "relative" }}>
          <figure className="image">
            <Link href={`/car/${car.id_produit}`}>
              <CarPhoto src={imageUrl} alt={getCarTitle(car)} width={400} height={220} variant="grid" />
            </Link>
          </figure>
          <div style={{ position: "absolute", top: 8, right: 8, zIndex: 2 }}>
            <FavouriteButton carId={car.id} initialFavourited={isFavourited} size="small" />
          </div>
        </div>
        <div className="content-box">
          <h6 className="title d-flex align-items-center gap-1">
            <Link href={`/car/${car.id_produit}`}>{getCarTitle(car)}</Link>
            {car.cart_users_count > 0 && (
              <span className="cart-popularity-grid">
                <CartPopularityIndicator count={car.cart_users_count} t={t} />
              </span>
            )}
          </h6>
          {car.id_produit && (
            <span className="car-ref-badge">{t("car.ref_label")}: {car.id_produit}</span>
          )}
          <div className="text">{getCarDescription(car)}</div>
          <MarketingBadges car={car} t={t} />
          <ul>
            <li><i className="flaticon-gasoline-pump" /> {formatMileage(car.mileage)}</li>
            <li><i className="flaticon-speedometer" /> {car.fuel_type || "N/A"}</li>
            <li><i className="flaticon-gearbox" /> {car.gearbox || "N/A"}</li>
          </ul>
          <div className="btn-box">
            <div className="btn-box__price">
              <CarPrice car={car} />
              {car.vat_type && <small>{car.vat_type}</small>}
            </div>
            <div className="btn-box__actions">
              {car.sync_status === "active" && !soldVisible && (
                <AddToCartButton
                  carId={car.id}
                  initialInCart={cartCarIds.includes(car.id)}
                  variant="button"
                  onChange={onCartChange}
                />
              )}
              <Link href={`/car/${car.id_produit}`} className="details">
                {t("general.view_details")}
                <svg xmlns="http://www.w3.org/2000/svg" width={14} height={14} viewBox="0 0 14 14" fill="none">
                  <path d="M13.6109 0H5.05533C4.84037 0 4.66643 0.173943 4.66643 0.388901C4.66643 0.603859 4.84037 0.777802 5.05533 0.777802H12.6721L0.113697 13.3362C-0.0382246 13.4881 -0.0382246 13.7342 0.113697 13.8861C0.18964 13.962 0.289171 14 0.388666 14C0.488161 14 0.587656 13.962 0.663635 13.8861L13.222 1.3277V8.94447C13.222 9.15943 13.3959 9.33337 13.6109 9.33337C13.8259 9.33337 13.9998 9.15943 13.9998 8.94447V0.388901C13.9998 0.173943 13.8258 0 13.6109 0Z" fill="#405FF2" />
                </svg>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
