<script setup lang="ts">
import MemberList from "@/components/lists/MemberList.vue";
import { useTreeStore } from "@/stores/trees";
import { faBarsFilter, faPlus } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { BButton, BFormInput, BToast } from "bootstrap-vue";
import { computed, onMounted, ref } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const treeStore = useTreeStore();
const showDeleteSuccess = ref(false);
const showError = ref(false);
const showAddSuccess = ref(false);
const showUpdateSuccess = ref(false);

const isListCentered = computed(() => {
  return route.name === "treeMembers";
});

onMounted(() => {
  treeStore.loadTree(route.params.treeId as unknown as number);
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
            <BFormInput
              v-model="treeStore.filter"
              class="border-0"
              :placeholder="`${$t('action.search')}...`"
            />
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
        <MemberList />
        <div class="text-center text-secondary py-3">
          {{ $tc("list.memberCount", treeStore.orderedMembers.length) }}
        </div>
      </div>
    </div>
    <div class="col col-scroll">
      <RouterView
        @newMember="showAddSuccess = true"
        @deletedMember="showDeleteSuccess = true"
        @updatedMember="showUpdateSuccess = true"
        @submitError="showError = true"
      />
    </div>
  </div>
  <BToast
    v-model="showAddSuccess"
    variant="success"
    :title="$t('toast.title.success')"
    solid
  >
    {{ $t("toast.body.memberAdded") }}
  </BToast>
  <BToast
    v-model="showUpdateSuccess"
    variant="success"
    :title="$t('toast.title.success')"
    solid
  >
    {{ $t("toast.body.memberUpdated") }}
  </BToast>
  <BToast
    v-model="showDeleteSuccess"
    variant="success"
    :title="$t('toast.title.success')"
    solid
  >
    {{ $t("toast.body.memberDeleted") }}
  </BToast>
  <BToast
    v-model="showError"
    variant="danger"
    :title="$t('toast.title.error')"
    solid
  >
    {{ $t("toast.body.submitError") }}
  </BToast>
</template>
