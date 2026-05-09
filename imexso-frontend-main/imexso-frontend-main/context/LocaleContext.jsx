"use client";

import { createContext, useContext, useState, useCallback, useEffect } from 'react';
import { useSearchParams } from 'next/navigation';
import { getTranslation, t as translate, supportedLocales, defaultLocale, localeLabels } from '@/lib/i18n';

const LocaleContext = createContext(null);

function updateUrlLang(newLocale) {
    if (typeof window === 'undefined') return;
    const url = new URL(window.location.href);
    url.searchParams.set('lang', newLocale);
    window.history.replaceState({}, '', url.toString());
}

export function LocaleProvider({ children }) {
    const searchParams = useSearchParams();
    const [locale, setLocale] = useState(defaultLocale);

    useEffect(() => {
        const urlLang = searchParams.get('lang');
        if (urlLang && supportedLocales.includes(urlLang)) {
            setLocale(urlLang);
            localStorage.setItem('locale', urlLang);
            document.cookie = `locale=${urlLang};path=/;max-age=31536000`;
            document.documentElement.lang = urlLang;
            return;
        }

        const stored = localStorage.getItem('locale');
        if (stored && supportedLocales.includes(stored)) {
            setLocale(stored);
            document.documentElement.lang = stored;
        }
    }, [searchParams]);

    const changeLocale = useCallback((newLocale) => {
        if (supportedLocales.includes(newLocale)) {
            setLocale(newLocale);
            if (typeof window !== 'undefined') {
                localStorage.setItem('locale', newLocale);
                document.cookie = `locale=${newLocale};path=/;max-age=31536000`;
                document.documentElement.lang = newLocale;
                updateUrlLang(newLocale);
            }
        }
    }, []);

    const t = useCallback((key, params = {}) => {
        return translate(key, locale, params);
    }, [locale]);

    return (
        <LocaleContext.Provider value={{ locale, changeLocale, t, supportedLocales, localeLabels }}>
            {children}
        </LocaleContext.Provider>
    );
}

export function useLocale() {
    const context = useContext(LocaleContext);
    if (!context) {
        throw new Error('useLocale must be used within a LocaleProvider');
    }
    return context;
}
