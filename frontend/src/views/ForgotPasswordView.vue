<script setup>
import { ref } from 'vue'
import api from '../api'

const email = ref('')
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const sendResetLink = async () => {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await api.post('/forgot-password', {
      email: email.value
    })

    successMessage.value =
      response.data?.message || 'Lien de réinitialisation envoyé'
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      "Impossible d'envoyer le lien de réinitialisation."
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="forgot-page">
    <div class="forgot-card">
      <h1>Mot de passe oublié ?</h1>

      <p>
        Entrez votre adresse email pour recevoir un lien
        de réinitialisation.
      </p>

      <form @submit.prevent="sendResetLink">
        <label>Email</label>

        <input
          v-model="email"
          type="email"
          placeholder="votre.email@example.com"
          required
        />

        <p v-if="successMessage" class="success">
          {{ successMessage }}
        </p>

        <p v-if="errorMessage" class="error">
          {{ errorMessage }}
        </p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Envoi...' : 'Envoyer le lien' }}
        </button>
      </form>

      <RouterLink to="/login" class="back">
        Retour à la connexion
      </RouterLink>
    </div>
  </main>
</template>

<style scoped>
.forgot-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
}

.forgot-card {
  width: 100%;
  max-width: 460px;
  padding: 40px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

h1 {
  margin-bottom: 10px;
}

p {
  color: #64748b;
  margin-bottom: 25px;
}

label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
}

input {
  width: 100%;
  padding: 14px;
  box-sizing: border-box;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  margin-bottom: 18px;
}

button {
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 8px;
  background: #1687e8;
  color: white;
  font-weight: 600;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
}

.success {
  color: #15803d;
}

.error {
  color: #dc2626;
}

.back {
  display: block;
  margin-top: 20px;
  text-align: center;
  color: #1687e8;
  text-decoration: none;
}
</style>