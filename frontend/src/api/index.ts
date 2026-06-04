import { ofetch } from 'ofetch'

export const backend = ofetch.create({
  baseURL: import.meta.env.VITE_BACKEND_BASE_URL,
  credentials: 'include',
  headers: {
    'Content-Type': 'application/ld+json',
    Accept: 'application/ld+json',
  },
})

export default {}
