// SCRUM-532 - Table des routes, isolee de la creation du routeur.
//
// Elle vivait dans index.js, aux cotes de createRouter() et de createWebHistory().
// Ces deux appels exigent un navigateur : la table n'etait donc verifiable
// qu'a la main, et les tests se rabattaient sur des objets de route
// fabriques pour l'occasion. Ils validaient la garde, jamais la table qu'elle
// protege - c'est exactement la ou se trouvait le trou corrige par ce ticket.
//
// Les composants sont charges paresseusement : la table reste ainsi
// importable sans compiler de fichier .vue, donc verifiable hors navigateur.

// SCRUM-13 - Chaque route declare si elle est protegee via meta.requiresAuth.
export const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('../views/ResetPasswordView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('../views/ForgotPasswordView.vue'),
    meta: { requiresAuth: false }
  },

  // Espace authentifie. requiresAuth est porte par le parent : vue-router
  // fusionne le meta de tous les enregistrements traverses, chaque enfant en
  // herite donc sans avoir a le repeter.
  //
  // SCRUM-532 : /dashboard etait declare deux fois, ici et une seconde fois
  // hors de ce layout, sous le meme nom. C'est l'enfant qui gagnait, la
  // declaration isolee ne servait donc a rien - mais elle laissait croire
  // qu'il existait un tableau de bord accessible sans le layout.
  {
    path: '/',
    component: () => import('../layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('../views/Dashboard/DashboardView.vue')
      },
      {
        path: 'patients',
        name: 'patients',
        component: () => import('../views/Patients/PatientsView.vue')
      },
      {
        path: 'patients/new',
        name: 'patient-create',
        component: () => import('../views/Patients/PatientCreateView.vue')
      },
      {
        path: 'utilisateurs',
        name: 'utilisateurs',
        component: () => import('../views/Utilisateur/UtilisateursView.vue')
      }
    ]
  },

  // SCRUM-532 - Tout chemin non declare.
  //
  // Sans cette route, une URL inconnue ne correspondait a aucun
  // enregistrement : to.meta etait vide, requiresAuth valait undefined et la
  // garde laissait passer. Un visiteur non authentifie qui saisissait
  // n'importe quelle adresse restait donc dans l'application au lieu d'etre
  // renvoye vers la connexion.
  //
  // Le cas n'avait rien de theorique : la barre laterale de MainLayout pointe
  // vers /rendez-vous, /consultations, /examens, /hospitalisations et
  // /facturation, cinq chemins qui ne correspondent a aucune route.
  //
  // La redirection suffit a fermer le trou : la garde s'applique ensuite a
  // /dashboard, qui exige une authentification. Un visiteur non authentifie
  // part vers /login, un utilisateur connecte atterrit sur son tableau de
  // bord plutot que sur une page vide.
  {
    path: '/:pathMatch(.*)*',
    name: 'non-trouve',
    redirect: '/dashboard'
  }
]

export default routes
