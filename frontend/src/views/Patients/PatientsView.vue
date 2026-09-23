<template>
  <section class="patients-page">
    <div class="patients-header">
      <div>
        <p class="breadcrumb">Accueil / Patients</p>
        <h1>Patients</h1>
        <p class="subtitle">Gérez les informations et le suivi de vos patients.</p>
      </div>
      <button class="btn-primary" type="button">
        <i class="fi fi-rr-plus"></i>
        Ajouter un patient
      </button>
    </div>

    <div class="quick-actions">
      <article class="quick-card">
        <span class="quick-icon"><i class="fi fi-rr-calendar"></i></span>
        <div><strong>Prendre rendez-vous</strong><small>Planifier une visite</small></div>
      </article>
      <article class="quick-card">
        <span class="quick-icon"><i class="fi fi-rr-stethoscope"></i></span>
        <div><strong>Nouvelle consultation</strong><small>Démarrer une consultation</small></div>
      </article>
      <article class="quick-card">
        <span class="quick-icon"><i class="fi fi-rr-document"></i></span>
        <div><strong>Historique médical</strong><small>Consulter le dossier</small></div>
      </article>
    </div>

    <div class="patients-panel">
      <div class="filters">
        <label class="search">
          <i class="fi fi-rr-search"></i>
            <input
        v-model="recherche"
        type="search"
        placeholder="Rechercher par nom, CIN ou téléphone..."
        />
        </label>
        <select><option>Tous les sexes</option><option>Femme</option><option>Homme</option></select>
        <select><option>Tous les groupes sanguins</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>AB+</option><option>AB-</option><option>O+</option><option>O-</option></select>
        <button class="btn-filter" type="button" @click="rechercherPatients"><i class="fi fi-rr-search"></i> Rechercher</button>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Patient</th><th>CIN</th><th>Date de naissance</th><th>Sexe</th><th>Téléphone</th><th>Groupe sanguin</th><th>Actions</th></tr>
          </thead>
            <tbody>
    <tr v-if="chargement" class="empty-row">
     <td colspan="8">
      <i class="fi fi-rr-users"></i>
      <strong>Chargement...</strong>
      <span>Chargement de la liste des patients.</span>
     </td>
    </tr>

    <tr v-else-if="erreur" class="empty-row">
     <td colspan="8">
      <i class="fi fi-rr-users"></i>
      <strong>Erreur</strong>
      <span>{{ erreur }}</span>
      </td>
     </tr>

    <tr v-else-if="patients.length === 0" class="empty-row">
    <td colspan="8">
      <i class="fi fi-rr-users"></i>
      <strong>Aucun patient</strong>
      <span>Aucun patient n'est enregistré pour le moment.</span>
     </td>
     </tr>

     <template v-else>
      <tr
       v-for="patient in patients"
       :key="patient.id_patient"
      >
        <td>{{ patient.id_patient }}</td>
        <td>{{ patient.nom }} {{ patient.prenom }}</td>
        <td>{{ patient.cin }}</td>
        <td>{{ patient.date_naissance }}</td>
        <td>{{ patient.sexe }}</td>
        <td>{{ patient.telephone }}</td>
        <td>{{ patient.groupe_sanguin || '-' }}</td>
        <td>-</td>
        </tr>
        </template>
         </tbody>
        </table>
      </div>
    </div>
  </section>
</template>

 <script setup>
import { ref, onMounted } from 'vue'
import api, { messageErreur } from '../../api.js'

const patients = ref([])
const chargement = ref(false)
const erreur = ref('')
const recherche = ref('')

const chargerPatients = async () => {
  chargement.value = true
  erreur.value = ''

  try {
    const response = await api.get('/patients')
    patients.value = response.data?.patients?.data ?? []
  } catch (error) {
    erreur.value = messageErreur(
      error,
      'Impossible de charger les patients.'
    )
  } finally {
    chargement.value = false
  }
}

const rechercherPatients = async () => {
  if (!recherche.value.trim()) {
    await chargerPatients()
    return
  }

  chargement.value = true
  erreur.value = ''

  try {
    const response = await api.get('/patients/search', {
      params: {
        cin: recherche.value.trim()
      }
    })

    patients.value = response.data?.patients?.data ?? []
  } catch (error) {
    erreur.value = messageErreur(
      error,
      'Impossible d’effectuer la recherche.'
    )
  } finally {
    chargement.value = false
  }
}

onMounted(chargerPatients)
</script>

<style scoped src="../../styles/patients.css"></style>
