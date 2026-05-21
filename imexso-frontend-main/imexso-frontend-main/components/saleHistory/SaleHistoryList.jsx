"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import RetentionDisplay from "@/components/common/RetentionDisplay";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";
import { apiGet } from "@/lib/api";

function formatOrderDate(dateStr) {
  if (!dateStr) {
    return "—";
  }

  const date = new Date(dateStr);

  if (Number.isNaN(date.getTime())) {
    return "—";
  }

  return date.toLocaleDateString("fr-BE", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

export default function SaleHistoryList() {
  const { isAuthenticated, isValidated, user } = useAuth();
  const { t } = useLocale();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const fetchHistory = useCallback(async () => {
    if (!isAuthenticated || !isValidated) {
      setLoading(false);
      return;
    }

    setLoading(true);
    setError("");

    try {
      const res = await apiGet("/api/sale-histories?per_page=50");

      if (!res.ok) {
        setError(t("sale_history.load_error"));
        setItems([]);
        return;
      }

      const json = await res.json();
      setItems(json.data ?? []);
    } catch {
      setError(t("sale_history.load_error"));
      setItems([]);
    } finally {
      setLoading(false);
    }
  }, [isAuthenticated, isValidated, t]);

  useEffect(() => {
    fetchHistory();
  }, [fetchHistory]);

  if (!isAuthenticated || !isValidated) {
    return (
      <p className="text-muted-foreground">{t("sale_history.login_required")}</p>
    );
  }

  const showClientColumn = Boolean(user?.is_admin);

  return (
    <div className="sale-history-list">
      <div className="mb-4">
        <h1 className="fw-bold">{t("sale_history.title")}</h1>
        <p className="mt-2 text-muted-foreground">
          {t("sale_history.client_reference")}:{" "}
          <strong>{user?.legacy_client_id ?? "—"}</strong>
        </p>
        {!user?.legacy_client_id && user?.is_admin && (
          <p className="mt-1 small text-muted-foreground">
            {t("sale_history.admin_no_client_id")}
          </p>
        )}
        {user?.is_admin && (
          <p className="mt-1 small text-muted-foreground">
            {t("sale_history.admin_view_all")}
          </p>
        )}
      </div>

      {loading && <p>{t("general.loading")}</p>}
      {error && <p className="text-danger">{error}</p>}

      {!loading && !error && items.length === 0 && (
        <p className="text-muted-foreground sale-history-empty-msg">
          {t("sale_history.empty")}
        </p>
      )}

      {!loading && !error && items.length > 0 && (
        <div className="table-responsive sale-history-table-wrap">
          <table className="table table-striped align-middle">
            <thead>
              <tr>
                {showClientColumn && (
                  <th>{t("sale_history.client_reference")}</th>
                )}
                <th>{t("sale_history.reference")}</th>
                <th>{t("sale_history.vehicle")}</th>
                <th>{t("car.retention")}</th>
                <th>{t("sale_history.status")}</th>
                <th>{t("sale_history.order_date")}</th>
              </tr>
            </thead>
            <tbody>
              {items.map((item) => (
                <tr key={item.id}>
                  {showClientColumn && (
                    <td className="font-monospace">{item.client_id ?? "—"}</td>
                  )}
                  <td>
                    {item.id_produit ? (
                      <Link href={`/car/${encodeURIComponent(item.id_produit)}`}>
                        {item.id_produit}
                      </Link>
                    ) : (
                      "—"
                    )}
                  </td>
                  <td>
                    {[item.make, item.model, item.trim_level]
                      .filter(Boolean)
                      .join(" ")}
                  </td>
                  <td>
                    <RetentionDisplay retentionDate={item.retention_date} />
                  </td>
                  <td>{item.status ?? "—"}</td>
                  <td>{formatOrderDate(item.order_date)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
