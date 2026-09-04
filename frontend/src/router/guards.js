// Extension explicite : ce module reste ainsi importable tel quel par Node,
// ce qui permet de verifier la garde sans navigateur.
import { isAuthenticated } from '../auth.js'

// SCRUM-13 - Decision de la garde de navigation, isolee dans une fonction pure
// pour rester lisible et verifiable sans navigateur.
// SCRUM-14 - La meme garde decide ou envoyer un visiteur non authentifie.
//
// Retourne `true` pour laisser passer la navigation, ou un objet de route pour
// rediriger (convention vue-router).
export function resolveNavigation(to) {
  const connecte = isAuthenticated()

  // SCRUM-14 : page protegee sans authentification -> page de connexion.
  // La destination voulue est memorisee dans la query `redirect` pour y
  // revenir apres la connexion.
  if (to.meta?.requiresAuth && !connecte) {
    return {
      name: 'login',
      query: { redirect: to.fullPath }
    }
  }

  // SCRUM-14 : un utilisateur deja connecte n'a rien a faire sur /login.
  // Cela evite aussi qu'un aller-retour login <-> dashboard ne boucle.
  if (to.name === 'login' && connecte) {
    return { name: 'dashboard' }
  }

  return true
}

/**
 * SCRUM-14 - Destination a atteindre apres une connexion reussie.
 *
 * On n'accepte qu'un chemin interne commencant par "/" et different de
 * "/login" : cela evite une boucle de redirection et empeche qu'un lien
 * malveillant (`?redirect=https://site-externe`) ne renvoie l'utilisateur
 * vers un autre site apres sa connexion.
 */
export function resolveRedirection(redirect) {
  if (typeof redirect !== 'string') {
    return '/dashboard'
  }

  const interne = redirect.startsWith('/') && !redirect.startsWith('//')

  if (!interne || redirect === '/login' || redirect.startsWith('/login?')) {
    return '/dashboard'
  }

  return redirect
}
