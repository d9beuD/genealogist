<script setup lang="ts">
import { BForm, BFormGroup, BFormInput, BFormTextarea } from "bootstrap-vue";
import { computed, onMounted } from "vue";
import formatters from "@/formatters";

interface Props {
  treeId: number;
  personId?: number;
}

const props = defineProps<Props>();

const isNewMember = computed(() => typeof props.personId === "undefined");

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
    <BForm>
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
                <BFormInput :placeholder="$t('form.label.firstname')" />
              </BFormGroup>
            </div>
            <div class="col-lg-auto">
              <BFormGroup :label="$t('form.label.lastname')">
                <BFormInput :placeholder="$t('form.label.lastname')" />
              </BFormGroup>
            </div>
          </div>

          <h4 class="section">{{ $t("page.title.importantDates") }}</h4>
          <div class="form-row">
            <div class="col-lg-auto">
              <BFormGroup :label="$t('form.label.birthdate')">
                <BFormInput
                  type="date"
                  :formatter="formatters.stringToDate"
                  :placeholder="$t('form.label.birthdate')"
                />
              </BFormGroup>
            </div>
            <div class="col-lg-auto">
              <BFormGroup :label="$t('form.label.deathdate')">
                <BFormInput
                  type="date"
                  :formatter="formatters.stringToDate"
                  :placeholder="$t('form.label.deathdate')"
                />
              </BFormGroup>
            </div>
          </div>

          <h4 class="section">{{ $t("page.title.others") }}</h4>
          <BFormGroup :label="$t('form.label.description')">
            <BFormTextarea :placeholder="$t('form.label.description')" />
          </BFormGroup>
        </div>
      </div>
    </BForm>
  </div>
</template>
