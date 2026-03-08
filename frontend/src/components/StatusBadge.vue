<script setup>
const props = defineProps({
  status: { type: String, required: true },
})

const statusStyles = {
  active: 'bg-success/20 text-success',
  provisioning: 'bg-warning/20 text-warning',
  unreachable: 'bg-danger/20 text-danger',
  deleting: 'bg-danger/20 text-danger',
  installing: 'bg-warning/20 text-warning',
  running: 'bg-success/20 text-success',
  pending: 'bg-text-muted/20 text-text-muted',
  completed: 'bg-success/20 text-success',
  failed: 'bg-danger/20 text-danger',
  stopped: 'bg-text-muted/20 text-text-muted',
  private: 'bg-primary/20 text-primary',
  public: 'bg-success/20 text-success',
}
</script>

<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="statusStyles[status] || 'bg-text-muted/20 text-text-muted'"
  >
    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="{
      'bg-success': ['active', 'running', 'completed', 'public'].includes(status),
      'bg-warning': ['provisioning', 'installing'].includes(status),
      'bg-danger': ['unreachable', 'deleting', 'failed'].includes(status),
      'bg-text-muted': ['pending', 'stopped'].includes(status),
      'bg-primary': status === 'private',
    }" />
    {{ status }}
  </span>
</template>
