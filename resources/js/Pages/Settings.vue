<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '../Layouts/AppLayout.vue'
import AppHeader from '../Components/AppHeader.vue'
import {
  KeyIcon,
  CloudIcon,
  CodeBracketIcon,
  ArrowRightStartOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  sshKeys: { type: Array, default: () => [] },
  credentials: { type: Array, default: () => [] },
  gitProviders: { type: Array, default: () => [] },
})

const showAddSsh = ref(false)
const showAddCredential = ref(false)
const showAddGit = ref(false)

const sshForm = useForm({ name: '', public_key: '' })
const credForm = useForm({ provider: 'digitalocean', name: '', token: '' })
const gitForm = useForm({ provider: 'github', name: '', token: '' })

function addSshKey() {
  sshForm.post('/settings/ssh-keys', {
    preserveScroll: true,
    onSuccess: () => { sshForm.reset(); showAddSsh.value = false },
  })
}

function deleteSshKey(id) {
  router.delete(`/settings/ssh-keys/${id}`, { preserveScroll: true })
}

function addCredential() {
  credForm.post('/settings/credentials', {
    preserveScroll: true,
    onSuccess: () => { credForm.reset(); showAddCredential.value = false },
  })
}

function deleteCredential(id) {
  router.delete(`/settings/credentials/${id}`, { preserveScroll: true })
}

function addGitProvider() {
  gitForm.post('/settings/git-providers', {
    preserveScroll: true,
    onSuccess: () => { gitForm.reset(); showAddGit.value = false },
  })
}

function deleteGitProvider(id) {
  router.delete(`/settings/git-providers/${id}`, { preserveScroll: true })
}

function handleLogout() {
  router.post('/logout')
}
</script>

<template>
  <AppLayout>
    <Head title="Settings" />
    <div class="pb-20">
      <AppHeader title="Settings" />

      <div class="max-w-lg mx-auto px-4 py-4 space-y-6">
        <!-- SSH Keys -->
        <section>
          <div class="flex items-center justify-between mb-3">
            <h2 class="flex items-center gap-2 font-semibold">
              <KeyIcon class="w-5 h-5 text-text-muted" /> SSH Keys
            </h2>
            <button @click="showAddSsh = !showAddSsh" class="text-sm text-primary">
              {{ showAddSsh ? 'Cancel' : '+ Add' }}
            </button>
          </div>

          <div v-if="showAddSsh" class="bg-surface-light rounded-xl p-4 border border-surface-lighter mb-3 space-y-3">
            <input v-model="sshForm.name" placeholder="Key name" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm focus:outline-none focus:border-primary" />
            <textarea v-model="sshForm.public_key" placeholder="ssh-rsa AAAA..." rows="3" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary resize-none" />
            <button @click="addSshKey" :disabled="sshForm.processing" class="px-4 py-2 bg-primary text-white text-sm rounded-lg">Save</button>
          </div>

          <div v-for="key in sshKeys" :key="key.id" class="flex items-center justify-between bg-surface-light rounded-lg p-3 border border-surface-lighter mb-2">
            <div>
              <p class="text-sm font-medium text-text">{{ key.name }}</p>
              <p class="text-xs text-text-muted font-mono">{{ key.fingerprint }}</p>
            </div>
            <button @click="deleteSshKey(key.id)" class="text-xs text-danger hover:underline">Remove</button>
          </div>
          <p v-if="!sshKeys.length && !showAddSsh" class="text-sm text-text-muted">No SSH keys added.</p>
        </section>

        <!-- Cloud Credentials -->
        <section>
          <div class="flex items-center justify-between mb-3">
            <h2 class="flex items-center gap-2 font-semibold">
              <CloudIcon class="w-5 h-5 text-text-muted" /> Cloud Credentials
            </h2>
            <button @click="showAddCredential = !showAddCredential" class="text-sm text-primary">
              {{ showAddCredential ? 'Cancel' : '+ Add' }}
            </button>
          </div>

          <div v-if="showAddCredential" class="bg-surface-light rounded-xl p-4 border border-surface-lighter mb-3 space-y-3">
            <input v-model="credForm.name" placeholder="Name (e.g. My DO Account)" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm focus:outline-none focus:border-primary" />
            <input v-model="credForm.token" type="password" placeholder="DigitalOcean API Token" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary" />
            <button @click="addCredential" :disabled="credForm.processing" class="px-4 py-2 bg-primary text-white text-sm rounded-lg">Save</button>
          </div>

          <div v-for="cred in credentials" :key="cred.id" class="flex items-center justify-between bg-surface-light rounded-lg p-3 border border-surface-lighter mb-2">
            <div>
              <p class="text-sm font-medium text-text">{{ cred.name }}</p>
              <p class="text-xs text-text-muted">{{ cred.provider }}</p>
            </div>
            <button @click="deleteCredential(cred.id)" class="text-xs text-danger hover:underline">Remove</button>
          </div>
          <p v-if="!credentials.length && !showAddCredential" class="text-sm text-text-muted">No credentials added.</p>
        </section>

        <!-- Git Providers -->
        <section>
          <div class="flex items-center justify-between mb-3">
            <h2 class="flex items-center gap-2 font-semibold">
              <CodeBracketIcon class="w-5 h-5 text-text-muted" /> Git Providers
            </h2>
            <button @click="showAddGit = !showAddGit" class="text-sm text-primary">
              {{ showAddGit ? 'Cancel' : '+ Add' }}
            </button>
          </div>

          <div v-if="showAddGit" class="bg-surface-light rounded-xl p-4 border border-surface-lighter mb-3 space-y-3">
            <select v-model="gitForm.provider" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm focus:outline-none focus:border-primary">
              <option value="github">GitHub</option>
              <option value="gitlab">GitLab</option>
            </select>
            <input v-model="gitForm.name" placeholder="Display name" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm focus:outline-none focus:border-primary" />
            <input v-model="gitForm.token" type="password" placeholder="Personal access token" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary" />
            <button @click="addGitProvider" :disabled="gitForm.processing" class="px-4 py-2 bg-primary text-white text-sm rounded-lg">Save</button>
          </div>

          <div v-for="provider in gitProviders" :key="provider.id" class="flex items-center justify-between bg-surface-light rounded-lg p-3 border border-surface-lighter mb-2">
            <div>
              <p class="text-sm font-medium text-text">{{ provider.name }}</p>
              <p class="text-xs text-text-muted">{{ provider.provider }}</p>
            </div>
            <button @click="deleteGitProvider(provider.id)" class="text-xs text-danger hover:underline">Remove</button>
          </div>
          <p v-if="!gitProviders.length && !showAddGit" class="text-sm text-text-muted">No git providers connected.</p>
        </section>

        <!-- Logout -->
        <button @click="handleLogout" class="w-full flex items-center justify-center gap-2 py-3 bg-surface-light border border-surface-lighter text-danger rounded-lg hover:bg-danger/10 transition-colors">
          <ArrowRightStartOnRectangleIcon class="w-4 h-4" />
          Sign Out
        </button>
      </div>
    </div>
  </AppLayout>
</template>
