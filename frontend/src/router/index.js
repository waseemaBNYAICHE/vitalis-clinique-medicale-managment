import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import { resolveNavigation } from './guards'

// SCRUM-13 - Chaque route declare si elle est protegee via `meta.requiresAuth`.
// Une nouvelle page est ainsi protegee en ajoutant une seule ligne, sans
// dupliquer de verification dans les composants.
const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Garde de navigation globale : elle s'applique a la navigation interne, a
// l'ouverture directe d'une URL et au rafraichissement de la page, puisque le
// routeur rejoue la navigation a chaque demarrage de l'application.
router.beforeEach(resolveNavigation)

export default router
