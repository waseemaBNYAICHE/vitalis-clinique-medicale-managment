import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './styles/MainLayout.css'

createApp(App)
  .use(router)
  .mount('#app')
