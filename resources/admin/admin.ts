import './admin.css';

import i18n from '@/i18n';
import App from '@admin/App.vue';
import router from '@admin/router';
import { createApp } from 'vue';

type Locale = typeof i18n.global.locale.value;

// Same source as the main app's locale store (pinia-persisted under "locale").
function storedLocale(): Locale | null {
    try {
        return JSON.parse(localStorage.getItem('locale') ?? 'null')?.locale;
    } catch {
        return null;
    }
}

const browserLocale = navigator.language.substring(0, 2) as Locale;
i18n.global.locale.value =
    storedLocale() ??
    (i18n.global.availableLocales.includes(browserLocale)
        ? browserLocale
        : 'en');

createApp(App).use(i18n).use(router).mount('#app');
