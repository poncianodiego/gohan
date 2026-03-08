<script setup>
import { Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

defineProps({
  title: { type: String, required: true },
  backTo: { type: String, default: null },
})
</script>

<template>
  <header class="sticky top-0 z-40 bg-surface/95 backdrop-blur border-b border-surface-lighter">
    <div class="flex items-center h-14 px-4 max-w-4xl mx-auto">
      <Link v-if="backTo" :href="backTo" class="mr-3 text-text-muted hover:text-text">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <h1 class="text-lg font-semibold truncate">{{ title }}</h1>
      <div class="ml-auto">
        <slot name="actions" />
      </div>
    </div>
  </header>
</template>
