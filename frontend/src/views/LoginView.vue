<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../api'
import { resolveRedirection } from '../router/guards'
import { saveSession } from '../auth.js'

const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const remember = ref(false)
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const login = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  loading.value = true
  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value
    })
    
    // SCRUM-15 : la session est enregistrée par le module auth, qui choisit le
    // stockage selon "Se souvenir de moi" et nettoie l'autre.
    saveSession({
      token: response.data.token,
      user: response.data.user,
      remember: remember.value
    })

    successMessage.value = response.data?.message || 'Connexion réussie'

    // SCRUM-14 : revenir sur la page demandée avant la redirection vers
    // /login, ou à défaut sur le tableau de bord.
    router.push(resolveRedirection(route.query.redirect))
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = 'Email ou mot de passe incorrect.'
    } else {
      errorMessage.value = error.response?.data?.message ||
        'Une erreur est survenue lors de la connexion.'
    }
  } finally {
    loading.value = false
  }
}
</script>
<template>
  <main class="login-page">
    <section class="visual-side">
      <img src="/images/login.jpg" alt="Réception VITALIS" class="reception-image" />
    </section>
    <div class="diagonal-line"></div>
    <section class="form-side">
      <div class="login-form">
        <h1>Connexion</h1>
        <p class="subtitle">Connectez-vous à votre compte</p>
        <div v-if="errorMessage" class="message error-message">
          {{ errorMessage }}
        </div>
        <div v-if="successMessage" class="message success-message">
          {{ successMessage }}
        </div>
        <form @submit.prevent="login">
          <div class="field">
            <label for="email">Email</label>
            <div class="input-wrapper">
              <span class="input-icon">✉</span>
              <input
                id="email"
                v-model.trim="email"
                type="email"
                placeholder="votre.email@example.com"
                autocomplete="email"
                required
              />
            </div>
          </div>
          <div class="field">
            <label for="password">Mot de passe</label>
            <div class="input-wrapper">
              <span class="input-icon">🔒</span>
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <button
                type="button"
                class="password-toggle"
                aria-label="Afficher ou masquer le mot de passe"
                @click="showPassword = !showPassword"
              >
                👁
              </button>
            </div>
          </div>
          <div class="options">
            <label class="remember">
              <input v-model="remember" type="checkbox" />
              <span>Se souvenir de moi</span>
            </label>
            <RouterLink to="/forgot-password">
              Mot de passe oublié ?
            </RouterLink>
          </div>
          <button type="submit" class="login-button" :disabled="loading">
            {{ loading ? 'Connexion...' : 'Se connecter' }}
          </button>
        </form>
        <div class="separator"><span>ou</span></div>
        <p class="contact">
          Vous n'avez pas de compte ?
          <a href="#" @click.prevent>Contacter l'administrateur</a>
        </p>
      </div>
    </section>
  </main>
</template>
<style src="../styles/login.css"></style>