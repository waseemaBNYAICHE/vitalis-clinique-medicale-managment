<script setup>
import { useRoute } from 'vue-router'
import AiPredictionDrawer from './components/ai/AiPredictionDrawer.vue'

const route = useRoute()
</script>

<template>
  <RouterView />

  <AiPredictionDrawer v-if="!route.meta.hideAiDrawer" />
</template>