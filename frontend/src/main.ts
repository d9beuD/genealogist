import { createApp } from "vue";
import { createPinia } from "pinia";
import { createI18n } from "vue-i18n";
import { useLocalesStore } from "./stores/locales";

import App from "./App.vue";
import router from "./router";

import "@/assets/custom.scss";
import en from "./locales/en";
import fr from "./locales/fr";

// Create Vue app
const app = createApp(App);

// Init Pinia
const pinia = createPinia();
app.use(pinia);
const localeStore = useLocalesStore();

// Init I18n
type MessageSchema = typeof en;
const i18n = createI18n<[MessageSchema], "en" | "fr">({
  locale: localeStore.locale,
  fallbackLocale: "en",
  allowComposition: true,
  messages: {
    en,
    fr,
  },
});
app.use(i18n);

// Use router
app.use(router);

app.mount("#app");
