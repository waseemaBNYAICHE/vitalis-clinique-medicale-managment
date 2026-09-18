<script setup>
import { computed, onMounted, ref } from 'vue'
import api, { messageErreur } from '../../api'

/**
 * SCRUM-614 - Un seul composant pour les 3 usages de la fiche rendez-vous.
 *
 * mode: 'view' (lecture seule), 'create' (formulaire vide) ou 'edit'
 * (formulaire pre-rempli). Evite de dupliquer la structure du formulaire
 * dans 3 fichiers separes pour des champs identiques.
 */
const props = defineProps({
  mode: {
    type: String,
    required: true,
    validator: (value) => ['view', 'create', 'edit'].includes(value)
  },
  rendezVous: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const patients = ref([])
const medecins = ref([])
const loadingListes = ref(false)

const loading = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const form = ref({
  id_patient: props.rendezVous?.id_patient ?? '',
  id_medecin: props.rendezVous?.id_medecin ?? '',
  date_rendez_vous: props.rendezVous?.date_rendez_vous ?? '',
  heure_debut: (props.rendezVous?.heure_debut ?? '').slice(0, 5),
  heure_fin: (props.rendezVous?.heure_fin ?? '').slice(0, 5),
  motif: props.rendezVous?.motif ?? '',
  statut: props.rendezVous?.statut ?? 'En attente'
})

const titre = computed(() => {
  if (props.mode === 'create') return 'Nouveau rendez-vous'
  if (props.mode === 'edit') return 'Modifier le rendez-vous'
  return 'Détails du rendez-vous'
})

const estLectureSeule = computed(() => props.mode === 'view')

/**
 * SCRUM-614 - GET /patients est paginee (10 par page) et n'accepte pas de
 * parametre de taille. Cette liste peut donc etre incomplete au-dela de 10
 * patients : limitation connue, a lever cote backend (per_page ou endpoint
 * de recherche) dans un ticket ulterieur.
 */
async function chargerListes() {
  loadingListes.value = true

  try {
    const [reponsePatients, reponseMedecins] = await Promise.all([
      api.get('/patients'),
      api.get('/medecins')
    ])

    patients.value = reponsePatients.data?.patients?.data ?? []
    medecins.value = reponseMedecins.data?.medecins ?? []
  } catch (error) {
    errorMessage.value = messageErreur(
      error,
      'Impossible de charger les patients et médecins.'
    )
  } finally {
    loadingListes.value = false
  }
}

async function soumettre() {
  loading.value = true
  errorMessage.value = ''
  validationErrors.value = {}

  const payload = {
    id_patient: Number(form.value.id_patient),
    id_medecin: Number(form.value.id_medecin),
    date_rendez_vous: form.value.date_rendez_vous,
    heure_debut: form.value.heure_debut,
    heure_fin: form.value.heure_fin,
    motif: form.value.motif,
    statut: form.value.statut
  }

  try {
    if (props.mode === 'create') {
      await api.post('/rendez-vous', payload)
    } else {
      await api.put(`/rendez-vous/${props.rendezVous.id_rendez_vous}`, payload)
    }

    emit('saved')
  } catch (error) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data?.errors || {}
      errorMessage.value = 'Veuillez vérifier les informations saisies.'
    } else if (error.response?.status === 409) {
      errorMessage.value = error.response.data?.message
        || 'Conflit de rendez-vous.'
    } else {
      errorMessage.value = messageErreur(
        error,
        "Une erreur est survenue lors de l'enregistrement."
      )
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (props.mode !== 'view') {
    chargerListes()
  }
})
</script>

<template>
  <div class="rv-modal-overlay" @click.self="emit('close')">
    <div class="rv-modal">
      <div class="rv-modal-header">
        <h2>{{ titre }}</h2>
        <button class="rv-modal-close" @click="emit('close')">×</button>
      </div>

      <!-- MODE LECTURE -->
      <div v-if="estLectureSeule" class="rv-modal-body">
        <div class="rv-detail-row">
          <span>Patient</span>
          <strong>
            {{ rendezVous.patient?.nom }} {{ rendezVous.patient?.prenom }}
          </strong>
        </div>
        <div class="rv-detail-row">
          <span>Médecin</span>
          <strong>
            Dr. {{ rendezVous.medecin?.nom }} {{ rendezVous.medecin?.prenom }}
          </strong>
        </div>
        <div class="rv-detail-row">
          <span>Date</span>
          <strong>{{ rendezVous.date_rendez_vous }}</strong>
        </div>
        <div class="rv-detail-row">
          <span>Heure</span>
          <strong>
            {{ (rendezVous.heure_debut || '').slice(0, 5) }}
            - {{ (rendezVous.heure_fin || '').slice(0, 5) }}
          </strong>
        </div>
        <div class="rv-detail-row">
          <span>Motif</span>
          <strong>{{ rendezVous.motif }}</strong>
        </div>
        <div class="rv-detail-row">
          <span>Statut</span>
          <strong>{{ rendezVous.statut }}</strong>
        </div>
      </div>

      <!-- MODE CREATE / EDIT -->
      <form v-else class="rv-modal-body" @submit.prevent="soumettre">
        <div class="rv-form-group">
          <label for="id_patient">Patient *</label>
          <select id="id_patient" v-model="form.id_patient" required>
            <option value="" disabled>Sélectionner un patient</option>
            <option
              v-for="patient in patients"
              :key="patient.id_patient"
              :value="patient.id_patient"
            >
              {{ patient.nom }} {{ patient.prenom }}
            </option>
          </select>
          <span v-if="validationErrors.id_patient" class="field-error">
            {{ validationErrors.id_patient[0] }}
          </span>
        </div>

        <div class="rv-form-group">
          <label for="id_medecin">Médecin *</label>
          <select id="id_medecin" v-model="form.id_medecin" required>
            <option value="" disabled>Sélectionner un médecin</option>
            <option
              v-for="medecin in medecins"
              :key="medecin.id_medecin"
              :value="medecin.id_medecin"
            >
              Dr. {{ medecin.nom }} {{ medecin.prenom }}
            </option>
          </select>
          <span v-if="validationErrors.id_medecin" class="field-error">
            {{ validationErrors.id_medecin[0] }}
          </span>
        </div>

        <div class="rv-form-group">
          <label for="date_rendez_vous">Date *</label>
          <input
            id="date_rendez_vous"
            v-model="form.date_rendez_vous"
            type="date"
            required
          />
          <span v-if="validationErrors.date_rendez_vous" class="field-error">
            {{ validationErrors.date_rendez_vous[0] }}
          </span>
        </div>

        <div class="rv-form-row">
          <div class="rv-form-group">
            <label for="heure_debut">Heure début *</label>
            <input
              id="heure_debut"
              v-model="form.heure_debut"
              type="time"
              required
            />
            <span v-if="validationErrors.heure_debut" class="field-error">
              {{ validationErrors.heure_debut[0] }}
            </span>
          </div>

          <div class="rv-form-group">
            <label for="heure_fin">Heure fin *</label>
            <input
              id="heure_fin"
              v-model="form.heure_fin"
              type="time"
              required
            />
            <span v-if="validationErrors.heure_fin" class="field-error">
              {{ validationErrors.heure_fin[0] }}
            </span>
          </div>
        </div>

        <div class="rv-form-group">
          <label for="motif">Motif *</label>
          <input
            id="motif"
            v-model="form.motif"
            type="text"
            required
          />
          <span v-if="validationErrors.motif" class="field-error">
            {{ validationErrors.motif[0] }}
          </span>
        </div>

        <div class="rv-form-group">
          <label for="statut">Statut *</label>
          <select id="statut" v-model="form.statut" required>
            <option value="Confirmé">Confirmé</option>
            <option value="En attente">En attente</option>
            <option value="En cours">En cours</option>
            <option value="Annulé">Annulé</option>
          </select>
        </div>

        <div v-if="errorMessage" class="alert error">
          {{ errorMessage }}
        </div>

        <div class="rv-modal-actions">
          <button type="button" class="btn-secondary" @click="emit('close')">
            Annuler
          </button>
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.rv-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.rv-modal {
  background: white;
  border-radius: 14px;
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 12px 40px rgba(15, 23, 42, 0.2);
}

.rv-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.rv-modal-header h2 {
  margin: 0;
  font-size: 18px;
  color: #0f172a;
}

.rv-modal-close {
  background: none;
  border: none;
  font-size: 22px;
  line-height: 1;
  cursor: pointer;
  color: #64748b;
}

.rv-modal-body {
  padding: 20px 24px;
}

.rv-detail-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}

.rv-detail-row span {
  color: #64748b;
  font-size: 14px;
}

.rv-form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 16px;
}

.rv-form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

label {
  margin-bottom: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}

input,
select {
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
}

.field-error {
  margin-top: 4px;
  font-size: 12px;
  color: #dc2626;
}

.alert.error {
  background: #fef2f2;
  color: #dc2626;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 12px;
}

.rv-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 8px;
}

.btn-secondary {
  background: white;
  color: #475569;
  border: 1px solid #cbd5e1;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
}

.btn-primary {
  background: #1687e8;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>