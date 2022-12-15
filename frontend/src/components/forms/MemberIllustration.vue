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
  size?: "sm" | "md" | "lg";
}

const props = withDefaults(defineProps<Props>(), {
  src: null,
  isMale: true,
  size: "lg",
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

const size = computed(() => {
  const sizes = {
    sm: "2rem",
    md: "5rem",
    lg: "10rem",
  };

  return sizes[props.size];
});

const fontSize = computed(() => {
  const sizes = {
    sm: "1.25rem",
    md: "3rem",
    lg: "5.5rem",
  };

  return sizes[props.size];
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
  ];

  return list;
});

const style = computed(() => {
  const styles = {
    height: size.value,
    width: size.value,
    fontSize: fontSize.value,
  };

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
