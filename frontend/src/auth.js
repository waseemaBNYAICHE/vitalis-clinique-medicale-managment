// SCRUM-13 - Etat d'authentification centralise.
//
// Le token est ecrit soit dans localStorage ("Se souvenir de moi" coche),
// soit dans sessionStorage (session courante uniquement). Toute lecture passe
// par ce module pour que le routeur, l'intercepteur axios et les vues voient
// exactement le meme etat.

const STOCKAGES = () => [localStorage, sessionStorage]

/**
 * Retourne le token de l'utilisateur connecte, ou null.
 */
export function getToken() {
  for (const stockage of STOCKAGES()) {
    const token = stockage.getItem('token')
    if (token) {
      return token
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
