import { z } from 'zod'

export const envSchema = z.object({
  VITE_BACKEND_BASE_URL: z.string().url().default('http://localhost:8000/api'),
})

export type PublicEnv = z.infer<typeof envSchema>
