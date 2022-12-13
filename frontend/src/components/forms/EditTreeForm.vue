<script setup lang="ts">
import api from "@/api";
import type { Tree } from "@/api/types";
import { faSave, faSync } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
  BButton,
  BCard,
  BForm,
  BFormGroup,
  BFormInput,
  BFormInvalidFeedback,
} from "bootstrap-vue";
import { computed, onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

const router = useRouter();
const route = useRoute();

const tree = reactive<Tree>({
  id: 0,
  name: "",
});

const isLoading = ref(false);
const minimumNameLength = ref(3);

const isReady = computed(() => tree.id !== 0);

const nameState = computed(() => {
  if (tree.name.length === 0) {
    return null;
  }

  return tree.name.length >= 3;
});

const isFormValid = computed(() => {
  return nameState.value === null ? false : nameState.value;
});

const submitIcon = computed(() => {
  return isLoading.value ? faSync : faSave;
});

function onSubmit() {
  if (!isFormValid.value) {
    return;
  }

  isLoading.value = true;

  // @todo redirect to tree page
  api.trees
    .edit(tree)
    .then(() => {
      router.push({ name: "home" });
    })
    .finally(() => {
      isLoading.value = false;
    });
}

onMounted(() => {
  api.trees.get(route.params.treeId as string).then((response) => {
    Object.assign(tree, response);
  });
});
</script>

<template>
  <BForm v-if="isReady" @submit.prevent="onSubmit" class="form-full-page mt-5">
    <BCard :title="$t('form.title.createTree')">
      <BFormGroup>
        <BFormInput
          v-model="tree.name"
          :placeholder="$t('form.label.name')"
          :state="nameState"
          required
          trim
        />
        <BFormInvalidFeedback>
          {{ $tc("form.feedback.tooShortLength", minimumNameLength) }}
        </BFormInvalidFeedback>
      </BFormGroup>
      <div class="text-right">
        <BButton type="submit" variant="primary" :disabled="!isFormValid">
          <FontAwesomeIcon
            :icon="submitIcon"
            :swap-opacity="!isLoading"
            :spin="isLoading"
          />
          {{ $t("action.save") }}
        </BButton>
      </div>
    </BCard>
  </BForm>
  <div v-else class="text-center display-4 mt-5">
    <FontAwesomeIcon :icon="faSync" spin />
  </div>
</template>
