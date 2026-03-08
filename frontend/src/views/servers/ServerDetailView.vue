<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useServerStore } from '../../stores/servers'
import { useSiteStore } from '../../stores/sites'
import AppHeader from '../../components/AppHeader.vue'
import StatusBadge from '../../components/StatusBadge.vue'
import LoadingSpinner from '../../components/LoadingSpinner.vue'
import {
  PlusIcon,
  ArrowPathIcon,
  TrashIcon,
  CommandLineIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const serverStore = useServerStore()
const siteStore = useSiteStore()

const serverId = computed(() => route.params.id)
const server = computed(() => serverStore.currentServer)
const deleting = ref(false)
const installingClaude = ref(false)

onMounted(async () => {
  await serverStore.fetchServer(serverId.value)
  await siteStore.fetchSites(serverId.value)
})

async function handleReboot() {
  if (!confirm('Reboot this server?')) return
  await serverStore.rebootServer(serverId.value)
}

async function handleDelete() {
  if (!confirm('Delete this server? This cannot be undone.')) return
  deleting.value = true
  await serverStore.deleteServer(serverId.value)
  router.push('/')
}

async function handleInstallClaudeCode() {
  installingClaude.value = true
  await serverStore.installClaudeCode(serverId.value)
  await serverStore.fetchServer(serverId.value)
  installingClaude.value = false
}
</script>

<template>
  <div class="pb-20">
    <AppHeader :title="server?.name || 'Server'" :back-to="'/'" />

    <LoadingSpinner v-if="serverStore.loading && !server" />

    <div v-else-if="server" class="max-w-4xl mx-auto px-4 py-4 space-y-4">
      <!-- Status Card -->
      <div class="bg-surface-light rounded-xl p-4 border border-surface-lighter">
        <div class="flex items-center justify-between mb-3">
          <StatusBadge :status="server.status" />
          <span class="text-xs text-text-muted">{{ server.provider }}</span>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <p class="text-text-muted text-xs">IP Address</p>
            <p class="text-text font-mono">{{ server.ip_address || '—' }}</p>
          </div>
          <div>
            <p class="text-text-muted text-xs">Region</p>
            <p class="text-text">{{ server.region }}</p>
          </div>
          <div>
            <p class="text-text-muted text-xs">Size</p>
            <p class="text-text">{{ server.size }}</p>
          </div>
          <div>
            <p class="text-text-muted text-xs">PHP</p>
            <p class="text-text">{{ server.php_version }}</p>
          </div>
        </div>
      </div>

      <!-- Claude Code -->
      <div class="bg-surface-light rounded-xl p-4 border border-surface-lighter">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <CommandLineIcon class="w-5 h-5 text-text-muted" />
            <span class="text-sm font-medium">Claude Code</span>
          </div>
          <button
            v-if="!server.claude_code_installed"
            @click="handleInstallClaudeCode"
            :disabled="installingClaude"
            class="px-3 py-1.5 bg-primary hover:bg-primary-dark text-white text-sm rounded-lg transition-colors disabled:opacity-50"
          >
            {{ installingClaude ? 'Installing...' : 'Install' }}
          </button>
          <div v-else class="flex items-center gap-1 text-success text-sm">
            <CheckCircleIcon class="w-4 h-4" />
            Installed
          </div>
        </div>
      </div>

      <!-- Sites -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-semibold">Sites</h2>
          <router-link
            :to="`/servers/${serverId}/sites/create`"
            class="flex items-center gap-1 text-sm text-primary hover:text-primary-dark"
          >
            <PlusIcon class="w-4 h-4" />
            Add Site
          </router-link>
        </div>

        <div v-if="!siteStore.sites.length" class="text-center py-8 text-text-muted text-sm">
          No sites yet
        </div>

        <div v-else class="space-y-2">
          <router-link
            v-for="site in siteStore.sites"
            :key="site.id"
            :to="`/sites/${site.id}`"
            class="block bg-surface-light rounded-xl p-4 border border-surface-lighter hover:border-primary/50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-medium text-text">{{ site.domain }}</h3>
                <p class="text-xs text-text-muted mt-0.5">{{ site.project_type }}</p>
              </div>
              <div class="flex items-center gap-2">
                <StatusBadge :status="site.visibility" />
                <span v-if="site.ssl_enabled" class="text-xs text-success">SSL</span>
              </div>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Actions -->
      <div class="space-y-2 pt-4">
        <button
          @click="handleReboot"
          class="w-full flex items-center justify-center gap-2 py-3 bg-surface-light border border-surface-lighter text-text rounded-lg hover:border-warning transition-colors"
        >
          <ArrowPathIcon class="w-4 h-4" />
          Reboot Server
        </button>
        <button
          @click="handleDelete"
          :disabled="deleting"
          class="w-full flex items-center justify-center gap-2 py-3 bg-surface-light border border-danger/30 text-danger rounded-lg hover:bg-danger/10 transition-colors disabled:opacity-50"
        >
          <TrashIcon class="w-4 h-4" />
          {{ deleting ? 'Deleting...' : 'Delete Server' }}
        </button>
      </div>
    </div>
  </div>
</template>
