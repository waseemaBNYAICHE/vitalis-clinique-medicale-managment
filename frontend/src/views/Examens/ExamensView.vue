<template>
  <div class="examens-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->
    <div class="page-header">

      <div>
        
        <div class="title-section">

          <div class="title-icon">
            <i class="fi fi-rr-document"></i>
          </div>

          <div>
            <h1>Examens</h1>

            <p>
              Gérez les examens médicaux et leurs résultats
            </p>
          </div>

        </div>
      </div>


      <button
        class="btn-primary"
        @click="openCreateModal"
      >
        <i class="fi fi-rr-plus"></i>
        Nouvel examen
      </button>

    </div>


    <!-- =====================================================
         QUICK ACTIONS
    ====================================================== -->

    <div class="quick-actions">

      <button class="quick-card">

        <div class="quick-icon blue">
          <i class="fi fi-rr-document"></i>
        </div>

        <div>
          <strong>Nouvel examen</strong>
          <span>Créer une demande d'examen</span>
        </div>

        <i class="fi fi-rr-angle-small-right arrow"></i>

      </button>


      <button class="quick-card">

        <div class="quick-icon orange">
          <i class="fi fi-rr-time-check"></i>
        </div>

        <div>
          <strong>Examens en attente</strong>
          <span>Consulter les examens à réaliser</span>
        </div>

        <i class="fi fi-rr-angle-small-right arrow"></i>

      </button>


      <button class="quick-card">

        <div class="quick-icon green">
          <i class="fi fi-rr-check-circle"></i>
        </div>

        <div>
          <strong>Résultats disponibles</strong>
          <span>Consulter les résultats médicaux</span>
        </div>

        <i class="fi fi-rr-angle-small-right arrow"></i>

      </button>

    </div>


    <!-- =====================================================
         CARD
    ====================================================== -->

    <div class="examens-card">

      <!-- SEARCH -->

      <div class="search-section">

        <div class="search-box">

          <i class="fi fi-rr-search"></i>

          <input
            v-model="search"
            type="text"
            placeholder="Rechercher par patient, type d'examen ou médecin..."
          />

        </div>


        <div class="filters">

          <select v-model="statusFilter">

            <option value="">
              Tous les statuts
            </option>

            <option value="En attente">
              En attente
            </option>

            <option value="En cours">
              En cours
            </option>

            <option value="Terminé">
              Terminé
            </option>

          </select>


          <select v-model="typeFilter">

            <option value="">
              Tous les types
            </option>

            <option
              v-for="type in examTypes"
              :key="type"
              :value="type"
            >
              {{ type }}
            </option>

          </select>


          <button class="btn-search">
            <i class="fi fi-rr-search"></i>
            Rechercher
          </button>


          <button
            class="btn-reset"
            @click="resetFilters"
          >
            <i class="fi fi-rr-refresh"></i>
            Réinitialiser
          </button>

        </div>

      </div>


      <!-- =================================================
           TABLE
      ================================================== -->

      <div class="table-responsive">

        <table class="examens-table">

          <thead>

            <tr>
              <th>#</th>
              <th>Patient</th>
              <th>Type d'examen</th>
              <th>Médecin</th>
              <th>Date</th>
              <th>Priorité</th>
              <th>Statut</th>
              <th>Résultat</th>
              <th>Actions</th>
            </tr>

          </thead>


          <tbody>

            <tr
              v-for="(examen, index) in paginatedExamens"
              :key="examen.id"
            >

              <td>
                {{ (currentPage - 1) * perPage + index + 1 }}
              </td>


              <!-- PATIENT -->

              <td>

                <div class="patient-info">

                  <div class="avatar">
                    {{ getInitials(examen.patient) }}
                  </div>

                  <div>
                    <strong>
                      {{ examen.patient }}
                    </strong>

                    <small>
                      {{ examen.patientReference }}
                    </small>
                  </div>

                </div>

              </td>


              <!-- TYPE -->

              <td>

                <div class="exam-type">

                  <div class="exam-icon">
                    <i class="fi fi-rr-document"></i>
                  </div>

                  <span>
                    {{ examen.type }}
                  </span>

                </div>

              </td>


              <!-- DOCTOR -->

              <td>
                {{ examen.medecin }}
              </td>


              <!-- DATE -->

              <td>
                {{ formatDate(examen.date) }}
              </td>


              <!-- PRIORITY -->

              <td>

                <span
                  class="priority-badge"
                  :class="priorityClass(examen.priorite)"
                >
                  {{ examen.priorite }}
                </span>

              </td>


              <!-- STATUS -->

              <td>

                <span
                  class="status-badge"
                  :class="statusClass(examen.statut)"
                >

                  <span class="status-dot"></span>

                  {{ examen.statut }}

                </span>

              </td>


              <!-- RESULT -->

              <td>

                <span
                  v-if="examen.resultat"
                  class="result-available"
                >
                  Disponible
                </span>

                <span
                  v-else
                  class="result-pending"
                >
                  —
                </span>

              </td>


              <!-- ACTIONS -->

              <td>

                <div class="actions">

                  <button
                    class="action-btn view"
                    title="Consulter"
                    @click="openViewModal(examen)"
                  >
                    <i class="fi fi-rr-eye"></i>
                  </button>


                  <button
                    class="action-btn edit"
                    title="Modifier"
                    @click="openEditModal(examen)"
                  >
                    <i class="fi fi-rr-pencil"></i>
                  </button>


                  <button
                    class="action-btn delete"
                    title="Supprimer"
                    @click="openDeleteModal(examen)"
                  >
                    <i class="fi fi-rr-trash"></i>
                  </button>

                </div>

              </td>

            </tr>


            <!-- EMPTY -->

            <tr v-if="filteredExamens.length === 0">

              <td colspan="9">

                <div class="empty-state">

                  <div class="empty-icon">
                    <i class="fi fi-rr-document"></i>
                  </div>

                  <h3>Aucun examen trouvé</h3>

                  <p>
                    Aucun examen ne correspond aux critères sélectionnés.
                  </p>

                  <button
                    class="btn-primary"
                    @click="openCreateModal"
                  >
                    <i class="fi fi-rr-plus"></i>
                    Nouvel examen
                  </button>

                </div>

              </td>

            </tr>

          </tbody>

        </table>

      </div>


      <!-- =================================================
           PAGINATION
      ================================================== -->

      <div
        v-if="filteredExamens.length"
        class="table-footer"
      >

        <span>

          Affichage de
          {{ firstItem }}
          à
          {{ lastItem }}
          sur
          {{ filteredExamens.length }}
          examens

        </span>


        <div class="pagination">

          <button
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            ‹
          </button>


          <button
            v-for="page in totalPages"
            :key="page"
            :class="{ active: currentPage === page }"
            @click="currentPage = page"
          >
            {{ page }}
          </button>


          <button
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            ›
          </button>

        </div>

      </div>

    </div>





    <!-- =====================================================
         CREATE / EDIT MODAL
    ====================================================== -->

    <div
      v-if="formModalOpen"
      class="modal-overlay"
      @click.self="closeFormModal"
    >

      <div class="modal">

        <div class="modal-header">

          <div>

            <h2>
              {{
                editingExamen
                  ? 'Modifier l’examen'
                  : 'Nouvel examen'
              }}
            </h2>

            <p>
              {{
                editingExamen
                  ? 'Modifiez les informations de l’examen'
                  : 'Renseignez les informations de l’examen médical'
              }}
            </p>

          </div>


          <button
            class="modal-close"
            @click="closeFormModal"
          >
            ×
          </button>

        </div>


        <form @submit.prevent="saveExamen">

          <div class="form-grid">


            <!-- PATIENT -->

            <div class="form-group">

              <label>Patient *</label>

              <input
                v-model="form.patient"
                type="text"
                placeholder="Nom et prénom du patient"
                required
              />

            </div>


            <!-- REFERENCE -->

            <div class="form-group">

              <label>Référence patient</label>

              <input
                v-model="form.patientReference"
                type="text"
                placeholder="Ex : PAT-001"
              />

            </div>


            <!-- TYPE -->

            <div class="form-group">

              <label>Type d'examen *</label>

              <select
                v-model="form.type"
                required
              >

                <option value="">
                  Sélectionner
                </option>

                <option
                  v-for="type in examTypes"
                  :key="type"
                  :value="type"
                >
                  {{ type }}
                </option>

              </select>

            </div>


            <!-- DOCTOR -->

            <div class="form-group">

              <label>Médecin *</label>

              <input
                v-model="form.medecin"
                type="text"
                placeholder="Dr. ..."
                required
              />

            </div>


            <!-- DATE -->

            <div class="form-group">

              <label>Date *</label>

              <input
                v-model="form.date"
                type="date"
                required
              />

            </div>


            <!-- PRIORITY -->

            <div class="form-group">

              <label>Priorité</label>

              <select v-model="form.priorite">

                <option value="Normale">
                  Normale
                </option>

                <option value="Urgente">
                  Urgente
                </option>

              </select>

            </div>


            <!-- STATUS -->

            <div class="form-group">

              <label>Statut</label>

              <select v-model="form.statut">

                <option value="En attente">
                  En attente
                </option>

                <option value="En cours">
                  En cours
                </option>

                <option value="Terminé">
                  Terminé
                </option>

              </select>

            </div>


            <!-- RESULT -->

            <div class="form-group full">

              <label>Résultat</label>

              <textarea
                v-model="form.resultat"
                rows="4"
                placeholder="Résultat de l'examen..."
              ></textarea>

            </div>


            <!-- NOTES -->

            <div class="form-group full">

              <label>Notes</label>

              <textarea
                v-model="form.notes"
                rows="3"
                placeholder="Informations ou observations complémentaires..."
              ></textarea>

            </div>

          </div>


          <div class="modal-footer">

            <button
              type="button"
              class="btn-cancel"
              @click="closeFormModal"
            >
              Annuler
            </button>


            <button
              type="submit"
              class="btn-save"
            >

              <i class="fi fi-rr-check"></i>

              {{
                editingExamen
                  ? 'Enregistrer les modifications'
                  : 'Créer l’examen'
              }}

            </button>

          </div>

        </form>

      </div>

    </div>


    <!-- =====================================================
         VIEW MODAL
    ====================================================== -->

    <div
      v-if="selectedExamen"
      class="modal-overlay"
      @click.self="selectedExamen = null"
    >

      <div class="modal view-modal">

        <div class="modal-header">

          <div>
            <h2>Détails de l'examen</h2>

            <p>
              Informations complètes de l'examen médical
            </p>
          </div>


          <button
            class="modal-close"
            @click="selectedExamen = null"
          >
            ×
          </button>

        </div>


        <div class="patient-profile">

          <div class="large-avatar">
            {{ getInitials(selectedExamen.patient) }}
          </div>

          <div>

            <h3>
              {{ selectedExamen.patient }}
            </h3>

            <span>
              {{ selectedExamen.patientReference }}
            </span>

          </div>

        </div>


        <div class="details-grid">

          <div class="detail-item">
            <span>Type d'examen</span>
            <strong>{{ selectedExamen.type }}</strong>
          </div>


          <div class="detail-item">
            <span>Médecin</span>
            <strong>{{ selectedExamen.medecin }}</strong>
          </div>


          <div class="detail-item">
            <span>Date</span>
            <strong>
              {{ formatDate(selectedExamen.date) }}
            </strong>
          </div>


          <div class="detail-item">
            <span>Priorité</span>

            <strong>
              {{ selectedExamen.priorite }}
            </strong>
          </div>


          <div class="detail-item">
            <span>Statut</span>

            <strong>
              {{ selectedExamen.statut }}
            </strong>
          </div>

        </div>


        <div class="result-section">

          <span>Résultat</span>

          <p>
            {{
              selectedExamen.resultat ||
              'Aucun résultat disponible pour le moment.'
            }}
          </p>

        </div>


        <div
          v-if="selectedExamen.notes"
          class="result-section"
        >

          <span>Notes</span>

          <p>
            {{ selectedExamen.notes }}
          </p>

        </div>

      </div>

    </div>


    <!-- =====================================================
         DELETE MODAL
    ====================================================== -->

    <div
      v-if="examenToDelete"
      class="modal-overlay"
      @click.self="examenToDelete = null"
    >

      <div class="delete-modal">

        <div class="delete-icon">
          <i class="fi fi-rr-trash"></i>
        </div>

        <h2>Supprimer l'examen ?</h2>

        <p>
          Voulez-vous vraiment supprimer l'examen

          <strong>
            {{ examenToDelete.type }}
          </strong>

          du patient

          <strong>
            {{ examenToDelete.patient }}
          </strong>
          ?
        </p>


        <span>
          Cette action est irréversible.
        </span>


        <div class="delete-actions">

          <button
            class="btn-cancel"
            @click="examenToDelete = null"
          >
            Annuler
          </button>


          <button
            class="btn-delete"
            @click="deleteExamen"
          >
            Supprimer
          </button>

        </div>

      </div>

    </div>


    <!-- =====================================================
         NOTIFICATION
    ====================================================== -->

    <transition name="notification">

      <div
        v-if="notification.show"
        class="notification"
        :class="notification.type"
      >

        <i
          :class="
            notification.type === 'success'
              ? 'fi fi-rr-check-circle'
              : 'fi fi-rr-exclamation'
          "
        ></i>

        {{ notification.message }}

      </div>

    </transition>

  </div>
</template>


<script setup>
import {
  ref,
  computed,
  watch
} from 'vue'


/*
|--------------------------------------------------------------------------
| SCRUM-749
|--------------------------------------------------------------------------
|
| Frontend Examens finalisé.
|
| IMPORTANT :
| Les données ci-dessous servent uniquement à finaliser
| l'interface Frontend.
|
| Dans SCRUM-750 :
|
| import api from '../api'
|
| GET    /examens
| POST   /examens
| PUT    /examens/{id}
| DELETE /examens/{id}
|
| seront ajoutés lorsque le Backend Examens sera disponible.
|
*/


/* =========================================================
   TYPES
========================================================= */

const examTypes = [
  'Analyse sanguine',
  'Radiographie',
  'Échographie',
  'IRM',
  'Scanner',
  'ECG',
  'Analyse urinaire',
  'Autre'
]


/* =========================================================
   DATA DEMO FRONTEND
========================================================= */

const examens = ref([
  {
    id: 1,
    patient: 'Sara Benali',
    patientReference: 'PAT-001',
    type: 'Analyse sanguine',
    medecin: 'Dr. Ahmed',
    date: '2026-09-18',
    priorite: 'Normale',
    statut: 'Terminé',
    resultat: 'Résultats disponibles.',
    notes: ''
  },

  {
    id: 2,
    patient: 'Yassine Amrani',
    patientReference: 'PAT-002',
    type: 'Radiographie',
    medecin: 'Dr. Karim',
    date: '2026-09-19',
    priorite: 'Urgente',
    statut: 'En attente',
    resultat: '',
    notes: ''
  },

  {
    id: 3,
    patient: 'Nadia El Mansouri',
    patientReference: 'PAT-003',
    type: 'Échographie',
    medecin: 'Dr. Salma',
    date: '2026-09-19',
    priorite: 'Normale',
    statut: 'En cours',
    resultat: '',
    notes: ''
  }
])


/* =========================================================
   FILTERS
========================================================= */

const search = ref('')
const statusFilter = ref('')
const typeFilter = ref('')


/* =========================================================
   PAGINATION
========================================================= */

const currentPage = ref(1)

const perPage = 6


/* =========================================================
   MODALS
========================================================= */

const formModalOpen = ref(false)

const editingExamen = ref(null)

const selectedExamen = ref(null)

const examenToDelete = ref(null)


/* =========================================================
   FORM
========================================================= */

const emptyForm = () => ({
  patient: '',
  patientReference: '',
  type: '',
  medecin: '',
  date: '',
  priorite: 'Normale',
  statut: 'En attente',
  resultat: '',
  notes: ''
})


const form = ref(emptyForm())


/* =========================================================
   NOTIFICATION
========================================================= */

const notification = ref({
  show: false,
  type: 'success',
  message: ''
})


let notificationTimer = null


const showNotification = (
  message,
  type = 'success'
) => {

  clearTimeout(notificationTimer)

  notification.value = {
    show: true,
    type,
    message
  }


  notificationTimer = setTimeout(() => {

    notification.value.show = false

  }, 3000)
}


/* =========================================================
   FILTERED DATA
========================================================= */

const filteredExamens = computed(() => {

  const value =
    search.value
      .trim()
      .toLowerCase()


  return examens.value.filter(examen => {

    const searchable = [
      examen.patient,
      examen.patientReference,
      examen.type,
      examen.medecin
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()


    const matchesSearch =
      !value ||
      searchable.includes(value)


    const matchesStatus =
      !statusFilter.value ||
      examen.statut === statusFilter.value


    const matchesType =
      !typeFilter.value ||
      examen.type === typeFilter.value


    return (
      matchesSearch &&
      matchesStatus &&
      matchesType
    )

  })

})


/* =========================================================
   PAGINATION
========================================================= */

const totalPages = computed(() => {

  return Math.max(
    1,
    Math.ceil(
      filteredExamens.value.length /
      perPage
    )
  )

})


const paginatedExamens = computed(() => {

  const start =
    (currentPage.value - 1) *
    perPage


  return filteredExamens.value.slice(
    start,
    start + perPage
  )

})


const firstItem = computed(() => {

  if (!filteredExamens.value.length) {
    return 0
  }


  return (
    (currentPage.value - 1) *
      perPage +
    1
  )

})


const lastItem = computed(() => {

  return Math.min(
    currentPage.value * perPage,
    filteredExamens.value.length
  )

})


watch(
  [
    search,
    statusFilter,
    typeFilter
  ],
  () => {

    currentPage.value = 1

  }
)


/* =========================================================
   RESET FILTERS
========================================================= */

const resetFilters = () => {

  search.value = ''

  statusFilter.value = ''

  typeFilter.value = ''

  currentPage.value = 1
}


/* =========================================================
   CREATE
========================================================= */

const openCreateModal = () => {

  editingExamen.value = null

  form.value = emptyForm()

  formModalOpen.value = true
}


/* =========================================================
   EDIT
========================================================= */

const openEditModal = examen => {

  editingExamen.value = examen

  form.value = {
    ...examen
  }

  formModalOpen.value = true
}


/* =========================================================
   CLOSE FORM
========================================================= */

const closeFormModal = () => {

  formModalOpen.value = false

  editingExamen.value = null

  form.value = emptyForm()
}


/* =========================================================
   SAVE
========================================================= */

const saveExamen = () => {

  /*
   * SCRUM-749 :
   * fonctionnement local uniquement.
   *
   * SCRUM-750 :
   * cette fonction sera remplacée
   * par api.post() / api.put().
   */


  if (editingExamen.value) {

    const index =
      examens.value.findIndex(
        examen =>
          examen.id ===
          editingExamen.value.id
      )


    if (index !== -1) {

      examens.value[index] = {
        ...examens.value[index],
        ...form.value
      }

    }


    showNotification(
      'Examen modifié avec succès.'
    )

  } else {

    const newId =
      examens.value.length
        ? Math.max(
            ...examens.value.map(
              examen => examen.id
            )
          ) + 1
        : 1


    examens.value.unshift({
      id: newId,
      ...form.value
    })


    showNotification(
      'Examen ajouté avec succès.'
    )

  }


  closeFormModal()

  currentPage.value = 1
}


/* =========================================================
   VIEW
========================================================= */

const openViewModal = examen => {

  selectedExamen.value = examen
}


/* =========================================================
   DELETE
========================================================= */

const openDeleteModal = examen => {

  examenToDelete.value = examen
}


const deleteExamen = () => {

  /*
   * SCRUM-750 :
   * sera remplacé par :
   *
   * await api.delete(`/examens/${id}`)
   */


  examens.value =
    examens.value.filter(
      examen =>
        examen.id !==
        examenToDelete.value.id
    )


  examenToDelete.value = null


  if (
    currentPage.value >
    totalPages.value
  ) {

    currentPage.value =
      totalPages.value

  }


  showNotification(
    'Examen supprimé avec succès.'
  )
}


/* =========================================================
   HELPERS
========================================================= */

const getInitials = name => {

  if (!name) {
    return '--'
  }


  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map(word => word[0])
    .join('')
    .toUpperCase()
}


const formatDate = date => {

  if (!date) {
    return '—'
  }


  const parsedDate =
    new Date(`${date}T00:00:00`)


  if (
    Number.isNaN(
      parsedDate.getTime()
    )
  ) {

    return date

  }


  return parsedDate
    .toLocaleDateString('fr-FR')
}


const statusClass = status => {

  switch (status) {

    case 'Terminé':
      return 'completed'

    case 'En cours':
      return 'progress'

    default:
      return 'pending'

  }
}


const priorityClass = priority => {

  return priority === 'Urgente'
    ? 'urgent'
    : 'normal'
}
</script>


<style scoped src="../../styles/examens.css"></style>