import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './styles/MainLayout.css'
import '@flaticon/flaticon-uicons/css/regular/rounded.css'

createApp(App)
  .use(router)
  .mount('#app')
