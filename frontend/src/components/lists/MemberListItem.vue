<script setup lang="ts">
import type { Person } from "@/api/types";
import { faChevronRight } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import MemberIllustration from "../forms/MemberIllustration.vue";

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
    <div class="row no-gutters align-items-center">
      <div class="col-auto">
        <MemberIllustration
          size="sm"
          :src="person.picture"
          :is-male="person.gender"
        />
      </div>
      <div class="col ml-1">
        {{ props.person.lastname }} {{ props.person.firstname }}
      </div>
      <div class="col-auto chevron">
        <FontAwesomeIcon :icon="faChevronRight" swap-opacity />
      </div>
    </div>
  </RouterLink>
</template>
