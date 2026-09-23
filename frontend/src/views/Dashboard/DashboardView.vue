<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api.js'
import { getUser } from '../../auth.js'
import { peutAction, peutAuMoinsUneAction } from '../../actions.js'

import dashboardBanner from '../../assets/images/banner.png'

const ACTIONS_RAPIDES = [
  'tableauBord.nouveauRendezVous',
  'tableauBord.nouveauPatient',
  'tableauBord.consultationRapide',
  'tableauBord.demandeExamen'
]

const router = useRouter()

const loading = ref(true)
const errorMessage = ref('')
const role = ref('')
const userName = ref('')

const stats = ref({
  patients: 0,
  rendezVous: 0,
  consultations: 0,
  chiffreAffaires: '0 DH'
})

const rendezVous = ref([])
const examensEnAttente = ref(0)
const rawData = ref(null)

/* =========================================================
   USER
========================================================= */

const utilisateur = getUser()

userName.value =
  utilisateur?.name ||
  utilisateur?.nom ||
  'Utilisateur'

const formattedRole = computed(() => {
  const value = role.value || utilisateur?.role || 'utilisateur'

  const roles = {
    administrateur: 'Administrateur',
    medecin: 'Médecin',
    secretaire: 'Secrétaire',
    infirmier: 'Infirmier',
    patient: 'Patient'
  }

  return roles[value] || value
})

/* =========================================================
   DATE
========================================================= */

const currentDate = computed(() => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  }).format(new Date())
})

/* =========================================================
   DASHBOARD API
========================================================= */

const fetchDashboard = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/dashboard')

    role.value =
      response.data?.role ||
      utilisateur?.role ||
      ''

    const data = response.data?.data || {}

    rawData.value = data

    /* =========================
       MEDECIN
    ========================= */

    if (role.value === 'medecin' && data.stats) {
      stats.value = {
        patients: data.stats.patients ?? 0,
        rendezVous: data.stats.rendezVous ?? 0,
        consultations: data.stats.consultations ?? 0,
        chiffreAffaires:
          data.stats.chiffreAffaires ?? '0 DH'
      }

      rendezVous.value = data.rendezVous || []

      examensEnAttente.value =
        data.examensEnAttente ?? 0
    }

    /* =========================
       ADMINISTRATEUR
    ========================= */

    else if (role.value === 'administrateur') {
      stats.value = {
        patients:
          data.total_patients ?? 0,

        rendezVous:
          data.rendez_vous_aujourdhui ?? 0,

        consultations:
          data.total_medecins ?? 0,

        chiffreAffaires:
          `${data.revenu_total ?? 0} DH`
      }

      rendezVous.value =
        data.rendezVous ||
        data.rendez_vous ||
        []
    }

    /* =========================
       AUTRES ROLES
    ========================= */

    else {
      stats.value = {
        patients:
          data.total_patients ??
          data.patients ??
          0,

        rendezVous:
          data.rendez_vous_aujourdhui ??
          data.rendezVous ??
          0,

        consultations:
          data.consultations ??
          0,

        chiffreAffaires:
          data.chiffreAffaires ??
          '0 DH'
      }

      rendezVous.value =
        data.rendezVous ||
        data.rendez_vous ||
        []

      examensEnAttente.value =
        data.examensEnAttente ?? 0
    }
  } catch (error) {
    console.error(
      'Erreur dashboard :',
      error
    )

    if (error.response?.status !== 401) {
      errorMessage.value =
        "Impossible de charger les données du tableau de bord."
    }
  } finally {
    loading.value = false
  }
}

/* =========================================================
   NAVIGATION
========================================================= */

const goTo = (path) => {
  router.push(path)
}

/* =========================================================
   INITIALS
========================================================= */

const patientInitials = (name = '') => {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((word) => word[0])
    .join('')
    .toUpperCase()
}

/* =========================================================
   MOUNT
========================================================= */

onMounted(fetchDashboard)
</script>


<template>
  <div class="dashboard-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="dashboard-page-header">

      <div class="dashboard-heading">

        <div class="dashboard-title-icon">
          <i class="fi fi-rr-apps"></i>
        </div>

        <div>

          <div class="dashboard-breadcrumb">
            Accueil
            <i class="fi fi-rr-angle-small-right"></i>
            Tableau de bord
          </div>

          <h1>Tableau de bord</h1>

          <p>
            Vue d'ensemble de l'activité de votre clinique.
          </p>

        </div>

      </div>


      <div class="dashboard-header-info">

        <!-- ROLE -->

        <div class="dashboard-role-badge">

          <i class="fi fi-rr-user"></i>

          <div>
            <small>Vue utilisateur</small>
            <strong>{{ formattedRole }}</strong>
          </div>

        </div>


        <!-- DATE -->

        <div class="dashboard-date">

          <i class="fi fi-rr-calendar"></i>

          <span>
            {{ currentDate }}
          </span>

        </div>

      </div>

    </header>


    <!-- =====================================================
         LOADING
    ====================================================== -->

    <div
      v-if="loading"
      class="dashboard-state"
    >

      <div class="dashboard-loader"></div>

      <div>
        <strong>Chargement...</strong>
        <p>
          Préparation de votre tableau de bord
        </p>
      </div>

    </div>


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <div
      v-else-if="errorMessage"
      class="dashboard-error"
    >

      <i class="fi fi-rr-exclamation"></i>

      <div>
        <strong>Une erreur est survenue</strong>
        <p>{{ errorMessage }}</p>
      </div>

      <button
        type="button"
        @click="fetchDashboard"
      >
        Réessayer
      </button>

    </div>


    <template v-else>

      <!-- ===================================================
           AI WELCOME BANNER
      ==================================================== -->

      <section
        class="welcome-banner"
        :style="{
          backgroundImage: `url(${dashboardBanner})`
        }"
      >

        <div class="welcome-overlay"></div>

        <div class="welcome-content">

          <span class="welcome-badge">
            <i class="fi fi-rr-sparkles"></i>
            VITALIS Intelligence
          </span>

          <h2>
            Bonjour {{ userName }} 👋
          </h2>

          <p>
            Une gestion médicale intelligente, centralisée et efficace.
          </p>


          <!-- TECHNOLOGIES -->

          <div class="welcome-technologies">

            <span>
              <i class="fi fi-rr-code-simple"></i>
              Patients
            </span>

            <span>
              <i class="fi fi-rr-browser"></i>
              Rendez-vous
            </span>

            <span>
              <i class="fi fi-rr-database"></i>
              Consultations
            </span>

            <span>
              <i class="fi fi-rr-box"></i>
              Hospitalisations
            </span>

            <span>
              <i class="fi fi-rr-brain"></i>
            VITALIS AI
            </span>

          </div>

        </div>

      </section>


      <!-- ===================================================
           STATISTIQUES
      ==================================================== -->

      <section class="dashboard-stats">

        <!-- PATIENTS -->

        <article class="dashboard-stat-card">

          <div class="stat-top">

            <div class="stat-icon patients-icon">
              <i class="fi fi-rr-users-medical"></i>
            </div>

            <i class="fi fi-rr-arrow-trend-up stat-indicator"></i>

          </div>

          <div class="stat-content">

            <span class="stat-label">
              Patients aujourd'hui
            </span>

            <strong class="stat-number">
              {{ stats.patients }}
            </strong>

            <small>
              Patients pris en charge
            </small>

          </div>

        </article>


        <!-- RENDEZ-VOUS -->

        <article class="dashboard-stat-card">

          <div class="stat-top">

            <div class="stat-icon appointment-icon">
              <i class="fi fi-rr-calendar"></i>
            </div>

            <i class="fi fi-rr-arrow-trend-up stat-indicator"></i>

          </div>

          <div class="stat-content">

            <span class="stat-label">
              Rendez-vous
            </span>

            <strong class="stat-number">
              {{ stats.rendezVous }}
            </strong>

            <small>
              Rendez-vous aujourd'hui
            </small>

          </div>

        </article>


        <!-- CONSULTATIONS -->

        <article class="dashboard-stat-card">

          <div class="stat-top">

            <div class="stat-icon consultation-icon">
              <i class="fi fi-rr-stethoscope"></i>
            </div>

            <i class="fi fi-rr-arrow-trend-up stat-indicator"></i>

          </div>

          <div class="stat-content">

            <span class="stat-label">
              Consultations
            </span>

            <strong class="stat-number">
              {{ stats.consultations }}
            </strong>

            <small>
              Activité médicale
            </small>

          </div>

        </article>


        <!-- CA -->

        <article class="dashboard-stat-card">

          <div class="stat-top">

            <div class="stat-icon revenue-icon">
              <i class="fi fi-rr-coins"></i>
            </div>

            <i class="fi fi-rr-arrow-trend-up stat-indicator"></i>

          </div>

          <div class="stat-content">

            <span class="stat-label">
              Chiffre d'affaires
            </span>

            <strong class="stat-number stat-money">
              {{ stats.chiffreAffaires }}
            </strong>

            <small>
              Revenus enregistrés
            </small>

          </div>

        </article>

      </section>


      <!-- ===================================================
           MAIN GRID
      ==================================================== -->

      <section class="dashboard-main-grid">

        <!-- =================================================
             RENDEZ-VOUS
        ================================================== -->

        <article class="dashboard-card">

          <div class="dashboard-card-header">

            <div class="card-heading">

              <div class="small-card-icon">
                <i class="fi fi-rr-calendar"></i>
              </div>

              <div>
                <h3>Rendez-vous du jour</h3>

                <p>
                  Planning médical de la journée
                </p>
              </div>

            </div>


            <button
              class="link-button"
              type="button"
              @click="goTo('/rendez-vous')"
            >
              Voir tout

              <i class="fi fi-rr-arrow-small-right"></i>
            </button>

          </div>


          <!-- TABLE -->

          <div
            v-if="rendezVous.length"
            class="appointments-table-wrapper"
          >

            <table class="appointments-table">

              <thead>

                <tr>
                  <th>HEURE</th>
                  <th>PATIENT</th>
                  <th>MOTIF</th>
                  <th>STATUT</th>
                </tr>

              </thead>


              <tbody>

                <tr
                  v-for="(rdv, index) in rendezVous"
                  :key="rdv.id || `${rdv.heure}-${index}`"
                >

                  <td>

                    <div class="appointment-time">

                      <i class="fi fi-rr-clock"></i>

                      {{ rdv.heure || '--:--' }}

                    </div>

                  </td>


                  <td>

                    <div class="patient-cell">

                      <div class="patient-avatar">
                        {{ patientInitials(rdv.patient) }}
                      </div>

                      <strong>
                        {{ rdv.patient || 'Patient' }}
                      </strong>

                    </div>

                  </td>


                  <td class="appointment-reason">

                    {{ rdv.motif || 'Consultation' }}

                  </td>


                  <td>

                    <span
                      class="appointment-status"
                      :class="
                        rdv.statut === 'Confirmé'
                          ? 'status-confirmed'
                          : 'status-progress'
                      "
                    >

                      <span class="status-dot"></span>

                      {{ rdv.statut || 'En attente' }}

                    </span>

                  </td>

                </tr>

              </tbody>

            </table>

          </div>


          <!-- EMPTY -->

          <div
            v-else
            class="dashboard-empty"
          >

            <div class="empty-icon">
              <i class="fi fi-rr-calendar"></i>
            </div>

            <h4>
              Aucun rendez-vous
            </h4>

            <p>
              Aucun rendez-vous prévu pour le moment.
            </p>

            <button
              v-if="peutAction('tableauBord.nouveauRendezVous')"
              type="button"
              @click="goTo('/rendez-vous')"
            >

              <i class="fi fi-rr-plus-small"></i>

              Nouveau rendez-vous

            </button>

          </div>

        </article>


        <!-- =================================================
             ACTIVITE RAPIDE
        ================================================== -->

        <article
          v-if="peutAuMoinsUneAction(ACTIONS_RAPIDES)"
          class="dashboard-card quick-actions-card"
        >

          <div class="dashboard-card-header">

            <div class="card-heading">

              <div class="small-card-icon">
                <i class="fi fi-rr-bolt"></i>
              </div>

              <div>
                <h3>Activité rapide</h3>

                <p>
                  Accès rapide aux fonctionnalités
                </p>
              </div>

            </div>

          </div>


          <div class="quick-actions-list">

            <!-- RENDEZ-VOUS -->

            <button
              v-if="peutAction('tableauBord.nouveauRendezVous')"
              class="quick-action"
              type="button"
              @click="goTo('/rendez-vous')"
            >

              <span class="quick-action-icon">
                <i class="fi fi-rr-calendar-plus"></i>
              </span>

              <span>
                <strong>Nouveau rendez-vous</strong>
                <small>Planifier une consultation</small>
              </span>

              <i class="fi fi-rr-angle-small-right action-arrow"></i>

            </button>


            <!-- PATIENT -->

            <button
              v-if="peutAction('tableauBord.nouveauPatient')"
              class="quick-action"
              type="button"
              @click="goTo('/patients')"
            >

              <span class="quick-action-icon">
                <i class="fi fi-rr-user-add"></i>
              </span>

              <span>
                <strong>Nouveau patient</strong>
                <small>Créer un dossier patient</small>
              </span>

              <i class="fi fi-rr-angle-small-right action-arrow"></i>

            </button>


            <!-- CONSULTATION -->

            <button
              v-if="peutAction('tableauBord.consultationRapide')"
              class="quick-action"
              type="button"
              @click="goTo('/consultations')"
            >

              <span class="quick-action-icon">
                <i class="fi fi-rr-stethoscope"></i>
              </span>

              <span>
                <strong>Consultation rapide</strong>
                <small>Créer une consultation</small>
              </span>

              <i class="fi fi-rr-angle-small-right action-arrow"></i>

            </button>


            <!-- EXAMEN -->

            <button
              v-if="peutAction('tableauBord.demandeExamen')"
              class="quick-action"
              type="button"
              @click="goTo('/examens')"
            >

              <span class="quick-action-icon">
                <i class="fi fi-rr-document"></i>
              </span>

              <span>
                <strong>Demande d'examen</strong>
                <small>Prescrire un nouvel examen</small>
              </span>

              <i class="fi fi-rr-angle-small-right action-arrow"></i>

            </button>

          </div>


          <!-- ALERT EXAMENS -->

          <div
            v-if="examensEnAttente > 0"
            class="exam-alert"
          >

            <div class="exam-alert-icon">
              <i class="fi fi-rr-triangle-warning"></i>
            </div>

            <div>

              <strong>
                {{ examensEnAttente }}
                résultat(s) en attente
              </strong>

              <span>
                Des examens nécessitent votre attention.
              </span>

            </div>

          </div>

        </article>

      </section>


      <!-- ===================================================
           BOTTOM GRID
      ==================================================== -->

      <section class="dashboard-bottom-grid">

        <!-- ACTIVITE -->

        <article class="dashboard-card">

          <div class="dashboard-card-header">

            <div class="card-heading">

              <div class="small-card-icon">
                <i class="fi fi-rr-chart-histogram"></i>
              </div>

              <div>
                <h3>Activité de la clinique</h3>

                <p>
                  Vue synthétique de la semaine
                </p>
              </div>

            </div>


            <select class="period-select">
              <option>Cette semaine</option>
              <option>Ce mois</option>
            </select>

          </div>


          <div class="activity-placeholder">

            <div class="fake-chart">

              <span style="height: 42%"></span>
              <span style="height: 67%"></span>
              <span style="height: 53%"></span>
              <span style="height: 82%"></span>
              <span style="height: 61%"></span>
              <span style="height: 74%"></span>
              <span style="height: 47%"></span>

            </div>


            <div class="chart-days">

              <span>Lun</span>
              <span>Mar</span>
              <span>Mer</span>
              <span>Jeu</span>
              <span>Ven</span>
              <span>Sam</span>
              <span>Dim</span>

            </div>

          </div>

        </article>


        <!-- SYSTEME -->

        <article class="dashboard-card">

          <div class="dashboard-card-header">

            <div class="card-heading">

              <div class="small-card-icon">
                <i class="fi fi-rr-shield-check"></i>
              </div>

              <div>
                <h3>État du système</h3>

                <p>
                  Services VITALIS
                </p>
              </div>

            </div>

          </div>


          <div class="system-list">

            <div class="system-item">

              <span>
                <i class="fi fi-rr-server"></i>
                API Laravel
              </span>

              <strong class="system-ok">
                <span></span>
                Opérationnel
              </strong>

            </div>


            <div class="system-item">

              <span>
                <i class="fi fi-rr-database"></i>
                PostgreSQL
              </span>

              <strong class="system-ok">
                <span></span>
                Connecté
              </strong>

            </div>


            <div class="system-item">

              <span>
                <i class="fi fi-rr-box"></i>
                Docker
              </span>

              <strong class="system-ok">
                <span></span>
                Actif
              </strong>

            </div>


            <div class="system-item">

              <span>
                <i class="fi fi-rr-brain"></i>
                Module IA
              </span>

              <strong class="system-ok">
                <span></span>
                Disponible
              </strong>

            </div>

          </div>

        </article>

      </section>

    </template>

  </div>
</template>


<style src="../../styles/dashboard.css"></style>