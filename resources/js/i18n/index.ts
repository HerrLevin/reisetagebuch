import { createI18n } from 'vue-i18n';
import de from './locales/de.json';
import en from './locales/en.json';

export type MessageSchema = typeof en;

export default createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',
    messages: { en, de },
});
