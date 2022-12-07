<script setup lang="ts">
import { computed, ref } from "vue";
import {
  BAlert,
  BButton,
  BCard,
  BForm,
  BFormGroup,
  BFormInput,
  BFormInvalidFeedback,
} from "bootstrap-vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faRightToBracket } from "@fortawesome/pro-duotone-svg-icons";
import { RouterLink } from "vue-router";
import api from "@/api";
import { useSessionStore } from "@/stores/session";
import type { AxiosError } from "axios";

const sessionStore = useSessionStore();

const email = ref("");
const password = ref("");
const error = ref(null as string | null);
const minPasswordLength = ref(8);
const isLoading = ref(false);

const isPasswordFilled = computed(() => {
  return password.value.length > 0;
});

const isPasswordLengthCorrect = computed(() => {
  return password.value.length >= minPasswordLength.value;
});

const passwordState = computed(() => {
  if (!isPasswordFilled.value) {
    return null;
  }

  return isPasswordLengthCorrect.value;
});

const isFormValid = computed(() => {
  return email.value.length > 0 && isPasswordLengthCorrect;
});

function onSubmit() {
  if (!isFormValid.value) {
    return;
  }

  error.value = null;
  isLoading.value = true;

  api.session
    .login({ username: email.value, password: password.value })
    .then((response) => response.data)
    .then((data) => {
      sessionStore.setUser(data);
    })
    .catch((apiError: AxiosError) => {
      error.value = apiError.message;
      console.log(apiError);
    })
    .finally(() => {
      isLoading.value = false;
    });
}
</script>

<template>
  <BForm @submit.prevent="onSubmit" class="form-full-page mt-5">
    <BCard :title="$t('form.title.login')">
      <BFormGroup :label="$t('form.label.identifier')">
        <BFormInput
          v-model="email"
          type="email"
          :placeholder="$t('form.label.identifier')"
        />
      </BFormGroup>

      <BFormGroup :label="$t('form.label.password')">
        <BFormInput
          v-model="password"
          type="password"
          :placeholder="$t('form.label.password')"
          :state="passwordState"
        />
        <BFormInvalidFeedback>
          {{ $tc("form.feedback.tooShortLength", minPasswordLength) }}.
        </BFormInvalidFeedback>
      </BFormGroup>

      <div class="row no-gutters">
        <div class="col">
          <RouterLink :to="{ name: 'login' }">
            {{ $t("form.message.noAccount") }}
          </RouterLink>
        </div>
        <div class="col-auto">
          <BButton type="submit" variant="primary">
            <FontAwesomeIcon :icon="faRightToBracket" />
            {{ $t("action.login") }}
          </BButton>
        </div>
      </div>

      <BAlert class="mt-2 mb-0" variant="danger" :show="error !== null">
        {{ error }}
      </BAlert>
    </BCard>
  </BForm>
</template>
