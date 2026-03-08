<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '../../Layouts/AppLayout.vue'
import AppHeader from '../../Components/AppHeader.vue'

const props = defineProps({
  site: Object,
  logLines: { type: Array, default: () => [] },
  logType: { type: String, default: 'laravel' },
})

const logTypes = ['nginx-access', 'nginx-error', 'laravel', 'php-fpm', 'queue']
const selectedType = ref(props.logType)

function fetchLogs(type) {
  selectedType.value = type
  router.get(`/sites/${props.site.id}/logs`, { type }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Logs" />
    <div class="pb-20">
      <AppHeader title="Logs" :back-to="`/sites/${site.id}`" />

      <div class="max-w-4xl mx-auto px-4 py-4">
        <div class="flex gap-2 overflow-x-auto pb-3 -mx-4 px-4 scrollbar-hide">
          <button
            v-for="type in logTypes" :key="type"
            @click="fetchLogs(type)"
            class="px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-colors"
            :class="selectedType === type ? 'bg-primary text-white' : 'bg-surface-light text-text-muted'"
          >
            {{ type }}
          </button>
        </div>

        <div class="bg-surface-light rounded-xl border border-surface-lighter overflow-hidden">
          <div class="p-4 font-mono text-xs leading-relaxed text-text overflow-x-auto min-h-[300px]">
            <div v-if="!logLines.length" class="text-text-muted">No log entries.</div>
            <div v-for="(line, i) in logLines" :key="i" class="whitespace-pre">{{ line }}</div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
