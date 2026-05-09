"use client";

import React, { useState, useEffect, useCallback } from "react";
import { apiGet, apiDelete } from "@/lib/api";
import { useLocale } from "@/context/LocaleContext";

export default function SavedSearchesList({ onLoadSearch, refreshKey = 0 }) {
  const { t } = useLocale();
  const [searches, setSearches] = useState([]);
  const [loading, setLoading] = useState(true);
  const [expanded, setExpanded] = useState(false);

  const fetchSearches = useCallback(async () => {
    try {
      const res = await apiGet("/api/saved-searches?per_page=50");
      if (res.ok) {
        const json = await res.json();
        setSearches(json.data || []);
      }
    } catch {
      // Silently fail
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchSearches();
  }, [fetchSearches, refreshKey]);

  async function handleDelete(id) {
    try {
      const res = await apiDelete(`/api/saved-searches/${id}`);
      if (res.ok) {
        setSearches((prev) => prev.filter((s) => s.id !== id));
      }
    } catch {
      // Silently fail
    }
  }

  function formatFilters(filters) {
    if (!filters) return "";
    return Object.entries(filters)
      .filter(([, v]) => v !== "" && v !== null && v !== undefined)
      .map(([k, v]) => `${k}: ${v}`)
      .join(", ");
  }

  if (loading || searches.length === 0) return null;

  return (
    <div className="saved-searches-panel">
      <button
        type="button"
        className="d-flex align-items-center justify-content-between w-100 border-0 bg-transparent p-0 mb-2"
        style={{ cursor: "pointer" }}
        onClick={() => setExpanded(!expanded)}
      >
        <span style={{ fontWeight: 600, fontSize: 14 }}>
          {t("filters.saved_searches")} ({searches.length})
        </span>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width={16}
          height={16}
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth={2}
          style={{
            transform: expanded ? "rotate(180deg)" : "rotate(0deg)",
            transition: "transform 0.2s ease",
          }}
        >
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </button>

      {expanded && (
        <div>
          {searches.map((search) => (
            <div key={search.id} className="saved-search-item">
              <div>
                <div className="saved-search-name">
                  {search.name || "Unnamed Search"}
                </div>
                <div className="saved-search-filters">
                  {formatFilters(search.filters)}
                </div>
              </div>
              <div className="saved-search-actions">
                <button
                  type="button"
                  className="saved-search-btn"
                  onClick={() => onLoadSearch(search.filters)}
                >
                  {t("filters.load_search")}
                </button>
                <button
                  type="button"
                  className="saved-search-btn saved-search-btn--delete"
                  onClick={() => handleDelete(search.id)}
                >
                  {t("filters.delete_search")}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
