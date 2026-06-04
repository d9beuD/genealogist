<script setup lang="ts">
import { defineAsyncComponent } from 'vue'
import { RouterView } from 'vue-router'

const VueQueryDevtools = import.meta.env.DEV
  ? defineAsyncComponent(() =>
      import('@tanstack/vue-query-devtools').then(
        ({ VueQueryDevtools }) => VueQueryDevtools,
      ),
    )
  : undefined
</script>

<template>
  <RouterView />
  <component :is="VueQueryDevtools" v-if="VueQueryDevtools" />
</template>
