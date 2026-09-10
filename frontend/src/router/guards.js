// Extension explicite : ce module reste ainsi importable tel quel par Node,
// ce qui permet de verifier la garde sans navigateur.
import { isAuthenticated } from '../auth.js'
import { peut } from '../rbac.js'

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

  // SCRUM-535 : la route exige-t-elle une permission que ce role n'a pas ?
  //
  // requiresAuth ne verifiait que la presence d'un compte. Un patient
  // authentifie atteignait donc /utilisateurs ou /patients en saisissant
  // l'URL, meme si le menu ne lui proposait pas le lien (SCRUM-533) et que
  // les boutons y etaient masques (SCRUM-534) : il voyait l'ecran.
  //
  // On renvoie vers le tableau de bord, seul ecran ouvert a tous les roles
  // authentifies : pas de cul-de-sac, et pas de nouvelle page a creer.
  //
  // Ce controle n'est pas une securite : il evite d'ouvrir un ecran dont
  // toutes les donnees viendront de requetes que le backend refusera.
  if (connecte && to.meta?.permission && !peut(to.meta.permission)) {
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
 *
 * SCRUM-531 - Les antislashs sont normalises en slashs avant le controle,
 * comme le fait le navigateur, sans quoi "/\\site-externe" serait accepte.
 */
export function resolveRedirection(redirect) {
  if (typeof redirect !== 'string') {
    return '/dashboard'
  }

  // SCRUM-531 - Les navigateurs traitent l'antislash comme un slash dans une
  // URL. "/\\site-externe" passait donc le controle ci-dessous (il commence
  // par "/" et pas par "//") tout en etant resolu par le navigateur en
  // "//site-externe", c'est-a-dire une adresse externe. On normalise avant de
  // decider, plutot que d'ajouter un cas particulier de plus.
  const normalise = redirect.replaceAll('\\', '/')

  const interne = normalise.startsWith('/') && !normalise.startsWith('//')

  if (!interne || normalise === '/login' || normalise.startsWith('/login?')) {
    return '/dashboard'
  }

  // SCRUM-531 - On renvoie la valeur normalisee, pas l'originale : sinon
  // l'appelant naviguerait vers la chaine non verifiee.
  return normalise
}
