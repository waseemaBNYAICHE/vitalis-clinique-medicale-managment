<script setup>
import { computed, nextTick, ref } from 'vue'
import AnimatedAiIcon from './AnimatedAiIcon.vue'
import api from '../../api.js'
const drawerState = ref('collapsed')
const activeStep = ref(1)
const isLoading = ref(false)
const errorMessage = ref('')
const prediction = ref(null)

const contentRef = ref(null)
const patientSection = ref(null)
const symptomsSection = ref(null)
const predictionSection = ref(null)

const patient = ref({
  age: '',
  gender: '',
  duration: ''
})

const selectedSymptoms = ref([])

const symptoms = [
  { id: 'fever', label: 'Fièvre', icon: '🌡️' },
  { id: 'cough', label: 'Toux', icon: '🫁' },
  { id: 'headache', label: 'Mal de tête', icon: '🤕' },
  { id: 'fatigue', label: 'Fatigue', icon: '😴' },
  { id: 'sore_throat', label: 'Mal de gorge', icon: '🗣️' },
  { id: 'runny_nose', label: 'Nez qui coule', icon: '🤧' },
  { id: 'shortness_of_breath', label: 'Essoufflement', icon: '💨' },
  { id: 'chest_pain', label: 'Douleur thoracique', icon: '❤️' },
  { id: 'nausea', label: 'Nausée', icon: '🤢' },
  { id: 'vomiting', label: 'Vomissements', icon: '🤮' },
  { id: 'diarrhea', label: 'Diarrhée', icon: '🩺' },
  { id: 'abdominal_pain', label: 'Douleur abdominale', icon: '🩹' },
  { id: 'skin_rash', label: 'Éruption cutanée', icon: '🔴' },
  { id: 'itching', label: 'Démangeaisons', icon: '✋' },
  { id: 'joint_pain', label: 'Douleur articulaire', icon: '🦴' },
  { id: 'muscle_pain', label: 'Douleur musculaire', icon: '💪' },
  { id: 'dizziness', label: 'Vertiges', icon: '💫' }
]

const patientIsValid = computed(() => {
  const age = Number(patient.value.age)

  return (
    age >= 1 &&
    age <= 120 &&
    patient.value.gender !== ''
  )
})

const predictionIsValid = computed(() => {
  return (
    patientIsValid.value &&
    selectedSymptoms.value.length > 0 &&
    patient.value.duration !== ''
  )
})

const genderLabel = computed(() => {
  const labels = {
    male: 'Homme',
    female: 'Femme',
    other: 'Autre'
  }

  return labels[patient.value.gender] || '—'
})

const durationLabel = computed(() => {
  const labels = {
    today: 'Aujourd’hui',
    '1-2-days': '1 à 2 jours',
    '3-7-days': '3 à 7 jours',
    '1-2-weeks': '1 à 2 semaines',
    'more-than-2-weeks': 'Plus de 2 semaines'
  }

  return labels[patient.value.duration] || '—'
})

const scrollToSection = async (sectionReference, step) => {
  activeStep.value = step
  errorMessage.value = ''

  await nextTick()

  if (!contentRef.value || !sectionReference.value) {
    return
  }

  contentRef.value.scrollTo({
    top: sectionReference.value.offsetTop,
    behavior: 'smooth'
  })
}

const openDrawer = async () => {
  drawerState.value = 'open'

  await nextTick()
  scrollToSection(patientSection, 1)
}

const collapseDrawer = () => {
  drawerState.value = 'collapsed'
}

const closeDrawer = () => {
  drawerState.value = 'closed'
}

const goToPatient = () => {
  scrollToSection(patientSection, 1)
}

const goToSymptoms = () => {
  errorMessage.value = ''

  if (!patientIsValid.value) {
    activeStep.value = 1
    errorMessage.value =
      'Veuillez saisir un âge valide et sélectionner le sexe.'

    return
  }

  scrollToSection(symptomsSection, 2)
}

const goToPrediction = () => {
  errorMessage.value = ''

  if (selectedSymptoms.value.length === 0) {
    activeStep.value = 2
    errorMessage.value =
      'Veuillez sélectionner au moins un symptôme.'

    return
  }

  if (!patient.value.duration) {
    activeStep.value = 2
    errorMessage.value =
      'Veuillez sélectionner la durée des symptômes.'

    return
  }

  scrollToSection(predictionSection, 3)
}

const handleScroll = () => {
  if (!contentRef.value) return

  const scrollTop = contentRef.value.scrollTop
  const symptomsTop = symptomsSection.value?.offsetTop || 0
  const predictionTop = predictionSection.value?.offsetTop || 0

  if (scrollTop >= predictionTop - 160) {
    activeStep.value = 3
  } else if (scrollTop >= symptomsTop - 160) {
    activeStep.value = 2
  } else {
    activeStep.value = 1
  }
}

const predictDisease = async () => {
  if (!predictionIsValid.value || isLoading.value) {
    return
  }

  prediction.value = null
  errorMessage.value = ''
  isLoading.value = true

  try {
    const response = await api.post('/ai/predict', {
      symptoms: selectedSymptoms.value
    })

    prediction.value = response.data
  } catch (error) {
    console.error('Erreur lors de la prédiction IA :', error)

    if (error.response?.status === 400) {
      errorMessage.value =
        'Les symptômes sélectionnés ne sont pas valides.'
    } else if (error.response?.status === 503) {
      errorMessage.value =
        'Le service IA est temporairement indisponible.'
    } else {
      errorMessage.value =
        'Impossible d’effectuer la prédiction. Veuillez réessayer.'
    }
  } finally {
    isLoading.value = false
  }
}

const resetPrediction = () => {
  patient.value = {
    age: '',
    gender: '',
    duration: ''
  }

  selectedSymptoms.value = []
  prediction.value = null
  errorMessage.value = ''

  goToPatient()
}
</script>

<template>
  <div class="ai-widget">
    <!-- Bouton après fermeture -->
    <button
      v-if="drawerState === 'closed'"
      class="ai-launcher"
      type="button"
      aria-label="Ouvrir la prédiction IA"
      @click="openDrawer"
    >
      <AnimatedAiIcon />
    </button>

    <!-- Fenêtre repliée -->
    <button
      v-else-if="drawerState === 'collapsed'"
      class="collapsed-drawer"
      type="button"
      @click="openDrawer"
    >
      <span class="collapsed-icon">
        <AnimatedAiIcon />
      </span>

      <span class="collapsed-text">
        <strong>AI Disease Prediction</strong>
        <small>Analyser les symptômes du patient</small>
      </span>

      <span class="open-action">
        Ouvrir

        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="M6 15l6-6 6 6" />
        </svg>
      </span>
    </button>

    <!-- Fenêtre ouverte -->
    <aside
      v-else
      class="prediction-drawer"
      aria-label="AI Disease Prediction"
    >
      <header class="drawer-header">
        <div class="header-identity">
          <span class="header-icon">
            <AnimatedAiIcon />
          </span>

          <div>
            <h2>AI Disease Prediction</h2>
            <p>Assistant de prédiction médicale</p>
          </div>
        </div>

        <div class="header-actions">
          <button
            type="button"
            title="Réduire"
            aria-label="Réduire"
            @click="collapseDrawer"
          >
            —
          </button>

          <button
            type="button"
            title="Fermer"
            aria-label="Fermer"
            @click="closeDrawer"
          >
            ×
          </button>
        </div>
      </header>

      <!-- Indicateur de progression -->
      <nav class="steps" aria-label="Progression">
        <button
          :class="[
            'step',
            {
              active: activeStep === 1,
              completed: activeStep > 1
            }
          ]"
          type="button"
          @click="goToPatient"
        >
          <span>{{ activeStep > 1 ? '✓' : '1' }}</span>
          <small>Patient</small>
        </button>

        <div
          :class="[
            'step-line',
            { completed: activeStep > 1 }
          ]"
        />

        <button
          :class="[
            'step',
            {
              active: activeStep === 2,
              completed: activeStep > 2
            }
          ]"
          type="button"
          @click="goToSymptoms"
        >
          <span>{{ activeStep > 2 ? '✓' : '2' }}</span>
          <small>Symptômes</small>
        </button>

        <div
          :class="[
            'step-line',
            { completed: activeStep > 2 }
          ]"
        />

        <button
          :class="[
            'step',
            { active: activeStep === 3 }
          ]"
          type="button"
          @click="goToPrediction"
        >
          <span>3</span>
          <small>Prédiction</small>
        </button>
      </nav>

      <!-- Zone défilable -->
      <div
        ref="contentRef"
        class="drawer-content"
        @scroll="handleScroll"
      >
        <!-- Étape 1 -->
        <section
          ref="patientSection"
          class="drawer-section"
        >
          <div class="section-title">
            <span class="section-icon">👤</span>

            <div>
              <h3>Informations du patient</h3>
              <p>Renseignez les informations générales.</p>
            </div>
          </div>

          <div class="form-group">
            <label for="patient-age">
              Âge <span>*</span>
            </label>

            <div class="input-container">
              <input
                id="patient-age"
                v-model="patient.age"
                type="number"
                min="1"
                max="120"
                placeholder="Exemple : 35"
              />

              <small>ans</small>
            </div>
          </div>

          <div class="form-group">
            <label for="patient-gender">
              Sexe <span>*</span>
            </label>

            <select
              id="patient-gender"
              v-model="patient.gender"
            >
              <option value="" disabled>
                Sélectionner le sexe
              </option>

              <option value="male">Homme</option>
              <option value="female">Femme</option>
              <option value="other">Autre</option>
            </select>
          </div>

          <p
            v-if="errorMessage && activeStep === 1"
            class="error-message"
          >
            {{ errorMessage }}
          </p>

          <button
            class="primary-button"
            type="button"
            @click="goToSymptoms"
          >
            Continuer
            <span>→</span>
          </button>

          <button
            class="scroll-button"
            type="button"
            @click="goToSymptoms"
          >
            <span>Faire défiler vers les symptômes</span>

            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M6 9l6 6 6-6" />
            </svg>
          </button>
        </section>

        <!-- Étape 2 -->
        <section
          ref="symptomsSection"
          class="drawer-section"
        >
          <div class="section-title">
            <span class="section-icon">🩺</span>

            <div>
              <h3>Symptômes</h3>
              <p>Sélectionnez les symptômes observés.</p>
            </div>
          </div>

          <div class="selected-counter">
            {{ selectedSymptoms.length }}
            symptôme(s) sélectionné(s)
          </div>

          <div class="symptoms-grid">
            <label
              v-for="symptom in symptoms"
              :key="symptom.id"
              :class="[
                'symptom-card',
                {
                  selected:
                    selectedSymptoms.includes(symptom.id)
                }
              ]"
            >
              <input
                v-model="selectedSymptoms"
                type="checkbox"
                :value="symptom.id"
              />

              <span class="symptom-icon">
                {{ symptom.icon }}
              </span>

              <span class="symptom-label">
                {{ symptom.label }}
              </span>

              <span class="check-mark">✓</span>
            </label>
          </div>

          <div class="form-group duration-field">
            <label for="symptom-duration">
              Durée des symptômes <span>*</span>
            </label>

            <select
              id="symptom-duration"
              v-model="patient.duration"
            >
              <option value="" disabled>
                Sélectionner la durée
              </option>

              <option value="today">Aujourd’hui</option>
              <option value="1-2-days">1 à 2 jours</option>
              <option value="3-7-days">3 à 7 jours</option>
              <option value="1-2-weeks">
                1 à 2 semaines
              </option>
              <option value="more-than-2-weeks">
                Plus de 2 semaines
              </option>
            </select>
          </div>

          <p
            v-if="errorMessage && activeStep === 2"
            class="error-message"
          >
            {{ errorMessage }}
          </p>

          <div class="navigation-buttons">
            <button
              class="secondary-button"
              type="button"
              @click="goToPatient"
            >
              ← Retour
            </button>

            <button
              class="primary-button"
              type="button"
              @click="goToPrediction"
            >
              Continuer →
            </button>
          </div>

          <button
            class="scroll-button"
            type="button"
            @click="goToPrediction"
          >
            <span>Continuer vers la prédiction</span>

            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M6 9l6 6 6-6" />
            </svg>
          </button>
        </section>

        <!-- Étape 3 -->
        <section
          ref="predictionSection"
          class="drawer-section"
        >
          <div class="section-title">
            <span class="section-icon">✨</span>

            <div>
              <h3>Prédiction</h3>
              <p>Vérifiez les informations avant l’analyse.</p>
            </div>
          </div>

          <div class="summary-card">
            <div>
              <span>Âge</span>
              <strong>{{ patient.age || '—' }} ans</strong>
            </div>

            <div>
              <span>Sexe</span>
              <strong>{{ genderLabel }}</strong>
            </div>

            <div>
              <span>Symptômes</span>
              <strong>{{ selectedSymptoms.length }}</strong>
            </div>

            <div>
              <span>Durée</span>
              <strong>{{ durationLabel }}</strong>
            </div>
          </div>

          <button
            class="predict-button"
            type="button"
            :disabled="!predictionIsValid || isLoading"
            @click="predictDisease"
          >
            <span v-if="isLoading" class="loader" />
            <span v-else class="sparkles">✦</span>

            {{
              isLoading
                ? 'Analyse en cours...'
                : 'Prédire la maladie'
            }}
          </button>

          <div v-if="isLoading" class="analysis-status">
            <div class="loading-line">
              <span />
            </div>

            <p>
              Analyse des symptômes par intelligence artificielle…
            </p>
          </div>

          <div v-if="prediction" class="result-card">
            <div class="result-title">
              <span class="success-icon">✓</span>

              <div>
                <small>Résultat de la prédiction</small>
                <h4>{{ prediction.disease }}</h4>
              </div>
            </div>

            <div class="confidence">
              <div class="confidence-heading">
                <span>Niveau de confiance</span>
                <strong>{{ prediction.confidence }} %</strong>
              </div>

              <div class="confidence-bar">
                <span
                  :style="{
                    width: `${prediction.confidence}%`
                  }"
                />
              </div>
            </div>

            <p class="recommendation">
              {{ prediction.recommendation }}
            </p>

            <button
              class="secondary-button full-width"
              type="button"
              @click="resetPrediction"
            >
              Nouvelle prédiction
            </button>
          </div>

          <p class="medical-warning">
            Cette prédiction est fournie à titre indicatif et ne
            remplace pas le diagnostic d’un professionnel de santé.
          </p>

          <button
            class="back-link"
            type="button"
            @click="goToSymptoms"
          >
            ← Modifier les symptômes
          </button>
        </section>
      </div>
    </aside>
  </div>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

button,
input,
select {
  font: inherit;
}

.ai-widget {
  --primary: #2447e8;
  --primary-dark: #1734bd;
  --turquoise: #32d5df;
  --navy: #10234a;
  --muted: #72809f;
  --border: #dfe6f2;
  --surface: #ffffff;
  --light-blue: #f4f8ff;
  --danger: #dc3545;
  --success: #19b987;

  position: fixed;
  right: 24px;
  bottom: 20px;
  z-index: 9999;
  color: var(--navy);
}

.ai-launcher {
  width: 64px;
  height: 64px;
  padding: 4px;
  border: 0;
  border-radius: 50%;
  background: transparent;
  cursor: pointer;
  filter: drop-shadow(0 10px 22px rgb(36 71 232 / 35%));
  transition: transform 0.25s ease;
}

.ai-launcher:hover {
  transform: scale(1.08);
}

.collapsed-drawer {
  width: 390px;
  min-height: 78px;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 15px;
  border: 1px solid rgb(36 71 232 / 15%);
  border-radius: 22px;
  background: rgb(255 255 255 / 97%);
  box-shadow: 0 16px 45px rgb(15 39 83 / 22%);
  cursor: pointer;
  text-align: left;
  backdrop-filter: blur(12px);
  transition: 0.25s ease;
}

.collapsed-drawer:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 50px rgb(15 39 83 / 28%);
}

.collapsed-icon {
  width: 50px;
  height: 50px;
  flex: 0 0 50px;
}

.collapsed-text {
  min-width: 0;
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 4px;
}

.collapsed-text strong {
  overflow: hidden;
  font-size: 15px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.collapsed-text small {
  overflow: hidden;
  color: var(--muted);
  font-size: 11px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.open-action {
  display: flex;
  align-items: center;
  gap: 3px;
  color: var(--primary);
  font-size: 12px;
  font-weight: 700;
}

.open-action svg {
  width: 20px;
  fill: none;
  stroke: currentColor;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  animation: arrow-up 1.3s ease-in-out infinite;
}

.prediction-drawer {
  width: 410px;
  height: min(620px, calc(100vh - 40px));
  overflow: hidden;
  border: 1px solid rgb(36 71 232 / 15%);
  border-radius: 25px;
  background: white;
  box-shadow: 0 24px 70px rgb(11 35 75 / 30%);
  animation: drawer-open 0.35s ease-out;
}

.drawer-header {
  height: 78px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 16px;
  border-bottom: 1px solid var(--border);
  background: white;
}

.header-identity {
  display: flex;
  align-items: center;
  gap: 11px;
}

.header-icon {
  width: 47px;
  height: 47px;
}

.header-identity h2 {
  margin: 0;
  font-size: 16px;
}

.header-identity p {
  margin: 4px 0 0;
  color: var(--muted);
  font-size: 11px;
}

.header-actions {
  display: flex;
  gap: 4px;
}

.header-actions button {
  width: 32px;
  height: 32px;
  border: 0;
  border-radius: 10px;
  color: var(--navy);
  background: transparent;
  font-size: 20px;
  cursor: pointer;
}

.header-actions button:hover {
  color: var(--primary);
  background: var(--light-blue);
}

.steps {
  height: 68px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 10px 15px 7px;
  border-bottom: 1px solid var(--border);
  background: #fbfcff;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  border: 0;
  color: #9ca6bb;
  background: transparent;
  cursor: pointer;
}

.step > span {
  width: 29px;
  height: 29px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: #e9edf5;
  font-size: 12px;
  font-weight: 700;
  transition: 0.3s ease;
}

.step small {
  font-size: 10px;
  font-weight: 600;
}

.step.active {
  color: var(--primary);
}

.step.active > span {
  color: white;
  background: var(--primary);
  box-shadow: 0 0 0 5px rgb(36 71 232 / 10%);
  transform: scale(1.08);
}

.step.completed {
  color: var(--success);
}

.step.completed > span {
  color: white;
  background: var(--success);
}

.step-line {
  width: 43px;
  height: 2px;
  margin: 14px 7px 0;
  background: #dfe5ef;
  transition: background 0.3s ease;
}

.step-line.completed {
  background: var(--success);
}

.drawer-content {
  position: relative;
  height: calc(100% - 146px);
  overflow-x: hidden;
  overflow-y: auto;
  scroll-behavior: smooth;
  scrollbar-color: #b9c7df transparent;
  scrollbar-width: thin;
}

.drawer-content::-webkit-scrollbar {
  width: 6px;
}

.drawer-content::-webkit-scrollbar-thumb {
  border-radius: 10px;
  background: #b9c7df;
}

.drawer-section {
  min-height: 100%;
  padding: 24px 20px;
  scroll-margin-top: 0;
}

.drawer-section + .drawer-section {
  border-top: 8px solid #f3f6fb;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 11px;
  margin-bottom: 23px;
}

.section-icon {
  width: 40px;
  height: 40px;
  display: grid;
  flex: 0 0 40px;
  place-items: center;
  border-radius: 12px;
  background: #eef3ff;
  font-size: 19px;
}

.section-title h3 {
  margin: 0;
  font-size: 17px;
}

.section-title p {
  margin: 5px 0 0;
  color: var(--muted);
  font-size: 11px;
}

.form-group {
  margin-bottom: 17px;
}

.form-group label {
  display: block;
  margin-bottom: 7px;
  font-size: 13px;
  font-weight: 700;
}

.form-group label span {
  color: var(--danger);
}

.form-group input,
.form-group select {
  width: 100%;
  height: 47px;
  padding: 0 13px;
  border: 1px solid var(--border);
  border-radius: 12px;
  outline: none;
  color: var(--navy);
  background: white;
  transition: 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgb(36 71 232 / 10%);
}

.input-container {
  position: relative;
}

.input-container input {
  padding-right: 53px;
}

.input-container small {
  position: absolute;
  top: 50%;
  right: 14px;
  color: var(--muted);
  transform: translateY(-50%);
}

.primary-button,
.predict-button {
  width: 100%;
  min-height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  border: 0;
  border-radius: 13px;
  color: white;
  background: linear-gradient(
    135deg,
    var(--primary),
    #315dff
  );
  box-shadow: 0 9px 20px rgb(36 71 232 / 20%);
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s ease;
}

.primary-button:hover,
.predict-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 13px 26px rgb(36 71 232 / 28%);
}

.predict-button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

.secondary-button {
  min-height: 47px;
  padding: 0 15px;
  border: 1px solid var(--border);
  border-radius: 12px;
  color: var(--navy);
  background: white;
  font-weight: 700;
  cursor: pointer;
}

.scroll-button {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  margin-top: 20px;
  padding: 7px;
  border: 0;
  color: var(--primary);
  background: transparent;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

.scroll-button svg {
  width: 21px;
  fill: none;
  stroke: currentColor;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  animation: arrow-down 1.3s ease-in-out infinite;
}

.selected-counter {
  margin-bottom: 12px;
  padding: 9px 12px;
  border-radius: 10px;
  color: var(--primary);
  background: #eef3ff;
  font-size: 12px;
  font-weight: 700;
}

.symptoms-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 9px;
}

.symptom-card {
  position: relative;
  min-height: 66px;
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 10px;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: white;
  cursor: pointer;
  transition: 0.22s ease;
}

.symptom-card:hover {
  border-color: #9eb2ff;
  transform: translateY(-2px);
}

.symptom-card.selected {
  border-color: var(--primary);
  background: #eef3ff;
  transform: scale(1.02);
}

.symptom-card input {
  position: absolute;
  opacity: 0;
}

.symptom-icon {
  font-size: 20px;
}

.symptom-label {
  padding-right: 12px;
  font-size: 11px;
  font-weight: 700;
}

.check-mark {
  position: absolute;
  top: 6px;
  right: 7px;
  color: var(--primary);
  font-size: 12px;
  opacity: 0;
  transform: scale(0);
  transition: 0.2s ease;
}

.symptom-card.selected .check-mark {
  opacity: 1;
  transform: scale(1);
}

.duration-field {
  margin-top: 18px;
}

.navigation-buttons {
  display: grid;
  grid-template-columns: 1fr 1.4fr;
  gap: 10px;
}

.summary-card {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 9px;
  margin-bottom: 18px;
}

.summary-card > div {
  display: flex;
  flex-direction: column;
  gap: 5px;
  padding: 12px;
  border: 1px solid var(--border);
  border-radius: 11px;
  background: var(--light-blue);
}

.summary-card span {
  color: var(--muted);
  font-size: 10px;
}

.summary-card strong {
  font-size: 12px;
}

.loader {
  width: 19px;
  height: 19px;
  border: 2px solid rgb(255 255 255 / 40%);
  border-top-color: white;
  border-radius: 50%;
  animation: rotation 0.8s linear infinite;
}

.sparkles {
  animation: sparkle 1.2s ease-in-out infinite;
}

.analysis-status {
  margin-top: 16px;
  text-align: center;
}

.analysis-status p {
  color: var(--muted);
  font-size: 11px;
}

.loading-line {
  height: 6px;
  overflow: hidden;
  border-radius: 10px;
  background: #e6ebf4;
}

.loading-line span {
  width: 40%;
  height: 100%;
  display: block;
  border-radius: inherit;
  background: linear-gradient(
    90deg,
    var(--primary),
    var(--turquoise)
  );
  animation: analysis-loading 1.3s ease-in-out infinite;
}

.result-card {
  margin-top: 18px;
  padding: 16px;
  border: 1px solid #bcecdc;
  border-radius: 15px;
  background: #effcf7;
  animation: result-enter 0.4s ease-out;
}

.result-title {
  display: flex;
  align-items: center;
  gap: 11px;
}

.success-icon {
  width: 36px;
  height: 36px;
  display: grid;
  flex: 0 0 36px;
  place-items: center;
  border-radius: 50%;
  color: white;
  background: var(--success);
  font-weight: 800;
  animation: success-pop 0.45s ease;
}

.result-title small {
  color: #38846c;
  font-size: 10px;
}

.result-title h4 {
  margin: 4px 0 0;
  color: #116249;
  font-size: 14px;
}

.confidence {
  margin-top: 15px;
}

.confidence-heading {
  display: flex;
  justify-content: space-between;
  margin-bottom: 7px;
  color: #176a52;
  font-size: 11px;
}

.confidence-bar {
  height: 8px;
  overflow: hidden;
  border-radius: 10px;
  background: #d3efe5;
}

.confidence-bar span {
  height: 100%;
  display: block;
  border-radius: inherit;
  background: linear-gradient(
    90deg,
    var(--success),
    var(--turquoise)
  );
  animation: confidence-fill 1s ease;
}

.recommendation {
  color: #386b5c;
  font-size: 11px;
  line-height: 1.6;
}

.medical-warning {
  margin-top: 18px;
  padding: 11px;
  border-radius: 10px;
  color: #876d23;
  background: #fff8df;
  font-size: 10px;
  line-height: 1.5;
}

.back-link {
  width: 100%;
  margin-top: 13px;
  border: 0;
  color: var(--primary);
  background: transparent;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.error-message {
  margin: 0 0 13px;
  padding: 10px;
  border-radius: 9px;
  color: var(--danger);
  background: #fff0f1;
  font-size: 11px;
}

.full-width {
  width: 100%;
}

@keyframes drawer-open {
  from {
    opacity: 0;
    transform: translateY(35px) scale(0.96);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes arrow-up {
  0%,
  100% {
    opacity: 0.5;
    transform: translateY(3px);
  }

  50% {
    opacity: 1;
    transform: translateY(-3px);
  }
}

@keyframes arrow-down {
  0%,
  100% {
    opacity: 0.4;
    transform: translateY(-3px);
  }

  50% {
    opacity: 1;
    transform: translateY(4px);
  }
}

@keyframes rotation {
  to {
    transform: rotate(360deg);
  }
}

@keyframes sparkle {
  0%,
  100% {
    opacity: 0.6;
    transform: scale(0.9);
  }

  50% {
    opacity: 1;
    transform: scale(1.2) rotate(15deg);
  }
}

@keyframes analysis-loading {
  from {
    transform: translateX(-110%);
  }

  to {
    transform: translateX(350%);
  }
}

@keyframes success-pop {
  from {
    opacity: 0;
    transform: scale(0.3);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes confidence-fill {
  from {
    width: 0;
  }
}

@keyframes result-enter {
  from {
    opacity: 0;
    transform: translateY(15px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 600px) {
  .ai-widget {
    right: 10px;
    bottom: 10px;
    left: 10px;
  }

  .prediction-drawer,
  .collapsed-drawer {
    width: 100%;
  }

  .prediction-drawer {
    height: min(680px, calc(100vh - 20px));
  }

  .collapsed-text small {
    display: none;
  }

  .open-action {
    font-size: 0;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    scroll-behavior: auto !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>