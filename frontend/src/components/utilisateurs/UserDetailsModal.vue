<script setup>
defineProps({
  open: {
    type: Boolean,
    default: false,
  },

  user: {
    type: Object,
    default: null,
  },

  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])

const formatRole = (role) => {
  const roles = {
    administrateur: 'Administrateur',
    medecin: 'Médecin',
    secretaire: 'Secrétaire',
    infirmier: 'Infirmier',
    patient: 'Patient',
  }

  return roles[role] || role || '—'
}

const getInitials = (name = '') => {
  return name
    .split(' ')
    .map((part) => part.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
}
</script>

<template>
  <Teleport to="body">
    <Transition name="details-modal">
      <div
        v-if="open"
        class="details-overlay"
        @click.self="emit('close')"
      >
        <div
          class="details-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="details-title"
        >
          <div class="details-header">
            <div>
              <h2 id="details-title">
                Informations de l’utilisateur
              </h2>

              <p>Informations générales du compte.</p>
            </div>

            <button
              type="button"
              class="details-close"
              aria-label="Fermer"
              @click="emit('close')"
            >
              <i class="fi fi-rr-cross-small"></i>
            </button>
          </div>

          <div v-if="loading" class="details-loading">
            <i class="fi fi-rr-spinner"></i>
            <span>Chargement...</span>
          </div>

          <div v-else-if="user" class="details-content">
            <div class="details-profile">
              <div class="details-avatar">
                {{ getInitials(user.name) }}
              </div>

              <div>
                <h3>{{ user.name }}</h3>
                <p>#{{ user.id }}</p>
              </div>
            </div>

            <div class="details-grid">
              <div class="details-item">
                <span>Adresse email</span>
                <strong>{{ user.email || '—' }}</strong>
              </div>

              <div class="details-item">
                <span>Rôle</span>
                <strong>{{ formatRole(user.role) }}</strong>
              </div>

              <div class="details-item">
                <span>Statut</span>

                <strong
                  :class="
                    user.statut === 'Actif'
                      ? 'active-text'
                      : 'inactive-text'
                  "
                >
                  {{ user.statut || '—' }}
                </strong>
              </div>

              <div class="details-item">
                <span>Identifiant médecin</span>
                <strong>{{ user.id_medecin || '—' }}</strong>
              </div>

              <div class="details-item">
                <span>Identifiant patient</span>
                <strong>{{ user.id_patient || '—' }}</strong>
              </div>

              <div class="details-item">
                <span>Date de création</span>
                <strong>
                  {{
                    user.created_at
                      ? new Date(user.created_at).toLocaleDateString('fr-FR')
                      : '—'
                  }}
                </strong>
              </div>
            </div>

            <div class="details-actions">
              <button
                type="button"
                class="details-button"
                @click="emit('close')"
              >
                Fermer
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.details-overlay {
  position: fixed;
  inset: 0;
  z-index: 9000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(15, 23, 42, 0.48);
  backdrop-filter: blur(4px);
}

.details-modal {
  width: min(580px, 100%);
  overflow: hidden;
  background: #ffffff;
  border-radius: 18px;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.25);
}

.details-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 22px;
  border-bottom: 1px solid #e2e8f0;
}

.details-header h2 {
  margin: 0 0 5px;
  color: #1e293b;
  font-size: 20px;
}

.details-header p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.details-close {
  width: 34px;
  height: 34px;
  color: #64748b;
  background: transparent;
  border: 0;
  border-radius: 8px;
  cursor: pointer;
}

.details-close:hover {
  color: #1e293b;
  background: #f1f5f9;
}

.details-loading {
  display: flex;
  justify-content: center;
  gap: 10px;
  padding: 50px;
  color: #64748b;
}

.details-loading i {
  animation: spinner 0.8s linear infinite;
}

.details-content {
  padding: 22px;
}

.details-profile {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 24px;
}

.details-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 58px;
  height: 58px;
  color: #ffffff;
  font-weight: 700;
  background: #2563eb;
  border-radius: 16px;
}

.details-profile h3 {
  margin: 0 0 4px;
  color: #1e293b;
}

.details-profile p {
  margin: 0;
  color: #64748b;
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.details-item {
  padding: 14px;
  background: #f8fafc;
  border-radius: 10px;
}

.details-item span {
  display: block;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
}

.details-item strong {
  color: #1e293b;
  font-size: 14px;
}

.active-text {
  color: #16a34a !important;
}

.inactive-text {
  color: #dc2626 !important;
}

.details-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 22px;
}

.details-button {
  padding: 10px 20px;
  color: #ffffff;
  background: #2563eb;
  border: 0;
  border-radius: 9px;
  cursor: pointer;
}

.details-modal-enter-active,
.details-modal-leave-active {
  transition: opacity 0.25s ease;
}

.details-modal-enter-from,
.details-modal-leave-to {
  opacity: 0;
}

@keyframes spinner {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 560px) {
  .details-grid {
    grid-template-columns: 1fr;
  }
}
</style>