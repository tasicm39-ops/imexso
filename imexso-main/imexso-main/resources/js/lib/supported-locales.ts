/** Locales exposed to the public site and admin CMS (must match config/locales.php). */
export const supportedLocales = ['fr', 'nl', 'de', 'it', 'en'] as const;

export type SupportedLocale = (typeof supportedLocales)[number];

/** Announcements require French or English; other locales fall back when left empty. */
export const announcementPrimaryLocales: readonly SupportedLocale[] = ['fr', 'en'];

export function isAnnouncementLocaleOptional(locale: SupportedLocale): boolean {
    return !announcementPrimaryLocales.includes(locale);
}
