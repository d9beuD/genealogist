import { createI18n } from 'vue-i18n'

import enFormats from './formats/en.json'
import frFormats from './formats/fr.json'
import en from './locales/en.json'
import fr from './locales/fr.json'

export const SUPPORTED_LOCALES = ['en', 'fr'] as const
export type SupportedLocale = (typeof SUPPORTED_LOCALES)[number]

export const DEFAULT_LOCALE: SupportedLocale = 'en'
export const LOCALE_STORAGE_KEY = 'genealogist.locale'

const messages = {
  en,
  fr,
}

const datetimeFormats = {
  en: enFormats.datetime,
  fr: frFormats.datetime,
} as unknown as Record<SupportedLocale, Record<string, Intl.DateTimeFormatOptions>>

const numberFormats = {
  en: enFormats.number,
  fr: frFormats.number,
} as unknown as Record<SupportedLocale, Record<string, Intl.NumberFormatOptions>>

function isSupportedLocale(locale: string): locale is SupportedLocale {
  return SUPPORTED_LOCALES.includes(locale as SupportedLocale)
}

function normalizeLocale(locale: string): string {
  return locale.toLowerCase().split('-')[0] ?? locale
}

export function resolveLocale(): SupportedLocale {
  const storedLocale = globalThis.localStorage?.getItem(LOCALE_STORAGE_KEY)

  if (storedLocale && isSupportedLocale(storedLocale)) {
    return storedLocale
  }

  const browserLocale = globalThis.navigator?.language

  if (browserLocale) {
    const normalizedLocale = normalizeLocale(browserLocale)

    if (isSupportedLocale(normalizedLocale)) {
      return normalizedLocale
    }
  }

  return DEFAULT_LOCALE
}

export const i18n = createI18n({
  legacy: false,
  locale: resolveLocale(),
  fallbackLocale: DEFAULT_LOCALE,
  messages,
  datetimeFormats,
  numberFormats,
})
