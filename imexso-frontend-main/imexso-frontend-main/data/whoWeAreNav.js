/** Routes grouped under "Who we are" in the main header (authenticated users). */
export const WHO_WE_ARE_NAV_ITEMS = [
  { href: "/installations", labelKey: "nav.installations" },
  { href: "/historique", labelKey: "nav.historique" },
  { href: "/engagements", labelKey: "nav.engagements" },
  { href: "/myimexso/equipe", labelKey: "nav.equipe" },
];

export function isWhoWeAreNavActive(pathname) {
  return WHO_WE_ARE_NAV_ITEMS.some((item) => item.href === pathname);
}
