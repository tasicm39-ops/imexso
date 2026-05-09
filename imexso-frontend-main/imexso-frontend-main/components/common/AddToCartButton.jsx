"use client";

import React, { useState } from "react";
import { trackEvent, EventTypes } from "@/lib/tracking";
import { useLocale } from "@/context/LocaleContext";
import { useCart } from "@/context/CartContext";

export default function AddToCartButton({
  carId,
  initialInCart = false,
  size = "normal",
  disabled = false,
  variant = "circle",
  onChange,
}) {
  const { t } = useLocale();
  const { isInCart, addToCart, removeFromCart, loaded } = useCart();
  const [loading, setLoading] = useState(false);

  const inCart = loaded ? isInCart(carId) : initialInCart;

  async function handleClick(e) {
    e.preventDefault();
    e.stopPropagation();
    if (loading || disabled) return;

    setLoading(true);
    try {
      if (inCart) {
        await removeFromCart(carId);
        trackEvent(EventTypes.REMOVE_FROM_CART, { car_id: carId });
      } else {
        await addToCart(carId);
        trackEvent(EventTypes.ADD_TO_CART, { car_id: carId });
      }
      if (onChange) onChange();
    } catch {
      // Silently fail
    } finally {
      setLoading(false);
    }
  }

  if (variant === "button") {
    return (
      <button
        type="button"
        className={`cart-action-btn ${inCart ? "cart-action-btn--active" : ""}`}
        onClick={handleClick}
        disabled={loading || disabled}
        title={inCart ? t("car.remove_from_cart") : t("car.add_to_cart")}
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
          aria-hidden
        >
          <circle cx="9" cy="21" r="1" />
          <circle cx="20" cy="21" r="1" />
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
        </svg>
        {inCart ? t("car.in_selection") : t("car.select_vehicle")}
      </button>
    );
  }

  const sizeClass = size === "small" ? "cart-btn--small" : "";

  return (
    <button
      type="button"
      className={`cart-btn ${sizeClass} ${inCart ? "cart-btn--active" : ""}`}
      onClick={handleClick}
      disabled={loading || disabled}
      title={inCart ? t("car.remove_from_cart") : t("car.add_to_cart")}
      aria-label={inCart ? t("car.remove_from_cart") : t("car.add_to_cart")}
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width={size === "small" ? 16 : 20}
        height={size === "small" ? 16 : 20}
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth={2}
        strokeLinecap="round"
        strokeLinejoin="round"
        aria-hidden
      >
        <circle cx="9" cy="21" r="1" />
        <circle cx="20" cy="21" r="1" />
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
      </svg>
    </button>
  );
}
