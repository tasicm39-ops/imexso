"use client";

import { useCallback, useEffect, useState } from "react";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";
import { apiGet } from "@/lib/api";

const STORAGE_PREFIX = "imexso_announcement_dismissed_";

export default function AnnouncementBanner() {
  const { isAuthenticated, isValidated, loading } = useAuth();
  const { locale, t } = useLocale();
  const [items, setItems] = useState([]);

  const load = useCallback(async () => {
    if (!isAuthenticated || !isValidated) {
      setItems([]);
      return;
    }
    try {
      const res = await apiGet(
        `/api/announcements/active?locale=${encodeURIComponent(locale)}`,
      );
      if (!res.ok) {
        setItems([]);
        return;
      }
      const json = await res.json();
      const list = Array.isArray(json.data) ? json.data : [];
      setItems(
        list.filter((row) => {
          if (typeof window === "undefined") {
            return true;
          }
          return (
            localStorage.getItem(`${STORAGE_PREFIX}${row.id}`) !== "1"
          );
        }),
      );
    } catch {
      setItems([]);
    }
  }, [isAuthenticated, isValidated, locale]);

  useEffect(() => {
    if (loading) {
      return;
    }
    load();
  }, [loading, load]);

  function dismiss(id) {
    if (typeof window !== "undefined") {
      localStorage.setItem(`${STORAGE_PREFIX}${id}`, "1");
    }
    setItems((prev) => prev.filter((x) => x.id !== id));
  }

  if (!items.length) {
    return null;
  }

  return (
    <div className="announcement-banner-wrap">
      {items.map((row) => (
        <div key={row.id} className="announcement-banner" role="status">
          <div className="announcement-banner__inner">
            <div>
              <div className="announcement-banner__title">{row.title}</div>
              <div className="announcement-banner__body">{row.body}</div>
            </div>
            <button
              type="button"
              className="announcement-banner__close"
              aria-label={t("general.announcement.dismiss")}
              onClick={() => dismiss(row.id)}
            >
              ×
            </button>
          </div>
        </div>
      ))}
    </div>
  );
}
