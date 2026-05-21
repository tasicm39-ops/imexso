"use client";

import { formatRetentionDate } from "@/lib/carDisplay";

/**
 * Shows XML retention date (date de rétention) in brand orange.
 */
export default function RetentionDisplay({
  retentionDate,
  legacyCode,
  label = "Retention",
  className = "",
}) {
  const formatted = formatRetentionDate(retentionDate);
  const legacy =
    legacyCode && String(legacyCode).trim() !== "" ? String(legacyCode).trim() : null;

  if (!formatted && !legacy) {
    return <span className={className}>N/A</span>;
  }

  const valueText = formatted ?? legacy;

  return (
    <span
      className={`retention-display ${className}`.trim()}
      style={{ color: "#e67e22", fontWeight: 600 }}
    >
      {label ? `${label}: ${valueText}` : valueText}
    </span>
  );
}
