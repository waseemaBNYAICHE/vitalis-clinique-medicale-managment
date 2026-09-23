<template>
  <div class="app-layout">

    <!-- =====================================================
         SIDEBAR
    ====================================================== -->
    <aside class="sidebar">

      <!-- LOGO -->
      <div class="sidebar-brand">

        <img
          src="../assets/logo-vitalis.png"
          alt="VITALIS Clinique Médicale"
          class="brand-logo"
        />

        <div class="brand-text">
          <h1>VITALIS</h1>
          <p>Gestion intelligente de votre clinique</p>
        </div>

      </div>


      <!-- ===================================================
           MENU DYNAMIQUE
      ==================================================== -->
      <nav class="sidebar-menu">

        <RouterLink
          v-for="item in visibleMenuItems"
          :key="item.route"
          :to="item.route"
          class="menu-item"
        >

          <i
            :class="[item.icon, 'menu-icon']"
          ></i>

          <span>
            {{ item.displayLabel }}
          </span>

        </RouterLink>

      </nav>


      <!-- ===================================================
           PROFIL EN BAS DU SIDEBAR
      ==================================================== -->
      <div class="sidebar-user">

        <div class="sidebar-user-avatar">

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


        <div class="sidebar-user-info">

          <strong>
            {{ userName }}
          </strong>

          <span>
            {{ formattedRole }}
          </span>

        </div>


        <!-- DECONNEXION -->
        <button
          type="button"
          class="sidebar-logout"
          title="Déconnexion"
          @click="logout"
        >
          <i class="fi fi-rr-sign-out-alt"></i>
        </button>

      </div>

    </aside>


    <!-- =====================================================
         MAIN
    ====================================================== -->
    <div class="main-area">

      <!-- ===================================================
           TOPBAR
      ==================================================== -->
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


        <!-- =================================================
             ACTIONS DROITE
        ================================================== -->
        <div class="topbar-actions">

          <!-- =================================================
               SELECTEUR DE ROLE
               DEMO FRONTEND UNIQUEMENT
          ================================================== -->
          <div class="role-selector">

            <div class="role-selector-icon">
              <i class="fi fi-rr-user-gear"></i>
            </div>

            <div class="role-selector-content">

              <span class="role-selector-label">
                Vue utilisateur
              </span>

              <select
                v-model="selectedRole"
                @change="changeRole"
              >

                <option value="administrateur">
                  Administrateur
                </option>

                <option value="medecin">
                  Médecin
                </option>

                <option value="secretaire">
                  Secrétaire
                </option>

                <option value="infirmier">
                  Infirmier
                </option>

                <option value="patient">
                  Patient
                </option>

              </select>

            </div>

          </div>


          <!-- NOTIFICATIONS -->
          <button
            class="notification-btn"
            type="button"
            title="Notifications"
          >

            <i class="fi fi-rr-bell"></i>

            <span
              class="notification-dot"
            ></span>

          </button>

        </div>

      </header>


      <!-- ===================================================
           PAGE
      ==================================================== -->
      <main class="main-content">

        <RouterView />

      </main>


      <!-- ===================================================
           FOOTER
      ==================================================== -->
      <footer class="main-footer">

        <div class="footer-left">

          © {{ currentYear }} VITALIS –
          Gestion intelligente de votre clinique.
          Tous droits réservés.

        </div>


        <div class="footer-right">

          <i class="fi fi-rr-heart"></i>

          <span>
            Une meilleure santé,
            un meilleur avenir.
          </span>

        </div>

      </footer>

    </div>

  </div>
</template>


<script setup>

import {
  computed,
  ref
} from 'vue'

import {
  useRouter
} from 'vue-router'


/* =========================================================
   ROUTER
========================================================= */

const router = useRouter()


/* =========================================================
   SEARCH
========================================================= */

const searchText = ref('')


/* =========================================================
   UTILISATEUR CONNECTE
========================================================= */

const storedUser =
  localStorage.getItem('user') ||
  sessionStorage.getItem('user')


let parsedUser = {}


try {

  parsedUser = storedUser
    ? JSON.parse(storedUser)
    : {}

} catch (error) {

  console.error(
    'Erreur lecture utilisateur :',
    error
  )

  parsedUser = {}
}


/* =========================================================
   ROLE REEL DE L'UTILISATEUR
========================================================= */

const realUserRole =
  (
    parsedUser?.role ||
    'administrateur'
  )
    .toString()
    .toLowerCase()
    .trim()


/* =========================================================
   ROLE DE DEMONSTRATION

   Ce rôle sert uniquement à tester les interfaces.
   Il ne modifie PAS le rôle réel dans PostgreSQL.
========================================================= */

const selectedRole = ref(
  sessionStorage.getItem(
    'vitalis_demo_role'
  ) || realUserRole
)


/* =========================================================
   ROLE ACTUEL UTILISE PAR L'INTERFACE
========================================================= */

const userRole = computed(() => {

  return selectedRole.value

})


/* =========================================================
   CHANGEMENT ROLE DEMO
========================================================= */

const changeRole = () => {

  sessionStorage.setItem(
    'vitalis_demo_role',
    selectedRole.value
  )

  /*
   * On retourne au dashboard.
   *
   * Le menu est recalculé automatiquement
   * grâce à visibleMenuItems.
   */

  router.push('/dashboard')
}


/* =========================================================
   NOM UTILISATEUR
========================================================= */

const userName = computed(() => {

  /*
   * Laravel :
   * prenom + nom
   */

  if (
    parsedUser?.prenom ||
    parsedUser?.nom
  ) {

    return `${parsedUser?.prenom ?? ''} ${parsedUser?.nom ?? ''}`
      .trim()
  }


  /*
   * Cas name
   */

  if (parsedUser?.name) {

    return parsedUser.name
  }


  return 'Utilisateur'
})


/* =========================================================
   LABEL ROLE
========================================================= */

const roleLabels = {

  administrateur:
    'Administrateur',

  medecin:
    'Médecin',

  secretaire:
    'Secrétaire',

  infirmier:
    'Infirmier',

  patient:
    'Patient'

}


const formattedRole = computed(() => {

  return (
    roleLabels[userRole.value] ||
    'Utilisateur'
  )

})


/* =========================================================
   INITIAL
========================================================= */

const userInitial = computed(() => {

  const name =
    userName.value.trim()


  if (!name) {

    return 'U'
  }


  const parts =
    name.split(/\s+/)


  if (parts.length >= 2) {

    return (
      parts[0].charAt(0) +
      parts[1].charAt(0)
    ).toUpperCase()
  }


  return name
    .charAt(0)
    .toUpperCase()

})


/* =========================================================
   PHOTO
========================================================= */

const imageError = ref(false)


const userPhoto = computed(() => {

  if (imageError.value) {

    return null
  }


  /*
   * Photo backend
   */

  const backendPhoto =
    parsedUser?.photo_profil ||
    parsedUser?.photo ||
    parsedUser?.avatar


  if (backendPhoto) {

    return backendPhoto
  }


  /*
   * Photo locale Paramètres
   */

  return (
    localStorage.getItem(
      'vitalis_profile_photo'
    ) ||
    null
  )

})


const handleImageError = () => {

  imageError.value = true

}


/* =========================================================
   MENU
========================================================= */

const menuItems = [

  {
    label:
      'Tableau de bord',

    route:
      '/dashboard',

    icon:
      'fi fi-rr-home',

    roles: [
      'administrateur',
      'medecin',
      'secretaire',
      'infirmier',
      'patient'
    ]
  },


  {
    label:
      'Patients',

    route:
      '/patients',

    icon:
      'fi fi-rr-users-medical',

    roles: [
      'administrateur',
      'medecin',
      'secretaire',
      'infirmier'
    ]
  },


  {
    label:
      'Rendez-vous',

    patientLabel:
      'Mes rendez-vous',

    route:
      '/rendez-vous',

    icon:
      'fi fi-rr-calendar',

    roles: [
      'administrateur',
      'medecin',
      'secretaire',
      'patient'
    ]
  },


  {
    label:
      'Consultations',

    patientLabel:
      'Mes consultations',

    route:
      '/consultations',

    icon:
      'fi fi-rr-stethoscope',

    roles: [
      'administrateur',
      'medecin',
      'patient'
    ]
  },


  {
    label:
      'Ordonnances',

    patientLabel:
      'Mes ordonnances',

    route:
      '/ordonnances',

    icon:
      'fi fi-rr-prescription-bottle-pill',

    roles: [
      'administrateur',
      'medecin',
      'patient'
    ]
  },


  {
    label:
      'Examens',

    patientLabel:
      'Mes examens',

    route:
      '/examens',

    icon:
      'fi fi-rr-document',

    roles: [
      'administrateur',
      'medecin',
      'infirmier',
      'patient'
    ]
  },


  {
    label:
      'Hospitalisations',

    patientLabel:
      'Mes hospitalisations',

    route:
      '/hospitalisations',

    icon:
      'fi fi-rr-bed',

    roles: [
      'administrateur',
      'medecin',
      'secretaire',
      'infirmier',
      'patient'
    ]
  },


  {
    label:
      'Facturation',

    patientLabel:
      'Mes factures',

    route:
      '/facturation',

    icon:
      'fi fi-rr-receipt',

    roles: [
      'administrateur',
      'secretaire',
      'patient'
    ]
  },


  {
    label:
      'Utilisateurs',

    route:
      '/utilisateurs',

    icon:
      'fi fi-rr-user-gear',

    roles: [
      'administrateur'
    ]
  },


  {
    label:
      'Paramètres',

    route:
      '/parametres',

    icon:
      'fi fi-rr-settings',

    roles: [
      'administrateur',
      'medecin',
      'secretaire',
      'infirmier',
      'patient'
    ]
  }

]


/* =========================================================
   FILTRAGE DU MENU
========================================================= */

const visibleMenuItems = computed(() => {

  const role =
    userRole.value


  return menuItems

    .filter(item => {

      return item.roles.includes(role)

    })

    .map(item => {

      const displayLabel =

        role === 'patient' &&
        item.patientLabel

          ? item.patientLabel

          : item.label


      return {

        ...item,

        displayLabel

      }

    })

})


/* =========================================================
   FOOTER
========================================================= */

const currentYear =
  new Date().getFullYear()


/* =========================================================
   LOGOUT
========================================================= */

const logout = async () => {

  /*
   * Supprimer authentification
   */

  localStorage.removeItem(
    'token'
  )

  localStorage.removeItem(
    'user'
  )


  sessionStorage.removeItem(
    'token'
  )

  sessionStorage.removeItem(
    'user'
  )


  /*
   * Supprimer mode démonstration
   */

  sessionStorage.removeItem(
    'vitalis_demo_role'
  )


  /*
   * Login
   */

  await router.push(
    '/login'
  )

}
</script>


<style src="../styles/MainLayout.css"></style>