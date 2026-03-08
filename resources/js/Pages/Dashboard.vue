<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '../Layouts/AppLayout.vue'
import AppHeader from '../Components/AppHeader.vue'
import StatusBadge from '../Components/StatusBadge.vue'
import { PlusIcon } from '@heroicons/vue/24/solid'

defineProps({
  servers: { type: Array, default: () => [] },
})
</script>

<template>
  <AppLayout>
    <Head title="Servers" />
    <div class="pb-20">
      <AppHeader title="Servers" />

      <div class="max-w-4xl mx-auto px-4 py-4">
        <div v-if="!servers.length" class="text-center py-16">
          <p class="text-text-muted mb-4">No servers yet</p>
          <Link
            href="/servers/create"
            class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors"
          >
            <PlusIcon class="w-5 h-5 mr-1" />
            New Server
          </Link>
        </div>

        <div v-else class="space-y-3">
          <Link
            v-for="server in servers"
            :key="server.id"
            :href="`/servers/${server.id}`"
            class="block bg-surface-light rounded-xl p-4 border border-surface-lighter hover:border-primary/50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-semibold text-text">{{ server.name }}</h3>
                <p class="text-sm text-text-muted mt-0.5">
                  {{ server.ip_address || 'Provisioning...' }}
                  <span class="mx-1">&middot;</span>
                  {{ server.region }}
                </p>
              </div>
              <StatusBadge :status="server.status" />
            </div>
            <div class="flex items-center gap-3 mt-3 text-xs text-text-muted">
              <span>{{ server.size }}</span>
              <span>&middot;</span>
              <span>PHP {{ server.php_version }}</span>
              <span>&middot;</span>
              <span>{{ server.sites_count || 0 }} sites</span>
            </div>
          </Link>
        </div>
      </div>

      <Link
        v-if="servers.length"
        href="/servers/create"
        class="fixed bottom-20 right-4 w-14 h-14 bg-primary hover:bg-primary-dark text-white rounded-full shadow-lg flex items-center justify-center transition-colors z-40"
      >
        <PlusIcon class="w-7 h-7" />
      </Link>
    </div>
  </AppLayout>
</template>
