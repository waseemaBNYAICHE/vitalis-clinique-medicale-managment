<script setup>
import { ref } from 'vue'
import { apiUrl } from './api.js'

const email = ref('')
const password = ref('')

const loading = ref(false)
const error = ref('')
const success = ref('')
const user = ref(null)

async function login() {
  error.value = ''
  success.value = ''
  user.value = null

  if (!email.value || !password.value) {
    error.value = 'Veuillez remplir tous les champs.'
    return
  }

  loading.value = true

  try {
    const response = await fetch(`${apiUrl}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value
      })
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Identifiants invalides')
    }

    // Sauvegarder le token
    localStorage.setItem('token', data.token)

    // Sauvegarder l'utilisateur
    localStorage.setItem('user', JSON.stringify(data.user))

    user.value = data.user
    success.value = data.message || 'Connexion réussie'

  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')

  user.value = null
  email.value = ''
  password.value = ''
  success.value = ''
}
</script>

<template>
  <main class="page">

    <!-- LOGIN -->
    <div v-if="!user" class="login-card">

      <div class="logo">
        <div class="logo-icon">V</div>
        <div>
          <h1>VITALIS</h1>
          <p>Clinique Médicale</p>
        </div>
      </div>

      <div class="header">
        <h2>Connexion</h2>
        <p>Connectez-vous à votre espace</p>
      </div>

      <form @submit.prevent="login">

        <div class="form-group">
          <label for="email">Adresse email</label>

          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="exemple@email.com"
            autocomplete="email"
          />
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>

          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="Votre mot de passe"
            autocomplete="current-password"
          />
        </div>

        <div v-if="error" class="message error">
          ❌ {{ error }}
        </div>

        <div v-if="success" class="message success">
          ✅ {{ success }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Connexion...' : 'Se connecter' }}
        </button>

      </form>

      <div class="api-info">
        API :
        <code>{{ apiUrl }}</code>
      </div>

    </div>

    <!-- AFTER LOGIN -->
    <div v-else class="dashboard-card">

      <div class="success-icon">✓</div>

      <h1>Connexion réussie !</h1>

      <p class="welcome">
        Bienvenue <strong>{{ user.name }}</strong>
      </p>

      <div class="user-info">
        <p>
          <strong>ID :</strong>
          {{ user.id }}
        </p>

        <p>
          <strong>Nom :</strong>
          {{ user.name }}
        </p>

        <p>
          <strong>Email :</strong>
          {{ user.email }}
        </p>
      </div>

      <button @click="logout" class="logout">
        Se déconnecter
      </button>

    </div>

  </main>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #eef5ff, #f8fbff);
  padding: 30px;
  font-family: Arial, sans-serif;
}

.login-card,
.dashboard-card {
  width: 100%;
  max-width: 430px;
  background: white;
  padding: 40px;
  border-radius: 18px;
  box-shadow: 0 15px 45px rgba(0, 0, 0, 0.12);
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 35px;
}

.logo-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #1677ff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 25px;
  font-weight: bold;
}

.logo h1 {
  margin: 0;
  color: #1677ff;
  font-size: 23px;
}

.logo p {
  margin: 3px 0 0;
  color: #777;
  font-size: 13px;
}

.header {
  margin-bottom: 25px;
}

.header h2 {
  margin: 0;
  color: #222;
  font-size: 27px;
}

.header p {
  color: #777;
  margin-top: 7px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

input {
  width: 100%;
  padding: 13px 14px;
  border: 1px solid #d9dfe8;
  border-radius: 9px;
  font-size: 15px;
  outline: none;
}

input:focus {
  border-color: #1677ff;
  box-shadow: 0 0 0 3px rgba(22, 119, 255, 0.1);
}

button {
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 9px;
  background: #1677ff;
  color: white;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  margin-top: 5px;
}

button:hover {
  background: #0868e8;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.message {
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 15px;
  font-size: 14px;
}

.error {
  background: #fff0f0;
  color: #c62828;
}

.success {
  background: #edfff4;
  color: #168044;
}

.api-info {
  margin-top: 25px;
  text-align: center;
  color: #888;
  font-size: 12px;
}

.api-info code {
  color: #555;
}

.dashboard-card {
  text-align: center;
}

.success-icon {
  width: 70px;
  height: 70px;
  margin: 0 auto 20px;
  border-radius: 50%;
  background: #e5f8ed;
  color: #159447;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  font-weight: bold;
}

.dashboard-card h1 {
  color: #222;
}

.welcome {
  color: #666;
}

.user-info {
  text-align: left;
  background: #f6f8fb;
  padding: 18px;
  border-radius: 10px;
  margin: 25px 0;
}

.user-info p {
  margin: 8px 0;
}

.logout {
  background: #e53935;
}

.logout:hover {
  background: #c62828;
}
</style>