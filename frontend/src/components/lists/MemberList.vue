<script setup lang="ts">
import { useTreeStore } from "@/stores/trees";
import MemberListItem from "./MemberListItem.vue";

const treeStore = useTreeStore();
</script>

<template>
  <div
    class="mb-3"
    v-for="(group, letter) in treeStore.groupedMembers"
    :key="letter"
  >
    <div class="bg-white border-bottom font-weight-bold px-3 sticky-top">
      {{ letter }}
    </div>
    <MemberListItem v-for="member in group" :key="member.id" :person="member" />
  </div>
</template>
