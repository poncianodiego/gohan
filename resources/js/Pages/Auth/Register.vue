<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post('/register')
}
</script>

<template>
  <Head title="Create Account" />
  <div class="min-h-screen flex items-center justify-center px-4 bg-surface">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-text">Gohan</h1>
        <p class="text-text-muted mt-2">Create your account</p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm text-text-muted mb-1">Name</label>
          <input v-model="form.name" type="text" required class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary" />
          <p v-if="form.errors.name" class="text-danger text-xs mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Email</label>
          <input v-model="form.email" type="email" required class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary" />
          <p v-if="form.errors.email" class="text-danger text-xs mt-1">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Password</label>
          <input v-model="form.password" type="password" required class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary" />
          <p v-if="form.errors.password" class="text-danger text-xs mt-1">{{ form.errors.password }}</p>
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" required class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary" />
        </div>

        <button type="submit" :disabled="form.processing" class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors disabled:opacity-50">
          {{ form.processing ? 'Creating account...' : 'Create Account' }}
        </button>
      </form>

      <p class="text-center text-text-muted text-sm mt-6">
        Already have an account?
        <Link href="/login" class="text-primary hover:underline">Sign in</Link>
      </p>
    </div>
  </div>
</template>
