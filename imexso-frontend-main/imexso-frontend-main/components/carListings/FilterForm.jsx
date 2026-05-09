"use client";
import React, { useState, useEffect, useRef } from "react";
import SelectComponent from "../common/SelectComponent";
import Slider from "rc-slider";
import { useLocale } from "@/context/LocaleContext";

export default function FilterForm({
  filterOptions,
  activeFilters,
  onFilterChange,
  onResetFilters,
}) {
  const [localPriceRange, setLocalPriceRange] = useState([0, 100000]);
  const [localMileageRange, setLocalMileageRange] = useState([0, 200000]);
  const [localSearch, setLocalSearch] = useState(activeFilters.search || "");
  const searchTimeout = useRef(null);

  const priceMin = filterOptions?.ranges?.price?.min ?? 0;
  const priceMax = filterOptions?.ranges?.price?.max ?? 100000;

  const mileageMin = filterOptions?.ranges?.mileage?.min ?? 0;
  const mileageMax = filterOptions?.ranges?.mileage?.max ?? 200000;

  useEffect(() => {
    setLocalPriceRange([
      activeFilters.price_min ?? priceMin,
      activeFilters.price_max ?? priceMax,
    ]);
  }, [activeFilters.price_min, activeFilters.price_max, priceMin, priceMax]);

  useEffect(() => {
    setLocalMileageRange([
      activeFilters.mileage_min ?? mileageMin,
      activeFilters.mileage_max ?? mileageMax,
    ]);
  }, [activeFilters.mileage_min, activeFilters.mileage_max, mileageMin, mileageMax]);

  useEffect(() => {
    setLocalSearch(activeFilters.search || "");
  }, [activeFilters.search]);

  const { t } = useLocale();

  const transmissionGroups = React.useMemo(() => {
    const groups = filterOptions?.transmission_groups;
    if (!groups || Object.keys(groups).length === 0) return [];
    return Object.entries(groups).map(([label, values]) => ({ label, values }));
  }, [filterOptions?.transmission_groups]);

  const colorGroups = React.useMemo(() => {
    const raw = filterOptions?.color_groups;
    if (!raw) return [];
    if (Array.isArray(raw)) {
      return raw.map((g) => ({
        key: g.key,
        hex: g.hex ?? "#bdbdbd",
        filterValue: [...(g.codes ?? []), ...(g.legacy_colors ?? [])]
          .filter(Boolean)
          .join(","),
      }));
    }
    return Object.entries(raw).map(([label, values]) => ({
      key: label,
      hex: "#bdbdbd",
      filterValue: Array.isArray(values) ? values.join(",") : "",
    }));
  }, [filterOptions?.color_groups]);

  if (!filterOptions) {
    return (
      <div className="inventroy-widget widget-location">
        <div className="d-flex justify-content-center py-4">
          <div className="spinner-border spinner-border-sm" role="status">
            <span className="visually-hidden">Loading filters...</span>
          </div>
        </div>
      </div>
    );
  }

  const models =
    activeFilters.make && filterOptions.models_by_make
      ? filterOptions.models_by_make[activeFilters.make] || []
      : filterOptions.models || [];

  const yearMin = filterOptions.ranges?.year?.min || 2000;
  const yearMax = filterOptions.ranges?.year?.max || new Date().getFullYear();
  const yearOptions = [];
  for (let y = yearMax; y >= yearMin; y--) yearOptions.push(String(y));

  const handleSearchChange = (value) => {
    setLocalSearch(value);
    if (searchTimeout.current) clearTimeout(searchTimeout.current);
    searchTimeout.current = setTimeout(() => {
      onFilterChange("search", value);
    }, 500);
  };

  const handlePriceAfterChange = (value) => {
    onFilterChange({
      price_min: value[0] > priceMin ? value[0] : null,
      price_max: value[1] < priceMax ? value[1] : null,
    });
  };

  const handleMileageAfterChange = (value) => {
    onFilterChange({
      mileage_min: value[0] > mileageMin ? value[0] : null,
      mileage_max: value[1] < mileageMax ? value[1] : null,
    });
  };

  const mileageSliderMax = Math.max(mileageMin, mileageMax);

  return (
    <div className="inventroy-widget widget-location">
      <div className="row">
        <div className="col-lg-12">
          <div className="form_boxes">
            <label>{t("filters.search")}</label>
            <input
              type="text"
              className="form-control"
              placeholder={t("filters.search_placeholder")}
              value={localSearch}
              onChange={(e) => handleSearchChange(e.target.value)}
              style={{
                border: "1px solid #e0e0e0",
                borderRadius: "4px",
                padding: "8px 12px",
                width: "100%",
                fontSize: "14px",
              }}
            />
          </div>
        </div>

        {filterOptions.condition_types?.length > 0 && (
          <div className="col-lg-12">
            <div className="form_boxes">
              <label>{t("filters.condition")}</label>
              <SelectComponent
                options={filterOptions.condition_types}
                value={activeFilters.condition_type}
                onChange={(val) => onFilterChange("condition_type", val)}
                placeholder={t("filters.all_conditions")}
              />
            </div>
          </div>
        )}

        {filterOptions.categories?.length > 0 && (
          <div className="col-lg-12">
            <div className="form_boxes">
              <label>{t("filters.category")}</label>
              <SelectComponent
                options={filterOptions.categories}
                value={activeFilters.category}
                onChange={(val) => onFilterChange("category", val)}
                placeholder={t("filters.all_categories")}
              />
            </div>
          </div>
        )}

        <div className="col-lg-12">
          <div className="form_boxes">
            <label>{t("filters.make")}</label>
            <SelectComponent
              options={filterOptions.makes || []}
              value={activeFilters.make}
              onChange={(val) => onFilterChange("make", val)}
              placeholder={t("filters.all_makes")}
            />
          </div>
        </div>

        <div className="col-lg-12">
          <div className="form_boxes">
            <label>{t("filters.model")}</label>
            <SelectComponent
              options={models}
              value={activeFilters.model}
              onChange={(val) => onFilterChange("model", val)}
              placeholder={t("filters.all_models")}
            />
          </div>
        </div>

        <div className="col-lg-6">
          <div className="form_boxes">
            <label>{t("filters.min_year")}</label>
            <SelectComponent
              options={yearOptions}
              value={
                activeFilters.year_min ? String(activeFilters.year_min) : ""
              }
              onChange={(val) =>
                onFilterChange("year_min", val ? parseInt(val) : null)
              }
              placeholder={t("filters.any")}
            />
          </div>
        </div>
        <div className="col-lg-6">
          <div className="form_boxes">
            <label>{t("filters.max_year")}</label>
            <SelectComponent
              options={yearOptions}
              value={
                activeFilters.year_max ? String(activeFilters.year_max) : ""
              }
              onChange={(val) =>
                onFilterChange("year_max", val ? parseInt(val) : null)
              }
              placeholder={t("filters.any")}
            />
          </div>
        </div>

        <div className="col-lg-12">
          <div className="price-box">
            <h6 className="title">{t("filters.price")}</h6>
            <form onSubmit={(e) => e.preventDefault()} className="row g-0">
              <div className="form-column col-lg-6">
                <div className="form_boxes">
                  <label>{t("filters.min_price")}</label>
                  <div className="drop-menu">
                    &euro;{localPriceRange[0].toLocaleString()}
                  </div>
                </div>
              </div>
              <div className="form-column v2 col-lg-6">
                <div className="form_boxes">
                  <label>{t("filters.max_price")}</label>
                  <div className="drop-menu">
                    &euro;{localPriceRange[1].toLocaleString()}
                  </div>
                </div>
              </div>
            </form>
            <div className="widget-price">
              <Slider
                range
                min={Math.floor(priceMin)}
                max={Math.ceil(priceMax)}
                value={localPriceRange}
                onChange={(value) => setLocalPriceRange(value)}
                onAfterChange={handlePriceAfterChange}
              />
            </div>
          </div>
        </div>

        {mileageSliderMax > mileageMin && (
          <div className="col-lg-12">
            <div className="price-box">
              <h6 className="title">{t("filters.mileage")}</h6>
              <form onSubmit={(e) => e.preventDefault()} className="row g-0">
                <div className="form-column col-lg-6">
                  <div className="form_boxes">
                    <label>{t("filters.min_mileage")}</label>
                    <div className="drop-menu">
                      {Number(localMileageRange[0]).toLocaleString()} km
                    </div>
                  </div>
                </div>
                <div className="form-column v2 col-lg-6">
                  <div className="form_boxes">
                    <label>{t("filters.max_mileage")}</label>
                    <div className="drop-menu">
                      {Number(localMileageRange[1]).toLocaleString()} km
                    </div>
                  </div>
                </div>
              </form>
              <div className="widget-price">
                <Slider
                  range
                  min={Math.floor(mileageMin)}
                  max={Math.ceil(mileageSliderMax)}
                  value={localMileageRange}
                  onChange={(value) => setLocalMileageRange(value)}
                  onAfterChange={handleMileageAfterChange}
                />
              </div>
            </div>
          </div>
        )}

        {filterOptions.fuel_types?.length > 0 && (
          <div className="col-lg-12">
            <div className="categories-box border-none-bottom">
              <h6 className="title">{t("filters.fuel_type")}</h6>
              <div className="cheak-box">
                {filterOptions.fuel_types.map((fuel) => (
                  <label className="contain" key={fuel}>
                    {fuel}
                    <input
                      type="checkbox"
                      checked={activeFilters.fuel_type === fuel}
                      onChange={() =>
                        onFilterChange(
                          "fuel_type",
                          activeFilters.fuel_type === fuel ? "" : fuel,
                        )
                      }
                    />
                    <span className="checkmark" />
                  </label>
                ))}
              </div>
            </div>
          </div>
        )}

        {transmissionGroups.length > 0 && (
          <div className="col-lg-12">
            <div className="categories-box border-none-bottom">
              <h6 className="title">{t("car.transmission")}</h6>
              <div className="cheak-box">
                {transmissionGroups.map(({ label, values }) => {
                  const filterValue = values.join(",");
                  const isActive = activeFilters.gearbox === filterValue;
                  return (
                    <label className="contain" key={label}>
                      {label}
                      <input
                        type="checkbox"
                        checked={isActive}
                        onChange={() =>
                          onFilterChange("gearbox", isActive ? "" : filterValue)
                        }
                      />
                      <span className="checkmark" />
                    </label>
                  );
                })}
              </div>
            </div>
          </div>
        )}

        {colorGroups.length > 0 && (
          <div className="col-lg-12">
            <div className="categories-box border-none-bottom">
              <h6 className="title">{t("filters.color")}</h6>
              <div className="cheak-box">
                {colorGroups.map(({ key, hex, filterValue }) => {
                  const label = t(`filters.color_group.${key}`);
                  const isActive = activeFilters.color === filterValue;
                  return (
                    <label className="contain" key={key}>
                      <span
                        style={{
                          display: "inline-flex",
                          alignItems: "center",
                          gap: 8,
                          flexWrap: "wrap",
                        }}
                      >
                        <span
                          aria-hidden
                          style={{
                            width: 18,
                            height: 18,
                            borderRadius: "50%",
                            background: hex,
                            flexShrink: 0,
                            border:
                              key === "white"
                                ? "1px solid rgba(0,0,0,0.12)"
                                : "none",
                            boxShadow: "inset 0 0 0 1px rgba(0,0,0,0.06)",
                          }}
                        />
                        {label}
                      </span>
                      <input
                        type="checkbox"
                        checked={isActive}
                        onChange={() =>
                          onFilterChange("color", isActive ? "" : filterValue)
                        }
                      />
                      <span className="checkmark" />
                    </label>
                  );
                })}
              </div>
            </div>
          </div>
        )}

        <div className="col-lg-12">
          <div className="categories-box border-none-bottom">
            <h6 className="title">{t("filters.special")}</h6>
            <div className="cheak-box">
              <label className="contain">
                {t("filters.new_cars_only")}
                <input
                  type="checkbox"
                  checked={!!activeFilters.is_new}
                  onChange={() =>
                    onFilterChange("is_new", activeFilters.is_new ? "" : "1")
                  }
                />
                <span className="checkmark" />
              </label>
              <label className="contain">
                {t("filters.promotions_only")}
                <input
                  type="checkbox"
                  checked={!!activeFilters.has_promotion}
                  onChange={() =>
                    onFilterChange("has_promotion", activeFilters.has_promotion ? "" : "1")
                  }
                />
                <span className="checkmark" />
              </label>
            </div>
          </div>
        </div>

        <div className="col-lg-12">
          <div className="form_boxes" style={{ marginTop: "10px" }}>
            <button
              type="button"
              onClick={onResetFilters}
              style={{
                width: "100%",
                display: "inline-flex",
                alignItems: "center",
                justifyContent: "center",
                padding: "12px 24px",
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
        </div>
      </div>
    </div>
  );
}
