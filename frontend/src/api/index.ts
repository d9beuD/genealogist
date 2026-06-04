import { ofetch } from 'ofetch'
import { toAppError } from '@/lib/errors'

export const backend = ofetch.create({
  baseURL: import.meta.env.VITE_BACKEND_BASE_URL,
  credentials: 'include',
  headers: {
    'Content-Type': 'application/ld+json',
    Accept: 'application/ld+json',
  },
  onRequestError({ error }) {
    throw toAppError(error)
  },
  onResponseError({ error, response }) {
    throw toAppError(Object.assign(error ?? new Error(response.statusText), { data: response._data, response }))
  },
})

export default {}
