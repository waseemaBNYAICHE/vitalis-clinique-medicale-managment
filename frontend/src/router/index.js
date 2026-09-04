import { createRouter, createWebHistory } from 'vue-router'
import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import ResetPasswordView from '../views/ResetPasswordView.vue'

import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import PatientCreateView from '../views/patients/PatientCreateView.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView
  },
  
  {
  path: '/reset-password',
  name: 'reset-password',
  component: ResetPasswordView
  },
  {
  path: '/forgot-password',
  name: 'forgot-password',
  component: ForgotPasswordView
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView
  },
  {
  path: '/patients/new',
  name: 'patient-create',
  component: PatientCreateView
}
]


const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router