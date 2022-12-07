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
} from "@fortawesome/pro-duotone-svg-icons";
import { useI18n } from "vue-i18n";
import { useLocalesStore } from "@/stores/locales";
import { useSessionStore } from "@/stores/session";

const i18n = useI18n();
const localeStore = useLocalesStore();
const sessionStore = useSessionStore();

function changeLocale(locale: string) {
  i18n.locale.value = locale;
  localeStore.setLocale(locale);
}
</script>

<template>
  <BNavbar type="dark" variant="dark" toggleable="md">
    <BNavbarBrand>
      <FontAwesomeIcon :icon="faTreeDeciduous" />
      Genealogist
    </BNavbarBrand>
    <BNavbarToggle target="main-navbar-collapse" />

    <BCollapse id="main-navbar-collapse" is-nav>
      <BNavbarNav>
        <BNavItem>Home</BNavItem>
      </BNavbarNav>

      <BNavbarNav class="ml-auto">
        <BNavItemDropdown right>
          <template #button-content>
            <FontAwesomeIcon :icon="faLanguage" />
            {{ $t("nav.item.language") }}
          </template>
          <BDropdownItem
            v-for="locale in i18n.availableLocales"
            :key="`locale-${locale}`"
            @click="changeLocale(locale)"
          >
            {{ locale }}
          </BDropdownItem>
        </BNavItemDropdown>

        <BNavItemDropdown v-if="sessionStore.isLoggedIn" right>
          <template #button-content>
            <FontAwesomeIcon :icon="faUser" />
            {{ sessionStore.user?.firstname }}
          </template>
        </BNavItemDropdown>
      </BNavbarNav>
    </BCollapse>
  </BNavbar>
</template>
