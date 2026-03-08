<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  ServerStackIcon,
  Cog6ToothIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()
const url = computed(() => page.url)

const navItems = [
  { name: 'Servers', href: '/', icon: ServerStackIcon },
  { name: 'Settings', href: '/settings', icon: Cog6ToothIcon },
]
</script>

<template>
  <div class="min-h-screen bg-surface">
    <slot />

    <nav class="fixed bottom-0 left-0 right-0 bg-surface-light border-t border-surface-lighter safe-bottom z-50">
      <div class="flex justify-around items-center h-16 max-w-lg mx-auto">
        <Link
          v-for="item in navItems"
          :key="item.name"
          :href="item.href"
          class="flex flex-col items-center gap-1 px-4 py-2 text-xs transition-colors"
          :class="url === item.href || (item.href !== '/' && url.startsWith(item.href)) ? 'text-primary' : 'text-text-muted'"
        >
          <component :is="item.icon" class="w-6 h-6" />
          <span>{{ item.name }}</span>
        </Link>
      </div>
    </nav>
  </div>
</template>

<style scoped>
.safe-bottom {
  padding-bottom: env(safe-area-inset-bottom);
}
</style>
