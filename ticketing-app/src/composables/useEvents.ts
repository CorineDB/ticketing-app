import { ref, readonly } from 'vue'
import eventService from '@/services/eventService'
import type { Event, CreateEventData, UpdateEventData, EventFilters } from '@/types/api'
import { useNotificationStore } from '@/stores/notifications'

export function useEvents() {
  const notifications = useNotificationStore()

  const events = ref<Event[]>([])
  const currentEvent = ref<Event | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const totalPages = ref(1)
  const currentPage = ref(1)

  async function fetchEvents(filters?: EventFilters) {
    loading.value = true
    error.value = null

    try {
      const response = await eventService.getAll(filters)
      console.log(response);
      events.value = response.data
      totalPages.value = response.meta?.last_page
      currentPage.value = response.meta?.current_page
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch events'
      notifications.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  async function fetchEvent(id: number) {
    loading.value = true
    error.value = null

    try {
      currentEvent.value = await eventService.getById(id)
      return currentEvent.value
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch event'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function fetchEventBySlug(slug: string) {
    loading.value = true
    error.value = null

    try {
      currentEvent.value = await eventService.getBySlug(slug)
      return currentEvent.value
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Event not found'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function createEvent(data: CreateEventData) {
    loading.value = true
    error.value = null

    try {
      const newEvent = await eventService.create(data)
      events.value.unshift(newEvent)
      notifications.success('Success', 'Event created successfully')
      return newEvent
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create event'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateEvent(id: number, data: UpdateEventData) {
    loading.value = true
    error.value = null

    try {
      const updatedEvent = await eventService.update(id, data)
      const index = events.value.findIndex((e) => e.id === id)
      if (index > -1) {
        events.value[index] = updatedEvent
      }
      if (currentEvent.value?.id === id) {
        currentEvent.value = updatedEvent
      }
      notifications.success('Success', 'Event updated successfully')
      return updatedEvent
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update event'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function deleteEvent(id: number) {
    loading.value = true
    error.value = null

    try {
      await eventService.delete(id)
      events.value = events.value.filter((e) => e.id !== id)
      notifications.success('Success', 'Event deleted successfully')
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to delete event'
      notifications.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  async function publishEvent(id: number) {
    loading.value = true
    error.value = null

    try {
      const publishedEvent = await eventService.publish(id)
      const index = events.value.findIndex((e) => e.id === id)
      if (index > -1) {
        events.value[index] = publishedEvent
      }
      if (currentEvent.value?.id === id) {
        currentEvent.value = publishedEvent
      }
      notifications.success('Success', 'Event published successfully')
      return publishedEvent
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to publish event'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function unpublishEvent(id: number) {
    loading.value = true
    error.value = null

    try {
      const unpublishedEvent = await eventService.unpublish(id)
      const index = events.value.findIndex((e) => e.id === id)
      if (index > -1) {
        events.value[index] = unpublishedEvent
      }
      if (currentEvent.value?.id === id) {
        currentEvent.value = unpublishedEvent
      }
      notifications.success('Success', 'Event unpublished')
      return unpublishedEvent
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to unpublish event'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function fetchMyEvents(filters?: EventFilters) {
    loading.value = true
    error.value = null

    try {
      const response = await eventService.getMyEvents(filters)
      events.value = response.data
      totalPages.value = response.meta.last_page
      currentPage.value = response.meta.current_page
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch my events'
      notifications.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  return {
    events: readonly(events),
    currentEvent: readonly(currentEvent),
    loading: readonly(loading),
    error: readonly(error),
    totalPages: readonly(totalPages),
    currentPage: readonly(currentPage),

    fetchEvents,
    fetchEvent,
    fetchEventBySlug,
    createEvent,
    updateEvent,
    deleteEvent,
    publishEvent,
    unpublishEvent,
    fetchMyEvents
  }
}
