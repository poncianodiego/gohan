import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api/client'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))

  const isAuthenticated = computed(() => !!token.value)

  async function login(email, password) {
    const { data } = await api.post('/login', { email, password })
    token.value = data.data.token
    user.value = data.data.user
    localStorage.setItem('token', data.data.token)
  }

  async function register(name, email, password, password_confirmation) {
    const { data } = await api.post('/register', { name, email, password, password_confirmation })
    token.value = data.data.token
    user.value = data.data.user
    localStorage.setItem('token', data.data.token)
  }

  async function fetchUser() {
    try {
      const { data } = await api.get('/user')
      user.value = data.data
    } catch {
      logout()
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch { /* ignore */ }
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  return { user, token, isAuthenticated, login, register, fetchUser, logout }
})
