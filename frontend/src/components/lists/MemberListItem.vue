<script setup lang="ts">
import type { Person } from "@/api/types";
import { faChevronRight } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

interface Props {
  person: Person;
}

const props = defineProps<Props>();
</script>

<template>
  <RouterLink
    class="member-list-item"
    :to="{ name: 'showMember', params: { memberId: props.person.id } }"
  >
    <div class="form-row">
      <div class="col">
        {{ props.person.lastname }} {{ props.person.firstname }}
      </div>
      <div class="col-auto chevron">
        <FontAwesomeIcon :icon="faChevronRight" swap-opacity />
      </div>
    </div>
  </RouterLink>
</template>
