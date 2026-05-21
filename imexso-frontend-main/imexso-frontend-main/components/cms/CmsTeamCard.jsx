"use client";

import { cmsImage } from "@/lib/cmsPaths";

export default function CmsTeamCard({ image, name, phone, imageStyle }) {
  return (
    <div className="cms-team-card text-center">
      {/* eslint-disable-next-line @next/next/no-img-element */}
      <img
        src={cmsImage(image)}
        alt={name || ""}
        className="cms-team-card__photo w-100"
        style={imageStyle}
      />
      {(name || phone) && (
        <div className="cms-team-card__info">
          {name && <p className="cms-team-card__name mb-0">{name}</p>}
          {phone && <p className="cms-team-card__phone mb-0">{phone}</p>}
        </div>
      )}
    </div>
  );
}
