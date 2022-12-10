<script setup lang="ts">
import type { Tree } from "@/api/types";
import { faPlusCircle } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import TreeListItem from "./TreeListItem.vue";
interface Props {
  trees?: Tree[];
}

interface Emits {
  (e: "needReload"): void;
}

withDefaults(defineProps<Props>(), {
  trees: () => [],
});

const emit = defineEmits<Emits>();

function askReload() {
  emit("needReload");
}
</script>

<template>
  <div class="row">
    <div
      class="col-sm-6 col-md-4 col-lg-3"
      v-for="tree in trees"
      :key="tree.id"
    >
      <TreeListItem :tree="tree" @removed="askReload" />
    </div>
    <div class="col-sm-6 col-md-4 col-lg-3">
      <RouterLink
        class="tree-list-item tree-list-item-placeholder"
        :to="{ name: 'newTree' }"
      >
        <FontAwesomeIcon :icon="faPlusCircle" />
      </RouterLink>
    </div>
  </div>
</template>
