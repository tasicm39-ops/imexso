"use client";

import { useEffect, useState } from "react";
import { useLocale } from "@/context/LocaleContext";
import { apiGet } from "@/lib/api";

/**
 * Fetches published CMS payload from Laravel (`GET /api/site-pages/{slug}`).
 * Returns `null` when the page is unpublished (404) or the request fails.
 * Optional `payload` fields (e.g. `bannerSrc`) can override defaults in page components.
 */
export function useSitePageCms(slug) {
  const { locale } = useLocale();
  const [data, setData] = useState(null);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const res = await apiGet(
          `/api/site-pages/${encodeURIComponent(slug)}?locale=${encodeURIComponent(locale)}`,
        );
        if (cancelled) {
          return;
        }
        if (res.status === 404) {
          setData(null);
          return;
        }
        if (!res.ok) {
          setData(null);
          return;
        }
        const json = await res.json();
        if (!cancelled) {
          setData(json);
        }
      } catch {
        if (!cancelled) {
          setData(null);
        }
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [slug, locale]);

  return data;
}
