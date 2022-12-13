<script setup lang="ts">
import type { UserWithPassword } from "@/api/types";
import { computed, reactive, ref } from "vue";
import { useSessionStore } from "@/stores/session";
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
import { faUserPlus, faSync } from "@fortawesome/pro-duotone-svg-icons";
import api from "@/api";
import { useRouter } from "vue-router";

const router = useRouter();
const sessionStore = useSessionStore();
const isLoading = ref(false);
const currentError = ref<string | null>(null);

const form = reactive<UserWithPassword>({
  firstname: "",
  lastname: "",
  email: "",
  password: "",
});

const isPasswordFilled = computed(() => {
  return form.password.length > 0;
});

const isPasswordLengthCorrect = computed(() => {
  return form.password.length >= sessionStore.minPasswordLength;
});

const passwordState = computed(() => {
  if (!isPasswordFilled.value) {
    return null;
  }

  return isPasswordLengthCorrect.value;
});

const submitIcon = computed(() => {
  return isLoading.value ? faSync : faUserPlus;
});

function onSubmit() {
  isLoading.value = true;

  api.session
    .register(form)
    .then((response) => response)
    .then((user) => {
      if (user.id !== null) {
        router.push({ name: "login" });
      }
    })
    .catch((error) => {
      currentError.value = error;
    })
    .finally(() => {
      isLoading.value = false;
    });
}
</script>

<template>
  <BForm @submit.prevent="onSubmit" class="form-full-page mt-5">
    <BCard class="shadow" :title="$t('form.title.register')">
      <div class="form-row">
        <div class="col-md-6">
          <BFormGroup :label="$t('form.label.firstname')">
            <BFormInput
              v-model="form.firstname"
              required
              :placeholder="$t('form.label.firstname')"
            />
          </BFormGroup>
        </div>
        <div class="col">
          <BFormGroup :label="$t('form.label.lastname')">
            <BFormInput
              v-model="form.lastname"
              required
              :placeholder="$t('form.label.lastname')"
            />
          </BFormGroup>
        </div>
      </div>

      <BFormGroup :label="$t('form.label.identifier')">
        <BFormInput
          v-model="form.email"
          type="email"
          required
          :placeholder="$t('form.label.identifier')"
        />
      </BFormGroup>

      <BFormGroup :label="$t('form.label.password')">
        <BFormInput
          v-model="form.password"
          type="password"
          required
          :placeholder="$t('form.label.password')"
          :state="passwordState"
        />
        <BFormInvalidFeedback>
          {{
            $tc("form.feedback.tooShortLength", sessionStore.minPasswordLength)
          }}.
        </BFormInvalidFeedback>
      </BFormGroup>

      <div class="row no-gutters">
        <div class="col">
          <RouterLink :to="{ name: 'login' }">
            {{ $t("form.message.hasAnAccount") }}
          </RouterLink>
        </div>
        <div class="col-auto">
          <BButton type="submit" variant="primary">
            <FontAwesomeIcon :icon="submitIcon" fixed-width :spin="isLoading" />
            {{ $t("action.register") }}
          </BButton>
        </div>
      </div>

      <BAlert class="mt-2 mb-0" variant="danger" :show="currentError !== null">
        {{ currentError }}
      </BAlert>
    </BCard>
  </BForm>
</template>
