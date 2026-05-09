"use client";

import React, { useState } from "react";
import { apiPost } from "@/lib/api";
import { trackEvent, EventTypes } from "@/lib/tracking";

export default function FavouriteButton({ carId, initialFavourited = false, size = "normal", onToggle }) {
  const [favourited, setFavourited] = useState(initialFavourited);
  const [loading, setLoading] = useState(false);

  async function handleToggle(e) {
    e.preventDefault();
    e.stopPropagation();
    if (loading) return;

    setLoading(true);
    try {
      const res = await apiPost("/api/favourites", { car_id: carId });
      if (res.ok) {
        const data = await res.json();
        const isFav = data.favourited !== false;
        setFavourited(isFav);
        trackEvent(EventTypes.FAVOURITE, { car_id: carId, favourited: isFav });
        if (onToggle) onToggle(isFav);
      }
    } catch {
      // Silently fail
    } finally {
      setLoading(false);
    }
  }

  const sizeClass = size === "small" ? "favourite-btn--small" : "";

  return (
    <button
      type="button"
      className={`favourite-btn ${sizeClass} ${favourited ? "favourite-btn--active" : ""}`}
      onClick={handleToggle}
      disabled={loading}
      title={favourited ? "Remove from favourites" : "Add to favourites"}
      aria-label={favourited ? "Remove from favourites" : "Add to favourites"}
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width={size === "small" ? 16 : 20}
        height={size === "small" ? 16 : 20}
        viewBox="0 0 24 24"
        fill={favourited ? "currentColor" : "none"}
        stroke="currentColor"
        strokeWidth={2}
        strokeLinecap="round"
        strokeLinejoin="round"
      >
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
      </svg>
    </button>
  );
}
