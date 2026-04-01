import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

export const useLocaleSore = defineStore(
    'locale',
    () => {
        const i18n = useI18n();
        const locale = ref<null | string>(null);

        const setLocale = (localeStr: string) => {
            locale.value = localeStr;
            syncLocale();
        };

        const syncLocale = () => {
            if (locale.value === null) {
                setFallbackLocale();
                return;
            }
            i18n.locale.value = locale.value;
        };

        const setFallbackLocale = () => {
            i18n.locale.value = getFallbackLocale();
        };

        const getFallbackLocale = () => {
            const browser = getBrowserLocale().substring(0, 2);
            if (i18n.availableLocales.includes(browser)) {
                return getBrowserLocale();
            } else {
                return 'en';
            }
        };

        const getBrowserLocale = () => {
            return navigator.language;
        };

        return {
            locale,
            setLocale,
            syncLocale,
            getFallbackLocale,
        };
    },
    {
        persist: true,
    },
);
