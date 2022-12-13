<script setup lang="ts">
import {
  BCollapse,
  BDropdownItem,
  BNavbar,
  BNavbarBrand,
  BNavbarNav,
  BNavbarToggle,
  BNavItem,
  BNavItemDropdown,
} from "bootstrap-vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
  faTreeDeciduous,
  faLanguage,
  faUser,
  faPowerOff,
} from "@fortawesome/pro-duotone-svg-icons";
import { useI18n } from "vue-i18n";
import { useLocalesStore } from "@/stores/locales";
import { useSessionStore } from "@/stores/session";
import api from "@/api";
import { useRouter } from "vue-router";

const i18n = useI18n();
const localeStore = useLocalesStore();
const sessionStore = useSessionStore();
const router = useRouter();

function changeLocale(locale: string) {
  i18n.locale.value = locale;
  localeStore.setLocale(locale);
}

function logout() {
  api.session.logout().then((response) => {
    if (response === null) {
      sessionStore.logout();
      router.push({ name: "login" });
    }
  });
}
</script>

<template>
  <BNavbar type="dark" variant="dark" toggleable="md">
    <BNavbarBrand :to="{ name: 'home' }">
      <FontAwesomeIcon :icon="faTreeDeciduous" />
      Genealogist
    </BNavbarBrand>
    <BNavbarToggle target="main-navbar-collapse" />

    <BCollapse id="main-navbar-collapse" is-nav>
      <BNavbarNav>
        <BNavItem :to="{ name: 'home' }">Home</BNavItem>
      </BNavbarNav>

      <BNavbarNav class="ml-auto">
        <BNavItemDropdown right>
          <template #button-content>
            <FontAwesomeIcon :icon="faLanguage" />
            {{ $t("nav.item.language") }}
          </template>

          <BDropdownItem
            v-for="(locale, code) in localeStore.availableLocales"
            :key="code"
            @click="changeLocale(code)"
          >
            {{ locale }}
          </BDropdownItem>
        </BNavItemDropdown>

        <BNavItemDropdown v-if="sessionStore.isLoggedIn" right>
          <template #button-content>
            <FontAwesomeIcon :icon="faUser" />
            {{ sessionStore.user?.firstname }}
          </template>

          <BDropdownItem @click="logout">
            <FontAwesomeIcon :icon="faPowerOff" fixed-width />
            {{ $t("action.logout") }}
          </BDropdownItem>
        </BNavItemDropdown>
      </BNavbarNav>
    </BCollapse>
  </BNavbar>
</template>
