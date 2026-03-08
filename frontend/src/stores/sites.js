import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/client'

export const useSiteStore = defineStore('sites', () => {
  const sites = ref([])
  const currentSite = ref(null)
  const loading = ref(false)

  async function fetchSites(serverId) {
    loading.value = true
    try {
      const { data } = await api.get(`/servers/${serverId}/sites`)
      sites.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function fetchSite(id) {
    loading.value = true
    try {
      const { data } = await api.get(`/sites/${id}`)
      currentSite.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function createSite(serverId, siteData) {
    const { data } = await api.post(`/servers/${serverId}/sites`, siteData)
    sites.value.unshift(data.data)
    return data.data
  }

  async function deleteSite(id) {
    await api.delete(`/sites/${id}`)
    sites.value = sites.value.filter(s => s.id !== id)
  }

  async function deploy(siteId) {
    const { data } = await api.post(`/sites/${siteId}/deployments`)
    return data.data
  }

  async function toggleVisibility(siteId, visibility) {
    await api.post(`/sites/${siteId}/visibility`, { visibility })
  }

  async function installSsl(siteId) {
    await api.post(`/sites/${siteId}/ssl`)
  }

  async function fetchEnv(siteId) {
    const { data } = await api.get(`/sites/${siteId}/env`)
    return data.data.env
  }

  async function updateEnv(siteId, env) {
    await api.put(`/sites/${siteId}/env`, { env })
  }

  return {
    sites, currentSite, loading,
    fetchSites, fetchSite, createSite, deleteSite,
    deploy, toggleVisibility, installSsl, fetchEnv, updateEnv,
  }
})
