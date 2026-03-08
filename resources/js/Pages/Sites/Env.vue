<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '../../Layouts/AppLayout.vue'
import AppHeader from '../../Components/AppHeader.vue'

const props = defineProps({
  site: Object,
  envContent: { type: String, default: '' },
})

const envVars = ref(parseEnv(props.envContent))
const rawMode = ref(false)
const rawContent = ref(props.envContent)
const saving = ref(false)
const saved = ref(false)

function parseEnv(content) {
  return content.split('\n')
    .filter(line => line.trim() && !line.startsWith('#'))
    .map(line => {
      const [key, ...rest] = line.split('=')
      return { key: key.trim(), value: rest.join('=').trim() }
    })
}

function toRaw() {
  return envVars.value.map(v => `${v.key}=${v.value}`).join('\n')
}

function addVar() {
  envVars.value.push({ key: '', value: '' })
}

function handleSave() {
  saving.value = true
  const content = rawMode.value ? rawContent.value : toRaw()
  router.put(`/sites/${props.site.id}/env`, { env: content }, {
    preserveScroll: true,
    onSuccess: () => {
      saved.value = true
      setTimeout(() => saved.value = false, 2000)
    },
    onFinish: () => saving.value = false,
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Environment" />
    <div class="pb-20">
      <AppHeader title="Environment" :back-to="`/sites/${site.id}`">
        <template #actions>
          <button @click="rawMode = !rawMode" class="text-xs text-text-muted hover:text-text px-2 py-1">
            {{ rawMode ? 'Key-Value' : 'Raw' }}
          </button>
        </template>
      </AppHeader>

      <div class="max-w-lg mx-auto px-4 py-4">
        <div v-if="!rawMode" class="space-y-2">
          <div v-for="(env, index) in envVars" :key="index" class="flex gap-2">
            <input v-model="env.key" placeholder="KEY" class="flex-1 px-3 py-2 bg-surface-light border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary" />
            <input v-model="env.value" placeholder="value" class="flex-1 px-3 py-2 bg-surface-light border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary" />
          </div>
          <button @click="addVar" class="text-sm text-primary hover:text-primary-dark">+ Add Variable</button>
        </div>

        <textarea v-else v-model="rawContent" rows="20" class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary resize-none" />

        <button @click="handleSave" :disabled="saving" class="w-full mt-4 py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors disabled:opacity-50">
          {{ saved ? 'Saved!' : saving ? 'Saving...' : 'Save Environment' }}
        </button>
      </div>
    </div>
  </AppLayout>
</template>
