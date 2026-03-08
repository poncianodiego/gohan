<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
})

function submit() {
  form.post('/login')
}
</script>

<template>
  <Head title="Sign In" />
  <div class="min-h-screen flex items-center justify-center px-4 bg-surface">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-text">Gohan</h1>
        <p class="text-text-muted mt-2">Cloud Dev Environments</p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div v-if="form.errors.email" class="bg-danger/10 text-danger text-sm p-3 rounded-lg">
          {{ form.errors.email }}
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
            placeholder="you@example.com"
          />
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
          />
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors disabled:opacity-50"
        >
          {{ form.processing ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>

      <p class="text-center text-text-muted text-sm mt-6">
        Don't have an account?
        <Link href="/register" class="text-primary hover:underline">Sign up</Link>
      </p>
    </div>
  </div>
</template>
