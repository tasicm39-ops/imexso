import en from '@/messages/en.json';
import fr from '@/messages/fr.json';
import nl from '@/messages/nl.json';
import de from '@/messages/de.json';
import it from '@/messages/it.json';

const translations = { en, fr, nl, de, it };

export function getTranslation(locale = 'fr') {
    return translations[locale] || translations.fr;
}

export function t(key, locale = 'fr', params = {}) {
    const messages = getTranslation(locale);
    const keys = key.split('.');
    let value = messages;
    for (const k of keys) {
        value = value?.[k];
    }
    if (typeof value !== 'string') return key;

    return Object.entries(params).reduce(
        (str, [param, val]) => str.replace(`{${param}}`, String(val)),
        value
    );
}

export const supportedLocales = ['fr', 'nl', 'de', 'it', 'en'];
export const defaultLocale = 'fr';

export const localeLabels = {
    fr: 'Français',
    nl: 'Nederlands',
    de: 'Deutsch',
    it: 'Italiano',
    en: 'English',
};
