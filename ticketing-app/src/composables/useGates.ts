/**
 * useGates Composable
 * Business logic for gate management
 */

import { ref, computed } from 'vue'
import type {
  Gate,
  CreateGateData,
  UpdateGateData,
  GateFilters,
  GateType,
  GateStatus
} from '@/types/api'
import gateService from '@/services/gateService'
import { useNotificationStore } from '@/stores/notifications'

export function useGates(eventId?: number) {
  const notifications = useNotificationStore()

  // State
  const gates = ref<Gate[]>([])
  const currentGate = ref<Gate | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const activeGates = computed(() =>
    gates.value.filter(gate => gate.status === 'active')
  )

  const entranceGates = computed(() =>
    gates.value.filter(gate => gate.gate_type === 'entrance')
  )

  const exitGates = computed(() =>
    gates.value.filter(gate => gate.gate_type === 'exit')
  )

  const vipGates = computed(() =>
    gates.value.filter(gate => gate.gate_type === 'vip')
  )

  const gatesByType = computed(() => {
    const byType: Record<GateType, Gate[]> = {
      entrance: [],
      exit: [],
      vip: [],
      other: []
    }

    gates.value.forEach(gate => {
      byType[gate.gate_type].push(gate)
    })

    return byType
  })

  const gatesByStatus = computed(() => {
    const byStatus: Record<GateStatus, Gate[]> = {
      active: [],
      pause: [],
      inactive: []
    }

    gates.value.forEach(gate => {
      byStatus[gate.status].push(gate)
    })

    return byStatus
  })

  // Actions

  /**
   * Fetch all gates with optional filters
   */
  async function fetchGates(filters?: GateFilters) {
    loading.value = true
    error.value = null

    try {
      if (eventId) {
        gates.value = await gateService.getByEventId(eventId, filters)
      } else {
        const response = await gateService.getAll(filters)
        gates.value = response.data
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch gates'
      notifications.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch a single gate by ID
   */
  async function fetchGateById(id: number) {
    loading.value = true
    error.value = null

    try {
      currentGate.value = await gateService.getById(id)
      return currentGate.value
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch gate'
      notifications.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a new gate
   */
  async function createGate(data: CreateGateData) {
    loading.value = true
    error.value = null

    try {
      const newGate = await gateService.create(data)
      gates.value.push(newGate)
      notifications.success('Success', 'Gate created successfully')
      return newGate
    } catch (err: any) {
      error.value = err.message || 'Failed to create gate'
      notifications.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update an existing gate
   */
  async function updateGate(id: number, data: UpdateGateData) {
    loading.value = true
    error.value = null

    try {
      const updatedGate = await gateService.update(id, data)

      // Update in local array
      const index = gates.value.findIndex(g => g.id === id)
      if (index !== -1) {
        gates.value[index] = updatedGate
      }

      // Update current gate if it's the same
      if (currentGate.value?.id === id) {
        currentGate.value = updatedGate
      }

      notifications.success('Success', 'Gate updated successfully')
      return updatedGate
    } catch (err: any) {
      error.value = err.message || 'Failed to update gate'
      notifications.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete a gate
   */
  async function deleteGate(id: number) {
    loading.value = true
    error.value = null

    try {
      await gateService.delete(id)

      // Remove from local array
      gates.value = gates.value.filter(g => g.id !== id)

      // Clear current gate if it's the same
      if (currentGate.value?.id === id) {
        currentGate.value = null
      }

      notifications.success('Success', 'Gate deleted successfully')
    } catch (err: any) {
      error.value = err.message || 'Failed to delete gate'
      notifications.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update gate status
   */
  async function updateGateStatus(id: number, status: GateStatus) {
    loading.value = true
    error.value = null

    try {
      const updatedGate = await gateService.updateStatus(id, status)

      // Update in local array
      const index = gates.value.findIndex(g => g.id === id)
      if (index !== -1) {
        gates.value[index] = updatedGate
      }

      notifications.success('Success', `Gate ${status === 'active' ? 'activated' : status === 'pause' ? 'paused' : 'deactivated'}`)
      return updatedGate
    } catch (err: any) {
      error.value = err.message || 'Failed to update gate status'
      notifications.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Assign scanner to gate
   */
  async function assignScanner(gateId: number, scannerId: number | null) {
    loading.value = true
    error.value = null

    try {
      const updatedGate = await gateService.assignScanner(gateId, scannerId)

      // Update in local array
      const index = gates.value.findIndex(g => g.id === gateId)
      if (index !== -1) {
        gates.value[index] = updatedGate
      }

      notifications.success(
        'Success',
        scannerId ? 'Scanner assigned successfully' : 'Scanner unassigned'
      )
      return updatedGate
    } catch (err: any) {
      error.value = err.message || 'Failed to assign scanner'
      notifications.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Get gate statistics
   */
  async function fetchGateStatistics(gateId: number, dateFrom?: string, dateTo?: string) {
    loading.value = true
    error.value = null

    try {
      return await gateService.getStatistics(gateId, dateFrom, dateTo)
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch statistics'
      notifications.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Bulk create gates for event
   */
  async function bulkCreateGates(gateData: Omit<CreateGateData, 'event_id'>[]) {
    if (!eventId) {
      throw new Error('Event ID is required for bulk create')
    }

    loading.value = true
    error.value = null

    try {
      const newGates = await gateService.bulkCreate(eventId, gateData)
      gates.value.push(...newGates)
      notifications.success('Success', `${newGates.length} gates created successfully`)
      return newGates
    } catch (err: any) {
      error.value = err.message || 'Failed to create gates'
      notifications.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Get gate by ID from local array
   */
  function getGateById(id: number): Gate | undefined {
    return gates.value.find(g => g.id === id)
  }

  /**
   * Filter gates by type
   */
  function filterByType(type: GateType): Gate[] {
    return gates.value.filter(g => g.gate_type === type)
  }

  /**
   * Filter gates by status
   */
  function filterByStatus(status: GateStatus): Gate[] {
    return gates.value.filter(g => g.status === status)
  }

  return {
    // State
    gates,
    currentGate,
    loading,
    error,

    // Computed
    activeGates,
    entranceGates,
    exitGates,
    vipGates,
    gatesByType,
    gatesByStatus,

    // Actions
    fetchGates,
    fetchGateById,
    createGate,
    updateGate,
    deleteGate,
    updateGateStatus,
    assignScanner,
    fetchGateStatistics,
    bulkCreateGates,
    getGateById,
    filterByType,
    filterByStatus
  }
}
