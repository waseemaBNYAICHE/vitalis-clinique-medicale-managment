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
      <!-- SCRUM-533 : les entrees sont filtrees selon le role (permissions
           du backend) et selon les routes qui existent reellement. Voir
           src/navigation.js. -->
      <nav class="sidebar-menu">

        <RouterLink
          v-for="entree in entreesMenu"
          :key="entree.to"
          :to="entree.to"
          class="menu-item"
        >
          <i :class="['fi', entree.icone, 'menu-icon']"></i>
          <span>{{ entree.libelle }}</span>
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
import api from '../api'
import { closeSession, getUser } from '../auth.js'
import { entreesVisibles } from '../navigation.js'

const router = useRouter()

/* =========================
   NAVIGATION SELON LE ROLE
========================= */

// SCRUM-533 : la barre laterale affichait ses neuf liens a tout le monde. Un
// patient se voyait donc proposer "Patients" et "Utilisateurs", qui lui
// repondent 403, et six liens menaient a des chemins sans route.
//
// entreesVisibles() croise deux filtres : les permissions du role (miroir de
// la matrice backend, src/rbac.js) et les chemins reellement desservis par le
// routeur. Une entree reapparaitra d'elle-meme quand sa route sera ecrite.
//
// Rappel : cela ne protege rien, c'est de l'affichage. Le backend refuse un
// acces interdit par un 403, que le lien ait ete montre ou non.
const entreesMenu = computed(() => entreesVisibles())

/* =========================
   SEARCH
========================= */
const searchText = ref('')

/* =========================
   USER
========================= */

// SCRUM-531 : la session se lit via auth.js, seul module a connaitre les
// cles de stockage. Ce composant les recopiait ('user', 'token') et refaisait
// le JSON.parse de son cote : deux implementations a maintenir, dont une qui
// serait restee en arriere le jour ou les cles changent. auth.js gere deja le
// cas d'une donnee illisible.
const parsedUser = getUser() ?? {}

const imageError = ref(false)

// SCRUM-531 : sans session lisible, l'en-tete affichait "Administrateur" et
// un role 'administrateur'. Un defaut qui accorde le role le plus eleve est
// un defaut qui echoue du mauvais cote : c'est precisement la valeur sur
// laquelle SCRUM-533 et SCRUM-534 s'appuieront pour decider ce qui est
// visible. On repart donc d'une identite neutre et sans privilege.
//
// L'autorisation reelle reste cote backend : cette valeur ne sert qu'a
// l'affichage.
const userName = computed(() => {
  return (
    parsedUser?.name ||
    parsedUser?.nom ||
    'Utilisateur'
  )
})

const userRole = computed(() => {
  return parsedUser?.role || null
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

// SCRUM-531 : la deconnexion se contentait de vider le stockage du
// navigateur. Le jeton Sanctum restait donc valide cote serveur jusqu'a son
// expiration (12 h) : sur un poste partage de la clinique, un jeton recupere
// apres coup continuait d'ouvrir l'API alors que l'utilisateur se croyait
// deconnecte. La route POST /api/logout existait deja et revoque le jeton
// courant - elle n'etait simplement jamais appelee.
//
// closeSession() revoque puis efface, et efface meme si la revocation echoue.
const logout = async () => {
  await closeSession(() => api.post('/logout'))

  await router.push('/login')
}
</script>

<style src="../styles/MainLayout.css"></style>