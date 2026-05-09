"use client";

import React, { useMemo, useState } from "react";
import { useLocale } from "@/context/LocaleContext";

function getEquipmentList(equipment, locale) {
  if (!equipment) return [];
  if (typeof equipment === "object" && !Array.isArray(equipment)) {
    return equipment[locale] || equipment.en || equipment.fr || [];
  }
  if (Array.isArray(equipment)) return equipment;
  return [];
}

function chunkArray(arr, size) {
  const chunks = [];
  for (let i = 0; i < arr.length; i += size) {
    chunks.push(arr.slice(i, i + size));
  }
  return chunks;
}

/** Same column split as expanded grid: column-major chunks of ceil(n/3) items. */
function buildColumns(items) {
  if (!items.length) return [];
  const colSize = Math.ceil(items.length / 3) || 1;
  return chunkArray(items, colSize);
}

function EquipmentSection({ title, items }) {
  const { t } = useLocale();
  const [expanded, setExpanded] = useState(false);

  const columns = useMemo(() => buildColumns(items), [items]);

  if (!items.length) return null;

  const needsAccordion = columns.some((col) => col.length > 3);

  const fadeLineStyle = {
    WebkitMaskImage:
      "linear-gradient(to bottom, #000 0%, #000 45%, transparent 100%)",
    maskImage:
      "linear-gradient(to bottom, #000 0%, #000 45%, transparent 100%)",
  };

  const showCollapsed = needsAccordion && !expanded;

  return (
    <div className="equipment-section-block" style={{ marginBottom: 28 }}>
      <h6 className="title">{title}</h6>

      <div className="row">
        {columns.map((column, colIndex) => (
          <div
            className="list-column col-lg-4 col-md-6 col-sm-12"
            key={colIndex}
          >
            <div className="inner-column">
              <ul className="feature-list">
                {showCollapsed ? (
                  <>
                    {column.slice(0, 3).map((item, itemIndex) => (
                      <li key={itemIndex}>
                        <i className="fa-solid fa-check" />
                        {item}
                      </li>
                    ))}
                    {column.length > 3 && (
                      <li style={fadeLineStyle}>
                        <i className="fa-solid fa-check" />
                        {column[3]}
                      </li>
                    )}
                  </>
                ) : (
                  column.map((item, itemIndex) => (
                    <li key={itemIndex}>
                      <i className="fa-solid fa-check" />
                      {item}
                    </li>
                  ))
                )}
              </ul>
            </div>
          </div>
        ))}
      </div>

      {needsAccordion && (
        <button
          type="button"
          className="btn btn-link p-0 mt-2 equipment-toggle"
          onClick={() => setExpanded(!expanded)}
          style={{
            fontSize: 15,
            fontWeight: 600,
            color: "#405FF2",
            textDecoration: "none",
          }}
        >
          {expanded ? t("car.show_less") : t("car.show_more")}
        </button>
      )}
    </div>
  );
}

export default function Features({ carItem }) {
  const { locale, t } = useLocale();

  if (!carItem) return null;

  const standardEquipment = getEquipmentList(
    carItem.standard_equipment,
    locale,
  );
  const supplementaryEquipment = getEquipmentList(
    carItem.supplementary_equipment,
    locale,
  );

  const hasFeatures =
    standardEquipment.length > 0 || supplementaryEquipment.length > 0;

  if (!hasFeatures) return null;

  return (
    <>
      <h4 className="title">{t("car.features_equipment")}</h4>
      <div className="row">
        <div className="col-12">
          {supplementaryEquipment.length > 0 && (
            <EquipmentSection
              title={t("car.supplementary_equipment")}
              items={supplementaryEquipment}
            />
          )}
          {standardEquipment.length > 0 && (
            <EquipmentSection
              title={t("car.standard_equipment")}
              items={standardEquipment}
            />
          )}
        </div>
      </div>
    </>
  );
}
