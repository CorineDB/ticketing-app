import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Notification, NotificationType } from '@/types/api'

export const useNotificationStore = defineStore('notifications', () => {
  // State
  const notifications = ref<Notification[]>([])

  // Actions
  function addNotification(
    type: NotificationType,
    title: string,
    message: string,
    duration: number = 5000
  ) {
    const id = `notification-${Date.now()}-${Math.random()}`
    const notification: Notification = {
      id,
      type,
      title,
      message,
      duration,
      timestamp: Date.now()
    }

    notifications.value.push(notification)

    // Auto-remove after duration
    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, duration)
    }

    return id
  }

  function removeNotification(id: string) {
    const index = notifications.value.findIndex((n) => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  function clearAll() {
    notifications.value = []
  }

  // Convenience methods
  function success(title: string, message: string = '', duration: number = 5000) {
    return addNotification('success', title, message, duration)
  }

  function error(title: string, message: string = '', duration: number = 7000) {
    return addNotification('error', title, message, duration)
  }

  function warning(title: string, message: string = '', duration: number = 6000) {
    return addNotification('warning', title, message, duration)
  }

  function info(title: string, message: string = '', duration: number = 5000) {
    return addNotification('info', title, message, duration)
  }

  return {
    // State
    notifications,

    // Actions
    addNotification,
    removeNotification,
    clearAll,
    success,
    error,
    warning,
    info
  }
})
