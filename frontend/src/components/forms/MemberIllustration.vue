<script setup lang="ts">
import {
  faUser,
  faUserHair,
  faUserHairLong,
} from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { computed } from "vue";
import { baseURL } from "@/api/instance";

interface Props {
  src?: string | null;
  isMale?: boolean | null;
}

const props = withDefaults(defineProps<Props>(), {
  src: null,
  isMale: true,
});

const placeholderIcon = computed(() => {
  if (props.isMale === null) {
    return faUser;
  }
  return props.isMale ? faUserHair : faUserHairLong;
});

const isImageSupplied = computed(() => {
  return props.src !== null;
});

const classes = computed(() => {
  const list = [
    "border",
    "rounded-pill",
    "d-flex",
    "align-items-center",
    "justify-content-center",
    "bg-white",
    "mx-auto",
    "display-2",
  ];

  return list;
});

const style = computed(() => {
  const styles = { height: "10rem", width: "10rem" };

  if (isImageSupplied.value) {
    const encoded = encodeURI(`${baseURL}${props.src}`);
    Object.assign(styles, {
      background: `url("${encoded}")`,
      backgroundPosition: "center",
      backgroundSize: "cover",
      backgroundRepeat: "no-repeat",
    });
  }

  return styles;
});
</script>

<template>
  <div :class="classes" :style="style">
    <FontAwesomeIcon
      v-if="props.src === null"
      :icon="placeholderIcon"
      :style="{
        '--fa-secondary-opacity': 1,
        '--fa-secondary-color': '#ffd6b3',
        '--fa-primary-color': '#462e1c',
      }"
    />
  </div>
</template>
