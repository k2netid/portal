export interface LocaleConfig {
    code: string;
    name: string;
    flag: string;
}

export interface I18nConfig {
    legacy: boolean;
    locale: string;
    fallbackLocale: string | string[] | Record<string, string[]>;
    availableLocales: LocaleConfig[];
    fallbackWarn: boolean;
    missingWarn: boolean;
}

const config: I18nConfig = {
    legacy: false,
    locale: 'id',
    fallbackLocale: {
        su: ['id', 'en'],
        default: ['id', 'en'],
    },
    availableLocales: [
        { code: 'id', name: 'Bahasa Indonesia', flag: '🇮🇩' },
        { code: 'en', name: 'English', flag: '🇬🇧' },
        { code: 'su', name: 'Basa Sunda', flag: '🇮🇩' },
    ],
    fallbackWarn: false,
    missingWarn: false,
};

export default config;
