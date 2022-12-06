<script setup lang="ts">
import {
  BCollapse,
  BNavbar,
  BNavbarBrand,
  BNavbarNav,
  BNavbarToggle,
  BNavItem,
} from "bootstrap-vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faTreeDeciduous } from "@fortawesome/pro-duotone-svg-icons";
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
    </BCollapse>
  </BNavbar>
</template>
