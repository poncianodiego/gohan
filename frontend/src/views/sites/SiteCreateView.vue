<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSiteStore } from '../../stores/sites'
import AppHeader from '../../components/AppHeader.vue'

const route = useRoute()
const router = useRouter()
const siteStore = useSiteStore()
const serverId = route.params.serverId

const form = ref({
  domain: '',
  project_type: 'laravel',
  fresh_laravel: true,
  repository: '',
  branch: 'main',
})
const pathway = ref('fresh') // 'fresh' or 'repo'
const loading = ref(false)
const error = ref('')

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    const data = {
      domain: form.value.domain,
      project_type: form.value.project_type,
      fresh_laravel: pathway.value === 'fresh',
      branch: form.value.branch,
    }
    if (pathway.value === 'repo') {
      data.repository = form.value.repository
    }
    const site = await siteStore.createSite(serverId, data)
    router.push(`/sites/${site.id}`)
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to create site.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="pb-20">
    <AppHeader title="New Site" :back-to="`/servers/${serverId}`" />

    <div class="max-w-lg mx-auto px-4 py-4">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div v-if="error" class="bg-danger/10 text-danger text-sm p-3 rounded-lg">
          {{ error }}
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Domain</label>
          <input
            v-model="form.domain"
            type="text"
            required
            placeholder="myapp.dev.example.com"
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
          />
        </div>

        <!-- Pathway -->
        <div>
          <label class="block text-sm text-text-muted mb-2">Setup</label>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="pathway = 'fresh'"
              class="p-3 rounded-lg border text-sm text-center transition-colors"
              :class="pathway === 'fresh' ? 'border-primary bg-primary/10 text-primary' : 'border-surface-lighter text-text-muted'"
            >
              Fresh Laravel
            </button>
            <button
              type="button"
              @click="pathway = 'repo'"
              class="p-3 rounded-lg border text-sm text-center transition-colors"
              :class="pathway === 'repo' ? 'border-primary bg-primary/10 text-primary' : 'border-surface-lighter text-text-muted'"
            >
              From Repository
            </button>
          </div>
        </div>

        <div v-if="pathway === 'repo'" class="space-y-4">
          <div>
            <label class="block text-sm text-text-muted mb-1">Repository URL</label>
            <input
              v-model="form.repository"
              type="text"
              placeholder="https://github.com/user/repo.git"
              class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
            />
          </div>
          <div>
            <label class="block text-sm text-text-muted mb-1">Branch</label>
            <input
              v-model="form.branch"
              type="text"
              class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
            />
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors disabled:opacity-50"
        >
          {{ loading ? 'Creating...' : 'Create Site' }}
        </button>
      </form>
    </div>
  </div>
</template>
