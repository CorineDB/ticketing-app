import { ref } from 'vue'
import gateService from '@/services/gateService'
import type { Gate, CreateGateData, UpdateGateData, GateFilters } from '@/types/api'

export function useGates() {
  const gates = ref<Gate[]>([])
  const gate = ref<Gate | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchGates = async (filters?: GateFilters) => {
    loading.value = true
    error.value = null
    try {
      const response = await gateService.getAll(filters)
      gates.value = response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch gates'
      console.error('Error fetching gates:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchGatesByEvent = async (eventId: string, filters?: GateFilters) => {
    loading.value = true
    error.value = null
    try {
      gates.value = await gateService.getByEventId(eventId, filters)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch gates'
      console.error('Error fetching gates:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchGate = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      gate.value = await gateService.getById(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch gate'
      console.error('Error fetching gate:', e)
    } finally {
      loading.value = false
    }
  }

  const createGate = async (data: CreateGateData) => {
    loading.value = true
    error.value = null
    try {
      const newGate = await gateService.create(data)
      gates.value.push(newGate)
      return newGate
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create gate'
      console.error('Error creating gate:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateGate = async (id: string, data: UpdateGateData) => {
    loading.value = true
    error.value = null
    try {
      const updatedGate = await gateService.update(id, data)
      const index = gates.value.findIndex(g => g.id === id)
      if (index !== -1) {
        gates.value[index] = updatedGate
      }
      if (gate.value?.id === id) {
        gate.value = updatedGate
      }
      return updatedGate
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update gate'
      console.error('Error updating gate:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const deleteGate = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      await gateService.delete(id)
      gates.value = gates.value.filter(g => g.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to delete gate'
      console.error('Error deleting gate:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateGateStatus = async (id: string, status: 'active' | 'pause' | 'inactive') => {
    loading.value = true
    error.value = null
    try {
      const updated = await gateService.updateStatus(id, status)
      const index = gates.value.findIndex(g => g.id === id)
      if (index !== -1) {
        gates.value[index] = updated
      }
      if (gate.value?.id === id) {
        gate.value = updated
      }
      return updated
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update gate status'
      console.error('Error updating gate status:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    gates,
    gate,
    loading,
    error,
    fetchGates,
    fetchGatesByEvent,
    fetchGate,
    createGate,
    updateGate,
    deleteGate,
    updateGateStatus
  }
}
