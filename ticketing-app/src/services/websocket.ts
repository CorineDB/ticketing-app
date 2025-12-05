import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Make Pusher available globally
declare global {
    interface Window {
        Pusher: typeof Pusher
        Echo: any
    }
}

window.Pusher = Pusher

// Helper to strip a trailing '/api' from a base URL (if present)
function stripApiPrefix(url: string): string {
    return url.replace(/\/api\/?$/, '')
}

// Create Echo instance
export const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'app-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT) : 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT) : 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    authEndpoint: `${stripApiPrefix(import.meta.env.VITE_API_URL || 'http://localhost:8000')}/broadcasting/auth`,
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('token') || ''}`,
            Accept: 'application/json',
        },
    },
})

// Export for use in components
export default echo

// Helper to update auth token
export function updateEchoAuth(token: string) {
    if (echo.connector && echo.connector.pusher) {
        // Update auth headers
        const pusher = echo.connector.pusher as any
        if (pusher.config) {
            pusher.config.auth = {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            }
        }
    }
}

// Helper to disconnect
export function disconnectEcho() {
    echo.disconnect()
}
