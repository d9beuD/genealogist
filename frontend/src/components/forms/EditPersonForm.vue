<script setup lang="ts">
import {
  BButton,
  BDropdown,
  BDropdownItem,
  BForm,
  BFormCheckbox,
  BFormFile,
  BFormGroup,
  BFormInput,
  BFormRadio,
  BFormTextarea,
} from "bootstrap-vue";
import { onMounted, reactive, ref, watch } from "vue";
import formatters from "@/formatters";
import type { Person } from "@/api/types";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
  faBabyCarriage,
  faCoffinCross,
  faFileImage,
  faFileXmark,
  faImagePolaroidUser,
  faMars,
  faSave,
  faVenus,
} from "@fortawesome/pro-duotone-svg-icons";
import api from "@/api";
import MemberToolbar from "../toolbars/MemberToolbar.vue";
import { useRouter } from "vue-router";
import MemberIllustration from "./MemberIllustration.vue";

interface Props {
  treeId: number;
  memberId: number;
}

interface Emits {
  (e: "deletedMember"): void;
  (e: "updatedMember"): void;
  (e: "submitError"): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();
const router = useRouter();

const isLoading = ref(false);
const person = reactive<Person>({
  id: 0,
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
  parents: [],
  children: [],
});
const tempPicture = ref<File | null>(null);

function onSubmit() {
  isLoading.value = true;
  api.people
    .update(props.memberId, person)
    .then(() => {
      emit("updatedMember");
    })
    .catch(() => {
      emit("submitError");
    })
    .finally(() => {
      isLoading.value = false;
    });
}

function onDelete() {
  api.people
    .delete(props.memberId)
    .then(() => {
      emit("deletedMember");
      router.push({ name: "treeMembers" });
    })
    .catch(() => {
      emit("submitError");
    });
}

function loadData() {
  return api.people.getMember(props.memberId).then((response) => {
    Object.assign(person, response);
  });
}

function selectPicture() {
  const input = document.getElementById("fileInput") as HTMLInputElement;

  if (input === null) {
    return;
  }

  input.click();
}

function uploadPicture() {
  if (tempPicture.value === null) {
    return;
  }
  return api.people.setPicture(props.memberId, tempPicture.value).then(() => {
    loadData();
  });
}

function removePicture() {
  api.people.removePicture(person.id).finally(() => {
    loadData();
  });
}

onMounted(() => {
  loadData();
});

watch(
  () => props.memberId,
  () => loadData()
);

watch(
  () => tempPicture.value,
  (value) => {
    if (value !== null) {
      const request = uploadPicture();
      if (typeof request !== "undefined") {
        request.finally(() => {
          tempPicture.value = null;
        });
      }
    }
  }
);
</script>

<template>
  <MemberToolbar @remove-member="onDelete" />
  <div class="pb-5 flex-scroll">
    <div class="container-fluid">
      <BForm @submit.prevent="onSubmit">
        <div class="row">
          <div class="col-md-auto">
            <div class="pt-2 sticky-top">
              <MemberIllustration
                class="mb-2"
                :is-male="person.gender"
                :src="person.picture"
              />
              <BDropdown block>
                <template #button-content>
                  <FontAwesomeIcon :icon="faImagePolaroidUser" />
                  {{ $t("button.image") }}
                </template>

                <BDropdownItem @click="selectPicture">
                  <FontAwesomeIcon :icon="faFileImage" fixed-width />
                  {{ $t("action.select") }}
                </BDropdownItem>
                <BDropdownItem @click="removePicture">
                  <FontAwesomeIcon :icon="faFileXmark" fixed-width />
                  {{ $t("action.remove") }}
                </BDropdownItem>
              </BDropdown>
              <BFormFile
                v-model="tempPicture"
                id="fileInput"
                class="d-none"
                plain
              />
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
                <FontAwesomeIcon :icon="faSave" swap-opacity />
                {{ $t("action.update") }}
              </BButton>
            </div>
          </div>
        </div>
      </BForm>
    </div>
  </div>
</template>
