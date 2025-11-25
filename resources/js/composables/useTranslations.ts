import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

interface Translations {
    common: Record<string, string>;
}

interface PageProps {
    locale: string;
    translations: Translations;
}

export function useTranslations() {
    const page = usePage<PageProps>();
    
    const locale = computed(() => page.props.locale || 'en');
    const translations = computed(() => page.props.translations || { common: {} });

    /**
     * Translate a key from translations
     * Supports 'common.key' format or just 'key' (defaults to common namespace)
     */
    const t = (key: string, params?: Record<string, string | number>): string => {
        const keys = key.split('.');
        let value: any = translations.value;

        // If key doesn't have a namespace (like 'common'), default to 'common'
        if (keys.length === 1) {
            value = translations.value.common;
            const k = keys[0];
            if (value && typeof value === 'object' && k in value) {
                value = value[k];
            } else {
                return key; // Return key if translation not found
            }
        } else {
            // Handle nested keys like 'common.status_pending'
            for (const k of keys) {
                if (value && typeof value === 'object' && k in value) {
                    value = value[k];
                } else {
                    return key; // Return key if translation not found
                }
            }
        }

        if (typeof value !== 'string') {
            return key;
        }

        // Replace parameters in translation string (e.g., :name, :count)
        if (params) {
            return value.replace(/:(\w+)/g, (match, paramKey) => {
                return params[paramKey]?.toString() || match;
            });
        }

        return value;
    };

    /**
     * Switch language
     */
    const switchLocale = (newLocale: string) => {
        const currentUrl = window.location.pathname + window.location.search;
        router.visit(`/locale/${newLocale}?redirect=${encodeURIComponent(currentUrl)}`, {
            preserveState: false,
            preserveScroll: false,
        });
    };

    /**
     * Get available locales
     */
    const availableLocales = [
        { code: 'ro', name: 'Română', native: 'Română' },
        { code: 'en', name: 'English', native: 'English' },
        { code: 'ru', name: 'Russian', native: 'Русский' },
    ];

    return {
        locale,
        translations,
        t,
        switchLocale,
        availableLocales,
    };
}

