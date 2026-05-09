"use client";

import React, { useState } from "react";
import { apiPost } from "@/lib/api";
import { trackEvent, EventTypes } from "@/lib/tracking";
import { useLocale } from "@/context/LocaleContext";

export default function SaveSearchButton({ activeFilters, onSaved }) {
  const { t } = useLocale();
  const [showModal, setShowModal] = useState(false);
  const [name, setName] = useState("");
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);

  const hasActiveFilters = Object.entries(activeFilters).some(
    ([, v]) => v !== "" && v !== null && v !== undefined
  );

  if (!hasActiveFilters) return null;

  async function handleSave() {
    setSaving(true);
    try {
      const res = await apiPost("/api/saved-searches", {
        name: name || null,
        filters: activeFilters,
      });
      if (res.ok) {
        setSaved(true);
        setShowModal(false);
        setName("");
        trackEvent(EventTypes.SAVE_SEARCH, { filters: activeFilters });
        if (onSaved) onSaved();
        setTimeout(() => setSaved(false), 3000);
      }
    } catch {
      // Silently fail
    } finally {
      setSaving(false);
    }
  }

  return (
    <>
      <button
        type="button"
        className={`save-search-btn ${saved ? "save-search-btn--saved" : ""}`}
        onClick={() => (saved ? null : setShowModal(true))}
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width={14}
          height={14}
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth={2}
          strokeLinecap="round"
          strokeLinejoin="round"
        >
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
          <polyline points="17 21 17 13 7 13 7 21" />
          <polyline points="7 3 7 8 15 8" />
        </svg>
        {saved ? t("filters.search_saved") : t("filters.save_search")}
      </button>

      {showModal && (
        <div
          className="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
          style={{ zIndex: 9999, background: "rgba(0,0,0,0.5)" }}
          onClick={() => setShowModal(false)}
        >
          <div
            className="bg-white rounded-3 p-4"
            style={{ maxWidth: 400, width: "90%" }}
            onClick={(e) => e.stopPropagation()}
          >
            <h5 className="mb-3">{t("filters.save_search")}</h5>
            <div className="mb-3">
              <label className="form-label" style={{ fontSize: 13 }}>
                {t("filters.save_search_name")}
              </label>
              <input
                type="text"
                className="form-control"
                value={name}
                onChange={(e) => setName(e.target.value)}
                placeholder="e.g. BMW Diesel under 30k"
                maxLength={255}
                autoFocus
              />
            </div>
            <div className="mb-3">
              <p style={{ fontSize: 12, color: "#888" }}>
                {Object.entries(activeFilters)
                  .filter(([, v]) => v !== "" && v !== null && v !== undefined)
                  .map(([k, v]) => `${k}: ${v}`)
                  .join(", ")}
              </p>
            </div>
            <div className="d-flex gap-2 justify-content-end">
              <button
                type="button"
                className="btn btn-sm btn-outline-secondary"
                onClick={() => setShowModal(false)}
              >
                {t("general.cancel")}
              </button>
              <button
                type="button"
                className="btn btn-sm btn-primary"
                onClick={handleSave}
                disabled={saving}
              >
                {saving ? t("general.loading") : t("general.save")}
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
