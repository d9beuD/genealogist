<script setup lang="ts">
import {
  BButton,
  BForm,
  BFormCheckbox,
  BFormGroup,
  BFormInput,
  BFormRadio,
  BFormTextarea,
} from "bootstrap-vue";
import { reactive, ref } from "vue";
import formatters from "@/formatters";
import type { personForm } from "@/api/types";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
  faBabyCarriage,
  faCoffinCross,
  faMars,
  faPlus,
  faVenus,
} from "@fortawesome/pro-duotone-svg-icons";
import api from "@/api";
import { useRouter } from "vue-router";
import MemberToolbar from "../toolbars/MemberToolbar.vue";

interface Props {
  treeId: number;
}

interface Emits {
  (e: "newMember"): void;
  (e: "submitError"): void;
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
  gender: null,
  picture: null,
  isBirthDateKnown: false,
  isDeathDateKnown: false,
  isBirthDateCertain: true,
  isDeathDateCertain: true,
  importantDates: [],
});

function onSubmit() {
  isLoading.value = true;
  api.people
    .add(props.treeId, person)
    .then((response) => {
      emit("newMember");
      router.push({ name: "showMember", params: { memberId: response.id } });
    })
    .catch(() => {
      emit("submitError");
    })
    .finally(() => {
      isLoading.value = false;
    });
}
</script>

<template>
  <MemberToolbar is-new-member />
  <div class="pb-5 flex-scroll">
    <div class="container-fluid">
      <BForm @submit.prevent="onSubmit">
        <div class="row">
          <div class="col-md-auto">
            <div class="pt-2 sticky-top">
              <div
                class="border rounded-pill d-flex align-items-center justify-content-center bg-white mx-auto"
                :style="{ height: '10rem', width: '10rem' }"
              >
                illustration
              </div>
            </div>
          </div>
          <div class="col pt-3">
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

              <div class="col-lg-auto">
                <BFormGroup :label="$t('form.label.gender')">
                  <div class="form-check form-check-inline">
                    <BFormRadio v-model="person.gender" :value="true">
                      <FontAwesomeIcon class="text-primary" :icon="faMars" />
                      {{ $t("form.option.male") }}
                    </BFormRadio>
                  </div>

                  <div class="form-check form-check-inline">
                    <BFormRadio v-model="person.gender" :value="false">
                      <FontAwesomeIcon class="text-danger" :icon="faVenus" />
                      {{ $t("form.option.female") }}
                    </BFormRadio>
                  </div>
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
                    <div class="form-check pl-0">
                      <BFormCheckbox
                        v-model="person.isBirthDateKnown"
                        type="date"
                      >
                        {{ $t("form.label.isDateKnown") }}
                      </BFormCheckbox>
                    </div>
                    <div v-if="person.isBirthDateKnown" class="form-check pl-0">
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
                    <div class="form-check pl-0">
                      <BFormCheckbox
                        v-model="person.isDeathDateKnown"
                        type="date"
                      >
                        {{ $t("form.label.isDateKnown") }}
                      </BFormCheckbox>
                    </div>
                    <div class="form-check pl-0">
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
  </div>
</template>
