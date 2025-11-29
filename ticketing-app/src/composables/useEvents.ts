import { ref } from 'vue'
import eventService from '@/services/eventService'
import type { Event, CreateEventData, UpdateEventData, EventFilters, PaginatedResponse, EventStatistics } from '@/types/api'

export function useEvents() {
  const events = ref<Event[]>([])
  const event = ref<Event | null>(null)
  const statistics = ref<EventStatistics | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
  })

  const fetchEvents = async (filters?: EventFilters) => {
    loading.value = true
    error.value = null
    try {
      const response: PaginatedResponse<Event> = await eventService.getAll(filters)
      events.value = response.data
      pagination.value = {
        total: response?.total,
        per_page: response?.per_page,
        current_page: response?.current_page,
        last_page: response?.last_page
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch events'
      console.error('Error fetching events:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchPublicEvents = async (filters?: EventFilters) => {
    loading.value = true
    error.value = null
    try {
      const response: PaginatedResponse<Event> = await eventService.getAllPublicEvents(filters)
      events.value = response.data
      pagination.value = {
        total: response?.total,
        per_page: response?.per_page,
        current_page: response?.current_page,
        last_page: response?.last_page
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch events'
      console.error('Error fetching events:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchEvent = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      event.value = await eventService.getById(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch event'
      console.error('useEvents: Error fetching event:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchEventBySlug = async (slug: string) => {
    loading.value = true
    error.value = null
    try {
      event.value = await eventService.getBySlug(slug)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch event'
      console.error('Error fetching event:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchPublicEvent = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      event.value = await eventService.getPublicById(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch event'
      console.error('Error fetching event:', e)
    } finally {
      loading.value = false
    }
  }

  const createEvent = async (data: CreateEventData) => {
    loading.value = true
    error.value = null
    try {
      const newEvent = await eventService.create(data)
      events.value.unshift(newEvent)
      return newEvent
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create event'
      console.error('Error creating event:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateEvent = async (id: string, data: UpdateEventData) => {
    loading.value = true
    error.value = null
    try {
      const updatedEvent = await eventService.update(id, data)
      const index = events.value.findIndex(e => e.id === id)
      if (index !== -1) {
        events.value[index] = updatedEvent
      }
      if (event.value?.id === id) {
        event.value = updatedEvent
      }
      return updatedEvent
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update event'
      console.error('Error updating event:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const deleteEvent = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      await eventService.delete(id)
      events.value = events.value.filter(e => e.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to delete event'
      console.error('Error deleting event:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const publishEvent = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      const published = await eventService.publish(id)
      const index = events.value.findIndex(e => e.id === id)
      if (index !== -1) {
        events.value[index] = published
      }
      if (event.value?.id === id) {
        event.value = published
      }
      return published
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to publish event'
      console.error('Error publishing event:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const unpublishEvent = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      const unpublished = await eventService.unpublish(id)
      const index = events.value.findIndex(e => e.id === id)
      if (index !== -1) {
        events.value[index] = unpublished
      }
      if (event.value?.id === id) {
        event.value = unpublished
      }
      return unpublished
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to unpublish event'
      console.error('Error unpublishing event:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const fetchStatistics = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      statistics.value = await eventService.getStatistics(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch statistics'
      console.error('Error fetching statistics:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchMyEvents = async (filters?: EventFilters) => {
    loading.value = true
    error.value = null
    try {
      const response: PaginatedResponse<Event> = await eventService.getMyEvents(filters)
      events.value = response.data
      pagination.value = {
        total: response.total,
        per_page: response.per_page,
        current_page: response.current_page,
        last_page: response.last_page
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch my events'
      console.error('Error fetching my events:', e)
    } finally {
      loading.value = false
    }
  }

  const duplicateEvent = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      const duplicated = await eventService.duplicate(id)
      events.value.unshift(duplicated)
      return duplicated
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to duplicate event'
      console.error('Error duplicating event:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    events,
    event,
    statistics,
    loading,
    error,
    pagination,
    fetchEvents,
    fetchPublicEvents,
    fetchEvent,
    fetchEventBySlug,
    fetchPublicEvent,
    createEvent,
    updateEvent,
    deleteEvent,
    publishEvent,
    unpublishEvent,
    fetchStatistics,
    fetchMyEvents,
    duplicateEvent
  }
}
