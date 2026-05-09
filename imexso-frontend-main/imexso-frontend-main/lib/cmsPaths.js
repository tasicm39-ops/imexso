/**
 * Public URL prefix for legacy CMS images (copied under public/images/cms).
 */
export const CMS_PUBLIC_PREFIX = "/images/cms";

export function cmsImage(relativePath) {
  const p = relativePath.replace(/^\/+/, "");
  return `${CMS_PUBLIC_PREFIX}/${p}`;
}
