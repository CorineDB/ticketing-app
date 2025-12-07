import { ref, onMounted, onUnmounted } from 'vue'
import { echo } from '@/services/websocket'

export interface Notification {
    id: string
    type: 'ticket.purchased' | 'ticket.scanned' | 'event.capacity.alert' | 'other'
    title: string
    message: string
    timestamp: Date
    read: boolean
    data?: any
}

export function useNotifications() {
    const notifications = ref<Notification[]>([])
    const unreadCount = ref(0)

    function addNotification(notification: Omit<Notification, 'id' | 'timestamp' | 'read'>) {
        const newNotification: Notification = {
            ...notification,
            id: `${Date.now()}-${Math.random()}`,
            timestamp: new Date(),
            read: false
        }

        notifications.value.unshift(newNotification)
        unreadCount.value++

        // Keep only last 50 notifications
        if (notifications.value.length > 50) {
            notifications.value.pop()
        }

        // Show browser notification if permitted
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: '/favicon.ico'
            })
        }
    }

    function markAsRead(notificationId: string) {
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification && !notification.read) {
            notification.read = true
            unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
    }

    function markAllAsRead() {
        notifications.value.forEach(n => n.read = true)
        unreadCount.value = 0
    }

    function clearAll() {
        notifications.value = []
        unreadCount.value = 0
    }

    function setupWebSocketListeners() {
        // Listen to ticket purchased events
        echo.channel('events')
            .listen('ticket.purchased', (event: any) => {
                addNotification({
                    type: 'ticket.purchased',
                    title: '🎫 Nouveau billet acheté',
                    message: `${event.ticket?.buyer_name || 'Un client'} a acheté un billet pour ${event.event?.title || 'un événement'}`,
                    data: event
                })
            })

        // Listen to ticket scanned events
        echo.channel('scans')
            .listen('ticket.scanned', (event: any) => {
                addNotification({
                    type: 'ticket.scanned',
                    title: '✅ Billet scanné',
                    message: `Billet scanné pour ${event.ticket?.buyer_name || 'un participant'}`,
                    data: event
                })
            })

        // Listen to capacity alerts
        echo.channel('alerts')
            .listen('event.capacity.alert', (event: any) => {
                addNotification({
                    type: 'event.capacity.alert',
                    title: '⚠️ Alerte capacité',
                    message: event.message || 'La capacité de l\'événement approche de la limite',
                    data: event
                })
            })
    }

    function cleanup() {
        echo.leave('events')
        echo.leave('scans')
        echo.leave('alerts')
    }

    // Request browser notification permission
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission()
        }
    }

    return {
        notifications,
        unreadCount,
        addNotification,
        markAsRead,
        markAllAsRead,
        clearAll,
        setupWebSocketListeners,
        cleanup,
        requestNotificationPermission
    }
}
