import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { authApi } from '@/api/auth'
import { clearToken, getToken, setToken } from '@/lib/http'
import type { LoginPayload, RegisterPayload, UpdateProfilePayload, User } from '@/types/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(getToken())
  const user = ref<User | null>(null)
  const bootstrapped = ref(false)

  const isAuthenticated = computed(() => Boolean(token.value))
  const isAdmin = computed(() => user.value?.role === 'ROLE_ADMIN')
  const fullName = computed(() =>
    user.value ? `${user.value.name} ${user.value.surname}`.trim() : '',
  )

  function applyToken(value: string) {
    token.value = value
    setToken(value)
  }

  function reset() {
    token.value = null
    user.value = null
    clearToken()
  }

  async function fetchMe() {
    if (!token.value) {
      user.value = null
      return null
    }

    user.value = await authApi.me()
    return user.value
  }

  async function login(payload: LoginPayload) {
    const { token: issued } = await authApi.login(payload)
    applyToken(issued)
    await fetchMe().catch(() => null)
    return issued
  }

  async function register(payload: RegisterPayload) {
    return authApi.register(payload)
  }

  async function updateProfile(payload: UpdateProfilePayload) {
    user.value = await authApi.updateMe(payload)
    return user.value
  }

  async function bootstrap() {
    if (bootstrapped.value) return
    if (token.value && !user.value) {
      await fetchMe().catch(() => null)
    }
    bootstrapped.value = true
  }

  function logout() {
    reset()
    bootstrapped.value = true
  }

  return {
    token,
    user,
    bootstrapped,
    isAuthenticated,
    isAdmin,
    fullName,
    login,
    register,
    fetchMe,
    updateProfile,
    bootstrap,
    logout,
  }
})
