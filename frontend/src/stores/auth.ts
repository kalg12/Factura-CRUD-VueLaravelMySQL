import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/api/client'
import type { User } from '@/types'

const STORAGE_TOKEN = 'token'
const STORAGE_USER = 'user'

function getStoredUser(): User | null {
  try {
    const raw = localStorage.getItem(STORAGE_USER)
    return raw ? (JSON.parse(raw) as User) : null
  } catch {
    return null
  }
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem(STORAGE_TOKEN))
  const user = ref<User | null>(getStoredUser())

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string) {
    const { data } = await api.post<{
      message: string
      token: string
      token_type: string
      user: User
    }>('/login', { email, password })
    token.value = data.token
    user.value = data.user
    localStorage.setItem(STORAGE_TOKEN, data.token)
    localStorage.setItem(STORAGE_USER, JSON.stringify(data.user))
    return data
  }

  async function register(
    name: string,
    email: string,
    password: string,
    password_confirmation: string
  ) {
    const { data } = await api.post<{
      message: string
      token: string
      token_type: string
      user: User
    }>('/register', { name, email, password, password_confirmation })
    token.value = data.token
    user.value = data.user
    localStorage.setItem(STORAGE_TOKEN, data.token)
    localStorage.setItem(STORAGE_USER, JSON.stringify(data.user))
    return data
  }

  async function logout() {
    try {
      await api.post('/logout')
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem(STORAGE_TOKEN)
      localStorage.removeItem(STORAGE_USER)
    }
  }

  async function fetchUser() {
    const { data } = await api.get<{ user: User }>('/user')
    user.value = data.user
    localStorage.setItem(STORAGE_USER, JSON.stringify(data.user))
    return data.user
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    register,
    logout,
    fetchUser,
  }
})
