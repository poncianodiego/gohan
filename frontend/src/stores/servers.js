import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/client'

export const useServerStore = defineStore('servers', () => {
  const servers = ref([])
  const currentServer = ref(null)
  const loading = ref(false)

  async function fetchServers() {
    loading.value = true
    try {
      const { data } = await api.get('/servers')
      servers.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function fetchServer(id) {
    loading.value = true
    try {
      const { data } = await api.get(`/servers/${id}`)
      currentServer.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function createServer(serverData) {
    const { data } = await api.post('/servers', serverData)
    servers.value.unshift(data.data)
    return data.data
  }

  async function deleteServer(id) {
    await api.delete(`/servers/${id}`)
    servers.value = servers.value.filter(s => s.id !== id)
  }

  async function rebootServer(id) {
    await api.post(`/servers/${id}/reboot`)
  }

  async function installClaudeCode(id) {
    await api.post(`/servers/${id}/claude-code`)
  }

  return {
    servers, currentServer, loading,
    fetchServers, fetchServer, createServer, deleteServer,
    rebootServer, installClaudeCode,
  }
})
