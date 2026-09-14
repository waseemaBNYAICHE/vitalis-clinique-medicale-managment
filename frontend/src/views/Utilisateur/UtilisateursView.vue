<template>
  <section class="users-page">

    <!-- HEADER -->
    <div class="users-header">
      <div>
        <h1>Gestion des utilisateurs</h1>
        <p>Gérez les comptes et les droits d'accès des utilisateurs.</p>
      </div>

      <!-- SCRUM-534 : la gestion des comptes est gouvernee par une seule
           permission backend, 'roles.manage', accordee au seul
           administrateur. -->
      <button
        class="btn-add-user"
        v-if="peutAction('utilisateurs.ajouter')"
      >
        <i class="fi fi-rr-user-add"></i>
        <span>Ajouter un utilisateur</span>
      </button>
    </div>

    <!-- FILTERS -->
    <div class="users-toolbar">

      <div class="users-search">
        <i class="fi fi-rr-search"></i>

        <input
          v-model="search"
          type="text"
          placeholder="Rechercher un utilisateur..."
        />
      </div>

      <select v-model="roleFilter" class="filter-select">
        <option value="">Tous les rôles</option>
        <option value="administrateur">Administrateur</option>
        <option value="medecin">Médecin</option>
        <option value="secretaire">Secrétaire</option>
        <option value="infirmier">Infirmier</option>
        <option value="patient">Patient</option>
      </select>

      <select v-model="statusFilter" class="filter-select">
        <option value="">Tous les statuts</option>
        <option value="actif">Actif</option>
        <option value="inactif">Inactif</option>
      </select>

    </div>

    <!-- TABLE -->
    <div class="users-card">

      <div class="table-responsive">
        <table class="users-table">

          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Email</th>
              <th>Rôle</th>
              <th>Statut</th>
              <th>Dernière connexion</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>

            <!-- CHARGEMENT -->
            <tr v-if="loading">
              <td colspan="6" class="empty-users">
                <i class="fi fi-rr-spinner"></i>

                <h3>Chargement...</h3>

                <p>
                  Récupération des utilisateurs depuis le serveur.
                </p>
              </td>
            </tr>

            <!-- ERREUR -->
            <tr v-else-if="errorMessage">
              <td colspan="6" class="empty-users">

                <i class="fi fi-rr-exclamation"></i>

                <h3>Erreur</h3>

                <p>
                  {{ errorMessage }}
                </p>

              </td>
            </tr>

            <!-- UTILISATEURS -->
            <template v-else>

              <tr
                v-for="user in paginatedUsers"
                :key="user.id"
              >

                <!-- USER -->
                <td>
                  <div class="user-info-cell">

                    <div
                      class="table-user-avatar"
                      :class="'avatar-' + ((user.id % 5) + 1)"
                    >
                      {{ getInitials(user.name) }}
                    </div>

                    <div class="user-info-text">
                      <span class="table-user-name">
                        {{ user.name }}
                      </span>

                      <span class="table-user-id">
                        #{{ user.id }}
                      </span>
                    </div>

                  </div>
                </td>

                <!-- EMAIL -->
                <td class="email-cell">
                  {{ user.email }}
                </td>

                <!-- ROLE -->
                <td>
                  <span
                    class="role-badge"
                    :class="getRoleClass(user.role)"
                  >
                    {{ formatRole(user.role) }}
                  </span>
                </td>

                <!-- STATUS -->
                <td>
                  <span
                    class="status-badge"
                    :class="
                      user.status === 'actif'
                        ? 'status-active'
                        : 'status-inactive'
                    "
                  >
                    <span class="status-dot"></span>

                    {{
                      user.status === 'actif'
                        ? 'Actif'
                        : 'Inactif'
                    }}
                  </span>
                </td>

                <!-- LAST LOGIN -->
                <td class="last-login">
                  {{ user.lastLogin }}
                </td>

                <!-- ACTIONS -->
                <td>
                  <div class="actions">

                    <button
                      v-if="peutAction('utilisateurs.consulter')"
                      class="action-btn view-btn"
                      title="Voir"
                    >
                      <i class="fi fi-rr-eye"></i>
                    </button>

                    <button
                      v-if="peutAction('utilisateurs.modifier')"
                      class="action-btn edit-btn"
                      title="Modifier"
                    >
                      <i class="fi fi-rr-pencil"></i>
                    </button>

                    <button
                      v-if="peutAction('utilisateurs.supprimer')"
                      class="action-btn delete-btn"
                      title="Supprimer"
                    >
                      <i class="fi fi-rr-trash"></i>
                    </button>

                  </div>
                </td>

              </tr>

              <!-- AUCUN UTILISATEUR -->
              <tr v-if="paginatedUsers.length === 0">
                <td colspan="6" class="empty-users">

                  <i class="fi fi-rr-users"></i>

                  <h3>Aucun utilisateur trouvé</h3>

                  <p>
                    Aucun utilisateur ne correspond à votre recherche.
                  </p>

                </td>
              </tr>

            </template>

          </tbody>

        </table>
      </div>

      <!-- PAGINATION -->
      <div class="users-pagination">

        <span class="pagination-info">
          Affichage de
          {{ startItem }}
          à
          {{ endItem }}
          sur
          {{ totalUsers }}
          utilisateurs
        </span>

        <div class="pagination-buttons">

          <button
            :disabled="currentPage === 1 || loading"
            @click="previousPage"
          >
            <i class="fi fi-rr-angle-left"></i>
          </button>

          <button class="page-active">
            {{ currentPage }}
          </button>

          <button
            :disabled="currentPage === totalPages || loading"
            @click="nextPage"
          >
            <i class="fi fi-rr-angle-right"></i>
          </button>

        </div>

      </div>

    </div>

  </section>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import api, { messageErreur } from '../../api.js'
import { peutAction } from '../../actions.js'

const search = ref('')
const roleFilter = ref('')
const statusFilter = ref('')

const currentPage = ref(1)
const perPage = 6

const users = ref([])
const loading = ref(false)
const errorMessage = ref('')

const totalUsers = ref(0)
const lastPage = ref(1)

/**
 * Récupération des utilisateurs depuis l'API Laravel.
 */
const fetchUsers = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/users', {
      params: {
        search: search.value || undefined,

        role: roleFilter.value || undefined,

        statut: statusFilter.value
          ? statusFilter.value.charAt(0).toUpperCase() +
            statusFilter.value.slice(1)
          : undefined,

        page: currentPage.value,

        per_page: perPage,
      },
    })

    const result = response.data.users

    /*
     * Adaptation des données backend au format
     * utilisé par l'interface frontend.
     */
    users.value = result.data.map((user) => ({
      id: user.id,
      name: user.name,
      email: user.email,
      role: user.role,
      status: (user.statut || '').toLowerCase(),
      lastLogin: '—',
    }))

    totalUsers.value = result.total
    lastPage.value = result.last_page

  } catch (error) {
    errorMessage.value = messageErreur(
      error,
      'Impossible de charger les utilisateurs.'
    )
  } finally {
    loading.value = false
  }
}

/**
 * Utilisateurs affichés sur la page courante.
 *
 * La pagination et les filtres sont maintenant
 * gérés directement par Laravel.
 */
const paginatedUsers = computed(() => {
  return users.value
})

const totalPages = computed(() => {
  return lastPage.value
})

const startItem = computed(() => {
  if (totalUsers.value === 0) {
    return 0
  }

  return (currentPage.value - 1) * perPage + 1
})

const endItem = computed(() => {
  return Math.min(
    currentPage.value * perPage,
    totalUsers.value
  )
})

/**
 * Page précédente.
 */
const previousPage = async () => {
  if (currentPage.value > 1) {
    currentPage.value--
    await fetchUsers()
  }
}

/**
 * Page suivante.
 */
const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    await fetchUsers()
  }
}

/**
 * Initiales de l'utilisateur.
 */
const getInitials = (name) => {
  return name
    .split(' ')
    .map((item) => item.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

/**
 * Traduction du rôle.
 */
const formatRole = (role) => {
  const labels = {
    administrateur: 'Administrateur',
    medecin: 'Médecin',
    secretaire: 'Secrétaire',
    infirmier: 'Infirmier',
    patient: 'Patient',
  }

  return labels[role] || role
}

/**
 * Classe CSS du rôle.
 */
const getRoleClass = (role) => {
  return `role-${role}`
}

/**
 * Recherche et filtres.
 *
 * À chaque modification :
 * - retour à la première page
 * - nouvelle requête vers Laravel
 */
watch(
  [search, roleFilter, statusFilter],
  async () => {
    currentPage.value = 1
    await fetchUsers()
  }
)

/**
 * Chargement initial.
 */
onMounted(() => {
  fetchUsers()
})
</script>

<style src="../../styles/utilisateurs.css"></style>