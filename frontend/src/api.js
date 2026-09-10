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

/**
 * SCRUM-531 - Message affichable a l'utilisateur pour une erreur d'API.
 *
 * Les vues affichaient error.response.data.message quel que soit le code.
 * Ce champ est redige pour l'utilisateur sur les erreurs 4xx (validation,
 * identifiants invalides, acces refuse), mais sur une erreur serveur il
 * decrit une panne technique - et en developpement, APP_DEBUG expose en plus
 * la classe d'exception et le chemin du fichier. Rien de tout cela n'a sa
 * place dans l'interface.
 *
 * On ne fait donc confiance au message du backend que pour les codes 4xx, et
 * on retombe sur un texte neutre pour le reste (5xx, coupure reseau).
 *
 * @param {unknown} error Erreur remontee par axios.
 * @param {string} secours Message affiche si celui du backend n'est pas sur.
 */
export function messageErreur(error, secours) {
  const statut = error?.response?.status
  const message = error?.response?.data?.message

  const estErreurClient = typeof statut === 'number' && statut >= 400 && statut < 500

  if (estErreurClient && typeof message === 'string' && message !== '') {
    return message
  }

  return secours
}

export default api
