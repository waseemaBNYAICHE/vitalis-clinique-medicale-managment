<template>
  <div class="hospitalisations-page">

    <!-- ===================== HEADER ===================== -->
    <div class="page-header">
      <div>
        <div class="title-section">
          <div class="title-icon">
            <i class="fi fi-rr-hospital"></i>
          </div>

          <div>
            <h1>Hospitalisations</h1>
            <p>Gérez les hospitalisations, les chambres et les sorties des patients</p>
          </div>
        </div>
      </div>

      <button class="btn-primary" @click="openCreateModal">
        <i class="fi fi-rr-plus"></i>
        Nouvelle hospitalisation
      </button>
    </div>


    <!-- ===================== QUICK ACTIONS ===================== -->
    <div class="quick-actions">

      <button class="quick-card" @click="openCreateModal">
        <div class="quick-icon blue">
          <i class="fi fi-rr-hospital"></i>
        </div>

        <div>
          <strong>Nouvelle hospitalisation</strong>
          <span>Hospitaliser un patient</span>
        </div>

        <i class="fi fi-rr-angle-small-right arrow"></i>
      </button>


      <button class="quick-card" @click="statusFilter = 'En cours'">
        <div class="quick-icon orange">
          <i class="fi fi-rr-bed"></i>
        </div>

        <div>
          <strong>Patients hospitalisés</strong>
          <span>Consulter les hospitalisations en cours</span>
        </div>

        <i class="fi fi-rr-angle-small-right arrow"></i>
      </button>


      <button class="quick-card" @click="statusFilter = 'Sortie'">
        <div class="quick-icon green">
          <i class="fi fi-rr-exit"></i>
        </div>

        <div>
          <strong>Sorties patients</strong>
          <span>Consulter les patients sortis</span>
        </div>

        <i class="fi fi-rr-angle-small-right arrow"></i>
      </button>

    </div>


    <!-- ===================== MAIN CARD ===================== -->
    <div class="hospitalisations-card">

      <div class="search-section">

        <div class="search-box">
          <i class="fi fi-rr-search"></i>

          <input
            v-model="search"
            type="text"
            placeholder="Rechercher par patient, médecin, chambre..."
          />
        </div>


        <div class="filters">

          <select v-model="statusFilter">
            <option value="">Tous les statuts</option>
            <option value="En cours">En cours</option>
            <option value="Sortie prévue">Sortie prévue</option>
            <option value="Sortie">Sortie</option>
          </select>


          <select v-model="roomFilter">
            <option value="">Toutes les chambres</option>

            <option
              v-for="room in rooms"
              :key="room"
              :value="room"
            >
              Chambre {{ room }}
            </option>
          </select>


          <button class="btn-search">
            <i class="fi fi-rr-search"></i>
            Rechercher
          </button>


          <button class="btn-reset" @click="resetFilters">
            <i class="fi fi-rr-refresh"></i>
            Réinitialiser
          </button>

        </div>
      </div>


      <!-- ===================== TABLE ===================== -->
      <div class="table-responsive">

        <table class="hospitalisations-table">

          <thead>
            <tr>
              <th>#</th>
              <th>Patient</th>
              <th>Chambre</th>
              <th>Médecin</th>
              <th>Date d'entrée</th>
              <th>Sortie prévue</th>
              <th>Motif</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>


          <tbody>

            <tr
              v-for="(hospitalisation, index) in paginatedHospitalisations"
              :key="hospitalisation.id"
            >

              <td>
                {{ (currentPage - 1) * perPage + index + 1 }}
              </td>


              <td>
                <div class="patient-info">

                  <div class="avatar">
                    {{ getInitials(hospitalisation.patient) }}
                  </div>

                  <div>
                    <strong>{{ hospitalisation.patient }}</strong>
                    <small>{{ hospitalisation.patientReference }}</small>
                  </div>

                </div>
              </td>


              <td>
                <div class="room-info">
                  <i class="fi fi-rr-bed"></i>

                  <div>
                    <strong>Chambre {{ hospitalisation.chambre }}</strong>
                    <small v-if="hospitalisation.lit">
                      Lit {{ hospitalisation.lit }}
                    </small>
                  </div>
                </div>
              </td>


              <td>{{ hospitalisation.medecin }}</td>


              <td>{{ formatDate(hospitalisation.dateEntree) }}</td>


              <td>
                {{
                  hospitalisation.dateSortiePrevue
                    ? formatDate(hospitalisation.dateSortiePrevue)
                    : '—'
                }}
              </td>


              <td>
                <span class="reason">
                  {{ hospitalisation.motif }}
                </span>
              </td>


              <td>
                <span
                  class="status-badge"
                  :class="statusClass(hospitalisation.statut)"
                >
                  <span class="status-dot"></span>
                  {{ hospitalisation.statut }}
                </span>
              </td>


              <td>
                <div class="actions">

                  <button
                    class="action-btn view"
                    title="Consulter"
                    @click="openViewModal(hospitalisation)"
                  >
                    <i class="fi fi-rr-eye"></i>
                  </button>


                  <button
                    class="action-btn edit"
                    title="Modifier"
                    @click="openEditModal(hospitalisation)"
                  >
                    <i class="fi fi-rr-pencil"></i>
                  </button>


                  <button
                    v-if="hospitalisation.statut !== 'Sortie'"
                    class="action-btn exit"
                    title="Sortie patient"
                    @click="openExitModal(hospitalisation)"
                  >
                    <i class="fi fi-rr-exit"></i>
                  </button>


                  <button
                    class="action-btn delete"
                    title="Supprimer"
                    @click="openDeleteModal(hospitalisation)"
                  >
                    <i class="fi fi-rr-trash"></i>
                  </button>

                </div>
              </td>

            </tr>


            <tr v-if="filteredHospitalisations.length === 0">
              <td colspan="9">

                <div class="empty-state">

                  <div class="empty-icon">
                    <i class="fi fi-rr-hospital"></i>
                  </div>

                  <h3>Aucune hospitalisation trouvée</h3>

                  <p>
                    Aucune hospitalisation ne correspond aux critères sélectionnés.
                  </p>

                  <button class="btn-primary" @click="openCreateModal">
                    <i class="fi fi-rr-plus"></i>
                    Nouvelle hospitalisation
                  </button>

                </div>

              </td>
            </tr>

          </tbody>
        </table>
      </div>


      <!-- ===================== PAGINATION ===================== -->
      <div
        v-if="filteredHospitalisations.length"
        class="table-footer"
      >

        <span>
          Affichage de {{ firstItem }} à {{ lastItem }}
          sur {{ filteredHospitalisations.length }} hospitalisations
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


    <!-- ===================== BACKEND INFO ===================== -->
    <div class="backend-info">
      <div class="backend-icon">
        <i class="fi fi-rr-info"></i>
      </div>

      <div>
        <strong>Interface Frontend prête</strong>

        <p>
          Le formulaire d'admission est préparé pour les données du Backend Laravel.
          La liaison avec les API sera réalisée dans le ticket de connexion Frontend/Backend.
        </p>
      </div>
    </div>


    <!-- ======================================================
         CREATE / EDIT MODAL
    ======================================================= -->
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
                editingHospitalisation
                  ? 'Modifier l’hospitalisation'
                  : 'Nouvelle hospitalisation'
              }}
            </h2>

            <p>
              {{
                editingHospitalisation
                  ? 'Modifiez les informations de l’hospitalisation'
                  : 'Renseignez les informations du patient à hospitaliser'
              }}
            </p>
          </div>


          <button class="modal-close" @click="closeFormModal">
            ×
          </button>

        </div>


        <form @submit.prevent="saveHospitalisation">

          <div class="form-grid">

            <!-- PATIENT -->
            <div class="form-group">
              <label>Patient *</label>

              <select v-model="form.id_patient" required>
                <option value="">Sélectionner un patient</option>

                <option
                  v-for="patient in patients"
                  :key="patient.id_patient"
                  :value="patient.id_patient"
                >
                  {{ patient.nom }} {{ patient.prenom }}
                </option>
              </select>
            </div>


            <!-- CHAMBRE -->
            <div class="form-group">
              <label>Chambre *</label>

              <select v-model="form.id_chambre" required>
                <option value="">Sélectionner une chambre</option>

                <option
                  v-for="chambre in chambres"
                  :key="chambre.id_chambre"
                  :value="chambre.id_chambre"
                >
                  Chambre {{ chambre.numero_chambre }}
                  - {{ chambre.type_chambre }}
                </option>
              </select>
            </div>


            <!-- MEDECIN -->
            <div class="form-group">
              <label>Médecin responsable *</label>

              <select v-model="form.id_medecin" required>
                <option value="">Sélectionner un médecin</option>

                <option
                  v-for="medecin in medecins"
                  :key="medecin.id_medecin"
                  :value="medecin.id_medecin"
                >
                  Dr. {{ medecin.nom }} {{ medecin.prenom }}
                </option>
              </select>
            </div>


            <!-- DATE ENTREE -->
            <div class="form-group">
              <label>Date d'entrée *</label>

              <input
                v-model="form.date_entree"
                type="date"
                required
              />
            </div>


            <!-- HEURE ENTREE -->
            <div class="form-group">
              <label>Heure d'entrée *</label>

              <input
                v-model="form.heure_entree"
                type="time"
                required
              />
            </div>


            <!-- MOTIF -->
            <div class="form-group full">
              <label>Motif d'hospitalisation *</label>

              <input
                v-model="form.motif_hospitalisation"
                type="text"
                placeholder="Motif de l'hospitalisation"
                required
              />
            </div>


            <!-- DIAGNOSTIC -->
            <div class="form-group full">
              <label>Diagnostic d'entrée *</label>

              <textarea
                v-model="form.diagnostic_entree"
                rows="3"
                placeholder="Diagnostic établi lors de l'admission..."
                required
              ></textarea>
            </div>


            <!-- OBSERVATIONS -->
            <div class="form-group full">
              <label>Observations médicales</label>

              <textarea
                v-model="form.observations"
                rows="4"
                placeholder="Observations complémentaires..."
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


            <button type="submit" class="btn-save">
              <i class="fi fi-rr-check"></i>

              {{
                editingHospitalisation
                  ? 'Enregistrer les modifications'
                  : 'Hospitaliser le patient'
              }}
            </button>

          </div>

        </form>

      </div>
    </div>


    <!-- ======================================================
         VIEW MODAL
    ======================================================= -->
    <div
      v-if="selectedHospitalisation"
      class="modal-overlay"
      @click.self="selectedHospitalisation = null"
    >

      <div class="modal view-modal">

        <div class="modal-header">

          <div>
            <h2>Détails de l'hospitalisation</h2>
            <p>Informations du patient hospitalisé</p>
          </div>

          <button
            class="modal-close"
            @click="selectedHospitalisation = null"
          >
            ×
          </button>

        </div>


        <div class="patient-profile">

          <div class="large-avatar">
            {{ getInitials(selectedHospitalisation.patient) }}
          </div>

          <div>
            <h3>{{ selectedHospitalisation.patient }}</h3>
            <span>{{ selectedHospitalisation.patientReference }}</span>
          </div>

        </div>


        <div class="details-grid">

          <div class="detail-item">
            <span>Chambre</span>
            <strong>
              Chambre {{ selectedHospitalisation.chambre }}
              <template v-if="selectedHospitalisation.lit">
                — Lit {{ selectedHospitalisation.lit }}
              </template>
            </strong>
          </div>


          <div class="detail-item">
            <span>Médecin</span>
            <strong>{{ selectedHospitalisation.medecin }}</strong>
          </div>


          <div class="detail-item">
            <span>Date d'entrée</span>
            <strong>
              {{ formatDate(selectedHospitalisation.dateEntree) }}
            </strong>
          </div>


          <div class="detail-item">
            <span>Sortie prévue</span>
            <strong>
              {{
                selectedHospitalisation.dateSortiePrevue
                  ? formatDate(selectedHospitalisation.dateSortiePrevue)
                  : 'Non définie'
              }}
            </strong>
          </div>


          <div class="detail-item">
            <span>Statut</span>
            <strong>{{ selectedHospitalisation.statut }}</strong>
          </div>


          <div class="detail-item">
            <span>Motif</span>
            <strong>{{ selectedHospitalisation.motif }}</strong>
          </div>

        </div>


        <div class="observations-section">
          <span>Observations médicales</span>

          <p>
            {{
              selectedHospitalisation.observations ||
              'Aucune observation enregistrée.'
            }}
          </p>
        </div>

      </div>
    </div>


    <!-- ======================================================
         SORTIE PATIENT
    ======================================================= -->
    <div
      v-if="hospitalisationToExit"
      class="modal-overlay"
      @click.self="hospitalisationToExit = null"
    >

      <div class="exit-modal">

        <div class="exit-icon">
          <i class="fi fi-rr-exit"></i>
        </div>

        <h2>Sortie du patient</h2>

        <p>
          Confirmer la sortie de
          <strong>{{ hospitalisationToExit.patient }}</strong>
          de la chambre
          <strong>{{ hospitalisationToExit.chambre }}</strong> ?
        </p>


        <div class="exit-form">

          <label>Date de sortie</label>

          <input
            v-model="exitDate"
            type="date"
          />

        </div>


        <div class="modal-actions">

          <button
            class="btn-cancel"
            @click="hospitalisationToExit = null"
          >
            Annuler
          </button>


          <button
            class="btn-exit"
            @click="confirmExit"
          >
            <i class="fi fi-rr-check"></i>
            Confirmer la sortie
          </button>

        </div>

      </div>
    </div>


    <!-- ======================================================
         DELETE MODAL
    ======================================================= -->
    <div
      v-if="hospitalisationToDelete"
      class="modal-overlay"
      @click.self="hospitalisationToDelete = null"
    >

      <div class="delete-modal">

        <div class="delete-icon">
          <i class="fi fi-rr-trash"></i>
        </div>

        <h2>Supprimer l'hospitalisation ?</h2>

        <p>
          Voulez-vous vraiment supprimer l'hospitalisation de
          <strong>{{ hospitalisationToDelete.patient }}</strong> ?
        </p>

        <span>Cette action est irréversible.</span>


        <div class="modal-actions">

          <button
            class="btn-cancel"
            @click="hospitalisationToDelete = null"
          >
            Annuler
          </button>


          <button
            class="btn-delete"
            @click="deleteHospitalisation"
          >
            Supprimer
          </button>

        </div>

      </div>
    </div>


    <!-- ===================== NOTIFICATION ===================== -->
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
| SCRUM-758
|--------------------------------------------------------------------------
| Formulaire d'admission d'un patient.
|
| Le formulaire utilise maintenant les champs attendus par
| l'API Laravel d'admission :
| id_patient, id_chambre, id_medecin, date_entree,
| heure_entree, motif_hospitalisation, diagnostic_entree,
| observations.
|
| La connexion aux API sera réalisée dans SCRUM-760.
|--------------------------------------------------------------------------
*/


/* ===================== DEMO REFERENCES ===================== */

/*
 * Données temporaires utilisées uniquement pour permettre
 * la sélection dans le formulaire.
 *
 * Elles seront remplacées par les données des API dans SCRUM-760.
 */

const patients = ref([
  {
    id_patient: 1,
    nom: 'Benali',
    prenom: 'Sara'
  },
  {
    id_patient: 2,
    nom: 'Amrani',
    prenom: 'Yassine'
  },
  {
    id_patient: 3,
    nom: 'El Mansouri',
    prenom: 'Nadia'
  }
])


const chambres = ref([
  {
    id_chambre: 1,
    numero_chambre: '101',
    type_chambre: 'Individuelle'
  },
  {
    id_chambre: 2,
    numero_chambre: '102',
    type_chambre: 'Double'
  },
  {
    id_chambre: 3,
    numero_chambre: '201',
    type_chambre: 'Soins intensifs'
  }
])


const medecins = ref([
  {
    id_medecin: 1,
    nom: 'El Amrani',
    prenom: 'Youssef'
  },
  {
    id_medecin: 2,
    nom: 'Berrada',
    prenom: 'Salma'
  },
  {
    id_medecin: 3,
    nom: 'Alaoui',
    prenom: 'Mehdi'
  }
])


/* ===================== ROOMS ===================== */

const rooms = [
  '101',
  '102',
  '103',
  '104',
  '201',
  '202',
  '203',
  '204'
]


/* ===================== DEMO HOSPITALISATIONS ===================== */

const hospitalisations = ref([
  {
    id: 1,
    patient: 'Sara Benali',
    patientReference: 'PAT-001',
    chambre: '101',
    lit: 'A',
    medecin: 'Dr. Ahmed',
    dateEntree: '2026-09-16',
    dateSortiePrevue: '2026-09-22',
    dateSortie: '',
    motif: 'Surveillance médicale',
    statut: 'En cours',
    observations: 'Patient sous surveillance médicale.'
  },
  {
    id: 2,
    patient: 'Yassine Amrani',
    patientReference: 'PAT-002',
    chambre: '103',
    lit: 'B',
    medecin: 'Dr. Karim',
    dateEntree: '2026-09-17',
    dateSortiePrevue: '2026-09-20',
    dateSortie: '',
    motif: 'Post-opératoire',
    statut: 'Sortie prévue',
    observations: 'Évolution favorable.'
  },
  {
    id: 3,
    patient: 'Nadia El Mansouri',
    patientReference: 'PAT-003',
    chambre: '201',
    lit: 'A',
    medecin: 'Dr. Salma',
    dateEntree: '2026-09-12',
    dateSortiePrevue: '2026-09-18',
    dateSortie: '2026-09-18',
    motif: 'Observation',
    statut: 'Sortie',
    observations: 'Sortie autorisée par le médecin.'
  }
])


/* ===================== FILTERS ===================== */

const search = ref('')
const statusFilter = ref('')
const roomFilter = ref('')


/* ===================== PAGINATION ===================== */

const currentPage = ref(1)
const perPage = 6


/* ===================== MODALS ===================== */

const formModalOpen = ref(false)

const editingHospitalisation = ref(null)

const selectedHospitalisation = ref(null)

const hospitalisationToDelete = ref(null)

const hospitalisationToExit = ref(null)

const exitDate = ref('')


/* ===================== ADMISSION FORM ===================== */

const emptyForm = () => ({
  id_patient: '',
  id_chambre: '',
  id_medecin: '',
  date_entree: '',
  heure_entree: '',
  motif_hospitalisation: '',
  diagnostic_entree: '',
  observations: ''
})

const form = ref(emptyForm())


/* ===================== NOTIFICATION ===================== */

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


/* ===================== FILTERING ===================== */

const filteredHospitalisations = computed(() => {

  const value = search.value
    .trim()
    .toLowerCase()

  return hospitalisations.value.filter(item => {

    const searchable = [
      item.patient,
      item.patientReference,
      item.medecin,
      item.chambre,
      item.motif
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    const matchesSearch =
      !value ||
      searchable.includes(value)

    const matchesStatus =
      !statusFilter.value ||
      item.statut === statusFilter.value

    const matchesRoom =
      !roomFilter.value ||
      item.chambre === roomFilter.value

    return (
      matchesSearch &&
      matchesStatus &&
      matchesRoom
    )
  })
})


/* ===================== PAGINATION ===================== */

const totalPages = computed(() =>
  Math.max(
    1,
    Math.ceil(
      filteredHospitalisations.value.length /
      perPage
    )
  )
)

const paginatedHospitalisations = computed(() => {

  const start =
    (currentPage.value - 1) *
    perPage

  return filteredHospitalisations.value.slice(
    start,
    start + perPage
  )
})

const firstItem = computed(() => {

  if (!filteredHospitalisations.value.length) {
    return 0
  }

  return (
    (currentPage.value - 1) *
      perPage +
    1
  )
})

const lastItem = computed(() =>
  Math.min(
    currentPage.value * perPage,
    filteredHospitalisations.value.length
  )
)

watch(
  [search, statusFilter, roomFilter],
  () => {
    currentPage.value = 1
  }
)


/* ===================== FILTER RESET ===================== */

const resetFilters = () => {
  search.value = ''
  statusFilter.value = ''
  roomFilter.value = ''
  currentPage.value = 1
}


/* ===================== CREATE ===================== */

const openCreateModal = () => {
  editingHospitalisation.value = null
  form.value = emptyForm()
  formModalOpen.value = true
}


/* ===================== EDIT ===================== */

const openEditModal = item => {
  editingHospitalisation.value = item

  /*
   * L'édition complète sera reliée aux vraies données
   * backend dans le ticket de connexion API.
   */
  form.value = {
    id_patient: item.id_patient || '',
    id_chambre: item.id_chambre || '',
    id_medecin: item.id_medecin || '',
    date_entree: item.date_entree || item.dateEntree || '',
    heure_entree: item.heure_entree || '',
    motif_hospitalisation:
      item.motif_hospitalisation || item.motif || '',
    diagnostic_entree: item.diagnostic_entree || '',
    observations: item.observations || ''
  }

  formModalOpen.value = true
}


/* ===================== CLOSE ===================== */

const closeFormModal = () => {
  formModalOpen.value = false
  editingHospitalisation.value = null
  form.value = emptyForm()
}


/* ===================== SAVE ===================== */

const saveHospitalisation = () => {

  /*
   * SCRUM-758 :
   * Le formulaire est maintenant conforme aux champs
   * nécessaires à l'admission.
   *
   * SCRUM-760 :
   * Cette fonction sera remplacée par un appel réel :
   * POST /hospitalisations/admettre
   */

  if (editingHospitalisation.value) {

    showNotification(
      'Le formulaire de modification est prêt pour la connexion API.'
    )

  } else {

    const patient = patients.value.find(
      item =>
        Number(item.id_patient) ===
        Number(form.value.id_patient)
    )

    const chambre = chambres.value.find(
      item =>
        Number(item.id_chambre) ===
        Number(form.value.id_chambre)
    )

    const medecin = medecins.value.find(
      item =>
        Number(item.id_medecin) ===
        Number(form.value.id_medecin)
    )

    const newId =
      hospitalisations.value.length
        ? Math.max(
            ...hospitalisations.value.map(
              item => item.id
            )
          ) + 1
        : 1

    hospitalisations.value.unshift({
      id: newId,

      id_patient: form.value.id_patient,
      id_chambre: form.value.id_chambre,
      id_medecin: form.value.id_medecin,

      patient: patient
        ? `${patient.prenom} ${patient.nom}`
        : 'Patient',

      patientReference:
        `PAT-${String(form.value.id_patient).padStart(3, '0')}`,

      chambre: chambre
        ? chambre.numero_chambre
        : '',

      lit: '',

      medecin: medecin
        ? `Dr. ${medecin.prenom} ${medecin.nom}`
        : '',

      dateEntree: form.value.date_entree,

      dateSortiePrevue: '',

      dateSortie: '',

      motif: form.value.motif_hospitalisation,

      diagnostic_entree:
        form.value.diagnostic_entree,

      statut: 'En cours',

      observations:
        form.value.observations
    })

    showNotification(
      'Formulaire d’admission validé avec succès.'
    )
  }

  closeFormModal()

  currentPage.value = 1
}


/* ===================== VIEW ===================== */

const openViewModal = item => {
  selectedHospitalisation.value = item
}


/* ===================== EXIT ===================== */

const openExitModal = item => {

  hospitalisationToExit.value = item

  exitDate.value =
    new Date()
      .toISOString()
      .slice(0, 10)
}

const confirmExit = () => {

  if (!hospitalisationToExit.value) {
    return
  }

  if (!exitDate.value) {
    showNotification(
      'Veuillez sélectionner la date de sortie.',
      'error'
    )

    return
  }

  const item =
    hospitalisations.value.find(
      hospitalisation =>
        hospitalisation.id ===
        hospitalisationToExit.value.id
    )

  if (item) {
    item.statut = 'Sortie'
    item.dateSortie = exitDate.value
  }

  hospitalisationToExit.value = null

  showNotification(
    'Sortie du patient enregistrée avec succès.'
  )
}


/* ===================== DELETE ===================== */

const openDeleteModal = item => {
  hospitalisationToDelete.value = item
}

const deleteHospitalisation = () => {

  if (!hospitalisationToDelete.value) {
    return
  }

  hospitalisations.value =
    hospitalisations.value.filter(
      item =>
        item.id !==
        hospitalisationToDelete.value.id
    )

  hospitalisationToDelete.value = null

  if (currentPage.value > totalPages.value) {
    currentPage.value = totalPages.value
  }

  showNotification(
    'Hospitalisation supprimée avec succès.'
  )
}


/* ===================== HELPERS ===================== */

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

  if (Number.isNaN(parsedDate.getTime())) {
    return date
  }

  return parsedDate.toLocaleDateString('fr-FR')
}

const statusClass = status => {

  switch (status) {

    case 'Sortie':
      return 'completed'

    case 'Sortie prévue':
      return 'planned'

    default:
      return 'progress'
  }
}
</script>


<style scoped src="../../styles/hospitalisations.css"></style>