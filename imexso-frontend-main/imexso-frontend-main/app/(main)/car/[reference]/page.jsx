"use client";

import React, { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import Single1 from "@/components/carSingles/Single1";
import { apiGet } from "@/lib/api";
import { trackEvent, EventTypes } from "@/lib/tracking";

async function fetchCarByReference(reference) {
  const res = await apiGet(`/api/cars/${encodeURIComponent(reference)}`);
  if (!res.ok) return { ok: false };
  const json = await res.json();
  return { ok: true, data: json.data };
}

export default function CarSinglePage() {
  const params = useParams();
  const [car, setCar] = useState(null);
  const [viewerCount, setViewerCount] = useState(1);
  const [isFavourited, setIsFavourited] = useState(false);
  const [cartOtherUsersCount, setCartOtherUsersCount] = useState(0);
  const [isInCart, setIsInCart] = useState(false);
  const [loading, setLoading] = useState(true);
  const [notFound, setNotFound] = useState(false);

  useEffect(() => {
    if (!params?.reference) return;

    async function loadCar() {
      try {
        const result = await fetchCarByReference(params.reference);
        if (result.ok) {
          setCar(result.data);
          setViewerCount(result.data?.viewer_count ?? 1);
          setIsFavourited(result.data?.is_favourited ?? false);
          setCartOtherUsersCount(result.data?.cart_other_users_count ?? 0);
          setIsInCart(result.data?.is_in_cart ?? false);
          trackEvent(EventTypes.VIEW_CAR, {
            car_id: result.data?.id,
            make: result.data?.make,
            model: result.data?.model,
          });
        } else {
          setNotFound(true);
        }
      } catch {
        setNotFound(true);
      } finally {
        setLoading(false);
      }
    }

    loadCar();
  }, [params?.reference]);

  async function handleCartUpdated() {
    if (!params?.reference) return;
    const result = await fetchCarByReference(params.reference);
    if (result.ok) {
      setCartOtherUsersCount(result.data?.cart_other_users_count ?? 0);
      setIsInCart(result.data?.is_in_cart ?? false);
    }
  }

  return (
    <>
      <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
      {loading ? (
        <section
          className="inventory-section layout-radius"
          style={{ minHeight: "60vh" }}
        >
          <div className="boxcar-container">
            <div
              className="d-flex justify-content-center align-items-center"
              style={{ minHeight: "50vh" }}
            >
              <div className="spinner-border" role="status">
                <span className="visually-hidden">Loading...</span>
              </div>
            </div>
          </div>
        </section>
      ) : notFound || !car ? (
        <section
          className="inventory-section layout-radius"
          style={{ minHeight: "60vh" }}
        >
          <div className="boxcar-container">
            <div
              className="d-flex flex-column justify-content-center align-items-center"
              style={{ minHeight: "50vh" }}
            >
              <h2>Car Not Found</h2>
              <p className="mt-3">
                The vehicle you are looking for does not exist or has been
                removed.
              </p>
              <a
                href="/inventory"
                className="theme-btn btn-style-one mt-4"
              >
                <span className="btn-title">Back to Inventory</span>
              </a>
            </div>
          </div>
        </section>
      ) : (
        <Single1
          carItem={car}
          viewerCount={viewerCount}
          isFavourited={isFavourited}
          cartOtherUsersCount={cartOtherUsersCount}
          isInCart={isInCart}
          onCartChange={handleCartUpdated}
        />
      )}
      <Footer1 parentClass="boxcar-footer footer-style-one v1 cus-st-1" />
    </>
  );
}
