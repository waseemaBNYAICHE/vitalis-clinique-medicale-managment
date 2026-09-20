<script setup>
import { computed, onMounted, ref } from 'vue'
import api, { messageErreur } from '../../api'

/**
 * SCRUM-38 - Meme patron que RendezVousFormModal : un seul composant pour
 * view / create / edit.
 *
 * Une consultation ne se cree que sur un rendez-vous existant (elle n'a pas
 * de patient/medecin directs). A la creation, on ne propose donc que les
 * rendez-vous eligibles : pas encore consommes par une consultation, et pas
 * annules (regle appliquee aussi cote backend dans store()).
 */
const props = defineProps({
  mode: {
    type: String,
    required: true,
    validator: (value) => ['view', 'create', 'edit'].includes(value)
  },
  consultation: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const rendezVousDisponibles = ref([])
const loadingListes = ref(false)

const loading = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const form = ref({
  id_rendez_vous: props.consultation?.id_rendez_vous ?? '',
  motif: props.consultation?.motif ?? '',
  diagnostic: props.consultation?.diagnostic ?? '',
  observations: props.consultation?.observations ?? '',
  poids: props.consultation?.poids ?? '',
  taille: props.consultation?.taille ?? '',
  tension_arterielle: props.consultation?.tension_arterielle ?? '',
  temperature: props.consultation?.temperature ?? ''
})

const titre = computed(() => {
  if (props.mode === 'create') return 'Nouvelle consultation'
  if (props.mode === 'edit') return 'Modifier la consultation'
  return 'Détails de la consultation'
})

const estLectureSeule = computed(() => props.mode === 'view')

/**
 * SCRUM-38 - GET /rendez-vous est paginee (10/page) et ne filtre pas par
 * statut cote serveur : le tri "Terminé sans consultation" se fait donc ici,
 * sur la page chargee. Limitation connue, a lever avec un endpoint dedie si
 * la liste s'avere incomplete en pratique.
 */
async function chargerRendezVousDisponibles() {
  loadingListes.value = true

  try {
    const reponse = await api.get('/rendez-vous')
    const tous = reponse.data?.rendez_vous?.data ?? []

    rendezVousDisponibles.value = tous.filter((rdv) => rdv.statut !== 'Annulé')
  } catch (error) {
    errorMessage.value = messageErreur(
      error,
      'Impossible de charger les rendez-vous.'
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
    id_rendez_vous: Number(form.value.id_rendez_vous),
    motif: form.value.motif,
    diagnostic: form.value.diagnostic,
    observations: form.value.observations || null,
    poids: form.value.poids || null,
    taille: form.value.taille || null,
    tension_arterielle: form.value.tension_arterielle || null,
    temperature: form.value.temperature || null
  }

  try {
    if (props.mode === 'create') {
      await api.post('/consultations', payload)
    } else {
      await api.put(`/consultations/${props.consultation.id_consultation}`, payload)
    }

    emit('saved')
  } catch (error) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data?.errors || {}
      errorMessage.value = 'Veuillez vérifier les informations saisies.'
    } else if (error.response?.status === 409) {
      errorMessage.value = error.response.data?.message
        || 'Conflit : cette consultation ne peut pas être enregistrée.'
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
  if (props.mode === 'create') {
    chargerRendezVousDisponibles()
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
            {{ consultation.rendez_vous?.patient?.nom }} {{ consultation.rendez_vous?.patient?.prenom }}
          </strong>
        </div>
        <div class="rv-detail-row">
          <span>Médecin</span>
          <strong>
            Dr. {{ consultation.rendez_vous?.medecin?.nom }} {{ consultation.rendez_vous?.medecin?.prenom }}
          </strong>
        </div>
        <div class="rv-detail-row">
          <span>Date</span>
          <strong>{{ consultation.rendez_vous?.date_rendez_vous }}</strong>
        </div>
        <div class="rv-detail-row">
          <span>Motif</span>
          <strong>{{ consultation.motif }}</strong>
        </div>
        <div class="rv-detail-row">
          <span>Diagnostic</span>
          <strong>{{ consultation.diagnostic }}</strong>
        </div>
        <div class="rv-detail-row" v-if="consultation.observations">
          <span>Observations</span>
          <strong>{{ consultation.observations }}</strong>
        </div>
        <div class="rv-detail-row" v-if="consultation.poids">
          <span>Poids</span>
          <strong>{{ consultation.poids }} kg</strong>
        </div>
        <div class="rv-detail-row" v-if="consultation.taille">
          <span>Taille</span>
          <strong>{{ consultation.taille }} cm</strong>
        </div>
        <div class="rv-detail-row" v-if="consultation.tension_arterielle">
          <span>Tension artérielle</span>
          <strong>{{ consultation.tension_arterielle }}</strong>
        </div>
        <div class="rv-detail-row" v-if="consultation.temperature">
          <span>Température</span>
          <strong>{{ consultation.temperature }} °C</strong>
        </div>
      </div>

      <!-- MODE CREATE / EDIT -->
      <form v-else class="rv-modal-body" @submit.prevent="soumettre">
        <div class="rv-form-group" v-if="mode === 'create'">
          <label for="id_rendez_vous">Rendez-vous *</label>
          <select id="id_rendez_vous" v-model="form.id_rendez_vous" required>
            <option value="" disabled>Sélectionner un rendez-vous</option>
            <option
              v-for="rdv in rendezVousDisponibles"
              :key="rdv.id_rendez_vous"
              :value="rdv.id_rendez_vous"
            >
              {{ rdv.patient?.nom }} {{ rdv.patient?.prenom }} — Dr. {{ rdv.medecin?.nom }}
              ({{ rdv.date_rendez_vous }} {{ (rdv.heure_debut || '').slice(0, 5) }})
            </option>
          </select>
          <span v-if="validationErrors.id_rendez_vous" class="field-error">
            {{ validationErrors.id_rendez_vous[0] }}
          </span>
        </div>

        <div class="rv-form-group">
          <label for="motif">Motif *</label>
          <input id="motif" v-model="form.motif" type="text" required />
          <span v-if="validationErrors.motif" class="field-error">
            {{ validationErrors.motif[0] }}
          </span>
        </div>

        <div class="rv-form-group">
          <label for="diagnostic">Diagnostic *</label>
          <textarea id="diagnostic" v-model="form.diagnostic" rows="3" required></textarea>
          <span v-if="validationErrors.diagnostic" class="field-error">
            {{ validationErrors.diagnostic[0] }}
          </span>
        </div>

        <div class="rv-form-group">
          <label for="observations">Observations</label>
          <textarea id="observations" v-model="form.observations" rows="2"></textarea>
        </div>

        <div class="rv-form-row">
          <div class="rv-form-group">
            <label for="poids">Poids (kg)</label>
            <input id="poids" v-model="form.poids" type="number" step="0.01" />
          </div>

          <div class="rv-form-group">
            <label for="taille">Taille (cm)</label>
            <input id="taille" v-model="form.taille" type="number" step="0.01" />
          </div>
        </div>

        <div class="rv-form-row">
          <div class="rv-form-group">
            <label for="tension_arterielle">Tension artérielle</label>
            <input id="tension_arterielle" v-model="form.tension_arterielle" type="text" placeholder="12/8" />
          </div>

          <div class="rv-form-group">
            <label for="temperature">Température (°C)</label>
            <input id="temperature" v-model="form.temperature" type="number" step="0.1" />
          </div>
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
  max-width: 560px;
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
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}

.rv-detail-row span {
  color: #64748b;
  font-size: 14px;
  flex-shrink: 0;
}

.rv-detail-row strong {
  text-align: right;
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
select,
textarea {
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
}

textarea {
  resize: vertical;
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