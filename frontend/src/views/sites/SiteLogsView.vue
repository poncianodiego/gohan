<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../api/client'
import AppHeader from '../../components/AppHeader.vue'

const route = useRoute()
const siteId = computed(() => route.params.id)

const logTypes = ['nginx-access', 'nginx-error', 'laravel', 'php-fpm', 'queue']
const selectedType = ref('laravel')
const logLines = ref([])
const loading = ref(false)

async function fetchLogs() {
  loading.value = true
  try {
    const { data } = await api.get(`/sites/${siteId.value}/logs/${selectedType.value}`)
    logLines.value = data.data.lines || []
  } finally {
    loading.value = false
  }
}

onMounted(fetchLogs)
</script>

<template>
  <div class="pb-20">
    <AppHeader title="Logs" :back-to="`/sites/${siteId}`" />

    <div class="max-w-4xl mx-auto px-4 py-4">
      <!-- Log Type Selector -->
      <div class="flex gap-2 overflow-x-auto pb-3 -mx-4 px-4 scrollbar-hide">
        <button
          v-for="type in logTypes"
          :key="type"
          @click="selectedType = type; fetchLogs()"
          class="px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-colors"
          :class="selectedType === type ? 'bg-primary text-white' : 'bg-surface-light text-text-muted'"
        >
          {{ type }}
        </button>
      </div>

      <!-- Log Output -->
      <div class="bg-surface-light rounded-xl border border-surface-lighter overflow-hidden">
        <div class="p-4 font-mono text-xs leading-relaxed text-text overflow-x-auto min-h-[300px]">
          <div v-if="loading" class="text-text-muted">Loading...</div>
          <div v-else-if="!logLines.length" class="text-text-muted">
            No log entries. Connect via WebSocket for real-time streaming.
          </div>
          <div v-for="(line, i) in logLines" :key="i" class="whitespace-pre">{{ line }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
