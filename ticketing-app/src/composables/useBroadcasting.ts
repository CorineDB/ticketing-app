import { onUnmounted } from 'vue'
import echo from '@/services/websocket'

export interface BroadcastEvent {
    [key: string]: any
}

export function useBroadcasting() {
    const channels: any[] = []

    /**
     * Listen to a private channel
     */
    function listenPrivate(channelName: string, eventName: string, callback: (data: BroadcastEvent) => void) {
        const channel = echo.private(channelName)
        channel.listen(eventName, callback)
        channels.push(channel)

        console.log(`📡 Listening to ${eventName} on private channel: ${channelName}`)

        return () => {
            channel.stopListening(eventName)
            echo.leaveChannel(`private-${channelName}`)
        }
    }

    /**
     * Listen to a public channel
     */
    function listenPublic(channelName: string, eventName: string, callback: (data: BroadcastEvent) => void) {
        const channel = echo.channel(channelName)
        channel.listen(eventName, callback)
        channels.push(channel)

        console.log(`📡 Listening to ${eventName} on public channel: ${channelName}`)

        return () => {
            channel.stopListening(eventName)
            echo.leaveChannel(channelName)
        }
    }

    /**
     * Listen to a presence channel
     */
    function listenPresence(channelName: string, eventName: string, callback: (data: BroadcastEvent) => void) {
        const channel = echo.join(channelName)
        channel.listen(eventName, callback)
        channels.push(channel)

        console.log(`📡 Listening to ${eventName} on presence channel: ${channelName}`)

        return () => {
            channel.stopListening(eventName)
            echo.leaveChannel(`presence-${channelName}`)
        }
    }

    /**
     * Auto-cleanup on component unmount
     */
    onUnmounted(() => {
        channels.forEach(channel => {
            if (channel && channel.name) {
                echo.leaveChannel(channel.name)
            }
        })
        console.log('🔌 Disconnected from all channels')
    })

    return {
        listenPrivate,
        listenPublic,
        listenPresence,
        echo
    }
}
