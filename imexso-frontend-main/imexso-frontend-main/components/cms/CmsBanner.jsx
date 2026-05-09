"use client";

import { cmsImage } from "@/lib/cmsPaths";

export default function CmsBanner({ src, alt = "" }) {
  const full = src.startsWith("/") ? src : cmsImage(src);
  return (
    <div className="cms-banner cms-banner--hero w-100">
      {/* eslint-disable-next-line @next/next/no-img-element -- legacy CMS assets with varying dimensions */}
      <img src={full} alt={alt} className="cms-banner__img w-100 d-block" />
    </div>
  );
}
