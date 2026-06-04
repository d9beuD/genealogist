import { MutationCache, QueryCache, QueryClient, VueQueryPlugin } from '@tanstack/vue-query'
import type { App } from 'vue'
import { toast } from 'vue-sonner'

import { toAppError } from '@/lib/errors'

export const queryClient = new QueryClient({
  mutationCache: new MutationCache({
    onError(error, _variables, _context, mutation) {
      if (mutation.options.onError) {
        return
      }

      toast.error(toAppError(error).message)
    },
  }),
  queryCache: new QueryCache({
    onError(error, query) {
      if (query.state.data !== undefined) {
        toast.error(toAppError(error).message)
      }
    },
  }),
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
