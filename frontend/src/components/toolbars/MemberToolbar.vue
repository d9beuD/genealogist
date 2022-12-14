<script setup lang="ts">
import { faChevronLeft, faTrashAlt } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { BButton, BTooltip } from "bootstrap-vue";

interface Props {
  isNewMember?: boolean;
}

interface Emits {
  (e: "removeMember"): void;
}

const props = withDefaults(defineProps<Props>(), {
  isNewMember: false,
});
const emit = defineEmits<Emits>();
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

      <div v-if="!props.isNewMember" class="col-auto ml-auto">
        <div class="row no-gutters">
          <div class="col-auto">
            <BTooltip
              boundary="body"
              target="member-delete-button"
              triggers="hover"
              placement="bottom"
            >
              {{ $t("action.delete") }}
            </BTooltip>
            <BButton
              id="member-delete-button"
              class="text-danger"
              variant="link"
              @click="emit('removeMember')"
            >
              <FontAwesomeIcon :icon="faTrashAlt" />
            </BButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
