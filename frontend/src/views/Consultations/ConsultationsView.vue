<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import gsap from 'gsap'
import '../../styles/consultations.css'

const search = ref('')
const selectedDoctor = ref('')
const selectedDate = ref('')
const selectedStatus = ref('')

const currentPage = ref(1)
const perPage = 5

const consultations = ref([
  {
    id: 1,
    patient: 'Karim Bennani',
    cin: 'CN456789',
    initials: 'KB',
    medecin: 'Dr. Samir Alaoui',
    specialite: 'Médecine générale',
    date: '10/09/2026',
    dateISO: '2026-09-10',
    heure: '10:30',
    motif: 'Toux persistante',
    diagnostic: 'Infection respiratoire',
    statut: 'Terminée'
  },
  {
    id: 2,
    patient: 'Amina Zahraoui',
    cin: 'CN987654',
    initials: 'AZ',
    medecin: 'Dr. Leila Haddad',
    specialite: 'Pédiatrie',
    date: '09/09/2026',
    dateISO: '2026-09-09',
    heure: '14:00',
    motif: 'Fièvre',
    diagnostic: 'Infection virale',
    statut: 'Terminée'
  },
  {
    id: 3,
    patient: 'Samir Alaoui',
    cin: 'CN112233',
    initials: 'SA',
    medecin: 'Dr. Yassine Bennis',
    specialite: 'Cardiologie',
    date: '08/09/2026',
    dateISO: '2026-09-08',
    heure: '11:15',
    motif: 'Douleurs thoraciques',
    diagnostic: 'Hypertension',
    statut: 'En cours'
  },
  {
    id: 4,
    patient: 'Leila Haddad',
    cin: 'CN445566',
    initials: 'LH',
    medecin: 'Dr. Samir Alaoui',
    specialite: 'Médecine générale',
    date: '07/09/2026',
    dateISO: '2026-09-07',
    heure: '16:00',
    motif: 'Contrôle',
    diagnostic: 'Diabète',
    statut: 'Terminée'
  },
  {
    id: 5,
    patient: 'Youssef El Amrani',
    cin: 'CN778899',
    initials: 'YE',
    medecin: 'Dr. Leila Haddad',
    specialite: 'Pédiatrie',
    date: '06/09/2026',
    dateISO: '2026-09-06',
    heure: '09:45',
    motif: 'Allergie',
    diagnostic: 'Rhinite allergique',
    statut: 'Annulée'
  }
])

const doctors = computed(() => [
  ...new Set(consultations.value.map(item => item.medecin))
])

const filteredConsultations = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  return consultations.value.filter(item => {
    const matchSearch =
      !keyword ||
      item.patient.toLowerCase().includes(keyword) ||
      item.medecin.toLowerCase().includes(keyword) ||
      item.motif.toLowerCase().includes(keyword) ||
      item.diagnostic.toLowerCase().includes(keyword)

    const matchDoctor =
      !selectedDoctor.value ||
      item.medecin === selectedDoctor.value

    const matchDate =
      !selectedDate.value ||
      item.dateISO === selectedDate.value

    const matchStatus =
      !selectedStatus.value ||
      item.statut === selectedStatus.value

    return matchSearch && matchDoctor && matchDate && matchStatus
  })
})

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredConsultations.value.length / perPage))
)

const paginatedConsultations = computed(() => {
  const start = (currentPage.value - 1) * perPage

  return filteredConsultations.value.slice(
    start,
    start + perPage
  )
})

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
  if (status === 'Terminée') return 'status-completed'
  if (status === 'En cours') return 'status-progress'
  if (status === 'Annulée') return 'status-cancelled'

  return ''
}

function createConsultation() {
  console.log('Nouvelle consultation')
}

function viewConsultation(item) {
  console.log('Voir', item)
}

function editConsultation(item) {
  console.log('Modifier', item)
}

function deleteConsultation(item) {
  console.log('Supprimer', item)
}

function createOrdonnance(item) {
  console.log('Nouvelle ordonnance', item)
}

function printOrdonnance(item) {
  console.log('Imprimer ordonnance', item)
}

function downloadOrdonnance(item) {
  console.log('Télécharger ordonnance', item)
}

/* =========================================================
   GSAP
========================================================= */

onMounted(async () => {
  await nextTick()

  const tl = gsap.timeline({
    defaults: {
      ease: 'power3.out'
    }
  })

  tl.from('.consultations-heading', {
    y: 18,
    opacity: 0,
    duration: 0.55
  })

    .from(
      '.consultation-stat-card',
      {
        y: 22,
        opacity: 0,
        scale: 0.97,
        stagger: 0.08,
        duration: 0.5
      },
      '-=0.25'
    )

    .from(
      '.consultation-filter-card',
      {
        y: 18,
        opacity: 0,
        duration: 0.45
      },
      '-=0.25'
    )

    .from(
      '.consultation-table-card',
      {
        y: 20,
        opacity: 0,
        duration: 0.5
      },
      '-=0.2'
    )
})
</script>

<template>
  <section class="consultations-page">

    <!-- HEADER -->

    <div class="consultations-heading">

      <div>

        <h1>
          Gestion des consultations
        </h1>

        <p class="consultation-subtitle">
          Consultez et gérez les consultations des patients
        </p>
      </div>

      <button
        class="new-consultation-btn"
        @click="createConsultation"
      >
        <i class="fi fi-rr-plus"></i>
        Nouvelle consultation
      </button>

    </div>

    <!-- STATS -->

    <div class="consultation-stats">

      <article class="consultation-stat-card">

        <div class="consultation-stat-icon blue">
          <i class="fi fi-rr-stethoscope"></i>
        </div>

        <div>
          <strong>256</strong>
          <span>Consultations</span>
          <small>↗ +12%</small>
        </div>

      </article>


      <article class="consultation-stat-card">

        <div class="consultation-stat-icon green">
          <i class="fi fi-rr-users"></i>
        </div>

        <div>
          <strong>198</strong>
          <span>Patients consultés</span>
          <small>↗ +8%</small>
        </div>

      </article>


      <article class="consultation-stat-card">

        <div class="consultation-stat-icon purple">
          <i class="fi fi-rr-user-md"></i>
        </div>

        <div>
          <strong>12</strong>
          <span>Médecins</span>
          <small>↗ +5%</small>
        </div>

      </article>


      <article class="consultation-stat-card">

        <div class="consultation-stat-icon orange">
          <i class="fi fi-rr-calendar"></i>
        </div>

        <div>
          <strong>45</strong>
          <span>Ce mois-ci</span>
          <small>↗ +18%</small>
        </div>

      </article>

    </div>

    <!-- FILTERS -->

    <div class="consultation-filter-card">

      <div class="consultation-search">

        <i class="fi fi-rr-search"></i>

        <input
          v-model="search"
          type="text"
          placeholder="Rechercher par patient, médecin ou diagnostic..."
        />

      </div>


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


      <input
        v-model="selectedDate"
        type="date"
      />


      <select v-model="selectedStatus">

        <option value="">
          Tous les statuts
        </option>

        <option value="Terminée">
          Terminée
        </option>

        <option value="En cours">
          En cours
        </option>

        <option value="Annulée">
          Annulée
        </option>

      </select>


      <button
        class="filter-icon-btn"
        title="Filtres"
      >
        <i class="fi fi-rr-filter"></i>
      </button>


      <button
        class="reset-consultation-btn"
        @click="resetFilters"
      >
        <i class="fi fi-rr-refresh"></i>
        Réinitialiser
      </button>

    </div>

    <!-- TABLE -->

    <div class="consultation-table-card">

      <div class="consultation-table-wrapper">

        <table class="consultation-table">

          <thead>
            <tr>
              <th>#</th>
              <th>Patient</th>
              <th>Médecin</th>
              <th>Date et heure</th>
              <th>Motif de consultation</th>
              <th>Diagnostic</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="item in paginatedConsultations"
              :key="item.id"
            >

              <td class="consultation-number">
                {{ item.id }}
              </td>


              <!-- PATIENT -->

              <td>

                <div class="consultation-person">

                  <div class="consultation-avatar">
                    {{ item.initials }}
                  </div>

                  <div>

                    <strong>
                      {{ item.patient }}
                    </strong>

                    <span>
                      {{ item.cin }}
                    </span>

                  </div>

                </div>

              </td>


              <!-- MEDECIN -->

              <td>

                <div class="consultation-doctor">

                  <strong>
                    {{ item.medecin }}
                  </strong>

                  <span>
                    {{ item.specialite }}
                  </span>

                </div>

              </td>


              <!-- DATE -->

              <td>

                <div class="consultation-date">

                  <strong>
                    {{ item.date }}
                  </strong>

                  <span>
                    {{ item.heure }}
                  </span>

                </div>

              </td>


              <!-- MOTIF -->

              <td>
                {{ item.motif }}
              </td>


              <!-- DIAGNOSTIC -->

              <td>
                {{ item.diagnostic }}
              </td>


              <!-- STATUS -->

              <td>

                <span
                  class="consultation-status"
                  :class="statusClass(item.statut)"
                >
                  {{ item.statut }}
                </span>

              </td>


              <!-- ACTIONS -->

              <td>

                <div class="consultation-actions">

                  <button
                    title="Voir"
                    class="consultation-action-btn"
                    @click="viewConsultation(item)"
                  >
                    <i class="fi fi-rr-eye"></i>
                  </button>


                  <button
                    title="Modifier"
                    class="consultation-action-btn"
                    @click="editConsultation(item)"
                  >
                    <i class="fi fi-rr-pencil"></i>
                  </button>


                  <button
                    title="Supprimer"
                    class="consultation-action-btn delete"
                    @click="deleteConsultation(item)"
                  >
                    <i class="fi fi-rr-trash"></i>
                  </button>


                  <button
                    title="Créer ordonnance"
                    class="consultation-action-btn"
                    @click="createOrdonnance(item)"
                  >
                    <i class="fi fi-rr-document"></i>
                  </button>


                  <button
                    title="Imprimer ordonnance"
                    class="consultation-action-btn"
                    @click="printOrdonnance(item)"
                  >
                    <i class="fi fi-rr-print"></i>
                  </button>


                  <button
                    title="Télécharger ordonnance"
                    class="consultation-action-btn"
                    @click="downloadOrdonnance(item)"
                  >
                    <i class="fi fi-rr-download"></i>
                  </button>

                </div>

              </td>

            </tr>


            <tr v-if="paginatedConsultations.length === 0">

              <td
                colspan="8"
                class="consultation-empty"
              >
                Aucune consultation trouvée.
              </td>

            </tr>

          </tbody>

        </table>

      </div>


      <!-- PAGINATION -->

      <div class="consultation-table-footer">

        <span>
          Affichage de 1 à
          {{ paginatedConsultations.length }}
          sur
          {{ filteredConsultations.length }}
          résultats
        </span>


        <div class="consultation-pagination">

          <button
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
          >
            ‹
          </button>


          <button
            v-for="page in totalPages"
            :key="page"
            :class="{ active: currentPage === page }"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>


          <button
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