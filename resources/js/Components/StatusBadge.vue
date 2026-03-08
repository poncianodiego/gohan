<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true },
})

const colorClass = computed(() => {
  const map = {
    active: 'bg-success/20 text-success',
    running: 'bg-success/20 text-success',
    completed: 'bg-success/20 text-success',
    public: 'bg-success/20 text-success',
    provisioning: 'bg-warning/20 text-warning',
    pending: 'bg-warning/20 text-warning',
    installing: 'bg-warning/20 text-warning',
    private: 'bg-primary/20 text-primary',
    failed: 'bg-danger/20 text-danger',
    unreachable: 'bg-danger/20 text-danger',
    deleting: 'bg-danger/20 text-danger',
  }
  return map[props.status] || 'bg-surface-lighter text-text-muted'
})
</script>

<template>
  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="colorClass">
    {{ status }}
  </span>
</template>
