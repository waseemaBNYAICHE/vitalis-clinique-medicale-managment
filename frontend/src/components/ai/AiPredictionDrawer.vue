<script setup>
import { ref } from 'vue'
import AnimatedAiIcon from './AnimatedAiIcon.vue'

const drawerState = ref('collapsed')

const patient = ref({
  age: '',
  gender: ''
})

const openDrawer = () => {
  drawerState.value = 'open'
}

const collapseDrawer = () => {
  drawerState.value = 'collapsed'
}

const closeDrawer = () => {
  drawerState.value = 'closed'
}
</script>

<template>
  <div class="ai-widget">
    <!-- Petit bouton après fermeture -->
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
    <Transition name="drawer">
      <section
        v-if="drawerState === 'open'"
        class="prediction-drawer"
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

        <main class="drawer-content">
          <div class="progress">
            <div class="progress-step active">
              <span>1</span>
              <small>Patient</small>
            </div>

            <div class="progress-line" />

            <div class="progress-step">
              <span>2</span>
              <small>Symptômes</small>
            </div>

            <div class="progress-line" />

            <div class="progress-step">
              <span>3</span>
              <small>Prédiction</small>
            </div>
          </div>

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

          <button class="continue-button" type="button">
            Continuer
            <span>→</span>
          </button>

          <button class="scroll-button" type="button">
            <span>Faire défiler vers les symptômes</span>

            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M6 9l6 6 6-6" />
            </svg>
          </button>
        </main>
      </section>
    </Transition>
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
  --turquoise: #32d5df;
  --navy: #10234a;
  --muted: #72809f;
  --border: #dfe6f2;

  position: fixed;
  right: 24px;
  bottom: 20px;
  z-index: 1000;
  color: var(--navy);
}

.ai-launcher {
  width: 64px;
  height: 64px;
  padding: 4px;
  border: none;
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
  border: 1px solid rgb(36 71 232 / 12%);
  border-radius: 22px;
  background: rgb(255 255 255 / 96%);
  box-shadow: 0 16px 45px rgb(15 39 83 / 20%);
  cursor: pointer;
  text-align: left;
  backdrop-filter: blur(12px);
  transition: 0.25s ease;
}

.collapsed-drawer:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 50px rgb(15 39 83 / 27%);
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
  color: var(--navy);
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
  overflow: hidden;
  border: 1px solid rgb(36 71 232 / 12%);
  border-radius: 25px;
  background: white;
  box-shadow: 0 24px 70px rgb(11 35 75 / 28%);
}

.drawer-header {
  min-height: 78px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 16px;
  border-bottom: 1px solid var(--border);
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
  color: var(--navy);
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
  border: none;
  border-radius: 10px;
  color: var(--navy);
  background: transparent;
  font-size: 20px;
  cursor: pointer;
}

.header-actions button:hover {
  color: var(--primary);
  background: #f1f5ff;
}

.drawer-content {
  padding: 20px;
}

.progress {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  margin-bottom: 27px;
}

.progress-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  color: #9ca6bb;
}

.progress-step > span {
  width: 29px;
  height: 29px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: #e9edf5;
  font-size: 12px;
  font-weight: 700;
}

.progress-step small {
  font-size: 10px;
  font-weight: 600;
}

.progress-step.active {
  color: var(--primary);
}

.progress-step.active > span {
  color: white;
  background: var(--primary);
  box-shadow: 0 0 0 5px rgb(36 71 232 / 10%);
}

.progress-line {
  width: 45px;
  height: 2px;
  margin: 14px 6px 0;
  background: #dfe5ef;
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
  color: #e43d4f;
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
  padding-right: 52px;
}

.input-container small {
  position: absolute;
  top: 50%;
  right: 14px;
  color: var(--muted);
  transform: translateY(-50%);
}

.continue-button {
  width: 100%;
  min-height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  border: none;
  border-radius: 13px;
  color: white;
  background: linear-gradient(135deg, var(--primary), #315dff);
  box-shadow: 0 9px 20px rgb(36 71 232 / 20%);
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s ease;
}

.continue-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 13px 26px rgb(36 71 232 / 28%);
}

.scroll-button {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  margin-top: 18px;
  padding: 7px;
  border: none;
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

.drawer-enter-active,
.drawer-leave-active {
  transition:
    opacity 0.3s ease,
    transform 0.3s ease;
}

.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
  transform: translateY(35px) scale(0.96);
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

  .collapsed-text small {
    display: none;
  }

  .open-action {
    font-size: 0;
  }
}
</style>