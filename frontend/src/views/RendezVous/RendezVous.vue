<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import gsap from 'gsap'
import '../../styles/rendez-vous.css'

const search = ref('')
const selectedDoctor = ref('')
const selectedDate = ref('')
const selectedStatus = ref('')

const currentPage = ref(1)
const perPage = 5

const appointments = ref([
  {
    id: 1,
    patient: 'Karim Benali',
    patientCode: 'P-001',
    patientInitials: 'KB',
    doctor: 'Dr. Samir Alaoui',
    speciality: 'Cardiologie',
    doctorInitials: 'SA',
    date: '10/09/2026',
    dateISO: '2026-09-10',
    heure: '09:00',
    motif: 'Consultation de routine',
    statut: 'Confirmé'
  },
  {
    id: 2,
    patient: 'Amina Zahraoui',
    patientCode: 'P-002',
    patientInitials: 'AZ',
    doctor: 'Dr. Leila Haddad',
    speciality: 'Pédiatrie',
    doctorInitials: 'LH',
    date: '10/09/2026',
    dateISO: '2026-09-10',
    heure: '10:30',
    motif: 'Suivi',
    statut: 'En attente'
  },
  {
    id: 3,
    patient: 'Youssef El Amrani',
    patientCode: 'P-003',
    patientInitials: 'YE',
    doctor: 'Dr. Samir Alaoui',
    speciality: 'Cardiologie',
    doctorInitials: 'SA',
    date: '10/09/2026',
    dateISO: '2026-09-10',
    heure: '11:00',
    motif: 'Douleurs thoraciques',
    statut: 'Confirmé'
  },
  {
    id: 4,
    patient: 'Salma Rachidi',
    patientCode: 'P-004',
    patientInitials: 'SR',
    doctor: 'Dr. Yassine Bennis',
    speciality: 'Dermatologie',
    doctorInitials: 'YB',
    date: '10/09/2026',
    dateISO: '2026-09-10',
    heure: '14:00',
    motif: 'Problème de peau',
    statut: 'En cours'
  },
  {
    id: 5,
    patient: 'Hassan Tazi',
    patientCode: 'P-005',
    patientInitials: 'HT',
    doctor: 'Dr. Leila Haddad',
    speciality: 'Pédiatrie',
    doctorInitials: 'LH',
    date: '11/09/2026',
    dateISO: '2026-09-11',
    heure: '09:00',
    motif: 'Vaccination',
    statut: 'Confirmé'
  },
  {
    id: 6,
    patient: 'Sara Mounir',
    patientCode: 'P-006',
    patientInitials: 'SM',
    doctor: 'Dr. Yassine Bennis',
    speciality: 'Dermatologie',
    doctorInitials: 'YB',
    date: '11/09/2026',
    dateISO: '2026-09-11',
    heure: '11:30',
    motif: 'Contrôle',
    statut: 'Annulé'
  }
])

const doctors = computed(() => [
  ...new Set(appointments.value.map(item => item.doctor))
])

const filteredAppointments = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  return appointments.value.filter(item => {
    const matchSearch =
      !keyword ||
      item.patient.toLowerCase().includes(keyword) ||
      item.doctor.toLowerCase().includes(keyword) ||
      item.motif.toLowerCase().includes(keyword)

    const matchDoctor =
      !selectedDoctor.value ||
      item.doctor === selectedDoctor.value

    const matchDate =
      !selectedDate.value ||
      item.dateISO === selectedDate.value

    const matchStatus =
      !selectedStatus.value ||
      item.statut === selectedStatus.value

    return (
      matchSearch &&
      matchDoctor &&
      matchDate &&
      matchStatus
    )
  })
})

const totalPages = computed(() =>
  Math.max(
    1,
    Math.ceil(filteredAppointments.value.length / perPage)
  )
)

const paginatedAppointments = computed(() => {
  const start = (currentPage.value - 1) * perPage

  return filteredAppointments.value.slice(
    start,
    start + perPage
  )
})

const firstDisplayed = computed(() => {
  if (!filteredAppointments.value.length) return 0

  return (currentPage.value - 1) * perPage + 1
})

const lastDisplayed = computed(() =>
  Math.min(
    currentPage.value * perPage,
    filteredAppointments.value.length
  )
)

function applyFilters() {
  currentPage.value = 1
}

function resetFilters() {
  search.value = ''
  selectedDoctor.value = ''
  selectedDate.value = ''
  selectedStatus.value = ''
  currentPage.value = 1
}

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

function statusClass(status) {
  if (status === 'Confirmé') return 'status-confirmed'
  if (status === 'En attente') return 'status-pending'
  if (status === 'En cours') return 'status-progress'
  if (status === 'Annulé') return 'status-cancelled'

  return ''
}

function createAppointment() {
  console.log('Nouveau rendez-vous')
}

function viewAppointment(item) {
  console.log('Voir', item)
}

function editAppointment(item) {
  console.log('Modifier', item)
}

function deleteAppointment(item) {
  console.log('Supprimer', item)
}

onMounted(async () => {
  await nextTick()

  const tl = gsap.timeline({
    defaults: {
      ease: 'power3.out'
    }
  })

  tl.from('.rv-page-title', {
    y: 18,
    opacity: 0,
    duration: 0.55
  })

    .from(
      '.rv-stat-card',
      {
        y: 20,
        opacity: 0,
        scale: 0.97,
        duration: 0.5,
        stagger: 0.09
      },
      '-=0.25'
    )

    .from(
      '.rv-filter-card',
      {
        y: 18,
        opacity: 0,
        duration: 0.45
      },
      '-=0.25'
    )

    .from(
      '.rv-table-card',
      {
        y: 20,
        opacity: 0,
        duration: 0.5
      },
      '-=0.2'
    )

  document
    .querySelectorAll('.counter-number')
    .forEach(element => {
      const target = Number(element.dataset.target)

      const counter = {
        value: 0
      }

      gsap.to(counter, {
        value: target,
        duration: 1.2,
        ease: 'power2.out',

        onUpdate() {
          element.textContent = Math.round(counter.value)
        }
      })
    })
})
</script>

<template>
  <section class="rendezvous-page">

    <!-- TITRE -->

    <div class="rv-page-title">

      <div>
        <h1>
          Gestion des rendez-vous
        </h1>

        <p>
          Planifiez et suivez tous les rendez-vous
          de la clinique en toute simplicité.
        </p>
      </div>

    </div>

    <!-- STATISTIQUES -->

    <div class="rv-stats">

      <article class="rv-stat-card">

        <div class="stat-icon stat-blue">
          <i class="fi fi-rr-calendar"></i>
        </div>

        <div class="stat-info">

          <strong
            class="counter-number stat-blue-text"
            data-target="12"
          >
            0
          </strong>

          <span>
            Rendez-vous aujourd'hui
          </span>

          <small class="trend positive">
            ↗ +20%
          </small>

        </div>

      </article>


      <article class="rv-stat-card">

        <div class="stat-icon stat-green">
          <i class="fi fi-rr-users"></i>
        </div>

        <div class="stat-info">

          <strong
            class="counter-number stat-green-text"
            data-target="48"
          >
            0
          </strong>

          <span>
            Rendez-vous cette semaine
          </span>

          <small class="trend positive">
            ↗ +12%
          </small>

        </div>

      </article>


      <article class="rv-stat-card">

        <div class="stat-icon stat-purple">
          <i class="fi fi-rr-clock"></i>
        </div>

        <div class="stat-info">

          <strong
            class="counter-number stat-purple-text"
            data-target="156"
          >
            0
          </strong>

          <span>
            Total rendez-vous
          </span>

          <small class="trend positive">
            ↗ +8%
          </small>

        </div>

      </article>


      <article class="rv-stat-card">

        <div class="stat-icon stat-red">
          <i class="fi fi-rr-cross-circle"></i>
        </div>

        <div class="stat-info">

          <strong
            class="counter-number stat-red-text"
            data-target="5"
          >
            0
          </strong>

          <span>
            Rendez-vous annulés
          </span>

          <small class="trend negative">
            ↘ -15%
          </small>

        </div>

      </article>

    </div>

    <!-- FILTRES -->

    <div class="rv-filter-card">

      <div class="rv-filter-group search-group">

        <label>
          Rechercher
        </label>

        <div class="rv-input-with-icon">

          <i class="fi fi-rr-search"></i>

          <input
            v-model="search"
            type="text"
            placeholder="Nom du patient, médecin..."
            @keyup.enter="applyFilters"
          />

        </div>

      </div>


      <div class="rv-filter-group">

        <label>Médecin</label>

        <select v-model="selectedDoctor">

          <option value="">
            Tous les médecins
          </option>

          <option
            v-for="doctor in doctors"
            :key="doctor"
            :value="doctor"
          >
            {{ doctor }}
          </option>

        </select>

      </div>


      <div class="rv-filter-group">

        <label>Date</label>

        <input
          v-model="selectedDate"
          type="date"
        />

      </div>


      <div class="rv-filter-group">

        <label>Statut</label>

        <select v-model="selectedStatus">

          <option value="">
            Tous les statuts
          </option>

          <option value="Confirmé">
            Confirmé
          </option>

          <option value="En attente">
            En attente
          </option>

          <option value="En cours">
            En cours
          </option>

          <option value="Annulé">
            Annulé
          </option>

        </select>

      </div>


      <button
        class="rv-search-button"
        @click="applyFilters"
      >
        <i class="fi fi-rr-search"></i>
        Rechercher
      </button>


      <button
        class="rv-reset-button"
        @click="resetFilters"
      >
        <i class="fi fi-rr-refresh"></i>
      </button>

    </div>

    <!-- LISTE -->

    <div class="rv-table-card">

      <div class="rv-table-heading">

        <div>

          <h2>
            Liste des rendez-vous
          </h2>

          <p>
            Consultez et gérez les rendez-vous programmés
          </p>

        </div>

        <button
          class="btn-new-appointment"
          @click="createAppointment"
        >
          <i class="fi fi-rr-plus"></i>

          Nouveau rendez-vous
        </button>

      </div>


      <div class="rv-table-wrapper">

        <table class="rv-table">

          <thead>

            <tr>
              <th>#</th>
              <th>Patient</th>
              <th>Médecin</th>
              <th>Date et heure</th>
              <th>Motif</th>
              <th>Statut</th>
              <th class="action-column">
                Actions
              </th>
            </tr>

          </thead>

          <tbody>

            <tr
              v-for="appointment in paginatedAppointments"
              :key="appointment.id"
            >

              <td class="appointment-number">
                {{ appointment.id }}
              </td>


              <!-- PATIENT -->

              <td>

                <div class="person-cell">

                  <div class="person-avatar patient-avatar">
                    {{ appointment.patientInitials }}
                  </div>

                  <div class="person-details">

                    <strong>
                      {{ appointment.patient }}
                    </strong>

                    <span>
                      ID: {{ appointment.patientCode }}
                    </span>

                  </div>

                </div>

              </td>


              <!-- MEDECIN -->

              <td>

                <div class="person-cell">

                  <div class="person-avatar doctor-avatar">
                    {{ appointment.doctorInitials }}
                  </div>

                  <div class="person-details">

                    <strong>
                      {{ appointment.doctor }}
                    </strong>

                    <span>
                      {{ appointment.speciality }}
                    </span>

                  </div>

                </div>

              </td>


              <!-- DATE -->

              <td>

                <div class="date-time">

                  <div>
                    <i class="fi fi-rr-calendar"></i>

                    {{ appointment.date }}
                  </div>

                  <span>
                    <i class="fi fi-rr-clock"></i>

                    {{ appointment.heure }}
                  </span>

                </div>

              </td>


              <!-- MOTIF -->

              <td class="motif-cell">
                {{ appointment.motif }}
              </td>


              <!-- STATUS -->

              <td>

                <span
                  class="status-badge"
                  :class="statusClass(appointment.statut)"
                >
                  {{ appointment.statut }}
                </span>

              </td>


              <!-- ACTIONS -->

              <td>

                <div class="rv-actions">

                  <button
                    class="action-button action-view"
                    title="Voir"
                    @click="viewAppointment(appointment)"
                  >
                    <i class="fi fi-rr-eye"></i>
                  </button>


                  <button
                    class="action-button action-edit"
                    title="Modifier"
                    @click="editAppointment(appointment)"
                  >
                    <i class="fi fi-rr-pencil"></i>
                  </button>


                  <button
                    class="action-button action-delete"
                    title="Supprimer"
                    @click="deleteAppointment(appointment)"
                  >
                    <i class="fi fi-rr-trash"></i>
                  </button>

                </div>

              </td>

            </tr>


            <tr v-if="paginatedAppointments.length === 0">

              <td
                colspan="7"
                class="rv-empty"
              >

                Aucun rendez-vous trouvé.

              </td>

            </tr>

          </tbody>

        </table>

      </div>


      <!-- FOOTER -->

      <div class="rv-table-footer">

        <span>

          Affichage de

          <strong>
            {{ firstDisplayed }}
          </strong>

          à

          <strong>
            {{ lastDisplayed }}
          </strong>

          sur

          <strong>
            {{ filteredAppointments.length }}
          </strong>

          rendez-vous

        </span>


        <div class="rv-pagination">

          <button
            class="page-button"
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
          >
            ‹
          </button>


          <button
            v-for="page in totalPages"
            :key="page"
            class="page-button"
            :class="{ active: page === currentPage }"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>


          <button
            class="page-button"
            :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)"
          >
            ›
          </button>

        </div>

      </div>

    </div>

  </section>
</template>