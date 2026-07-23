export type Role = 'ROLE_ADMIN' | 'ROLE_USER'
export const ALPHANUMERIC_REGEX = /^\p{L}+(?:[ '\-]\p{L}+)*$/u

export interface ApiEnvelope<T> {
  code: number
  message: string | null
  data: T
}

export interface ValidationErrorBody {
  message?: string
  errors?: Record<string, string[]>
}

export interface Student {
  id: string
  name: string
  surname: string
  number: string | null
  grade: string | null
  registration_code: string | null
}

export interface StudentWithParent extends Student {
  parent: {
    id: string
    name: string
    surname: string
    email: string
  } | null
}

export interface StudentListParams {
  page?: number
  perPage?: number
  search?: string
  sort?: string
  direction?: 'asc' | 'desc'
  [key: string]: string | number | boolean | undefined
}

export interface PaginationMeta {
  currentPage: number
  lastPage: number
  perPage: number
  total: number
}

export interface Paginated<T> {
  data: T[]
  meta: PaginationMeta
}

export interface User {
  id: string
  name: string
  surname: string
  email: string
  role: Role | null
  students: Student[]
}

export interface LoginPayload {
  email: string
  password: string
}

export interface LoginData {
  token: string
}

export interface RegisterPayload {
  name: string
  surname: string
  email: string
  password: string
  password_confirmation: string
  registrationCode: string
}

export interface RegisterData {
  name: string
  surname: string
  email: string
}

export interface UpdateProfilePayload {
  name?: string
  surname?: string
  email?: string
  password?: string
  password_confirmation?: string
}
