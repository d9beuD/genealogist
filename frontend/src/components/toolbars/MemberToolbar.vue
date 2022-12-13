<script setup lang="ts">
import { faChevronLeft } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { BButton } from "bootstrap-vue";

interface Props {}
</script>

<template>
  <div class="bg-white py-1 border-bottom">
    <div class="form-row">
      <div class="col-auto">
        <BButton
          class="text-decoration-none"
          variant="link"
          :to="{ name: 'treeMembers' }"
        >
          <FontAwesomeIcon :icon="faChevronLeft" swap-opacity />
          {{ $t("action.back") }}
        </BButton>
      </div>
    </div>
  </div>
</template>
