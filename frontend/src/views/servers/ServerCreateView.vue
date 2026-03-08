<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useServerStore } from '../../stores/servers'
import api from '../../api/client'
import AppHeader from '../../components/AppHeader.vue'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const serverStore = useServerStore()

const form = ref({
  name: 'dev-01',
  region: 'nyc1',
  size: 's-2vcpu-4gb',
  cloud_credential_id: '',
  php_version: '8.3',
  database_type: 'postgresql',
})
const credentials = ref([])
const showAdvanced = ref(false)
const loading = ref(false)
const error = ref('')

const regions = [
  { slug: 'nyc1', name: 'New York 1' },
  { slug: 'nyc3', name: 'New York 3' },
  { slug: 'sfo3', name: 'San Francisco 3' },
  { slug: 'ams3', name: 'Amsterdam 3' },
  { slug: 'sgp1', name: 'Singapore 1' },
  { slug: 'lon1', name: 'London 1' },
  { slug: 'fra1', name: 'Frankfurt 1' },
  { slug: 'tor1', name: 'Toronto 1' },
  { slug: 'blr1', name: 'Bangalore 1' },
  { slug: 'syd1', name: 'Sydney 1' },
]

const sizes = [
  { slug: 's-2vcpu-4gb', label: '2 vCPU / 4 GB', price: '$24/mo' },
  { slug: 's-4vcpu-8gb', label: '4 vCPU / 8 GB (Recommended)', price: '$48/mo' },
  { slug: 's-8vcpu-16gb', label: '8 vCPU / 16 GB', price: '$96/mo' },
]

onMounted(async () => {
  const { data } = await api.get('/credentials')
  credentials.value = data.data
  if (credentials.value.length) {
    form.value.cloud_credential_id = credentials.value[0].id
  }
})

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    const server = await serverStore.createServer(form.value)
    router.push(`/servers/${server.id}`)
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to create server.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="pb-20">
    <AppHeader title="New Server" :back-to="'/'" />

    <div class="max-w-lg mx-auto px-4 py-4">
      <div v-if="!credentials.length" class="text-center py-12">
        <p class="text-text-muted mb-4">Add a DigitalOcean API key first</p>
        <router-link to="/settings" class="text-primary hover:underline">Go to Settings</router-link>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-4">
        <div v-if="error" class="bg-danger/10 text-danger text-sm p-3 rounded-lg">
          {{ error }}
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Server Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
          />
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Cloud Credential</label>
          <select
            v-model="form.cloud_credential_id"
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
          >
            <option v-for="c in credentials" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Region</label>
          <select
            v-model="form.region"
            class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
          >
            <option v-for="r in regions" :key="r.slug" :value="r.slug">{{ r.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm text-text-muted mb-1">Size</label>
          <div class="space-y-2">
            <label
              v-for="s in sizes"
              :key="s.slug"
              class="flex items-center justify-between p-3 bg-surface-light border rounded-lg cursor-pointer transition-colors"
              :class="form.size === s.slug ? 'border-primary' : 'border-surface-lighter'"
            >
              <div class="flex items-center">
                <input type="radio" v-model="form.size" :value="s.slug" class="mr-3 accent-primary" />
                <span class="text-text text-sm">{{ s.label }}</span>
              </div>
              <span class="text-text-muted text-sm">{{ s.price }}</span>
            </label>
          </div>
        </div>

        <!-- Advanced options -->
        <button
          type="button"
          @click="showAdvanced = !showAdvanced"
          class="flex items-center text-sm text-text-muted hover:text-text"
        >
          <ChevronDownIcon class="w-4 h-4 mr-1 transition-transform" :class="{ 'rotate-180': showAdvanced }" />
          Advanced Options
        </button>

        <div v-if="showAdvanced" class="space-y-4 pl-2 border-l-2 border-surface-lighter">
          <div>
            <label class="block text-sm text-text-muted mb-1">PHP Version</label>
            <select
              v-model="form.php_version"
              class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
            >
              <option value="8.4">8.4</option>
              <option value="8.3">8.3</option>
              <option value="8.2">8.2</option>
              <option value="8.1">8.1</option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-text-muted mb-1">Database</label>
            <select
              v-model="form.database_type"
              class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary"
            >
              <option value="postgresql">PostgreSQL</option>
              <option value="mysql">MySQL</option>
            </select>
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors disabled:opacity-50"
        >
          {{ loading ? 'Creating...' : 'Create Server' }}
        </button>
      </form>
    </div>
  </div>
</template>
