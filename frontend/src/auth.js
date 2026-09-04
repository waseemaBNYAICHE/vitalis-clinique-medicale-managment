// SCRUM-13 - Etat d'authentification centralise.
// SCRUM-15 - Gestion complete de la session utilisateur.
//
// La session est ecrite soit dans localStorage ("Se souvenir de moi" coche,
// elle survit alors a la fermeture du navigateur), soit dans sessionStorage
// (elle disparait a la fermeture de l'onglet). Toute lecture et toute ecriture
// passent par ce module : le routeur, l'intercepteur axios et les vues voient
// ainsi exactement le meme etat, et il n'existe qu'un seul endroit ou la
// session est effacee.

const CLE_TOKEN = 'token'
const CLE_USER = 'user'

const stockages = () => [localStorage, sessionStorage]

/**
 * Retourne le token de l'utilisateur connecte, ou null.
 */
export function getToken() {
  for (const stockage of stockages()) {
    const token = stockage.getItem(CLE_TOKEN)
    if (token) {
      return token
    }
  }
  return null
}

/**
 * Retourne l'utilisateur connecte, ou null.
 *
 * Une donnee illisible (stockage corrompu, format modifie entre deux versions)
 * est traitee comme une absence d'utilisateur plutot que de faire planter la
 * page : la session est alors effacee pour ne pas garder un etat incoherent.
 */
export function getUser() {
  for (const stockage of stockages()) {
    const brut = stockage.getItem(CLE_USER)

    if (brut) {
      try {
        return JSON.parse(brut)
      } catch {
        clearSession()
        return null
      }
    }
  }
  return null
}

/**
 * Indique si un utilisateur est actuellement connecte.
 */
export function isAuthenticated() {
  return getToken() !== null
}

/**
 * Enregistre la session apres une connexion reussie.
 *
 * @param {{ token: string, user: object, remember?: boolean }} session
 */
export function saveSession({ token, user, remember = false }) {
  // On repart d'une session propre : sans cela, un ancien token pourrait
  // survivre dans l'autre stockage et etre relu en priorite.
  clearSession()

  const stockage = remember ? localStorage : sessionStorage

  stockage.setItem(CLE_TOKEN, token)
  stockage.setItem(CLE_USER, JSON.stringify(user))
}

/**
 * Efface la session dans les deux stockages.
 *
 * Utilise a la deconnexion et lorsque l'API repond 401 (session expiree ou
 * token revoque). Nettoyer les deux evite de laisser des donnees utilisateur
 * obsoletes derriere soi.
 */
export function clearSession() {
  for (const stockage of stockages()) {
    stockage.removeItem(CLE_TOKEN)
    stockage.removeItem(CLE_USER)
  }
}
