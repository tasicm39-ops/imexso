"use client";

/** Subsection title band (car wash, studio, etc.). */
export default function CmsBandTitle({ children }) {
  return (
    <div className="cms-band-title-wrap mb-4">
      <div className="boxcar-container">
        <div className="row">
          <div className="col-12">
            <div className="cms-band-outer">
              <h3 className="cms-band-title mb-0">{children}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
