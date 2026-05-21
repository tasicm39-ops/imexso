"use client";

/** Subsection title band (dark bar on engagements, etc.). */
export default function CmsBandTitle({ children, variant = "dark" }) {
  return (
    <div className="cms-band-title-wrap">
      <div className="boxcar-container">
        <h3 className={`cms-band-title cms-band-title--${variant} mb-0`}>
          {children}
        </h3>
      </div>
    </div>
  );
}
