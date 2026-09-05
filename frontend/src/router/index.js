import { createRouter, createWebHistory } from 'vue-router'

import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import ResetPasswordView from '../views/ResetPasswordView.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'

import MainLayout from '../layouts/MainLayout.vue'
import PatientCreateView from '../views/patients/PatientCreateView.vue'
import PatientsView from '../views/patients/PatientsView.vue'

import { resolveNavigation } from './guards'

// SCRUM-13 - Chaque route déclare si elle est protégée via meta.requiresAuth.

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
    path: '/reset-password',
    name: 'reset-password',
    component: ResetPasswordView,
    meta: { requiresAuth: false }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPasswordView,
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'patients',
        name: 'patients',
        component: PatientsView
      },
      {
        path: 'patients/new',
        name: 'patient-create',
        component: PatientCreateView
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(resolveNavigation)

export default router