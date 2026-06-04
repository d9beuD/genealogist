import { i18n } from '@/i18n'

export type AppErrorCode =
  | 'VALIDATION'
  | 'NOT_FOUND'
  | 'FORBIDDEN'
  | 'NETWORK'
  | 'SERVER'
  | 'UNKNOWN'

export type AppErrorFields = Record<string, string>

type AppErrorOptions = {
  status: number | null
  code: AppErrorCode
  message: string
  fields?: AppErrorFields
  isOperational?: boolean
  cause?: unknown
}

export class AppError extends Error {
  readonly status: number | null
  readonly code: AppErrorCode
  readonly fields?: AppErrorFields
  readonly isOperational: boolean

  constructor({
    status,
    code,
    message,
    fields,
    isOperational = true,
    cause,
  }: AppErrorOptions) {
    super(message, { cause })
    this.name = 'AppError'
    this.status = status
    this.code = code
    this.fields = fields
    this.isOperational = isOperational
  }
}

export function toAppError(error: unknown): AppError {
  if (error instanceof AppError) {
    return error
  }

  if (isOfetchLikeError(error)) {
    const status = error.response?.status ?? error.statusCode ?? null
    const data = error.data

    if (status === 422) {
      return new AppError({
        status,
        code: 'VALIDATION',
        message: readErrorMessage(data) ?? translateError('validation'),
        fields: readValidationFields(data),
        cause: error,
      })
    }

    return new AppError({
      status,
      code: codeForStatus(status),
      message: readErrorMessage(data) ?? messageForStatus(status, error.message),
      cause: error,
    })
  }

  if (error instanceof TypeError) {
    return new AppError({
      status: null,
      code: 'NETWORK',
      message: translateError('network'),
      cause: error,
    })
  }

  if (error instanceof Error) {
    return new AppError({
      status: null,
      code: 'UNKNOWN',
      message: error.message || translateError('unknown'),
      isOperational: false,
      cause: error,
    })
  }

  return new AppError({
    status: null,
    code: 'UNKNOWN',
    message: translateError('unknown'),
    isOperational: false,
    cause: error,
  })
}

type OfetchLikeError = Error & {
  data?: unknown
  response?: { status?: number }
  statusCode?: number
}

function isOfetchLikeError(error: unknown): error is OfetchLikeError {
  return error instanceof Error && ('data' in error || 'response' in error || 'statusCode' in error)
}

function codeForStatus(status: number | null): AppErrorCode {
  if (status === null) {
    return 'NETWORK'
  }

  if (status === 403) {
    return 'FORBIDDEN'
  }

  if (status === 404) {
    return 'NOT_FOUND'
  }

  if (status >= 500) {
    return 'SERVER'
  }

  return 'UNKNOWN'
}

function messageForStatus(status: number | null, fallback?: string): string {
  if (status === null) {
    return translateError('network')
  }

  if (status === 403) {
    return translateError('forbidden')
  }

  if (status === 404) {
    return translateError('notFound')
  }

  if (status >= 500) {
    return translateError('server')
  }

  return fallback || translateError('unknown')
}

function translateError(key: 'validation' | 'network' | 'forbidden' | 'notFound' | 'server' | 'unknown'): string {
  return i18n.global.t(`errors.${key}`)
}

function readErrorMessage(data: unknown): string | undefined {
  if (!isRecord(data)) {
    return undefined
  }

  return readString(data.message) ?? readString(data.detail) ?? readString(data.title)
}

function readValidationFields(data: unknown): AppErrorFields | undefined {
  if (!isRecord(data)) {
    return undefined
  }

  const directFields = readFieldRecord(data.fields) ?? readFieldRecord(data.errors)
  if (directFields) {
    return directFields
  }

  const violations = data.violations
  if (!Array.isArray(violations)) {
    return undefined
  }

  const fields: AppErrorFields = {}

  for (const violation of violations) {
    if (!isRecord(violation)) {
      continue
    }

    const field = readString(violation.propertyPath) ?? readString(violation.field)
    const message = readString(violation.message)

    if (field && message) {
      fields[field] = message
    }
  }

  return Object.keys(fields).length > 0 ? fields : undefined
}

function readFieldRecord(value: unknown): AppErrorFields | undefined {
  if (!isRecord(value)) {
    return undefined
  }

  const fields: AppErrorFields = {}

  for (const [field, message] of Object.entries(value)) {
    const firstMessage = Array.isArray(message) ? message[0] : message
    const text = readString(firstMessage)

    if (text) {
      fields[field] = text
    }
  }

  return Object.keys(fields).length > 0 ? fields : undefined
}

function readString(value: unknown): string | undefined {
  return typeof value === 'string' && value.length > 0 ? value : undefined
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null
}
