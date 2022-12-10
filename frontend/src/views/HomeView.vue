<script setup lang="ts">
import type { Tree } from "@/api/types";
import TreeList from "@/components/lists/TreeList.vue";
import { useSessionStore } from "@/stores/session";
import { reactive } from "vue";

const sessionStore = useSessionStore();
const trees = reactive<Tree[]>([]);
</script>

<template>
  <main class="container">
    <h1>
      {{ $t("page.title.homeWelcome", { name: sessionStore.user?.firstname }) }}
    </h1>
    <TreeList :trees="trees" />
  </main>
</template>
