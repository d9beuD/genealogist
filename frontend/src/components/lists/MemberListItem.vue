<script setup lang="ts">
import type { Person } from "@/api/types";

interface Props {
  person: Person;
}

const props = defineProps<Props>();
</script>

<template>
  <div class="px-3 py-1">
    {{ props.person.lastname }} {{ props.person.firstname }}
  </div>
</template>
