import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './style.css'
import clickOutside from '@venegrad/vue3-click-outside'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.directive('click-away', clickOutside)

app.mount('#app')