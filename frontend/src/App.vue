<script setup>
import { onMounted, ref } from 'vue'
import { apiGet, apiUrl } from './api.js'

// Environment smoke test: confirms the browser can reach the Laravel API and
// that the API can reach PostgreSQL and Redis. Replace with the real app.
const state = ref('loading')
const health = ref(null)
const error = ref(null)

onMounted(async () => {
  try {
    health.value = await apiGet('/health')
    state.value = health.value.status === 'ok' ? 'ok' : 'degraded'
  } catch (e) {
    error.value = e.message
    state.value = 'unreachable'
  }
})
</script>

<template>
  <main>
    <h1>Vue + Vite &rarr; Laravel API</h1>

    <p class="muted">
      <code>VITE_API_URL</code> = <code>{{ apiUrl }}</code>
    </p>

    <p v-if="state === 'loading'">Checking API&hellip;</p>

    <p v-else-if="state === 'unreachable'" class="bad">
      API unreachable: {{ error }}
    </p>

    <ul v-else>
      <li :class="state === 'ok' ? 'good' : 'bad'">api: {{ health.status }}</li>
      <li
        v-for="(status, service) in health.services"
        :key="service"
        :class="status === 'ok' ? 'good' : 'bad'"
      >
        {{ service }}: {{ status }}
      </li>
    </ul>
  </main>
</template>

<style scoped>
main {
  max-width: 38rem;
  margin: 4rem auto;
  padding: 0 1.5rem;
}

h1 {
  font-size: 1.5rem;
  margin: 0 0 0.5rem;
}

ul {
  list-style: none;
  padding: 0;
  margin-top: 1.5rem;
}

li {
  font-family: var(--mono);
  font-size: 0.95rem;
  padding: 0.15rem 0;
}

.muted {
  opacity: 0.75;
  font-size: 0.9rem;
}

.good {
  color: #1a7f4b;
}

.good::before {
  content: '\2713\00a0';
}

.bad {
  color: #d63b3b;
}

.bad::before {
  content: '\2717\00a0';
}
</style>
