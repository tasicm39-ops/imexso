"use client";

import React, { useState, useEffect, useCallback, Suspense } from "react";
import { useSearchParams } from "next/navigation";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import Sidebar from "@/components/carListings/Sidebar";
import Listings5 from "@/components/carListings/Listings5";
import SaveSearchButton from "@/components/common/SaveSearchButton";
import SavedSearchesList from "@/components/common/SavedSearchesList";
import { apiGet } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";
import { useCart } from "@/context/CartContext";

const FILTER_KEYS = [
  "make",
  "model",
  "fuel_type",
  "condition_type",
  "category",
  "gearbox",
  "color",
  "search",
  "price_min",
  "price_max",
  "year_min",
  "year_max",
  "mileage_min",
  "mileage_max",
  "is_new",
  "has_promotion",
];

function buildInitialFilters(searchParams) {
  const filters = {
    make: "",
    model: "",
    fuel_type: "",
    condition_type: "",
    category: "",
    gearbox: "",
    color: "",
    search: "",
    price_min: null,
    price_max: null,
    year_min: null,
    year_max: null,
    mileage_min: null,
    mileage_max: null,
    is_new: "",
    has_promotion: "",
  };

  FILTER_KEYS.forEach((key) => {
    const val = searchParams.get(key);
    if (val) {
      if (key.endsWith("_min") || key.endsWith("_max")) {
        const num = parseInt(val, 10);
        if (!isNaN(num)) filters[key] = num;
      } else {
        filters[key] = val;
      }
    }
  });

  return filters;
}

function InventoryPageContent() {
  const searchParams = useSearchParams();
  const { isAuthenticated } = useAuth();
  const { cartCarIds, refreshCart } = useCart();
  const [cars, setCars] = useState([]);
  const [favouriteCarIds, setFavouriteCarIds] = useState([]);
  const [filterOptions, setFilterOptions] = useState(null);
  const [activeFilters, setActiveFilters] = useState(() =>
    buildInitialFilters(searchParams),
  );
  const [sort, setSort] = useState(() => searchParams.get("sort") || "");
  const [page, setPage] = useState(() => {
    const p = parseInt(searchParams.get("page"), 10);
    return p > 0 ? p : 1;
  });
  const [perPage, setPerPage] = useState(() => {
    if (typeof window !== "undefined") {
      const saved = localStorage.getItem("inventoryPerPage");
      return saved ? parseInt(saved, 10) : 25;
    }
    return 25;
  });
  const [pagination, setPagination] = useState(null);
  const [totalAll, setTotalAll] = useState(0);
  const [loading, setLoading] = useState(true);
  const [savedSearchRefreshKey, setSavedSearchRefreshKey] = useState(0);
  const [viewMode, setViewMode] = useState(() => {
    if (typeof window !== "undefined") {
      return localStorage.getItem("inventoryViewMode") || "list";
    }
    return "list";
  });

  const handleViewModeChange = useCallback((mode) => {
    setViewMode(mode);
    localStorage.setItem("inventoryViewMode", mode);
  }, []);

  const urlQuery = searchParams.toString();
  useEffect(() => {
    const params = new URLSearchParams(urlQuery);
    setActiveFilters(buildInitialFilters(params));
    setSort(params.get("sort") || "");
    const p = parseInt(params.get("page"), 10);
    setPage(p > 0 ? p : 1);
  }, [urlQuery]);

  useEffect(() => {
    async function fetchFilters() {
      try {
        const res = await apiGet("/api/cars/filters");
        if (res.ok) {
          const data = await res.json();
          setFilterOptions(data);
        }
      } catch {
        // filters will remain null
      }
    }
    fetchFilters();
  }, []);

  useEffect(() => {
    if (!isAuthenticated) return;
    async function fetchFavouriteIds() {
      try {
        const res = await apiGet("/api/favourites/car-ids");
        if (res.ok) {
          const data = await res.json();
          setFavouriteCarIds(data.car_ids || []);
        }
      } catch {
        // favourites will remain empty
      }
    }
    fetchFavouriteIds();
  }, [isAuthenticated]);

  const fetchCars = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      Object.entries(activeFilters).forEach(([key, value]) => {
        if (value !== "" && value !== null && value !== undefined) {
          params.append(key, String(value));
        }
      });
      if (sort) params.append("sort", sort);
      params.append("page", String(page));
      params.append("per_page", String(perPage));

      const res = await apiGet(`/api/cars?${params.toString()}`);
      if (res.ok) {
        const json = await res.json();
        setCars(json.data || []);
        setPagination(json.meta || null);
        setTotalAll(json.total_all ?? 0);
      } else {
        setCars([]);
        setPagination(null);
        setTotalAll(0);
      }
    } catch {
      setCars([]);
      setPagination(null);
    } finally {
      setLoading(false);
    }
  }, [activeFilters, sort, page, perPage]);

  useEffect(() => {
    fetchCars();
  }, [fetchCars]);

  useEffect(() => {
    if (typeof window === "undefined") return;
    const params = new URLSearchParams();
    Object.entries(activeFilters).forEach(([key, value]) => {
      if (value !== "" && value !== null && value !== undefined) {
        params.set(key, String(value));
      }
    });
    if (sort) params.set("sort", sort);
    if (page > 1) params.set("page", String(page));
    if (perPage !== 25) params.set("per_page", String(perPage));

    const existing = new URLSearchParams(window.location.search);
    const langParam = existing.get("lang");
    if (langParam) params.set("lang", langParam);

    const qs = params.toString();
    const newUrl = qs ? `${window.location.pathname}?${qs}` : window.location.pathname;
    window.history.replaceState({}, "", newUrl);
  }, [activeFilters, sort, page, perPage]);

  const handleFilterChange = useCallback((keyOrObj, value) => {
    if (typeof keyOrObj === "object" && keyOrObj !== null) {
      setActiveFilters((prev) => ({ ...prev, ...keyOrObj }));
    } else {
      setActiveFilters((prev) => {
        const updated = { ...prev, [keyOrObj]: value };
        if (keyOrObj === "make") {
          updated.model = "";
        }
        return updated;
      });
    }
    setPage(1);
  }, []);

  const handleSortChange = useCallback((value) => {
    setSort(value);
    setPage(1);
  }, []);

  const handlePageChange = useCallback((newPage) => {
    setPage(newPage);
    window.scrollTo({ top: 0, behavior: "smooth" });
  }, []);

  const handlePerPageChange = useCallback((value) => {
    setPerPage(value);
    setPage(1);
    localStorage.setItem("inventoryPerPage", String(value));
  }, []);

  const handleResetFilters = useCallback(() => {
    setActiveFilters({
      make: "",
      model: "",
      fuel_type: "",
      condition_type: "",
      category: "",
      gearbox: "",
      color: "",
      search: "",
      price_min: null,
      price_max: null,
      year_min: null,
      year_max: null,
      mileage_min: null,
      mileage_max: null,
      is_new: "",
      has_promotion: "",
    });
    setSort("");
    setPage(1);
  }, []);

  const handleLoadSearch = useCallback((filters) => {
    const merged = {
      make: "",
      model: "",
      fuel_type: "",
      condition_type: "",
      category: "",
      gearbox: "",
      color: "",
      search: "",
      price_min: null,
      price_max: null,
      year_min: null,
      year_max: null,
      mileage_min: null,
      mileage_max: null,
      is_new: "",
      has_promotion: "",
      ...filters,
    };
    setActiveFilters(merged);
    setPage(1);
  }, []);

  return (
    <>
      <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
      <Sidebar
        filterOptions={filterOptions}
        activeFilters={activeFilters}
        onFilterChange={handleFilterChange}
        onResetFilters={handleResetFilters}
      />
      <Listings5
        cars={cars}
        filterOptions={filterOptions}
        activeFilters={activeFilters}
        sort={sort}
        perPage={perPage}
        pagination={pagination}
        totalAll={totalAll}
        loading={loading}
        viewMode={viewMode}
        favouriteCarIds={favouriteCarIds}
        cartCarIds={cartCarIds}
        onCartRefresh={refreshCart}
        onFilterChange={handleFilterChange}
        onSortChange={handleSortChange}
        onPageChange={handlePageChange}
        onPerPageChange={handlePerPageChange}
        onResetFilters={handleResetFilters}
        onViewModeChange={handleViewModeChange}
        saveSearchButton={
          <SaveSearchButton
            activeFilters={activeFilters}
            onSaved={() => setSavedSearchRefreshKey((k) => k + 1)}
          />
        }
        savedSearchesList={
          <SavedSearchesList
            onLoadSearch={handleLoadSearch}
            refreshKey={savedSearchRefreshKey}
          />
        }
      />
      <Footer1 parentClass="boxcar-footer footer-style-one v1 cus-st-1" />
    </>
  );
}

function InventorySearchParamsFallback() {
  return (
    <div className="d-flex justify-content-center align-items-center" style={{ minHeight: "100vh" }}>
      <div className="spinner-border text-primary" role="status">
        <span className="visually-hidden">Loading...</span>
      </div>
    </div>
  );
}

export default function InventoryPageRoute() {
  return (
    <Suspense fallback={<InventorySearchParamsFallback />}>
      <InventoryPageContent />
    </Suspense>
  );
}
