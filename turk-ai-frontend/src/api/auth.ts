import { http } from '@/lib/http'
import type {
  LoginData,
  LoginPayload,
  RegisterData,
  RegisterPayload,
  UpdateProfilePayload,
  User,
} from '@/types/api'

export const authApi = {
  login: (payload: LoginPayload) => http.post<LoginData>('/auth/login', payload, { auth: false }),
  register: (payload: RegisterPayload) =>
    http.post<RegisterData>('/auth/register', payload, { auth: false }),
  me: () => http.get<User>('/auth/me'),
  updateMe: (payload: UpdateProfilePayload) => http.put<User>('/auth/me', payload),
}
