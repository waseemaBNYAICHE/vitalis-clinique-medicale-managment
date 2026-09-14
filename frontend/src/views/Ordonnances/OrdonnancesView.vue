<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import gsap from 'gsap'
import '../../styles/ordonnances.css'

const search = ref('')
const selectedDoctor = ref('')
const selectedDate = ref('')
const selectedStatus = ref('')

const currentPage = ref(1)
const perPage = 5

const ordonnances = ref([
  {
    id: 1,
    patient: 'Karim Bennani',
    cin: 'CN456789',
    initials: 'KB',
    medecin: 'Dr. Samir Alaoui',
    specialite: 'Médecine générale',
    date: '10/09/2026',
    dateISO: '2026-09-10',
    diagnostic: 'Infection respiratoire',
    medicaments: ['Paracétamol', 'Amoxicilline', 'Vitamine C', 'Sirop'],
    statut: 'Active'
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
    diagnostic: 'Fièvre',
    medicaments: ['Doliprane', 'Vitamine C', 'Sirop'],
    statut: 'Active'
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
    diagnostic: 'Hypertension',
    medicaments: ['Amlodipine', 'Aspégic', 'Ramipril'],
    statut: 'Active'
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
    diagnostic: 'Diabète',
    medicaments: ['Metformine', 'Glimépiride'],
    statut: 'Active'
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
    diagnostic: 'Allergie',
    medicaments: ['Cétirizine', 'Corticoïde'],
    statut: 'À renouveler'
  }
])

const doctors = computed(() => [
  ...new Set(ordonnances.value.map(item => item.medecin))
])

const filteredOrdonnances = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  return ordonnances.value.filter(item => {
    const matchSearch =
      !keyword ||
      item.patient.toLowerCase().includes(keyword) ||
      item.medecin.toLowerCase().includes(keyword) ||
      item.diagnostic.toLowerCase().includes(keyword) ||
      item.medicaments.some(m =>
        m.toLowerCase().includes(keyword)
      )

    const matchDoctor =
      !selectedDoctor.value ||
      item.medecin === selectedDoctor.value

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
    Math.ceil(filteredOrdonnances.value.length / perPage)
  )
)

const paginatedOrdonnances = computed(() => {
  const start = (currentPage.value - 1) * perPage

  return filteredOrdonnances.value.slice(
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
  if (status === 'Active') {
    return 'status-active'
  }

  if (status === 'À renouveler') {
    return 'status-renew'
  }

  return ''
}

/* =========================================================
   ACTIONS
========================================================= */

function createOrdonnance() {
  console.log('Nouvelle ordonnance')
}

function viewOrdonnance(item) {
  console.log('Voir ordonnance', item)
}

function editOrdonnance(item) {
  console.log('Modifier ordonnance', item)
}

function deleteOrdonnance(item) {
  console.log('Supprimer ordonnance', item)
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

  tl.from('.ord-title', {
    y: 18,
    opacity: 0,
    duration: 0.55
  })

    .from(
      '.ord-stat-card',
      {
        y: 22,
        opacity: 0,
        scale: 0.97,
        duration: 0.5,
        stagger: 0.08
      },
      '-=0.25'
    )

    .from(
      '.ord-filter-card',
      {
        y: 18,
        opacity: 0,
        duration: 0.45
      },
      '-=0.25'
    )

    .from(
      '.ord-table-card',
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
  <section class="ordonnances-page">

    <!-- HEADER -->

    <div class="ord-title">

      <div>
        <h1>
          Gestion des ordonnances
        </h1>

        <p class="subtitle">
          Consultez et gérez les ordonnances des patients
        </p>
      </div>

      <button
        class="new-ordonnance-btn"
        @click="createOrdonnance"
      >
        <i class="fi fi-rr-plus"></i>

        Nouvelle ordonnance
      </button>

    </div>

    <!-- STATS -->

    <div class="ord-stats">

      <article class="ord-stat-card">

        <div class="stat-icon blue">
          <i class="fi fi-rr-document"></i>
        </div>

        <div>
          <strong>128</strong>
          <span>Ordonnances</span>
          <small>↗ +12%</small>
        </div>

      </article>


      <article class="ord-stat-card">

        <div class="stat-icon green">
          <i class="fi fi-rr-users"></i>
        </div>

        <div>
          <strong>98</strong>
          <span>Patients concernés</span>
          <small>↗ +8%</small>
        </div>

      </article>


      <article class="ord-stat-card">

        <div class="stat-icon purple">
          <i class="fi fi-rr-capsules"></i>
        </div>

        <div>
          <strong>245</strong>
          <span>Médicaments prescrits</span>
          <small>↗ +15%</small>
        </div>

      </article>


      <article class="ord-stat-card">

        <div class="stat-icon orange">
          <i class="fi fi-rr-calendar"></i>
        </div>

        <div>
          <strong>32</strong>
          <span>Ce mois-ci</span>
          <small>↗ +20%</small>
        </div>

      </article>

    </div>

    <!-- FILTRES -->

    <div class="ord-filter-card">

      <div class="search-input">

        <i class="fi fi-rr-search"></i>

        <input
          v-model="search"
          type="text"
          placeholder="Rechercher par patient, médecin ou médicament..."
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

        <option value="Active">
          Active
        </option>

        <option value="À renouveler">
          À renouveler
        </option>

      </select>


      <button
        class="reset-button"
        @click="resetFilters"
      >
        <i class="fi fi-rr-refresh"></i>

        Réinitialiser
      </button>

    </div>

    <!-- TABLEAU -->

    <div class="ord-table-card">

      <div class="table-wrapper">

        <table class="ord-table">

          <thead>
            <tr>
              <th>#</th>
              <th>Patient</th>
              <th>Médecin</th>
              <th>Date</th>
              <th>Diagnostic</th>
              <th>Médicaments</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>


          <tbody>

            <tr
              v-for="item in paginatedOrdonnances"
              :key="item.id"
            >

              <td>
                {{ item.id }}
              </td>


              <!-- PATIENT -->

              <td>

                <div class="person-cell">

                  <div class="avatar">
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

                <div class="doctor-cell">

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
                {{ item.date }}
              </td>


              <!-- DIAGNOSTIC -->

              <td>
                {{ item.diagnostic }}
              </td>


              <!-- MEDICAMENTS -->

              <td>

                <div class="medicines">

                  <span>
                    {{
                      item.medicaments
                        .slice(0, 2)
                        .join(', ')
                    }}
                  </span>

                  <small
                    v-if="item.medicaments.length > 2"
                  >
                    +{{ item.medicaments.length - 2 }}
                  </small>

                </div>

              </td>


              <!-- STATUT -->

              <td>

                <span
                  class="status-badge"
                  :class="statusClass(item.statut)"
                >
                  {{ item.statut }}
                </span>

              </td>


              <!-- ACTIONS -->

              <td>

                <div class="actions">

                  <button
                    class="action-btn"
                    title="Voir"
                    @click="viewOrdonnance(item)"
                  >
                    <i class="fi fi-rr-eye"></i>
                  </button>


                  <button
                    class="action-btn download"
                    title="Télécharger"
                    @click="downloadOrdonnance(item)"
                  >
                    <i class="fi fi-rr-download"></i>
                  </button>


                  <button
                    class="action-btn"
                    title="Modifier"
                    @click="editOrdonnance(item)"
                  >
                    <i class="fi fi-rr-pencil"></i>
                  </button>


                  <button
                    class="action-btn delete"
                    title="Supprimer"
                    @click="deleteOrdonnance(item)"
                  >
                    <i class="fi fi-rr-trash"></i>
                  </button>

                </div>

              </td>

            </tr>


            <tr v-if="paginatedOrdonnances.length === 0">

              <td
                colspan="8"
                class="ord-empty"
              >
                Aucune ordonnance trouvée.
              </td>

            </tr>

          </tbody>

        </table>

      </div>

      <!-- PAGINATION -->

      <div class="ord-table-footer">

        <span>
          Affichage de 1 à
          {{ paginatedOrdonnances.length }}
          sur
          {{ filteredOrdonnances.length }}
          résultats
        </span>


        <div class="pagination">

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