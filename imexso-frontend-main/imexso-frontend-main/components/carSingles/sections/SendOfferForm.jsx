"use client";
import React, { useState, useMemo, useCallback } from "react";
import { useLocale } from "@/context/LocaleContext";
import { useAuth } from "@/context/AuthContext";
import { apiRequest } from "@/lib/api";

function formatNumber(num) {
  if (!num && num !== 0) return "0";
  return Number(num).toLocaleString("fr-FR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

export default function SendOfferForm({ carItem }) {
  const { t } = useLocale();
  const { user } = useAuth();
  const [isOpen, setIsOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(null);
  const [errors, setErrors] = useState({});

  const basePrice = parseFloat(carItem.professional_price) || 0;

  const [form, setForm] = useState({
    margin_type: "",
    margin_amount: "",
    vat_rate: "20",
    validity_days: "",
    client_name: "",
    client_email: "",
    message: "",
    setup_fees: "",
    registration_fees: "",
    admin_fees: "",
    bonus_malus: "",
    ww_fees: "",
  });

  const handleChange = useCallback((e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
    setErrors((prev) => ({ ...prev, [name]: null }));
    setSuccess(null);
  }, []);

  const prices = useMemo(() => {
    let priceExclVat = basePrice;
    const marginAmount = parseFloat(form.margin_amount) || 0;
    const vatRate = parseFloat(form.vat_rate) || 0;

    if (form.margin_type === "percentage" && marginAmount > 0) {
      priceExclVat += (basePrice * marginAmount) / 100;
    } else if (form.margin_type === "fixed" && marginAmount > 0) {
      priceExclVat += marginAmount;
    }

    const priceInclVat =
      vatRate > 0
        ? priceExclVat + (priceExclVat * vatRate) / 100
        : priceExclVat;

    return {
      excl_vat: Math.round(priceExclVat * 100) / 100,
      incl_vat: Math.round(priceInclVat * 100) / 100,
    };
  }, [basePrice, form.margin_type, form.margin_amount, form.vat_rate]);

  const buildPayload = useCallback(() => {
    return {
      margin_type: form.margin_type || null,
      margin_amount: form.margin_amount ? parseFloat(form.margin_amount) : null,
      vat_rate: parseFloat(form.vat_rate),
      validity_days: form.validity_days
        ? parseInt(form.validity_days, 10)
        : null,
      price_excl_vat: prices.excl_vat,
      price_incl_vat: prices.incl_vat,
      client_name: form.client_name,
      client_email: form.client_email,
      message: form.message || null,
      setup_fees: form.setup_fees ? parseFloat(form.setup_fees) : 0,
      registration_fees: form.registration_fees
        ? parseFloat(form.registration_fees)
        : 0,
      admin_fees: form.admin_fees ? parseFloat(form.admin_fees) : 0,
      bonus_malus: form.bonus_malus ? parseFloat(form.bonus_malus) : 0,
      ww_fees: form.ww_fees ? parseFloat(form.ww_fees) : 0,
    };
  }, [form, prices]);

  const handleGeneratePdf = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    setSuccess(null);

    try {
      const res = await apiRequest(`/api/cars/${encodeURIComponent(carItem.id_produit)}/offers/pdf`, {
        method: "POST",
        body: JSON.stringify(buildPayload()),
        headers: {
          Accept: "application/pdf",
        },
      });

      if (res.ok) {
        const blob = await res.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `Devis_${carItem.make}_${carItem.model}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        setSuccess(t("offer.pdf_generated"));
      } else {
        const data = await res.json().catch(() => ({}));
        if (data.errors) {
          setErrors(data.errors);
        } else {
          setErrors({ general: data.message || t("offer.error_generating") });
        }
      }
    } catch {
      setErrors({ general: t("offer.error_generating") });
    } finally {
      setLoading(false);
    }
  };

  const handleSendEmail = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    setSuccess(null);

    try {
      const res = await apiRequest(`/api/cars/${encodeURIComponent(carItem.id_produit)}/offers/email`, {
        method: "POST",
        body: JSON.stringify(buildPayload()),
      });

      if (res.ok) {
        setSuccess(t("offer.email_sent"));
      } else {
        const data = await res.json().catch(() => ({}));
        if (data.errors) {
          setErrors(data.errors);
        } else {
          setErrors({ general: data.message || t("offer.error_sending") });
        }
      }
    } catch {
      setErrors({ general: t("offer.error_sending") });
    } finally {
      setLoading(false);
    }
  };

  const isFranceUser = user?.country === "France";

  return (
    <div className="offer-form-box mt-3">
      <div
        className="offer-toggle"
        onClick={() => setIsOpen(!isOpen)}
        style={{
          cursor: "pointer",
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          padding: "12px 16px",
          background: "#002833",
          color: "#fff",
          borderRadius: isOpen ? "8px 8px 0 0" : "8px",
          transition: "border-radius 0.2s",
        }}
      >
        <span style={{ fontWeight: 600, fontSize: "14px" }}>
          {t("offer.send_offer_title")}
        </span>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width={16}
          height={16}
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth={2}
          strokeLinecap="round"
          strokeLinejoin="round"
          style={{
            transform: isOpen ? "rotate(180deg)" : "rotate(0deg)",
            transition: "transform 0.2s",
          }}
        >
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </div>

      {isOpen && (
        <div
          className="offer-form-content"
          style={{
            border: "2px solid #002833",
            borderTop: "none",
            borderRadius: "0 0 8px 8px",
            padding: "20px 16px",
            background: "#f8f9fa",
          }}
        >
          <form onSubmit={(e) => e.preventDefault()}>
            {errors.general && (
              <div
                className="alert alert-danger mb-3"
                style={{ fontSize: "13px", padding: "8px 12px" }}
              >
                {errors.general}
              </div>
            )}
            {success && (
              <div
                className="alert alert-success mb-3"
                style={{ fontSize: "13px", padding: "8px 12px" }}
              >
                {success}
              </div>
            )}

            {/* Margin */}
            <div className="mb-3">
              <label
                className="form-label"
                style={{ fontWeight: 600, fontSize: "13px" }}
              >
                {t("offer.margin")}
              </label>
              <div className="row g-2">
                <div className="col-6">
                  <select
                    name="margin_type"
                    className="form-control form-control-sm"
                    value={form.margin_type}
                    onChange={handleChange}
                  >
                    <option value="">{t("offer.margin_type")}</option>
                    <option value="percentage">%</option>
                    <option value="fixed">€</option>
                  </select>
                </div>
                <div className="col-6">
                  <input
                    type="number"
                    name="margin_amount"
                    className="form-control form-control-sm"
                    placeholder={t("offer.amount")}
                    value={form.margin_amount}
                    onChange={handleChange}
                    min="0"
                    step="0.01"
                  />
                </div>
              </div>
              {errors.margin_type && (
                <small className="text-danger">{errors.margin_type[0]}</small>
              )}
            </div>

            {/* TVA Rate */}
            <div className="mb-3">
              <label
                className="form-label"
                style={{ fontWeight: 600, fontSize: "13px" }}
              >
                {t("offer.vat_rate")}
              </label>
              <select
                name="vat_rate"
                className="form-control form-control-sm"
                value={form.vat_rate}
                onChange={handleChange}
              >
                <option value="20">20%</option>
                <option value="19">19%</option>
                <option value="21">21%</option>
                <option value="22">22%</option>
                <option value="0">TTC</option>
              </select>
              {errors.vat_rate && (
                <small className="text-danger">{errors.vat_rate[0]}</small>
              )}
            </div>

            {/* Validity */}
            <div className="mb-3">
              <label
                className="form-label"
                style={{ fontWeight: 600, fontSize: "13px" }}
              >
                {t("offer.validity")}
              </label>
              <input
                type="number"
                name="validity_days"
                className="form-control form-control-sm"
                placeholder={t("offer.days_count")}
                value={form.validity_days}
                onChange={handleChange}
                min="1"
                max="365"
              />
            </div>

            {/* Price Summary */}
            <div
              className="mb-3 p-2"
              style={{
                background: "#fff",
                borderRadius: "6px",
                border: "1px solid #dee2e6",
              }}
            >
              <div className="d-flex justify-content-between mb-1">
                <span style={{ fontSize: "13px" }}>
                  {t("offer.price_excl_vat")}
                </span>
                <strong style={{ fontSize: "13px" }}>
                  {formatNumber(prices.excl_vat)} €
                </strong>
              </div>
              <div className="d-flex justify-content-between">
                <span style={{ fontSize: "13px", fontWeight: 700 }}>
                  {t("offer.price_incl_vat")}
                </span>
                <strong style={{ fontSize: "14px", color: "#002833" }}>
                  {formatNumber(prices.incl_vat)} €
                </strong>
              </div>
            </div>

            {/* Client Data */}
            <h6
              style={{
                fontWeight: 700,
                fontSize: "13px",
                marginBottom: "10px",
                marginTop: "16px",
              }}
            >
              {t("offer.client_data")}
            </h6>
            <div className="row g-2 mb-2">
              <div className="col-6">
                <input
                  type="text"
                  name="client_name"
                  className="form-control form-control-sm"
                  placeholder={t("offer.client_name")}
                  value={form.client_name}
                  onChange={handleChange}
                />
                {errors.client_name && (
                  <small className="text-danger">
                    {errors.client_name[0]}
                  </small>
                )}
              </div>
              <div className="col-6">
                <input
                  type="email"
                  name="client_email"
                  className="form-control form-control-sm"
                  placeholder={t("offer.client_email")}
                  value={form.client_email}
                  onChange={handleChange}
                />
                {errors.client_email && (
                  <small className="text-danger">
                    {errors.client_email[0]}
                  </small>
                )}
              </div>
            </div>
            <div className="mb-3">
              <textarea
                name="message"
                className="form-control form-control-sm"
                rows={2}
                placeholder={t("offer.message_placeholder")}
                value={form.message}
                onChange={handleChange}
              />
            </div>

            {/* Fees */}
            <h6
              style={{
                fontWeight: 700,
                fontSize: "13px",
                marginBottom: "10px",
              }}
            >
              {t("offer.fees")}
            </h6>
            <div className="mb-2">
              <label style={{ fontSize: "12px" }}>
                {t("offer.setup_fees")}
              </label>
              <input
                type="number"
                name="setup_fees"
                className="form-control form-control-sm"
                placeholder="€"
                value={form.setup_fees}
                onChange={handleChange}
                min="0"
                step="0.01"
              />
            </div>
            <div className="mb-2">
              <label style={{ fontSize: "12px" }}>
                {t("offer.registration_fees")}
              </label>
              <input
                type="number"
                name="registration_fees"
                className="form-control form-control-sm"
                placeholder="€"
                value={form.registration_fees}
                onChange={handleChange}
                min="0"
                step="0.01"
              />
            </div>
            <div className="mb-2">
              <label style={{ fontSize: "12px" }}>
                {t("offer.admin_fees")}
              </label>
              <input
                type="number"
                name="admin_fees"
                className="form-control form-control-sm"
                placeholder="€"
                value={form.admin_fees}
                onChange={handleChange}
                min="0"
                step="0.01"
              />
            </div>

            {isFranceUser && (
              <>
                <div className="mb-2">
                  <label style={{ fontSize: "12px" }}>
                    {t("offer.bonus_malus")}
                  </label>
                  <input
                    type="number"
                    name="bonus_malus"
                    className="form-control form-control-sm"
                    placeholder="€"
                    value={form.bonus_malus}
                    onChange={handleChange}
                    step="0.01"
                  />
                </div>
                <div className="mb-3">
                  <label style={{ fontSize: "12px" }}>
                    {t("offer.ww_fees")}
                  </label>
                  <input
                    type="number"
                    name="ww_fees"
                    className="form-control form-control-sm"
                    placeholder="€"
                    value={form.ww_fees}
                    onChange={handleChange}
                    min="0"
                    step="0.01"
                  />
                </div>
              </>
            )}

            {/* Actions */}
            <div className="d-flex gap-2 mt-3">
              <button
                type="button"
                className="btn btn-sm w-50"
                style={{
                  background: "#002833",
                  color: "#fff",
                  fontSize: "12px",
                  padding: "8px 10px",
                  borderRadius: "6px",
                }}
                onClick={handleGeneratePdf}
                disabled={loading}
              >
                {loading ? "..." : t("offer.generate_pdf")}
              </button>
              <button
                type="button"
                className="btn btn-sm w-50"
                style={{
                  background: "#3b82f6",
                  color: "#fff",
                  fontSize: "12px",
                  padding: "8px 10px",
                  borderRadius: "6px",
                }}
                onClick={handleSendEmail}
                disabled={loading}
              >
                {loading ? "..." : t("offer.send_email")}
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
