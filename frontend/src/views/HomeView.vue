<script setup lang="ts">
import api from "@/api";
import type { Tree } from "@/api/types";
import TreeList from "@/components/lists/TreeList.vue";
import { useSessionStore } from "@/stores/session";
import { onMounted, reactive, ref } from "vue";

const sessionStore = useSessionStore();
const trees = reactive<Tree[]>([]);
const isLoading = ref(false);

function loadData() {
  return api.trees
    .list()
    .then((response) => response.data)
    .then((data) => {
      trees.splice(0, trees.length);
      trees.push(...data.data);
    });
}

onMounted(() => {
  isLoading.value = true;
  loadData().finally(() => (isLoading.value = false));
});
</script>

<template>
  <main class="container mb-5">
    <h1>
      {{ $t("page.title.homeWelcome", { name: sessionStore.user?.firstname }) }}
    </h1>
    <TreeList :trees="trees" @need-reload="loadData" />
  </main>
</template>
