"use client";

import { createContext, useContext, useState, useEffect, useCallback } from "react";
import { apiGet, apiPost, apiDelete } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";

const CartContext = createContext(null);

export function CartProvider({ children }) {
  const { isAuthenticated, isValidated } = useAuth();
  const [cartCarIds, setCartCarIds] = useState([]);
  const [loading, setLoading] = useState(false);
  const [loaded, setLoaded] = useState(false);

  const cartCount = cartCarIds.length;

  const fetchCart = useCallback(async () => {
    if (!isAuthenticated || !isValidated) {
      setCartCarIds([]);
      setLoaded(false);
      return;
    }
    setLoading(true);
    try {
      const res = await apiGet("/api/cart/car-ids");
      if (res.ok) {
        const data = await res.json();
        setCartCarIds(data.car_ids || []);
      }
    } catch {
      // keep existing state
    } finally {
      setLoading(false);
      setLoaded(true);
    }
  }, [isAuthenticated, isValidated]);

  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

  const isInCart = useCallback(
    (carId) => cartCarIds.includes(carId),
    [cartCarIds],
  );

  const addToCart = useCallback(
    async (carId) => {
      setCartCarIds((prev) =>
        prev.includes(carId) ? prev : [...prev, carId],
      );
      try {
        const res = await apiPost("/api/cart", { car_id: carId });
        if (!res.ok) {
          setCartCarIds((prev) => prev.filter((id) => id !== carId));
        }
      } catch {
        setCartCarIds((prev) => prev.filter((id) => id !== carId));
      }
    },
    [],
  );

  const removeFromCart = useCallback(
    async (carId) => {
      setCartCarIds((prev) => prev.filter((id) => id !== carId));
      try {
        const res = await apiDelete(`/api/cart/${carId}`);
        if (!res.ok) {
          setCartCarIds((prev) =>
            prev.includes(carId) ? prev : [...prev, carId],
          );
        }
      } catch {
        setCartCarIds((prev) =>
          prev.includes(carId) ? prev : [...prev, carId],
        );
      }
    },
    [],
  );

  return (
    <CartContext.Provider
      value={{
        cartCarIds,
        cartCount,
        loading,
        loaded,
        isInCart,
        addToCart,
        removeFromCart,
        refreshCart: fetchCart,
      }}
    >
      {children}
    </CartContext.Provider>
  );
}

export function useCart() {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error("useCart must be used within a CartProvider");
  }
  return context;
}
