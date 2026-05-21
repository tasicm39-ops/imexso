"use client";
import Image from "next/image";
import Link from "next/link";
import React from "react";
import RetentionDisplay from "@/components/common/RetentionDisplay";
import { useLocale } from "@/context/LocaleContext";

function formatMileage(km) {
  if (!km && km !== 0) return "N/A";
  return Number(km).toLocaleString() + " km";
}

export default function Overview({ carItem }) {
  const { t } = useLocale();

  if (!carItem) return null;

  const retentionDetail = carItem.retention_date
    ? [
        {
          icon: "/images/resource/overview-icon-retention.png",
          label: t("car.retention"),
          value: (
            <RetentionDisplay
              retentionDate={carItem.retention_date}
              label=""
              className="overview-retention"
            />
          ),
          width: 18,
          height: 18,
        },
      ]
    : [];

  const vehicleDetails = [
    {
      icon: "/images/resource/insep2-1.svg",
      label: t("car.version"),
      value: carItem.trim_level || "N/A",
      width: 18,
      height: 18,
    },
    {
      icon: "/images/resource/insep1-1.svg",
      label: t("car.body"),
      value: carItem.category || "N/A",
      width: 18,
      height: 18,
    },
    {
      icon: "/images/resource/insep1-2.svg",
      label: t("car.mileage"),
      value: formatMileage(carItem.mileage),
      width: 18,
      height: 18,
    },
    {
      icon: "/images/resource/insep1-3.svg",
      label: t("car.fuel_type"),
      value: carItem.fuel_type || "N/A",
      width: 18,
      height: 18,
    },
    {
      icon: "/images/resource/insep1-4.svg",
      label: t("car.year"),
      value: carItem.manufacturing_year || "N/A",
      width: 16,
      height: 16,
    },
    {
      icon: "/images/resource/insep1-5.svg",
      label: t("car.transmission"),
      value: carItem.gearbox || "N/A",
      width: 16,
      height: 16,
    },
    {
      icon: "/images/resource/insep1-6.svg",
      label: t("car.horsepower"),
      value: carItem.horsepower ? `${carItem.horsepower} HP` : "N/A",
      width: 18,
      height: 18,
    },
    ...retentionDetail,
  ];

  const vehicleAdditionalDetails = [
    {
      icon: "/images/resource/insep1-7.svg",
      label: t("car.condition"),
      value: carItem.condition_type || "N/A",
      width: 18,
      height: 18,
    },
    {
      icon: "/images/resource/insep1-8.svg",
      label: t("car.engine_size"),
      value: carItem.engine_displacement || carItem.engine_code || "N/A",
      width: 18,
      height: 18,
    },
    {
      icon: "/images/resource/insep1-9.svg",
      label: t("car.doors"),
      value: carItem.doors ? `${carItem.doors}` : "N/A",
      width: 18,
      height: 18,
    },
    {
      icon: "/images/resource/overview-icon-co2.svg",
      label: t("car.co2"),
      value: carItem.co2 ? `${carItem.co2} g/km` : "N/A",
      width: 18,
      height: 18,
    },
    {
      icon: "/images/resource/overview-icon-color.svg",
      label: t("car.color"),
      value: carItem.color || "N/A",
      width: 18,
      height: 18,
    },
    {
      icon: "/images/resource/insep1-12.svg",
      label: t("car.vin"),
      value: (
        <Link href="/contact" className="text-decoration-underline">
          {t("car.contact_us")}
        </Link>
      ),
      width: 18,
      height: 18,
    },
  ];

  return (
    <>
      <h4 className="title">{t("car.overview")}</h4>
      <div className="row">
        <div className="content-column col-lg-6 col-md-12 col-sm-12">
          <div className="inner-column">
            <ul className="list">
              {vehicleDetails.map((detail, index) => (
                <li key={index}>
                  <span>
                    <Image
                      src={detail.icon}
                      width={detail.width}
                      height={detail.height}
                      alt=""
                    />
                    {detail.label}
                  </span>
                  {detail.value}
                </li>
              ))}
            </ul>
          </div>
        </div>
        <div className="content-column col-lg-6 col-md-12 col-sm-12">
          <div className="inner-column">
            <ul className="list">
              {vehicleAdditionalDetails.map((detail, index) => (
                <li key={index}>
                  <span>
                    <Image
                      src={detail.icon}
                      width={detail.width}
                      height={detail.height}
                      alt=""
                    />
                    {detail.label}
                  </span>
                  {detail.value}
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </>
  );
}
