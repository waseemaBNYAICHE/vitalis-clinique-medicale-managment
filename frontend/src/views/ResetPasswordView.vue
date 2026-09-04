<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'

const route = useRoute()
const router = useRouter()

const token = ref(route.query.token || '')
const email = ref(route.query.email || '')
const password = ref('')
const passwordConfirmation = ref('')

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const resetPassword = async () => {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await api.post('/reset-password', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    successMessage.value =
      response.data?.message || 'Mot de passe réinitialisé avec succès'

    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Impossible de réinitialiser le mot de passe.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="reset-page">
    <div class="reset-card">
      <h1>Nouveau mot de passe</h1>

      <p>Choisissez un nouveau mot de passe pour votre compte.</p>

      <form @submit.prevent="resetPassword">
        <label>Email</label>
        <input
          v-model="email"
          type="email"
          readonly
        />

        <label>Nouveau mot de passe</label>
        <input
          v-model="password"
          type="password"
          minlength="8"
          required
        />

        <label>Confirmer le mot de passe</label>
        <input
          v-model="passwordConfirmation"
          type="password"
          minlength="8"
          required
        />

        <p v-if="successMessage" class="success">
          {{ successMessage }}
        </p>

        <p v-if="errorMessage" class="error">
          {{ errorMessage }}
        </p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Réinitialisation...' : 'Réinitialiser le mot de passe' }}
        </button>
      </form>

      <RouterLink to="/login" class="back">
        Retour à la connexion
      </RouterLink>
    </div>
  </main>
</template>

<style scoped>
.reset-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
}

.reset-card {
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