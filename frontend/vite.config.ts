import { fileURLToPath, URL } from 'node:url'

import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

import { envSchema } from './src/env.schema'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const parsedEnv = envSchema.safeParse(loadEnv(mode, process.cwd()))

  if (!parsedEnv.success) {
    console.error(
      'Invalid VITE_* environment variables:',
      parsedEnv.error.flatten().fieldErrors,
    )
    throw new Error('Invalid VITE_* environment variables')
  }

  return {
    plugins: [vue(), vueDevTools(), tailwindcss()],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },
  }
})
