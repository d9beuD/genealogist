<script setup lang="ts">
import type { Tree } from "@/api/types";

interface Props {
  tree: Tree;
}

defineProps<Props>();
</script>

<template>
  <div class="tree-list-item">
    <h4>{{ tree.name }}</h4>
  </div>
</template>
