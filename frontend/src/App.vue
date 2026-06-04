<script setup lang="ts">
import { defineAsyncComponent } from 'vue'
import { RouterView } from 'vue-router'
import { Toaster } from 'vue-sonner'

import 'vue-sonner/style.css'

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
  <Toaster />
  <component :is="VueQueryDevtools" v-if="VueQueryDevtools" />
</template>
