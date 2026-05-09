"use client";

import React, { useEffect } from "react";
import { createPortal } from "react-dom";

/**
 * Full-screen Carlab iframe + close control (same layout as legacy `#ext` / `#iframe_pano`).
 */
export default function Carlab360Modal({ isOpen, onClose, viewerUrl, viewerTitle, closeLabel }) {
  useEffect(() => {
    if (!isOpen) {
      return undefined;
    }
    const html = document.documentElement;
    const prevHtmlOverflow = html.style.overflow;
    const prevBodyOverflow = document.body.style.overflow;
    html.style.overflow = "hidden";
    document.body.style.overflow = "hidden";

    const onKeyDown = (event) => {
      if (event.key === "Escape") {
        onClose();
      }
    };
    window.addEventListener("keydown", onKeyDown);

    return () => {
      html.style.overflow = prevHtmlOverflow;
      document.body.style.overflow = prevBodyOverflow;
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [isOpen, onClose]);

  if (!isOpen || !viewerUrl) {
    return null;
  }

  return createPortal(
    <div
      className="position-fixed border-0 p-0 m-0"
      style={{
        zIndex: 10050,
        top: 0,
        right: 0,
        bottom: 0,
        left: 0,
        background: "#000",
        overflow: "hidden",
      }}
      role="dialog"
      aria-modal="true"
      aria-label={viewerTitle}
    >
      <button
        type="button"
        className="position-absolute border-0 bg-transparent text-white"
        style={{
          top: 20,
          right: 28,
          zIndex: 10060,
          width: 50,
          height: 50,
          padding: 0,
          lineHeight: 1,
        }}
        onClick={onClose}
        aria-label={closeLabel}
      >
        <span aria-hidden="true" style={{ fontSize: 28 }}>
          ×
        </span>
      </button>
      <iframe
        title={viewerTitle}
        src={viewerUrl}
        className="position-fixed border-0 p-0 m-0"
        style={{
          zIndex: 10015,
          top: 0,
          right: 0,
          bottom: 0,
          left: 0,
          width: "100%",
          height: "100%",
        }}
        allowFullScreen
      />
    </div>,
    document.body,
  );
}
