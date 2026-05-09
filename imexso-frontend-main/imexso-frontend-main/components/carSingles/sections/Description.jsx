import Link from "next/link";
import React from "react";
import { useLocale } from "@/context/LocaleContext";

export default function Description({ carItem }) {
  const { locale, t } = useLocale();

  if (!carItem) return null;

  const statusText =
    carItem.status && typeof carItem.status === "object"
      ? carItem.status[locale] || carItem.status.en || carItem.status.fr || ""
      : "";

  const hasOptionalDescription =
    carItem.trim_level || carItem.catalogue_remark || statusText;

  if (!hasOptionalDescription && !carItem.registration_date) {
    return (
      <>
        <h4 className="title">Description</h4>
        <div className="text">
          <strong>{t("car.vin")}:</strong>{" "}
          <Link href="/contact" className="text-decoration-underline">
            {t("car.contact_us")}
          </Link>
        </div>
      </>
    );
  }

  return (
    <>
      <h4 className="title">Description</h4>
      {carItem.trim_level && (
        <div className="text two">
          <strong>Trim Level:</strong> {carItem.trim_level}
        </div>
      )}
      {carItem.catalogue_remark && (
        <div className="text">{carItem.catalogue_remark}</div>
      )}
      {statusText && <div className="text">{statusText}</div>}
      {carItem.registration_date && (
        <div className="text" style={{ marginTop: "10px" }}>
          <strong>Registration Date:</strong> {carItem.registration_date}
        </div>
      )}
      <div className="text">
        <strong>{t("car.vin")}:</strong>{" "}
        <Link href="/contact" className="text-decoration-underline">
          {t("car.contact_us")}
        </Link>
      </div>
    </>
  );
}
