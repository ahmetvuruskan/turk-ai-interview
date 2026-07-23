import axios, { AxiosError } from 'axios'
import type { ApiEnvelope, Paginated, PaginationMeta, ValidationErrorBody } from '@/types/api'
import { tr } from '@/locales/tr'

declare module 'axios' {
  export interface AxiosRequestConfig {
    skipAuth?: boolean
    skipUnwrap?: boolean
  }
}

const BASE_URL = (import.meta.env.VITE_API_BASE_URL ?? '/api/v1').replace(/\/$/, '')
const TOKEN_KEY = 'turk_ai_token'

export class ApiError extends Error {
  readonly status: number
  readonly errors: Record<string, string[]>

  constructor(status: number, message: string, errors: Record<string, string[]> = {}) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }

  get isValidation() {
    return this.status === 422 || Object.keys(this.errors).length > 0
  }

  firstError(field: string) {
    return this.errors[field]?.[0] ?? null
  }
}

let onUnauthorized: (() => void) | null = null

export function setUnauthorizedHandler(handler: () => void) {
  onUnauthorized = handler
}

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function setToken(token: string) {
  localStorage.setItem(TOKEN_KEY, token)
}

export function clearToken() {
  localStorage.removeItem(TOKEN_KEY)
}

const client = axios.create({
  baseURL: BASE_URL,
  headers: {
    Accept: 'application/json',
  },
})

client.interceptors.request.use((config) => {
  if (!config.skipAuth) {
    const token = getToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
  }
  return config
})

function toApiError(error: AxiosError<ValidationErrorBody & Partial<ApiEnvelope<unknown>>>) {
  if (!error.response) {
    return new ApiError(0, tr.errors.network)
  }

  const { status, data } = error.response

  if (data && typeof data === 'object') {
    const errors = data.errors ?? {}
    const message = data.message || Object.values(errors)[0]?.[0] || tr.errors.unexpected
    return new ApiError(status, message, errors)
  }

  return new ApiError(status, typeof data === 'string' && data ? data : tr.errors.unexpected)
}

client.interceptors.response.use(
  (response) => {
    if (response.config.skipUnwrap) {
      return response
    }

    const envelope = response.data as ApiEnvelope<unknown> | null
    if (envelope && typeof envelope === 'object' && 'data' in envelope) {
      response.data = envelope.data
    }
    return response
  },
  (error: unknown) => {
    if (axios.isCancel(error)) {
      return Promise.reject(error)
    }

    const apiError = toApiError(error as AxiosError<ValidationErrorBody>)

    if (apiError.status === 401) {
      clearToken()
      onUnauthorized?.()
    }

    return Promise.reject(apiError)
  },
)

interface RequestOptions {
  auth?: boolean
  signal?: AbortSignal
  params?: Record<string, string | number | boolean | undefined>
}

function toConfig(options: RequestOptions = {}) {
  return {
    skipAuth: options.auth === false,
    signal: options.signal,
    params: options.params,
  }
}

export function isCanceled(error: unknown) {
  return axios.isCancel(error)
}

export const http = {
  get: async <T>(path: string, options?: RequestOptions) => {
    const response = await client.get<T>(path, toConfig(options))
    return response.data
  },
  getPage: async <T>(path: string, options?: RequestOptions): Promise<Paginated<T>> => {
    const response = await client.get<ApiEnvelope<T[]> & { meta?: PaginationMeta }>(path, {
      ...toConfig(options),
      skipUnwrap: true,
    })

    const body = response.data
    const items = Array.isArray(body?.data) ? body.data : []
    const meta = body?.meta

    return {
      data: items,
      meta: {
        currentPage: meta?.currentPage ?? 1,
        lastPage: Math.max(meta?.lastPage ?? 1, 1),
        perPage: meta?.perPage ?? items.length,
        total: meta?.total ?? items.length,
      },
    }
  },
  post: async <T>(path: string, body?: unknown, options?: RequestOptions) => {
    const response = await client.post<T>(path, body, toConfig(options))
    return response.data
  },
  put: async <T>(path: string, body?: unknown, options?: RequestOptions) => {
    const response = await client.put<T>(path, body, toConfig(options))
    return response.data
  },
}

export { client as httpClient }
