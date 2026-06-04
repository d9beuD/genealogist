import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query'
import type { App } from 'vue'

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      // These are global query defaults; individual useQuery calls can override them, e.g. staleTime: Infinity for static lookup lists.
      // Disabling this avoids extra background refetches on tab refocus, trading off automatic freshness.
      refetchOnWindowFocus: false,
      retry: 1,
      staleTime: 30_000,
    },
  },
})

export const vueQueryPlugin = {
  install(app: App) {
    app.use(VueQueryPlugin, { queryClient })
  },
}
