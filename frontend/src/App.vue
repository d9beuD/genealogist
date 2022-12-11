<script setup lang="ts">
import { RouterView } from "vue-router";
import MainNavbar from "./components/navbars/MainNavbar.vue";
</script>

<template>
  <MainNavbar />

  <div id="container">
    <RouterView />
  </div>
</template>
