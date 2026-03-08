<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useSiteStore } from '../../stores/sites'
import AppHeader from '../../components/AppHeader.vue'

const route = useRoute()
const siteStore = useSiteStore()
const siteId = computed(() => route.params.id)

const envVars = ref([])
const rawMode = ref(false)
const rawContent = ref('')
const saving = ref(false)
const saved = ref(false)

onMounted(async () => {
  const content = await siteStore.fetchEnv(siteId.value)
  rawContent.value = content
  parseEnv(content)
})

function parseEnv(content) {
  envVars.value = content.split('\n')
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

async function handleSave() {
  saving.value = true
  try {
    const content = rawMode.value ? rawContent.value : toRaw()
    await siteStore.updateEnv(siteId.value, content)
    saved.value = true
    setTimeout(() => saved.value = false, 2000)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="pb-20">
    <AppHeader title="Environment" :back-to="`/sites/${siteId}`">
      <template #actions>
        <button
          @click="rawMode = !rawMode"
          class="text-xs text-text-muted hover:text-text px-2 py-1"
        >
          {{ rawMode ? 'Key-Value' : 'Raw' }}
        </button>
      </template>
    </AppHeader>

    <div class="max-w-lg mx-auto px-4 py-4">
      <!-- Key-Value Mode -->
      <div v-if="!rawMode" class="space-y-2">
        <div
          v-for="(env, index) in envVars"
          :key="index"
          class="flex gap-2"
        >
          <input
            v-model="env.key"
            placeholder="KEY"
            class="flex-1 px-3 py-2 bg-surface-light border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary"
          />
          <input
            v-model="env.value"
            placeholder="value"
            class="flex-1 px-3 py-2 bg-surface-light border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary"
          />
        </div>
        <button
          @click="addVar"
          class="text-sm text-primary hover:text-primary-dark"
        >
          + Add Variable
        </button>
      </div>

      <!-- Raw Mode -->
      <textarea
        v-else
        v-model="rawContent"
        rows="20"
        class="w-full px-4 py-3 bg-surface-light border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary resize-none"
      />

      <button
        @click="handleSave"
        :disabled="saving"
        class="w-full mt-4 py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors disabled:opacity-50"
      >
        {{ saved ? 'Saved!' : saving ? 'Saving...' : 'Save Environment' }}
      </button>
    </div>
  </div>
</template>
