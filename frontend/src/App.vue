<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import BottomNav from './components/BottomNav.vue'

const auth = useAuthStore()

onMounted(async () => {
  if (auth.isAuthenticated) {
    await auth.fetchUser()
  }
})
</script>

<template>
  <div class="min-h-screen bg-surface text-text">
    <router-view />
    <BottomNav v-if="auth.isAuthenticated" />
  </div>
</template>
