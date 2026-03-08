<script setup>
import { useRoute } from 'vue-router'
import {
  ServerStackIcon,
  GlobeAltIcon,
  KeyIcon,
  Cog6ToothIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()

const navItems = [
  { name: 'Servers', to: '/', icon: ServerStackIcon },
  { name: 'Settings', to: '/settings', icon: Cog6ToothIcon },
]
</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-surface-light border-t border-surface-lighter safe-bottom z-50">
    <div class="flex justify-around items-center h-16 max-w-lg mx-auto">
      <router-link
        v-for="item in navItems"
        :key="item.name"
        :to="item.to"
        class="flex flex-col items-center gap-1 px-4 py-2 text-xs transition-colors"
        :class="route.path === item.to ? 'text-primary' : 'text-text-muted'"
      >
        <component :is="item.icon" class="w-6 h-6" />
        <span>{{ item.name }}</span>
      </router-link>
    </div>
  </nav>
</template>

<style scoped>
.safe-bottom {
  padding-bottom: env(safe-area-inset-bottom);
}
</style>
