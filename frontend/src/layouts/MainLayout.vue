<template>
  <div class="app-layout">

    <!-- =========================
         SIDEBAR
    ========================== -->
    <aside class="sidebar">

      <!-- BRAND -->
      <div class="sidebar-brand">
        <div class="brand-icon">
          <i class="fi fi-rr-heart"></i>
        </div>

        <div class="brand-text">
          <h1>VITALIS</h1>
          <p>Clinique Médicale</p>
        </div>
      </div>

      <!-- NAVIGATION -->
      <nav class="sidebar-menu">

        <RouterLink to="/dashboard" class="menu-item">
          <i class="fi fi-rr-home menu-icon"></i>
          <span>Tableau de bord</span>
        </RouterLink>

        <RouterLink to="/patients" class="menu-item">
          <i class="fi fi-rr-users-medical menu-icon"></i>
          <span>Patients</span>
        </RouterLink>

        <RouterLink to="/rendez-vous" class="menu-item">
          <i class="fi fi-rr-calendar menu-icon"></i>
          <span>Rendez-vous</span>
        </RouterLink>

        <RouterLink to="/consultations" class="menu-item">
          <i class="fi fi-rr-stethoscope menu-icon"></i>
          <span>Consultations</span>
        </RouterLink>

        <RouterLink to="/examens" class="menu-item">
          <i class="fi fi-rr-document menu-icon"></i>
          <span>Examens</span>
        </RouterLink>

        <RouterLink to="/hospitalisations" class="menu-item">
          <i class="fi fi-rr-bed menu-icon"></i>
          <span>Hospitalisations</span>
        </RouterLink>

        <RouterLink to="/facturation" class="menu-item">
          <i class="fi fi-rr-receipt menu-icon"></i>
          <span>Facturation</span>
        </RouterLink>

        <RouterLink to="/utilisateurs" class="menu-item">
          <i class="fi fi-rr-user-gear menu-icon"></i>
          <span>Utilisateurs</span>
        </RouterLink>

        <RouterLink to="/parametres" class="menu-item">
          <i class="fi fi-rr-settings menu-icon"></i>
          <span>Paramètres</span>
        </RouterLink>

      </nav>

      <!-- LOGOUT -->
      <button
        class="menu-item logout-item"
        type="button"
        @click="logout"
      >
        <i class="fi fi-rr-sign-out-alt menu-icon"></i>
        <span>Déconnexion</span>
      </button>

    </aside>

    <!-- =========================
         MAIN AREA
    ========================== -->
    <div class="main-area">

      <!-- TOPBAR -->
      <header class="topbar">

        <!-- SEARCH -->
        <div class="topbar-search">
          <i class="fi fi-rr-search"></i>

          <input
            v-model="searchText"
            type="text"
            placeholder="Rechercher..."
            aria-label="Rechercher"
          />
        </div>

        <!-- RIGHT SIDE -->
        <div class="topbar-actions">

          <!-- NOTIFICATIONS -->
          <button
            class="notification-btn"
            type="button"
            title="Notifications"
          >
            <i class="fi fi-rr-bell"></i>
            <span class="notification-dot"></span>
          </button>

          <!-- PROFILE -->
          <div class="user-profile">

            <div class="user-avatar">

              <img
                v-if="userPhoto"
                :src="userPhoto"
                :alt="userName"
                @error="handleImageError"
              />

              <span v-else>
                {{ userInitial }}
              </span>

            </div>

            <div class="user-details">

              <span class="user-name">
                {{ userName }}
              </span>

              <span class="user-role">
                {{ formattedRole }}
              </span>

            </div>

            <i
              class="fi fi-rr-angle-small-down user-chevron"
            ></i>

          </div>

        </div>

      </header>

      <!-- PAGE -->
      <main class="main-content">
        <RouterView />
      </main>

      <!-- FOOTER -->
      <footer class="main-footer">

        <div class="footer-left">
          © {{ currentYear }} VITALIS – Gestion intelligence de votre clinique.
          Tous droits réservés.
        </div>

        <div class="footer-right">
          <i class="fi fi-rr-heart"></i>
          <span>Une meilleure santé, un meilleur avenir.</span>
        </div>

      </footer>

    </div>

  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

/* =========================
   SEARCH
========================= */
const searchText = ref('')

/* =========================
   USER
========================= */

const storedUser =
  localStorage.getItem('user') ||
  sessionStorage.getItem('user')

let parsedUser = {}

try {
  parsedUser = storedUser
    ? JSON.parse(storedUser)
    : {}
} catch (error) {
  parsedUser = {}
}

const imageError = ref(false)

const userName = computed(() => {
  return (
    parsedUser?.name ||
    parsedUser?.nom ||
    'Administrateur'
  )
})

const userRole = computed(() => {
  return parsedUser?.role || 'administrateur'
})

const formattedRole = computed(() => {
  const role = userRole.value

  if (!role) {
    return 'Utilisateur'
  }

  return role
    .replaceAll('_', ' ')
    .replaceAll('-', ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase())
})

const userInitial = computed(() => {
  return userName.value
    ? userName.value.charAt(0).toUpperCase()
    : 'A'
})

const userPhoto = computed(() => {
  if (imageError.value) {
    return null
  }

  return (
    parsedUser?.photo_profil ||
    parsedUser?.photo ||
    parsedUser?.avatar ||
    null
  )
})

const handleImageError = () => {
  imageError.value = true
}

/* =========================
   FOOTER
========================= */

const currentYear = new Date().getFullYear()

/* =========================
   LOGOUT
========================= */

const logout = async () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')

  sessionStorage.removeItem('token')
  sessionStorage.removeItem('user')

  await router.push('/login')
}
</script>

<style src="../styles/MainLayout.css"></style>