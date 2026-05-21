"use client";

import { cmsImage } from "@/lib/cmsPaths";

/** Image left + tinted text block right, with gutter like imexso.com engagements. */
export default function CmsMediaTextRow({ image, imageFit = "cover", children, footer }) {
  return (
    <div className="cms-media-text-row-wrap">
      <div className="boxcar-container">
        <div className="cms-media-text-row">
          <div className="cms-media-text-row__image">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={cmsImage(image)}
              alt=""
              style={{ objectFit: imageFit }}
            />
          </div>
          <div className="cms-media-text-row__body">
            {children}
            {footer}
          </div>
        </div>
      </div>
    </div>
  );
}
