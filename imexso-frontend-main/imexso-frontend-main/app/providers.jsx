"use client";

import { Suspense, useEffect } from "react";
import { usePathname } from "next/navigation";
import { AuthProvider } from "@/context/AuthContext";
import { CartProvider } from "@/context/CartContext";
import TrackingProvider from "@/components/tracking/TrackingProvider";
import { LocaleProvider } from "@/context/LocaleContext";
import Context from "@/context/Context";
import MobileMenu from "@/components/headers/MobileMenu";
import FilterSidebar from "@/components/common/FilterSidebar";
import BackToTop from "@/components/common/BackToTop";
import CookieConsent from "@/components/common/CookieConsent";
import AnnouncementBanner from "@/components/common/AnnouncementBanner";

export default function Providers({ children }) {
  const pathname = usePathname();

  useEffect(() => {
    if (typeof window !== "undefined") {
      import("bootstrap/dist/js/bootstrap.esm").then(() => {});
    }

    if (typeof window !== "undefined") {
      const { WOW } = require("wowjs");
      const wow = new WOW({
        mobile: false,
        live: false,
      });
      wow.init();
    }
  }, [pathname]);

  return (
    <>
      <Context>
        <Suspense fallback={null}>
          <LocaleProvider>
            <AuthProvider>
              <CartProvider>
                <TrackingProvider>
                  <MobileMenu />
                  <div className="boxcar-wrapper">
                    <AnnouncementBanner />
                    {children}
                  </div>
                  <FilterSidebar />
                  <CookieConsent />
                </TrackingProvider>
              </CartProvider>
            </AuthProvider>
          </LocaleProvider>
        </Suspense>
      </Context>
      <BackToTop />
    </>
  );
}
