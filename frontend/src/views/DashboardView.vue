<script setup>
import { ref } from 'vue'

const stats = ref({
  patients: 32,
  rendezVous: 18,
  consultations: 15,
  chiffreAffaires: '12 450 DH'
})

const rendezVous = ref([
  {
    heure: '09:00',
    patient: 'Fatima Zahra',
    motif: 'Consultation générale',
    statut: 'Confirmé'
  },
  {
    heure: '10:30',
    patient: 'Mohamed Ali',
    motif: 'Suivi',
    statut: 'Confirmé'
  },
  {
    heure: '11:00',
    patient: 'Sara El Amrani',
    motif: 'Consultation générale',
    statut: 'En cours'
  },
  {
    heure: '14:00',
    patient: 'Youssef El Khatib',
    motif: 'Consultation générale',
    statut: 'Confirmé'
  }
])
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

      <div class="logout">
        Déconnexion
      </div>
    </aside>

    <!-- CONTENU -->
    <main class="dashboard-content">

      <header class="dashboard-header">
        <div>
          <h1>Tableau de bord</h1>
          <p>Bonjour Dr. Ahmed Benali 👋</p>
        </div>

        <div class="profile">
          <div>
            <strong>Dr. Ahmed Benali</strong>
            <small>Médecin</small>
          </div>
        </div>
      </header>

      <!-- STATISTIQUES -->
      <section class="stats-grid">

        <div class="stat-card">
          <span>Patients aujourd'hui</span>
          <h2>{{ stats.patients }}</h2>
          <small>+5% vs hier</small>
        </div>

        <div class="stat-card">
          <span>Rendez-vous</span>
          <h2>{{ stats.rendezVous }}</h2>
          <small>+3% vs hier</small>
        </div>

        <div class="stat-card">
          <span>Consultations</span>
          <h2>{{ stats.consultations }}</h2>
          <small>+8% vs hier</small>
        </div>

        <div class="stat-card">
          <span>Chiffre d'affaires</span>
          <h2>{{ stats.chiffreAffaires }}</h2>
          <small>+12%</small>
        </div>

      </section>

      <!-- PARTIE CENTRALE -->
      <section class="dashboard-grid">

        <div class="panel">
          <div class="panel-title">
            <h3>Rendez-vous du jour</h3>
            <a href="#">Voir tous les rendez-vous</a>
          </div>

          <table>
            <tbody>
              <tr v-for="rdv in rendezVous" :key="rdv.heure">
                <td>{{ rdv.heure }}</td>
                <td><strong>{{ rdv.patient }}</strong></td>
                <td>{{ rdv.motif }}</td>
                <td>
                  <span
                    class="status"
                    :class="rdv.statut === 'Confirmé'
                      ? 'confirmed'
                      : 'progress'"
                  >
                    {{ rdv.statut }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ACTIONS -->
        <div class="panel">
          <h3>Activité rapide</h3>

          <button>＋ Nouveau rendez-vous</button>
          <button>＋ Nouveau patient</button>
          <button>＋ Consultation rapide</button>
          <button>＋ Demande d'examen</button>

          <div class="alert">
            ⚠ 3 résultats d'examens en attente
          </div>
        </div>

      </section>

    </main>
  </div>
</template>

<style src="../styles/dashboard.css"></style>
