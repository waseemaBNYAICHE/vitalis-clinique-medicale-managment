<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

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

// Données brutes pour les rôles autres que médecin (admin, secretaire, infirmier, patient)
const rawData = ref(null)

const fetchDashboard = async () => {
  loading.value = true
  errorMessage.value = ''

  // SCRUM-13 : l'acces a cette page est garanti par la garde du routeur,
  // il n'y a plus de verification d'authentification dupliquee ici.
  const storedUser = localStorage.getItem('user') || sessionStorage.getItem('user')

  if (storedUser) {
    try {
      const parsedUser = JSON.parse(storedUser)
      userName.value = `${parsedUser.name || ''}`.trim()
    } catch {
      userName.value = ''
    }
  }

  try {
    // L'en-tete Authorization est ajoute par l'intercepteur d'api.js.
    const response = await api.get('/dashboard')

    role.value = response.data.role
    const data = response.data.data
    rawData.value = data

    if (role.value === 'medecin' && data.stats) {
      // Le backend renvoie déjà le format attendu par l'UI pour les médecins
      stats.value = data.stats
      rendezVous.value = data.rendezVous || []
      examensEnAttente.value = data.examensEnAttente ?? 0
    } else if (role.value === 'administrateur') {
      // Adapter les clés admin (total_patients, etc.) au format des cartes
      stats.value = {
        patients: data.total_patients ?? 0,
        rendezVous: data.rendez_vous_aujourdhui ?? 0,
        consultations: data.total_medecins ?? 0,
        chiffreAffaires: `${data.revenu_total ?? 0} DH`
      }
      rendezVous.value = []
    } else {
      // Autres rôles (secretaire, infirmier, patient) : affichage minimal
      rendezVous.value = []
    }
  } catch (error) {
    // SCRUM-14 : le cas 401 (session expirée ou invalide) est traité une seule
    // fois, dans l'intercepteur de réponse d'api.js, qui nettoie la session et
    // redirige vers la connexion. Ici on ne gère que les autres erreurs.
    if (error.response?.status !== 401) {
      errorMessage.value = "Impossible de charger les données du tableau de bord."
    }
  } finally {
    loading.value = false
  }
}

const logout = async () => {
  const token = localStorage.getItem('token')
  try {
    if (token) {
      await api.post('/logout', {}, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }
  } catch {
    // Même si l'appel échoue, on déconnecte localement
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  }
}

onMounted(fetchDashboard)
</script>

<template>
  <div class="dashboard">

    <!-- MENU GAUCHE -->
    <aside class="sidebar">
      <div class="logo">
        <span>♥</span>
        <div>
          <h2>VITALIS</h2>
          <small>Clinique Médicale</small>
        </div>
      </div>

      <nav>
        <a class="active">▣ Tableau de bord</a>
        <a>♙ Patients</a>
        <a>▦ Rendez-vous</a>
        <a>▤ Consultations</a>
        <a>▧ Examens</a>
        <a>▥ Hospitalisations</a>
        <a>▤ Facturation</a>
        <a>♙ Utilisateurs</a>
        <a>⚙ Paramètres</a>
      </nav>

      <div class="logout" @click="logout" style="cursor: pointer;">
        Déconnexion
      </div>
    </aside>

    <!-- CONTENU -->
    <main class="dashboard-content">

      <header class="dashboard-header">
        <div>
          <h1>Tableau de bord</h1>
          <p v-if="userName">Bonjour {{ userName }} 👋</p>
        </div>

        <div class="profile">
          <div>
            <strong>{{ userName || 'Utilisateur' }}</strong>
            <small>{{ role }}</small>
          </div>
        </div>
      </header>

      <div v-if="loading">Chargement du tableau de bord...</div>
      <div v-else-if="errorMessage" class="alert">{{ errorMessage }}</div>

      <template v-else>
        <!-- STATISTIQUES -->
        <section class="stats-grid">

          <div class="stat-card">
            <span>Patients aujourd'hui</span>
            <h2>{{ stats.patients }}</h2>
          </div>

          <div class="stat-card">
            <span>Rendez-vous</span>
            <h2>{{ stats.rendezVous }}</h2>
          </div>

          <div class="stat-card">
            <span>Consultations</span>
            <h2>{{ stats.consultations }}</h2>
          </div>

          <div class="stat-card">
            <span>Chiffre d'affaires</span>
            <h2>{{ stats.chiffreAffaires }}</h2>
          </div>

        </section>

        <!-- PARTIE CENTRALE -->
        <section class="dashboard-grid">

          <div class="panel">
            <div class="panel-title">
              <h3>Rendez-vous du jour</h3>
              <a href="#">Voir tous les rendez-vous</a>
            </div>

            <table v-if="rendezVous.length">
              <tbody>
                <tr v-for="rdv in rendezVous" :key="rdv.heure + rdv.patient">
                  <td>{{ rdv.heure }}</td>
                  <td><strong>{{ rdv.patient }}</strong></td>
                  <td>{{ rdv.motif }}</td>
                  <td>
                    <span
                      class="status"
                      :class="rdv.statut === 'Confirmé' ? 'confirmed' : 'progress'"
                    >
                      {{ rdv.statut }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-else>Aucun rendez-vous pour le moment.</p>
          </div>

          <!-- ACTIONS -->
          <div class="panel">
            <h3>Activité rapide</h3>

            <button>＋ Nouveau rendez-vous</button>
            <button>＋ Nouveau patient</button>
            <button>＋ Consultation rapide</button>
            <button>＋ Demande d'examen</button>

            <div class="alert" v-if="examensEnAttente > 0">
              ⚠ {{ examensEnAttente }} résultats d'examens en attente
            </div>
          </div>

        </section>
      </template>

    </main>
  </div>
</template>

<style src="../styles/dashboard.css"></style>