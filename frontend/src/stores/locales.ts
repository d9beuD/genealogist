import { getBrowserLanguage } from "@/utils/locales";
import { defineStore } from "pinia";
import { ref } from "vue";

export const useLocalesStore = defineStore("locales", () => {
  const locale = ref(
    localStorage.getItem("app.settings.locale") || getBrowserLanguage()
  );

  function setLocale(newLocale: string) {
    locale.value = newLocale;
    localStorage.setItem("app.settings.locale", newLocale);
  }

  return { locale, setLocale };
});
