<template>
  <section class="users-page">
    <!-- HEADER -->
    <div class="users-header">
      <div>
        <h1>Gestion des utilisateurs</h1>

        <p>
          Gérez les comptes et les droits d'accès des utilisateurs.
        </p>
      </div>

      <button
        v-if="peutAction('utilisateurs.ajouter')"
        type="button"
        class="btn-add-user"
        @click="openCreateModal"
      >
        <i class="fi fi-rr-user-add"></i>
        <span>Ajouter un utilisateur</span>
      </button>
    </div>

    <!-- FILTRES -->
    <div class="users-toolbar">
      <div class="users-search">
        <i class="fi fi-rr-search"></i>

        <input
          v-model="search"
          type="text"
          placeholder="Rechercher un utilisateur..."
        />
      </div>

      <select
        v-model="roleFilter"
        class="filter-select"
      >
        <option value="">Tous les rôles</option>
        <option value="administrateur">Administrateur</option>
        <option value="medecin">Médecin</option>
        <option value="secretaire">Secrétaire</option>
        <option value="infirmier">Infirmier</option>
        <option value="patient">Patient</option>
      </select>

      <select
        v-model="statusFilter"
        class="filter-select"
      >
        <option value="">Tous les statuts</option>
        <option value="actif">Actif</option>
        <option value="inactif">Inactif</option>
      </select>
    </div>

    <!-- TABLEAU -->
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
              <td
                colspan="6"
                class="empty-users"
              >
                <i class="fi fi-rr-spinner"></i>

                <h3>Chargement...</h3>

                <p>
                  Récupération des utilisateurs depuis le serveur.
                </p>
              </td>
            </tr>

            <!-- ERREUR -->
            <tr v-else-if="errorMessage">
              <td
                colspan="6"
                class="empty-users"
              >
                <i class="fi fi-rr-exclamation"></i>

                <h3>Erreur</h3>

                <p>{{ errorMessage }}</p>

                <button
                  type="button"
                  class="btn-retry"
                  @click="fetchUsers"
                >
                  Réessayer
                </button>
              </td>
            </tr>

            <!-- UTILISATEURS -->
            <template v-else>
              <tr
                v-for="user in users"
                :key="user.id"
              >
                <!-- UTILISATEUR -->
                <td>
                  <div class="user-info-cell">
                    <div
                      class="table-user-avatar"
                      :class="
                        'avatar-' + ((user.id % 5) + 1)
                      "
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

                <!-- RÔLE -->
                <td>
                  <span
                    class="role-badge"
                    :class="getRoleClass(user.role)"
                  >
                    {{ formatRole(user.role) }}
                  </span>
                </td>

                <!-- STATUT -->
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

                <!-- DERNIÈRE CONNEXION -->
                <td class="last-login">
                  {{ user.lastLogin }}
                </td>

                <!-- ACTIONS -->
                <td>
                  <div class="actions">
                    <!-- CONSULTER -->
                    <button
                      v-if="
                        peutAction(
                          'utilisateurs.consulter'
                        )
                      "
                      type="button"
                      class="action-btn view-btn"
                      title="Voir"
                      @click="viewUser(user)"
                    >
                      <i class="fi fi-rr-eye"></i>
                    </button>

                    <!-- MODIFIER -->
                    <button
                      v-if="
                        peutAction(
                          'utilisateurs.modifier'
                        )
                      "
                      type="button"
                      class="action-btn edit-btn"
                      title="Modifier"
                      @click="openEditModal(user)"
                    >
                      <i class="fi fi-rr-pencil"></i>
                    </button>

                    <!-- SUPPRIMER -->
                    <button
                      v-if="
                        peutAction(
                          'utilisateurs.supprimer'
                        )
                      "
                      type="button"
                      class="action-btn delete-btn"
                      title="Supprimer"
                      @click="openDeleteModal(user)"
                    >
                      <i class="fi fi-rr-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- AUCUN UTILISATEUR -->
              <tr v-if="users.length === 0">
                <td
                  colspan="6"
                  class="empty-users"
                >
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
            type="button"
            :disabled="
              currentPage === 1 ||
              loading
            "
            @click="previousPage"
          >
            <i class="fi fi-rr-angle-left"></i>
          </button>

          <button
            type="button"
            class="page-active"
          >
            {{ currentPage }}
          </button>

          <button
            type="button"
            :disabled="
              currentPage >= totalPages ||
              loading
            "
            @click="nextPage"
          >
            <i class="fi fi-rr-angle-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- AJOUTER / MODIFIER -->
    <UserFormModal
      :open="showFormModal"
      :loading="savingUser"
      :mode="formMode"
      :user="editingUser"
      @close="closeFormModal"
      @submit="saveUser"
    />

    <!-- CONSULTER -->
    <UserDetailsModal
      :open="showDetailsModal"
      :user="selectedUser"
      :loading="loadingDetails"
      @close="closeDetailsModal"
    />

    <!-- SUPPRIMER -->
    <DeleteUserModal
      :open="showDeleteModal"
      :user="userToDelete"
      :loading="deletingUser"
      @close="closeDeleteModal"
      @confirm="deleteUser"
    />
  </section>
</template>

<script setup>
import {
  computed,
  onMounted,
  ref,
  watch,
} from 'vue'

import api, {
  messageErreur,
} from '../../api.js'

import {
  peutAction,
} from '../../actions.js'

import {
  useNotification,
} from '../../composables/useNotification.js'

import UserFormModal from '../../components/utilisateurs/UserFormModal.vue'

import UserDetailsModal from '../../components/utilisateurs/UserDetailsModal.vue'

import DeleteUserModal from '../../components/utilisateurs/DeleteUserModal.vue'

/* ==============================
   NOTIFICATIONS
================================ */

const {
  success,
  error: notifyError,
  warning,
} = useNotification()

/* ==============================
   LISTE ET FILTRES
================================ */

const users = ref([])
const loading = ref(false)
const errorMessage = ref('')

const search = ref('')
const roleFilter = ref('')
const statusFilter = ref('')

/* ==============================
   PAGINATION
================================ */

const currentPage = ref(1)
const perPage = 6

const totalUsers = ref(0)
const lastPage = ref(1)

/* ==============================
   AJOUTER / MODIFIER
================================ */

const showFormModal = ref(false)
const savingUser = ref(false)

const formMode = ref('create')
const editingUser = ref(null)

/* ==============================
   CONSULTER
================================ */

const showDetailsModal = ref(false)
const selectedUser = ref(null)
const loadingDetails = ref(false)

/* ==============================
   SUPPRIMER
================================ */

const showDeleteModal = ref(false)
const userToDelete = ref(null)
const deletingUser = ref(false)

/* ==============================
   CHARGER LES UTILISATEURS
================================ */

const fetchUsers = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/users', {
      params: {
        search:
          search.value || undefined,

        role:
          roleFilter.value || undefined,

        statut: statusFilter.value
          ? statusFilter.value
              .charAt(0)
              .toUpperCase() +
            statusFilter.value.slice(1)
          : undefined,

        page: currentPage.value,
        per_page: perPage,
      },
    })

    const result = response.data.users

    users.value = result.data.map((user) => ({
      id: user.id,
      name: user.name,
      email: user.email,
      role: user.role,

      statut:
        user.statut || 'Inactif',

      status: (
        user.statut || 'Inactif'
      ).toLowerCase(),

      id_medecin:
        user.id_medecin,

      id_patient:
        user.id_patient,

      lastLogin: formatLastLogin(
        user.derniere_connexion
      ),
    }))

    totalUsers.value = result.total
    lastPage.value = result.last_page
  } catch (requestError) {
    errorMessage.value = messageErreur(
      requestError,
      'Impossible de charger les utilisateurs.'
    )
  } finally {
    loading.value = false
  }
}

/* ==============================
   OUVRIR LE FORMULAIRE D'AJOUT
================================ */

const openCreateModal = () => {
  formMode.value = 'create'
  editingUser.value = null
  showFormModal.value = true
}

/* ==============================
   OUVRIR LE FORMULAIRE DE MODIFICATION
================================ */

const openEditModal = (user) => {
  formMode.value = 'edit'

  editingUser.value = {
    ...user,

    statut:
      user.statut ||
      (
        user.status === 'actif'
          ? 'Actif'
          : 'Inactif'
      ),
  }

  showFormModal.value = true
}

/* ==============================
   FERMER LE FORMULAIRE
================================ */

const closeFormModal = () => {
  if (savingUser.value) {
    return
  }

  showFormModal.value = false
  editingUser.value = null
  formMode.value = 'create'
}

/* ==============================
   AJOUTER OU MODIFIER
================================ */

const saveUser = async (formData) => {
  if (
    formMode.value === 'create' &&
    formData.password !==
      formData.password_confirmation
  ) {
    warning(
      'Les mots de passe ne correspondent pas.'
    )

    return
  }

  savingUser.value = true

  try {
    if (formMode.value === 'edit') {
      const response = await api.put(
        `/users/${editingUser.value.id}`,
        formData
      )

      success(
        response.data.message ||
        'Utilisateur modifié avec succès.'
      )
    } else {
      const response = await api.post(
        '/users',
        formData
      )

      success(
        response.data.message ||
        'Utilisateur ajouté avec succès.'
      )

      currentPage.value = 1
    }

    /*
     * Fermer directement la fenêtre.
     * closeFormModal ne peut pas être appelée
     * pendant savingUser = true.
     */
    showFormModal.value = false
    editingUser.value = null
    formMode.value = 'create'

    await fetchUsers()
  } catch (requestError) {
    notifyError(
      messageErreur(
        requestError,

        formMode.value === 'edit'
          ? "Impossible de modifier l'utilisateur."
          : "Impossible d'ajouter l'utilisateur."
      )
    )
  } finally {
    savingUser.value = false
  }
}

/* ==============================
   CONSULTER UN UTILISATEUR
================================ */

const viewUser = async (user) => {
  showDetailsModal.value = true
  selectedUser.value = null
  loadingDetails.value = true

  try {
    const response = await api.get(
      `/users/${user.id}`
    )

    selectedUser.value =
      response.data.user
  } catch (requestError) {
    showDetailsModal.value = false

    notifyError(
      messageErreur(
        requestError,
        "Impossible de charger les informations de l'utilisateur."
      )
    )
  } finally {
    loadingDetails.value = false
  }
}

const closeDetailsModal = () => {
  if (loadingDetails.value) {
    return
  }

  showDetailsModal.value = false
  selectedUser.value = null
}

/* ==============================
   OUVRIR LA CONFIRMATION DE SUPPRESSION
================================ */

const openDeleteModal = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

/* ==============================
   FERMER LA CONFIRMATION
================================ */

const closeDeleteModal = () => {
  if (deletingUser.value) {
    return
  }

  showDeleteModal.value = false
  userToDelete.value = null
}

/* ==============================
   SUPPRIMER UN UTILISATEUR
================================ */

const deleteUser = async () => {
  if (!userToDelete.value) {
    return
  }

  deletingUser.value = true

  try {
    const response = await api.delete(
      `/users/${userToDelete.value.id}`
    )

    success(
      response.data.message ||
      'Utilisateur supprimé avec succès.'
    )

    showDeleteModal.value = false
    userToDelete.value = null

    /*
     * Si le dernier utilisateur de la page
     * est supprimé, revenir à la page précédente.
     */
    if (
      users.value.length === 1 &&
      currentPage.value > 1
    ) {
      currentPage.value--
    }

    await fetchUsers()
  } catch (requestError) {
    notifyError(
      messageErreur(
        requestError,
        "Impossible de supprimer l'utilisateur."
      )
    )
  } finally {
    deletingUser.value = false
  }
}

/* ==============================
   PAGINATION
================================ */

const totalPages = computed(() => {
  return lastPage.value
})

const startItem = computed(() => {
  if (totalUsers.value === 0) {
    return 0
  }

  return (
    (currentPage.value - 1) *
      perPage +
    1
  )
})

const endItem = computed(() => {
  return Math.min(
    currentPage.value * perPage,
    totalUsers.value
  )
})

const previousPage = async () => {
  if (currentPage.value <= 1) {
    return
  }

  currentPage.value--

  await fetchUsers()
}

const nextPage = async () => {
  if (
    currentPage.value >= totalPages.value
  ) {
    return
  }

  currentPage.value++

  await fetchUsers()
}

/* ==============================
   FORMATAGE
================================ */

const getInitials = (name = '') => {
  return name
    .split(' ')
    .filter(Boolean)
    .map((item) => item.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

const formatRole = (role) => {
  const labels = {
    administrateur:
      'Administrateur',

    medecin:
      'Médecin',

    secretaire:
      'Secrétaire',

    infirmier:
      'Infirmier',

    patient:
      'Patient',
  }

  return labels[role] || role || '—'
}

const getRoleClass = (role) => {
  return `role-${role}`
}

const formatLastLogin = (date) => {
  if (!date) {
    return '—'
  }

  return new Date(date).toLocaleString(
    'fr-FR'
  )
}

/* ==============================
   FILTRES
================================ */

let filterTimer = null

watch(
  [
    search,
    roleFilter,
    statusFilter,
  ],

  () => {
    clearTimeout(filterTimer)

    filterTimer = setTimeout(async () => {
      currentPage.value = 1

      await fetchUsers()
    }, 350)
  }
)

/* ==============================
   CHARGEMENT INITIAL
================================ */

onMounted(() => {
  fetchUsers()
})
</script>

<style src="../../styles/utilisateurs.css"></style>