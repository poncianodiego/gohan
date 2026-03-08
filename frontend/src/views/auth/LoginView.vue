<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.errors?.email?.[0] || 'Login failed.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-4 bg-surface">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-text">Gohan</h1>
        <p class="text-text-muted mt-2">Cloud Dev Environments</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div v-if="error" class="bg-danger/10 text-danger text-sm p-3 rounded-lg">
          {{ error }}
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Email</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
            placeholder="you@example.com"
          />
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Password</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors disabled:opacity-50"
        >
          {{ loading ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>

      <p class="text-center text-text-muted text-sm mt-6">
        Don't have an account?
        <router-link to="/register" class="text-primary hover:underline">Sign up</router-link>
      </p>
    </div>
  </div>
</template>
