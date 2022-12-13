<script setup lang="ts">
import {
  BButton,
  BForm,
  BFormCheckbox,
  BFormGroup,
  BFormInput,
  BFormTextarea,
} from "bootstrap-vue";
import { computed, onMounted, reactive, ref } from "vue";
import formatters from "@/formatters";
import type { personForm } from "@/api/types";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
  faBabyCarriage,
  faCoffinCross,
  faPlus,
} from "@fortawesome/pro-duotone-svg-icons";
import api from "@/api";
import { useRouter } from "vue-router";

interface Props {
  treeId: number;
  personId?: number;
}

interface Emits {
  (e: "newMember"): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();
const router = useRouter();

const isLoading = ref(false);
const person = reactive<personForm>({
  firstname: null,
  lastname: null,
  birthName: null,
  birthDate: null,
  deathDate: null,
  description: null,
  picture: null,
  isBirthDateKnown: false,
  isDeathDateKnown: false,
  isBirthDateCertain: true,
  isDeathDateCertain: true,
  importantDates: [],
});

const isNewMember = computed(() => typeof props.personId === "undefined");

function onSubmit() {
  isLoading.value = true;
  api.people
    .add(props.treeId, person)
    .then((response) => {
      emit("newMember");
      router.push({ name: "treeMembers" });
    })
    .finally(() => {
      isLoading.value = false;
    });
}

onMounted(() => {
  if (!isNewMember.value) {
    //
  }
});
</script>

<template>
  <div class="container-fluid mt-2 mb-5">
    <h1 class="h3 mb-3" v-if="isNewMember">
      {{ $t("page.title.newTreeMember") }}
    </h1>
    <BForm @submit.prevent="onSubmit">
      <div class="row">
        <div class="col-md-auto">
          <div
            class="border rounded-pill d-flex align-items-center justify-content-center bg-white mx-auto mb-2"
            :style="{ height: '10rem', width: '10rem' }"
          >
            illustration
          </div>
        </div>
        <div class="col">
          <h4 class="section">{{ $t("page.title.generalInformation") }}</h4>
          <div class="form-row">
            <div class="col-lg-auto">
              <BFormGroup :label="$t('form.label.firstname')">
                <BFormInput
                  v-model="person.firstname"
                  :placeholder="$t('form.label.firstname')"
                  trim
                />
              </BFormGroup>
            </div>
            <div class="col-lg-auto">
              <BFormGroup :label="$t('form.label.lastname')">
                <BFormInput
                  v-model="person.lastname"
                  :placeholder="$t('form.label.lastname')"
                  trim
                />
              </BFormGroup>
            </div>
          </div>

          <h4 class="section">{{ $t("page.title.importantDates") }}</h4>
          <div class="row">
            <div class="col-lg-auto">
              <BFormGroup>
                <template #label>
                  <FontAwesomeIcon :icon="faBabyCarriage" />
                  {{ $t("form.label.birthdate") }}
                </template>
                <div class="mb-2">
                  <div class="form-check">
                    <BFormCheckbox
                      v-model="person.isBirthDateKnown"
                      type="date"
                    >
                      {{ $t("form.label.isDateKnown") }}
                    </BFormCheckbox>
                  </div>
                  <div v-if="person.isBirthDateKnown" class="form-check">
                    <BFormCheckbox
                      v-model="person.isBirthDateCertain"
                      type="date"
                    >
                      {{ $t("form.label.isDateCertain") }}
                    </BFormCheckbox>
                  </div>
                </div>
                <BFormInput
                  v-if="person.isBirthDateKnown"
                  v-model="person.birthDate"
                  type="date"
                  :lazy-formatter="formatters.dateNormalizer"
                  :placeholder="$t('form.label.birthdate')"
                  required
                />
              </BFormGroup>
            </div>
            <div class="col-lg-auto">
              <BFormGroup>
                <template #label>
                  <FontAwesomeIcon :icon="faCoffinCross" />
                  {{ $t("form.label.deathdate") }}
                </template>
                <div class="mb-2">
                  <div class="form-check">
                    <BFormCheckbox
                      v-model="person.isDeathDateKnown"
                      type="date"
                    >
                      {{ $t("form.label.isDateKnown") }}
                    </BFormCheckbox>
                  </div>
                  <div class="form-check">
                    <BFormCheckbox
                      v-if="person.isDeathDateKnown"
                      v-model="person.isDeathDateCertain"
                      type="date"
                    >
                      {{ $t("form.label.isDateCertain") }}
                    </BFormCheckbox>
                  </div>
                </div>
                <BFormInput
                  v-if="person.isDeathDateKnown"
                  v-model="person.deathDate"
                  type="date"
                  :lazy-formatter="formatters.dateNormalizer"
                  :placeholder="$t('form.label.deathdate')"
                  required
                />
              </BFormGroup>
            </div>
          </div>

          <h4 class="section">{{ $t("page.title.others") }}</h4>
          <BFormGroup :label="$t('form.label.description')">
            <BFormTextarea
              v-model="person.description"
              :placeholder="$t('form.label.description')"
            />
          </BFormGroup>

          <div class="text-right">
            <BButton type="submit" variant="primary">
              <FontAwesomeIcon :icon="faPlus" swap-opacity />
              {{ $t("action.add") }}
            </BButton>
          </div>
        </div>
      </div>
    </BForm>
  </div>
</template>
