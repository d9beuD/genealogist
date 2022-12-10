<script setup lang="ts">
import api from "@/api";
import type { treeForm, User } from "@/api/types";
import { useSessionStore } from "@/stores/session";
import { faPlus, faSync } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
  BButton,
  BCard,
  BForm,
  BFormGroup,
  BFormInput,
  BFormInvalidFeedback,
} from "bootstrap-vue";
import { computed, reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const tree = reactive<treeForm>({
  name: "",
});

const isLoading = ref(false);
const minimumNameLength = ref(3);

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
  return isLoading.value ? faSync : faPlus;
});

function onSubmit() {
  if (!isFormValid.value) {
    return;
  }

  isLoading.value = true;

  // @todo redirect to tree page
  api.trees
    .add(tree)
    .then(() => {
      router.push({ name: "home" });
    })
    .finally(() => {
      isLoading.value = false;
    });
}
</script>

<template>
  <BForm @submit.prevent="onSubmit" class="form-full-page mt-5">
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
          {{ $t("action.create") }}
        </BButton>
      </div>
    </BCard>
  </BForm>
</template>
