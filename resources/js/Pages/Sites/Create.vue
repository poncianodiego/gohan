<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '../../Layouts/AppLayout.vue'
import AppHeader from '../../Components/AppHeader.vue'

const props = defineProps({
  server: Object,
})

const form = useForm({
  domain: '',
  project_type: 'laravel',
  fresh_laravel: true,
  repository: '',
  branch: 'main',
})

const pathway = ref('fresh')

function submit() {
  form.transform((data) => ({
    domain: data.domain,
    project_type: data.project_type,
    fresh_laravel: pathway.value === 'fresh',
    branch: data.branch,
    ...(pathway.value === 'repo' ? { repository: data.repository } : {}),
  })).post(`/servers/${props.server.id}/sites`)
}
</script>

<template>
  <AppLayout>
    <Head title="New Site" />
    <div class="pb-20">
      <AppHeader title="New Site" :back-to="`/servers/${server.id}`" />

      <div class="max-w-lg mx-auto px-4 py-4">
        <form @submit.prevent="submit" class="space-y-4">
          <div v-if="form.errors.domain" class="bg-danger/10 text-danger text-sm p-3 rounded-lg">
            {{ form.errors.domain }}
          </div>

          <div>
            <label class="block text-sm text-text-muted mb-1">Domain</label>
            <input v-model="form.domain" type="text" required placeholder="myapp.dev.example.com" class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary" />
          </div>

          <div>
            <label class="block text-sm text-text-muted mb-2">Setup</label>
            <div class="grid grid-cols-2 gap-2">
              <button type="button" @click="pathway = 'fresh'" class="p-3 rounded-lg border text-sm text-center transition-colors" :class="pathway === 'fresh' ? 'border-primary bg-primary/10 text-primary' : 'border-surface-lighter text-text-muted'">
                Fresh Laravel
              </button>
              <button type="button" @click="pathway = 'repo'" class="p-3 rounded-lg border text-sm text-center transition-colors" :class="pathway === 'repo' ? 'border-primary bg-primary/10 text-primary' : 'border-surface-lighter text-text-muted'">
                From Repository
              </button>
            </div>
          </div>

          <div v-if="pathway === 'repo'" class="space-y-4">
            <div>
              <label class="block text-sm text-text-muted mb-1">Repository URL</label>
              <input v-model="form.repository" type="text" placeholder="https://github.com/user/repo.git" class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary" />
            </div>
            <div>
              <label class="block text-sm text-text-muted mb-1">Branch</label>
              <input v-model="form.branch" type="text" class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text focus:outline-none focus:border-primary" />
            </div>
          </div>

          <button type="submit" :disabled="form.processing" class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors disabled:opacity-50">
            {{ form.processing ? 'Creating...' : 'Create Site' }}
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
