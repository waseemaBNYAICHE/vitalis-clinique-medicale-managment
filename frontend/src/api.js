import axios from 'axios'
import { getToken, clearSession } from './auth.js'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  }
})

// Injecte automatiquement le token Bearer dans chaque requête
api.interceptors.request.use((config) => {
  const token = getToken()

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// SCRUM-14 - Session expirée ou token invalidé côté serveur : l'API répond 401.
// On efface l'état local devenu inutilisable et on renvoie vers la connexion,
// en mémorisant la page en cours pour y revenir après.
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // SCRUM-15 : un seul point d'effacement de la session.
      clearSession()

      // Import differe : le routeur importe les vues, qui importent ce
      // fichier. Charger le routeur ici evite cette dependance circulaire.
      const { default: router } = await import('./router')
      const courante = router.currentRoute.value

      if (courante.name !== 'login') {
        router.push({
          name: 'login',
          query: { redirect: courante.fullPath }
        })
      }
    }

    return Promise.reject(error)
  }
)

export default api
