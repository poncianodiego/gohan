<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useSiteStore } from '../../stores/sites'
import AppHeader from '../../components/AppHeader.vue'
import StatusBadge from '../../components/StatusBadge.vue'
import LoadingSpinner from '../../components/LoadingSpinner.vue'
import {
  RocketLaunchIcon,
  LockClosedIcon,
  LockOpenIcon,
  ShieldCheckIcon,
  DocumentTextIcon,
  Cog6ToothIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const siteStore = useSiteStore()

const siteId = computed(() => route.params.id)
const site = computed(() => siteStore.currentSite)
const deploying = ref(false)
const togglingVisibility = ref(false)
const installingSsl = ref(false)

onMounted(() => {
  siteStore.fetchSite(siteId.value)
})

async function handleDeploy() {
  deploying.value = true
  try {
    await siteStore.deploy(siteId.value)
    await siteStore.fetchSite(siteId.value)
  } finally {
    deploying.value = false
  }
}

async function handleToggleVisibility() {
  togglingVisibility.value = true
  const newVisibility = site.value.visibility === 'private' ? 'public' : 'private'
  try {
    await siteStore.toggleVisibility(siteId.value, newVisibility)
    await siteStore.fetchSite(siteId.value)
  } finally {
    togglingVisibility.value = false
  }
}

async function handleInstallSsl() {
  installingSsl.value = true
  try {
    await siteStore.installSsl(siteId.value)
    await siteStore.fetchSite(siteId.value)
  } finally {
    installingSsl.value = false
  }
}
</script>

<template>
  <div class="pb-20">
    <AppHeader
      :title="site?.domain || 'Site'"
      :back-to="site ? `/servers/${site.server_id}` : '/'"
    />

    <LoadingSpinner v-if="siteStore.loading && !site" />

    <div v-else-if="site" class="max-w-4xl mx-auto px-4 py-4 space-y-4">
      <!-- Deploy Button -->
      <button
        @click="handleDeploy"
        :disabled="deploying"
        class="w-full py-4 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl flex items-center justify-center gap-2 transition-colors disabled:opacity-50"
      >
        <RocketLaunchIcon class="w-5 h-5" />
        {{ deploying ? 'Deploying...' : 'Deploy' }}
      </button>

      <!-- Site Info -->
      <div class="bg-surface-light rounded-xl p-4 border border-surface-lighter">
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <p class="text-text-muted text-xs">Domain</p>
            <p class="text-text font-mono text-xs">{{ site.domain }}</p>
          </div>
          <div>
            <p class="text-text-muted text-xs">Branch</p>
            <p class="text-text font-mono text-xs">{{ site.branch }}</p>
          </div>
          <div>
            <p class="text-text-muted text-xs">PHP Version</p>
            <p class="text-text">{{ site.php_version }}</p>
          </div>
          <div>
            <p class="text-text-muted text-xs">Status</p>
            <StatusBadge :status="site.status" />
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-2 gap-2">
        <button
          @click="handleToggleVisibility"
          :disabled="togglingVisibility"
          class="flex items-center gap-2 p-3 bg-surface-light border border-surface-lighter rounded-xl text-sm hover:border-primary/50 transition-colors"
        >
          <component :is="site.visibility === 'private' ? LockClosedIcon : LockOpenIcon" class="w-4 h-4 text-text-muted" />
          <span>{{ site.visibility === 'private' ? 'Make Public' : 'Make Private' }}</span>
        </button>

        <button
          @click="handleInstallSsl"
          :disabled="installingSsl || site.ssl_enabled"
          class="flex items-center gap-2 p-3 bg-surface-light border border-surface-lighter rounded-xl text-sm hover:border-primary/50 transition-colors disabled:opacity-50"
        >
          <ShieldCheckIcon class="w-4 h-4" :class="site.ssl_enabled ? 'text-success' : 'text-text-muted'" />
          <span>{{ site.ssl_enabled ? 'SSL Active' : 'Install SSL' }}</span>
        </button>
      </div>

      <!-- Links -->
      <div class="space-y-2">
        <router-link
          :to="`/sites/${siteId}/logs`"
          class="flex items-center gap-3 p-4 bg-surface-light border border-surface-lighter rounded-xl hover:border-primary/50 transition-colors"
        >
          <DocumentTextIcon class="w-5 h-5 text-text-muted" />
          <span class="text-sm">View Logs</span>
        </router-link>

        <router-link
          :to="`/sites/${siteId}/env`"
          class="flex items-center gap-3 p-4 bg-surface-light border border-surface-lighter rounded-xl hover:border-primary/50 transition-colors"
        >
          <Cog6ToothIcon class="w-5 h-5 text-text-muted" />
          <span class="text-sm">Environment Variables</span>
        </router-link>
      </div>

      <!-- Recent Deployments -->
      <div v-if="site.deployments?.length">
        <h3 class="text-sm font-semibold text-text-muted mb-2">Recent Deployments</h3>
        <div class="space-y-2">
          <div
            v-for="dep in site.deployments"
            :key="dep.id"
            class="bg-surface-light rounded-lg p-3 border border-surface-lighter text-sm"
          >
            <div class="flex items-center justify-between">
              <span class="font-mono text-xs text-text-muted">{{ dep.commit_hash?.substring(0, 7) || '—' }}</span>
              <StatusBadge :status="dep.status" />
            </div>
            <p v-if="dep.commit_message" class="text-xs text-text-muted mt-1 truncate">{{ dep.commit_message }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
