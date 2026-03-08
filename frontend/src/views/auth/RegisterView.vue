<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errors = ref({})
const loading = ref(false)

async function handleRegister() {
  errors.value = {}
  loading.value = true
  try {
    await auth.register(name.value, email.value, password.value, passwordConfirmation.value)
    router.push('/')
  } catch (e) {
    errors.value = e.response?.data?.errors || {}
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
        <p class="text-text-muted mt-2">Create your account</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label class="block text-sm text-text-muted mb-1">Name</label>
          <input
            v-model="name"
            type="text"
            required
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
          />
          <p v-if="errors.name" class="text-danger text-xs mt-1">{{ errors.name[0] }}</p>
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Email</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
          />
          <p v-if="errors.email" class="text-danger text-xs mt-1">{{ errors.email[0] }}</p>
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Password</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
          />
          <p v-if="errors.password" class="text-danger text-xs mt-1">{{ errors.password[0] }}</p>
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Confirm Password</label>
          <input
            v-model="passwordConfirmation"
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
          {{ loading ? 'Creating account...' : 'Create Account' }}
        </button>
      </form>

      <p class="text-center text-text-muted text-sm mt-6">
        Already have an account?
        <router-link to="/login" class="text-primary hover:underline">Sign in</router-link>
      </p>
    </div>
  </div>
</template>
