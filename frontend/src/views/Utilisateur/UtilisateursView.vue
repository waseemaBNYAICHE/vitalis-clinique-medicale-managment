<template>
  <section class="users-page">

    <!-- HEADER -->
    <div class="users-header">
      <div>
        <h1>Gestion des utilisateurs</h1>
        <p>Gérez les comptes et les droits d'accès des utilisateurs.</p>
      </div>

      <button class="btn-add-user">
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
                    class="action-btn view-btn"
                    title="Voir"
                  >
                    <i class="fi fi-rr-eye"></i>
                  </button>

                  <button
                    class="action-btn edit-btn"
                    title="Modifier"
                  >
                    <i class="fi fi-rr-pencil"></i>
                  </button>

                  <button
                    class="action-btn delete-btn"
                    title="Supprimer"
                  >
                    <i class="fi fi-rr-trash"></i>
                  </button>

                </div>
              </td>

            </tr>

            <tr v-if="filteredUsers.length === 0">
              <td colspan="6" class="empty-users">

                <i class="fi fi-rr-users"></i>

                <h3>Aucun utilisateur trouvé</h3>

                <p>
                  Aucun utilisateur ne correspond à votre recherche.
                </p>

              </td>
            </tr>

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
          {{ filteredUsers.length }}
          utilisateurs
        </span>

        <div class="pagination-buttons">

          <button
            :disabled="currentPage === 1"
            @click="previousPage"
          >
            <i class="fi fi-rr-angle-left"></i>
          </button>

          <button class="page-active">
            {{ currentPage }}
          </button>

          <button
            :disabled="currentPage === totalPages"
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
import { computed, ref, watch } from 'vue'

const search = ref('')
const roleFilter = ref('')
const statusFilter = ref('')

const currentPage = ref(1)
const perPage = 6

/*
  Données temporaires pour SCRUM-704.
  Plus tard on pourra les remplacer par l'API backend.
*/
const users = ref([
  {
    id: 1,
    name: 'Mounia Stitou',
    email: 'mounia.stitou@vitalis.ma',
    role: 'administrateur',
    status: 'actif',
    lastLogin: '12/06/2026 - 14:32'
  },
  {
    id: 2,
    name: 'Ayoub El Fassi',
    email: 'ayoub.elfassi@vitalis.ma',
    role: 'medecin',
    status: 'actif',
    lastLogin: '11/06/2026 - 10:15'
  },
  {
    id: 3,
    name: 'Khadija Bennis',
    email: 'khadija.bennis@vitalis.ma',
    role: 'secretaire',
    status: 'actif',
    lastLogin: '10/06/2026 - 09:20'
  },
  {
    id: 4,
    name: 'Anas Ouazzani',
    email: 'anas.ouazzani@vitalis.ma',
    role: 'infirmier',
    status: 'inactif',
    lastLogin: '05/06/2026 - 16:45'
  },
  {
    id: 5,
    name: 'Ghita Amrani',
    email: 'ghita.amrani@vitalis.ma',
    role: 'medecin',
    status: 'actif',
    lastLogin: '12/06/2026 - 11:05'
  },
  {
    id: 6,
    name: 'Zakaria Naciri',
    email: 'zakaria.naciri@vitalis.ma',
    role: 'patient',
    status: 'actif',
    lastLogin: '09/06/2026 - 08:10'
  },
  {
    id: 7,
    name: 'Yasmine Belkacem',
    email: 'yasmine.belkacem@vitalis.ma',
    role: 'secretaire',
    status: 'inactif',
    lastLogin: '01/06/2026 - 12:00'
  },
  {
    id: 8,
    name: 'Mohamed Fikri',
    email: 'mohamed.fikri@vitalis.ma',
    role: 'infirmier',
    status: 'actif',
    lastLogin: '12/06/2026 - 13:50'
  }
])

const filteredUsers = computed(() => {
  const term = search.value.trim().toLowerCase()

  return users.value.filter((user) => {
    const matchesSearch =
      !term ||
      user.name.toLowerCase().includes(term) ||
      user.email.toLowerCase().includes(term) ||
      user.role.toLowerCase().includes(term)

    const matchesRole =
      !roleFilter.value ||
      user.role === roleFilter.value

    const matchesStatus =
      !statusFilter.value ||
      user.status === statusFilter.value

    return (
      matchesSearch &&
      matchesRole &&
      matchesStatus
    )
  })
})

const totalPages = computed(() => {
  return Math.max(
    1,
    Math.ceil(filteredUsers.value.length / perPage)
  )
})

const paginatedUsers = computed(() => {
  const start =
    (currentPage.value - 1) * perPage

  return filteredUsers.value.slice(
    start,
    start + perPage
  )
})

const startItem = computed(() => {
  if (filteredUsers.value.length === 0) {
    return 0
  }

  return (
    (currentPage.value - 1) * perPage + 1
  )
})

const endItem = computed(() => {
  return Math.min(
    currentPage.value * perPage,
    filteredUsers.value.length
  )
})

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const getInitials = (name) => {
  return name
    .split(' ')
    .map((item) => item.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

const formatRole = (role) => {
  const labels = {
    administrateur: 'Administrateur',
    medecin: 'Médecin',
    secretaire: 'Secrétaire',
    infirmier: 'Infirmier',
    patient: 'Patient'
  }

  return labels[role] || role
}

const getRoleClass = (role) => {
  return `role-${role}`
}

watch(
  [search, roleFilter, statusFilter],
  () => {
    currentPage.value = 1
  }
)
</script>

<style src="../../styles/utilisateurs.css"></style>