<script setup lang="ts">
import type { Person } from "@/api/types";
import { computed } from "vue";
import MemberListItem from "./MemberListItem.vue";

interface Props {
  members?: Person[];
}

const props = withDefaults(defineProps<Props>(), {
  members: () => [],
});

const orderedMembers = computed(() => {
  return [...props.members].sort((a, b) => {
    return `${a.lastname} ${a.firstname}`.localeCompare(
      `${b.lastname} ${b.firstname}`
    );
  });
});

const groups = computed(() => {
  const list: { [key: string]: Person[] } = {};

  orderedMembers.value.forEach((person) => {
    if (person.lastname === null || person.lastname.length === 0) {
      return;
    }

    const letter = person.lastname.toUpperCase().charAt(0);

    // If the letter does not exists as key object
    if (!Object.keys(list).find((key) => key === letter)) {
      list[letter] = [];
    }

    list[letter].push(person);
  });

  return list;
});
</script>

<template>
  <div class="mb-3" v-for="(group, letter) in groups" :key="letter">
    <div class="bg-white border-bottom font-weight-bold px-3 sticky-top">
      {{ letter }}
    </div>
    <MemberListItem v-for="member in group" :key="member.id" :person="member" />
  </div>
</template>
