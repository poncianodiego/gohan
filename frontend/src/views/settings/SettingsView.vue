<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'
import api from '../../api/client'
import AppHeader from '../../components/AppHeader.vue'
import {
  KeyIcon,
  CloudIcon,
  CodeBracketIcon,
  ArrowRightStartOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const auth = useAuthStore()

const sshKeys = ref([])
const credentials = ref([])
const gitProviders = ref([])

const newSshKey = ref({ name: '', public_key: '' })
const newCredential = ref({ provider: 'digitalocean', name: '', token: '' })
const newGitProvider = ref({ provider: 'github', name: '', token: '' })

const showAddSsh = ref(false)
const showAddCredential = ref(false)
const showAddGit = ref(false)

onMounted(async () => {
  const [sshRes, credRes, gitRes] = await Promise.all([
    api.get('/ssh-keys'),
    api.get('/credentials'),
    api.get('/git-providers'),
  ])
  sshKeys.value = sshRes.data.data
  credentials.value = credRes.data.data
  gitProviders.value = gitRes.data.data
})

async function addSshKey() {
  const { data } = await api.post('/ssh-keys', newSshKey.value)
  sshKeys.value.unshift(data.data)
  newSshKey.value = { name: '', public_key: '' }
  showAddSsh.value = false
}

async function deleteSshKey(id) {
  await api.delete(`/ssh-keys/${id}`)
  sshKeys.value = sshKeys.value.filter(k => k.id !== id)
}

async function addCredential() {
  const { data } = await api.post('/credentials', newCredential.value)
  credentials.value.unshift(data.data)
  newCredential.value = { provider: 'digitalocean', name: '', token: '' }
  showAddCredential.value = false
}

async function deleteCredential(id) {
  await api.delete(`/credentials/${id}`)
  credentials.value = credentials.value.filter(c => c.id !== id)
}

async function addGitProvider() {
  const { data } = await api.post('/git-providers', newGitProvider.value)
  gitProviders.value.unshift(data.data)
  newGitProvider.value = { provider: 'github', name: '', token: '' }
  showAddGit.value = false
}

async function deleteGitProvider(id) {
  await api.delete(`/git-providers/${id}`)
  gitProviders.value = gitProviders.value.filter(g => g.id !== id)
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
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
          <input v-model="newSshKey.name" placeholder="Key name" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm focus:outline-none focus:border-primary" />
          <textarea v-model="newSshKey.public_key" placeholder="ssh-rsa AAAA..." rows="3" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary resize-none" />
          <button @click="addSshKey" class="px-4 py-2 bg-primary text-white text-sm rounded-lg">Save</button>
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
          <input v-model="newCredential.name" placeholder="Name (e.g. My DO Account)" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm focus:outline-none focus:border-primary" />
          <input v-model="newCredential.token" type="password" placeholder="DigitalOcean API Token" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary" />
          <button @click="addCredential" class="px-4 py-2 bg-primary text-white text-sm rounded-lg">Save</button>
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
          <select v-model="newGitProvider.provider" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm focus:outline-none focus:border-primary">
            <option value="github">GitHub</option>
            <option value="gitlab">GitLab</option>
          </select>
          <input v-model="newGitProvider.name" placeholder="Display name" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm focus:outline-none focus:border-primary" />
          <input v-model="newGitProvider.token" type="password" placeholder="Personal access token" class="w-full px-3 py-2 bg-surface border border-surface-lighter rounded-lg text-text text-sm font-mono focus:outline-none focus:border-primary" />
          <button @click="addGitProvider" class="px-4 py-2 bg-primary text-white text-sm rounded-lg">Save</button>
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
      <button
        @click="handleLogout"
        class="w-full flex items-center justify-center gap-2 py-3 bg-surface-light border border-surface-lighter text-danger rounded-lg hover:bg-danger/10 transition-colors"
      >
        <ArrowRightStartOnRectangleIcon class="w-4 h-4" />
        Sign Out
      </button>
    </div>
  </div>
</template>
