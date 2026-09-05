<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api'

const router = useRouter()

const form = ref({
  nom: '',
  prenom: '',
  date_naissance: '',
  sexe: '',
  cin: '',
  telephone: '',
  email: '',
  groupe_sanguin: ''
})

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const validationErrors = ref({})

const submitPatient = async () => {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''
  validationErrors.value = {}

  try {
    const response = await api.post('/patients', form.value)

    successMessage.value =
      response.data?.message || 'Patient ajouté avec succès'

    setTimeout(() => {
      router.push('/patients')
    }, 1200)
  } catch (error) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data?.errors || {}
      errorMessage.value = 'Veuillez vérifier les informations saisies.'
    } else {
      errorMessage.value =
        error.response?.data?.message ||
        "Une erreur est survenue lors de l'ajout du patient."
    }
  } finally {
    loading.value = false
  }
}

const cancel = () => {
  router.push('/patients')
}
</script>

<template>
  <div class="patient-create-page">
    <div class="page-header">
      <div>
        <h1>Nouveau patient</h1>
        <p>Ajouter un nouveau patient à la clinique.</p>
      </div>
    </div>

    <div class="form-card">
      <form @submit.prevent="submitPatient">
        <div class="form-grid">
          <div class="form-group">
            <label for="nom">Nom *</label>
            <input
              id="nom"
              v-model="form.nom"
              type="text"
              placeholder="Ex: Zahra"
              required
            />
            <span v-if="validationErrors.nom" class="field-error">
              {{ validationErrors.nom[0] }}
            </span>
          </div>

          <div class="form-group">
            <label for="prenom">Prénom *</label>
            <input
              id="prenom"
              v-model="form.prenom"
              type="text"
              placeholder="Ex: Fatima"
              required
            />
            <span v-if="validationErrors.prenom" class="field-error">
              {{ validationErrors.prenom[0] }}
            </span>
          </div>

          <div class="form-group">
            <label for="date_naissance">Date de naissance *</label>
            <input
              id="date_naissance"
              v-model="form.date_naissance"
              type="date"
              required
            />
            <span v-if="validationErrors.date_naissance" class="field-error">
              {{ validationErrors.date_naissance[0] }}
            </span>
          </div>

          <div class="form-group">
            <label for="sexe">Sexe *</label>
            <select
              id="sexe"
              v-model="form.sexe"
              required
            >
              <option value="" disabled>Sélectionner</option>
              <option value="F">Femme</option>
              <option value="M">Homme</option>
            </select>
            <span v-if="validationErrors.sexe" class="field-error">
              {{ validationErrors.sexe[0] }}
            </span>
          </div>

          <div class="form-group">
            <label for="cin">CIN *</label>
            <input
              id="cin"
              v-model="form.cin"
              type="text"
              placeholder="Ex: AB123456"
              required
            />
            <span v-if="validationErrors.cin" class="field-error">
              {{ validationErrors.cin[0] }}
            </span>
          </div>

          <div class="form-group">
            <label for="telephone">Téléphone *</label>
            <input
              id="telephone"
              v-model="form.telephone"
              type="tel"
              placeholder="Ex: 0612345678"
              required
            />
            <span v-if="validationErrors.telephone" class="field-error">
              {{ validationErrors.telephone[0] }}
            </span>
          </div>

          <div class="form-group">
            <label for="email">Email *</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="Ex: patient@email.com"
              required
            />
            <span v-if="validationErrors.email" class="field-error">
              {{ validationErrors.email[0] }}
            </span>
          </div>

          <div class="form-group">
            <label for="groupe_sanguin">Groupe sanguin</label>
            <select
              id="groupe_sanguin"
              v-model="form.groupe_sanguin"
            >
              <option value="">Sélectionner</option>
              <option value="A+">A+</option>
              <option value="A-">A-</option>
              <option value="B+">B+</option>
              <option value="B-">B-</option>
              <option value="AB+">AB+</option>
              <option value="AB-">AB-</option>
              <option value="O+">O+</option>
              <option value="O-">O-</option>
            </select>
            <span v-if="validationErrors.groupe_sanguin" class="field-error">
              {{ validationErrors.groupe_sanguin[0] }}
            </span>
          </div>
        </div>

        <div v-if="successMessage" class="alert success">
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="alert error">
          {{ errorMessage }}
        </div>

        <div class="form-actions">
          <button
            type="button"
            class="btn-secondary"
            @click="cancel"
          >
            Annuler
          </button>

          <button
            type="submit"
            class="btn-primary"
            :disabled="loading"
          >
            {{ loading ? 'Enregistrement...' : 'Enregistrer le patient' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.patient-create-page {
  padding: 32px;
  min-height: 32vh;
  background: #f8fafc;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h1 {
  margin: 0;
  font-size: 28px;
  font-weight: 700;
  color: #0f172a;
}

.page-header p {
  margin-top: 6px;
  color: #64748b;
}

.form-card {
  max-width: 950px;
  background: #ffffff;
  padding: 30px;
  border-radius: 14px;
  box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 22px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #334155;
}

input,
select {
  width: 100%;
  box-sizing: border-box;
  padding: 12px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  outline: none;
  transition: 0.2s ease;
}

input:focus,
select:focus {
  border-color: #1687e8;
  box-shadow: 0 0 0 3px rgba(22, 135, 232, 0.12);
}

.field-error {
  margin-top: 6px;
  font-size: 13px;
  color: #dc2626;
}

.alert {
  margin-top: 24px;
  padding: 12px 14px;
  border-radius: 8px;
  font-size: 14px;
}

.success {
  background: #ecfdf5;
  color: #15803d;
}

.error {
  background: #fef2f2;
  color: #dc2626;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 28px;
}

button {
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: none;
}

.btn-secondary {
  background: white;
  color: #475569;
  border: 1px solid #cbd5e1;
}

.btn-primary {
  background: #1687e8;
  color: white;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .patient-create-page {
    padding: 18px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  button {
    width: 100%;
  }
}
</style>