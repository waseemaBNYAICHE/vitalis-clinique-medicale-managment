<script setup>
import { useRoute } from 'vue-router'

import AiPredictionDrawer from './components/ai/AiPredictionDrawer.vue'
import AiSmartChat from './components/ai/AiSmartChat.vue'
import ToastNotification from './components/notifications/ToastNotification.vue'

const route = useRoute()
</script>

<template>
  <RouterView />

  <AiPredictionDrawer
    v-if="!route.meta.hideAiDrawer"
  />

  <AiSmartChat
    v-if="!route.meta.hideAiDrawer"
  />

  <ToastNotification />
</template>