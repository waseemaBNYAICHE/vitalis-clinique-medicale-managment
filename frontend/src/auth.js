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

/**
 * SCRUM-531 - Ferme la session : revocation cote serveur PUIS effacement local.
 *
 * Se deconnecter en vidant seulement le stockage ne suffit pas. Le jeton
 * Sanctum reste valide cote serveur jusqu'a son expiration (12 h, SCRUM-526) :
 * un jeton recupere apres coup sur un poste partage de la clinique - console
 * du navigateur, sauvegarde de profil, extension - continuerait d'ouvrir
 * l'API. C'est justement ce que fait POST /api/logout, qui supprime le jeton
 * courant.
 *
 * L'effacement local a lieu dans tous les cas, y compris si l'appel echoue
 * (reseau coupe, jeton deja expire). Un serveur injoignable ne doit pas
 * laisser l'utilisateur connecte sur la machine : rester connecte localement
 * serait le pire des deux mondes.
 *
 * La revocation est passee en parametre plutot qu'importee : auth.js est
 * importe par api.js, l'inverse creerait un cycle. Cela rend aussi la
 * sequence verifiable sans navigateur.
 *
 * @param {() => Promise<unknown>} revoquer Appel HTTP de revocation.
 */
export async function closeSession(revoquer) {
  try {
    if (typeof revoquer === 'function') {
      await revoquer()
    }
  } catch {
    // Le jeton est peut-etre deja invalide, ou le serveur injoignable : il
    // n'y a rien a faire de plus, et surtout rien qui justifie de conserver
    // la session locale.
  } finally {
    clearSession()
  }
}
