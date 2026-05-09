/**
 * Carlab 360° viewer URLs use the same product id as XML `id_produit`
 * (e.g. N202242 → https://360-node-3.carlab.fr/N202242).
 */
const DEFAULT_CARLAB_360_BASE = "https://360-node-3.carlab.fr";

function getCarlab360BaseUrl() {
  const fromEnv =
    typeof process !== "undefined"
      ? process.env.NEXT_PUBLIC_CARLAB_360_BASE_URL
      : undefined;
  const raw = (fromEnv || DEFAULT_CARLAB_360_BASE).trim();
  return raw.replace(/\/+$/, "");
}

/**
 * @param {unknown} idProduit
 * @returns {string|null} Absolute viewer URL, or null if id is missing or invalid.
 */
export function buildCarlab360ViewerUrl(idProduit) {
  const id = String(idProduit ?? "").trim();
  if (!id) {
    return null;
  }
  return `${getCarlab360BaseUrl()}/${encodeURIComponent(id)}`;
}
