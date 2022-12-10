<script setup lang="ts">
import api from "@/api";
import type { Tree } from "@/api/types";
import {
  faCircleEllipsis,
  faEdit,
  faTrashAlt,
} from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { BDropdown, BDropdownItem } from "bootstrap-vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";

interface Props {
  tree: Tree;
}

interface Emits {
  (e: "removed"): void;
}

const emit = defineEmits<Emits>();
const props = defineProps<Props>();
const router = useRouter();
const { t } = useI18n();

function edit() {
  router.push({ name: "editTree", params: { id: props.tree.id } });
}

function remove() {
  if (confirm(t("confirm.treeDeletion"))) {
    api.trees.delete(props.tree).finally(() => {
      emit("removed");
    });
  }
}
</script>

<template>
  <div class="tree-list-item">
    <div class="form-row">
      <div class="col">
        <h4>{{ tree.name }}</h4>
      </div>
      <div class="col-auto">
        <BDropdown variant="link" no-caret>
          <template #button-content>
            <FontAwesomeIcon :icon="faCircleEllipsis" size="lg" />
          </template>

          <BDropdownItem @click="edit">
            <FontAwesomeIcon :icon="faEdit" />
            {{ $t("action.edit") }}
          </BDropdownItem>
          <BDropdownItem variant="danger" @click="remove">
            <FontAwesomeIcon :icon="faTrashAlt" />
            {{ $t("action.delete") }}
          </BDropdownItem>
        </BDropdown>
      </div>
    </div>
  </div>
</template>
