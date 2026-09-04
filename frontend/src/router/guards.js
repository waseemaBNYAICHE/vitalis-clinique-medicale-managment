// Extension explicite : ce module reste ainsi importable tel quel par Node,
// ce qui permet de verifier la garde sans navigateur.
import { isAuthenticated } from '../auth.js'

// SCRUM-13 - Decision de la garde de navigation, isolee dans une fonction pure
// pour rester lisible et verifiable sans navigateur.
//
// Retourne `true` pour laisser passer la navigation, ou un objet de route pour
// rediriger (convention vue-router).
export function resolveNavigation(to) {
  if (to.meta?.requiresAuth && !isAuthenticated()) {
    return { name: 'login' }
  }

  return true
}
