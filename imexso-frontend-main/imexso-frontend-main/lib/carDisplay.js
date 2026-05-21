/** Fallback when no vehicle photo (legacy Imexso stock image). */
export const DEFAULT_CAR_IMAGE = "/images/no-car.jpg";

export function getCarImageUrl(car) {
  if (car?.photos?.length > 0 && car.photos[0]?.url) {
    return car.photos[0].url;
  }

  return DEFAULT_CAR_IMAGE;
}

export function formatRetentionDate(dateStr) {
  if (!dateStr) {
    return null;
  }

  const date = new Date(dateStr);

  if (Number.isNaN(date.getTime())) {
    return null;
  }

  return date.toLocaleDateString("fr-BE", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}
