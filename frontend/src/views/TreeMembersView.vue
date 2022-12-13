<script setup lang="ts">
import api from "@/api";
import type { Person } from "@/api/types";
import MemberList from "@/components/lists/MemberList.vue";
import { faBarsFilter, faPlus } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { BButton, BFormInput } from "bootstrap-vue";
import { computed, onMounted, reactive, ref } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const members = reactive<Person[]>([]);
const isLoading = ref(false);

const isListCentered = computed(() => {
  return route.name === "treeMembers";
});

function loadMembers() {
  return api.people
    .getTreeMembers(route.params.treeId as unknown as number)
    .then((data) => {
      members.splice(0, members.length);
      members.push(...data.data);
    });
}

onMounted(() => {
  isLoading.value = true;
  loadMembers().finally(() => (isLoading.value = false));
});
</script>

<template>
  <div class="row no-gutters flex-scroll">
    <div
      class="col-sm-6 col-md-4 col-lg-3 col-scroll col-sidebar"
      :class="{ 'd-none d-sm-flex': !isListCentered }"
    >
      <div class="bg-white border-bottom py-1">
        <div class="row no-gutters align-items-center">
          <div class="col-auto">
            <div class="px-3">
              <FontAwesomeIcon :icon="faBarsFilter" />
            </div>
          </div>
          <div class="col">
            <BFormInput class="border-0" placeholder="search..." />
          </div>
          <div class="col-auto">
            <div class="">
              <BButton variant="link" :to="{ name: 'newTreeMember' }">
                <FontAwesomeIcon :icon="faPlus" size="lg" swap-opacity />
              </BButton>
            </div>
          </div>
        </div>
      </div>
      <div class="flex-scroll">
        <MemberList :members="members" />
        <div class="text-center text-secondary py-3">
          {{ $tc("list.memberCount", members.length) }}
        </div>
      </div>
    </div>
    <div class="col col-scroll">
      <RouterView @newMember="loadMembers" @updatedMember="loadMembers" />
    </div>
  </div>
</template>
