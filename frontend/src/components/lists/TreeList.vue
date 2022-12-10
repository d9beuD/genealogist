<script setup lang="ts">
import type { Tree } from "@/api/types";
import { faPlusCircle } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import TreeListItem from "./TreeListItem.vue";
interface Props {
  trees?: Tree[];
}

withDefaults(defineProps<Props>(), {
  trees: () => [],
});
</script>

<template>
  <div class="row">
    <div
      class="col-sm-6 col-md-4 col-lg-3"
      v-for="tree in trees"
      :key="tree.id"
    >
      <TreeListItem :tree="tree" />
    </div>
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="tree-list-item tree-list-item-placeholder">
        <FontAwesomeIcon :icon="faPlusCircle" />
      </div>
    </div>
  </div>
</template>
