import { getBrowserLanguage } from "@/utils/locales";
import { defineStore } from "pinia";
import { reactive, ref } from "vue";

export const useLocalesStore = defineStore("locales", () => {
  const locale = ref(
    localStorage.getItem("app.settings.locale") || getBrowserLanguage()
  );
  const availableLocales = reactive({
    en: "English",
    fr: "Français",
  });

  function setLocale(newLocale: string) {
    locale.value = newLocale;
    localStorage.setItem("app.settings.locale", newLocale);
  }

  return { locale, availableLocales, setLocale };
});
