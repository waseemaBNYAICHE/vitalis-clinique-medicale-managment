<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  sessionStorage.removeItem('token')
  sessionStorage.removeItem('user')

  router.push('/login')
}
</script>

<template>
  <div class="app-layout">

    <!-- MENU GAUCHE -->
    <aside class="sidebar">
      <div class="logo">
        <span>♥</span>
        <div>
          <h2>VITALIS</h2>
          <small>Clinique Médicale</small>
        </div>
      </div>

        <nav>
           <RouterLink to="/dashboard">▣ Tableau de bord</RouterLink>
           <RouterLink to="/patients">♙ Patients</RouterLink>
           <RouterLink to="/rendez-vous">▦ Rendez-vous</RouterLink>
           <RouterLink to="/consultations">▤ Consultations</RouterLink>
           <RouterLink to="/examens">▧ Examens</RouterLink>
           <RouterLink to="/hospitalisations">▥ Hospitalisations</RouterLink>
           <RouterLink to="/facturation">▤ Facturation</RouterLink>
           <RouterLink to="/utilisateurs">♙ Utilisateurs</RouterLink>
           <RouterLink to="/parametres">⚙️ Paramètres</RouterLink>
        </nav>

      <div class="logout" @click="logout" style="cursor: pointer;">
        Déconnexion
      </div>
    </aside>

    <main class="content">
      <RouterView />
    </main>

  </div>
</template>

