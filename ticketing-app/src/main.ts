import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './style.css'
import clickOutside from '@venegrad/vue3-click-outside'
import echo from './services/websocket'

const app = createApp(App)

// Make Echo available globally
app.config.globalProperties.$echo = echo

app.use(createPinia())
app.use(router)
app.directive('click-away', clickOutside)

app.mount('#app')

// Log broadcasting connection status
console.log('🔌 Broadcasting initialized with Reverb')