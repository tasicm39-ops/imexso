"use client";

import Image from "next/image";
import { useState } from "react";
import { DEFAULT_CAR_IMAGE } from "@/lib/carDisplay";

const VARIANT_STYLES = {
  list: {
    objectFit: "cover",
    height: "100%",
    width: "100%",
  },
  grid: {
    objectFit: "contain",
    height: "100%",
    width: "100%",
    background: "#f5f5f5",
  },
  card: {
    objectFit: "contain",
    width: "100%",
    height: "100%",
    background: "#f5f5f5",
  },
};

export default function CarPhoto({
  src,
  alt,
  width = 400,
  height = 240,
  variant = "list",
  className,
}) {
  const [failed, setFailed] = useState(false);
  const resolved = failed ? DEFAULT_CAR_IMAGE : src || DEFAULT_CAR_IMAGE;
  const style =
    variant === "slider"
      ? { width: "100%", height: "auto", objectFit: "cover" }
      : VARIANT_STYLES[variant] || VARIANT_STYLES.list;

  return (
    <Image
      alt={alt}
      src={resolved}
      width={width}
      height={height}
      className={className}
      style={style}
      unoptimized
      onError={() => {
        if (!failed) {
          setFailed(true);
        }
      }}
    />
  );
}
