"use client";

/**
 * Full-width section title strip (Boxcar palette).
 */
export default function CmsSectionHeader({ children, className = "" }) {
  return (
    <div className={`cms-section-header ${className}`}>
      <div className="boxcar-container">
        <div className="row">
          <div className="col-12">
            <h2 className="cms-section-header__title mb-0">{children}</h2>
          </div>
        </div>
      </div>
    </div>
  );
}
