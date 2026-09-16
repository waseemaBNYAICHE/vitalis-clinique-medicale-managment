<script setup>
import { computed, reactive, watch } from 'vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },

  loading: {
    type: Boolean,
    default: false,
  },

  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value),
  },

  user: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'submit'])

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
  statut: 'Actif',
})

const isEditMode = computed(() => {
  return props.mode === 'edit'
})

const modalTitle = computed(() => {
  return isEditMode.value
    ? "Modifier l'utilisateur"
    : 'Ajouter un utilisateur'
})

const modalDescription = computed(() => {
  return isEditMode.value
    ? 'Modifiez les informations du compte.'
    : 'Renseignez les informations du nouveau compte.'
})

const submitLabel = computed(() => {
  if (props.loading) {
    return isEditMode.value
      ? 'Modification...'
      : 'Ajout en cours...'
  }

  return isEditMode.value
    ? 'Enregistrer'
    : 'Ajouter'
})

const resetForm = () => {
  form.name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
  form.role = ''
  form.statut = 'Actif'
}

const fillForm = () => {
  if (isEditMode.value && props.user) {
    form.name = props.user.name || ''
    form.email = props.user.email || ''
    form.role = props.user.role || ''

    form.statut =
      props.user.statut ||
      (
        props.user.status === 'actif'
          ? 'Actif'
          : 'Inactif'
      )

    form.password = ''
    form.password_confirmation = ''
  } else {
    resetForm()
  }
}

const closeModal = () => {
  if (!props.loading) {
    emit('close')
  }
}

const submitForm = () => {
  const payload = {
    name: form.name,
    email: form.email,
    role: form.role,
    statut: form.statut,
  }

  if (!isEditMode.value) {
    payload.password = form.password
    payload.password_confirmation =
      form.password_confirmation
  }

  emit('submit', payload)
}

watch(
  [
    () => props.open,
    () => props.user,
    () => props.mode,
  ],
  ([isOpen]) => {
    if (isOpen) {
      fillForm()
    }
  },
  {
    immediate: true,
  }
)
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="open"
        class="modal-overlay"
        @click.self="closeModal"
      >
        <div
          class="user-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="user-modal-title"
        >
          <div class="modal-header">
            <div class="modal-header-icon">
              <i
                class="fi"
                :class="
                  isEditMode
                    ? 'fi-rr-pencil'
                    : 'fi-rr-user-add'
                "
              ></i>
            </div>

            <div>
              <h2 id="user-modal-title">
                {{ modalTitle }}
              </h2>

              <p>
                {{ modalDescription }}
              </p>
            </div>

            <button
              type="button"
              class="modal-close"
              aria-label="Fermer"
              :disabled="loading"
              @click="closeModal"
            >
              <i class="fi fi-rr-cross-small"></i>
            </button>
          </div>

          <form
            class="user-form"
            @submit.prevent="submitForm"
          >
            <!-- NOM -->
            <div class="form-group form-full">
              <label for="user-name">
                Nom complet <span>*</span>
              </label>

              <input
                id="user-name"
                v-model.trim="form.name"
                type="text"
                placeholder="Exemple : Salma Benali"
                required
              />
            </div>

            <!-- EMAIL -->
            <div class="form-group form-full">
              <label for="user-email">
                Adresse email <span>*</span>
              </label>

              <input
                id="user-email"
                v-model.trim="form.email"
                type="email"
                placeholder="exemple@vitalis.ma"
                required
              />
            </div>

            <!-- ROLE -->
            <div class="form-group">
              <label for="user-role">
                Rôle <span>*</span>
              </label>

              <select
                id="user-role"
                v-model="form.role"
                required
              >
                <option value="" disabled>
                  Sélectionner un rôle
                </option>

                <option value="administrateur">
                  Administrateur
                </option>

                <option value="medecin">
                  Médecin
                </option>

                <option value="secretaire">
                  Secrétaire
                </option>

                <option value="infirmier">
                  Infirmier
                </option>

                <option value="patient">
                  Patient
                </option>
              </select>
            </div>

            <!-- STATUT -->
            <div class="form-group">
              <label for="user-status">
                Statut
              </label>

              <select
                id="user-status"
                v-model="form.statut"
              >
                <option value="Actif">
                  Actif
                </option>

                <option value="Inactif">
                  Inactif
                </option>
              </select>
            </div>

            <!-- PASSWORD : AJOUT SEULEMENT -->
            <div
              v-if="!isEditMode"
              class="form-group"
            >
              <label for="user-password">
                Mot de passe <span>*</span>
              </label>

              <input
                id="user-password"
                v-model="form.password"
                type="password"
                minlength="8"
                placeholder="Minimum 8 caractères"
                required
              />
            </div>

            <!-- CONFIRMATION : AJOUT SEULEMENT -->
            <div
              v-if="!isEditMode"
              class="form-group"
            >
              <label for="user-confirmation">
                Confirmation <span>*</span>
              </label>

              <input
                id="user-confirmation"
                v-model="form.password_confirmation"
                type="password"
                minlength="8"
                placeholder="Confirmer le mot de passe"
                required
              />
            </div>

            <!-- ACTIONS -->
            <div class="modal-actions">
              <button
                type="button"
                class="btn-cancel"
                :disabled="loading"
                @click="closeModal"
              >
                Annuler
              </button>

              <button
                type="submit"
                class="btn-submit"
                :disabled="loading"
              >
                <i
                  class="fi"
                  :class="
                    loading
                      ? 'fi-rr-spinner'
                      : isEditMode
                        ? 'fi-rr-pencil'
                        : 'fi-rr-user-add'
                  "
                ></i>

                {{ submitLabel }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
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

.user-modal {
  width: min(620px, 100%);
  max-height: 90vh;
  overflow-y: auto;
  background: #ffffff;
  border-radius: 18px;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.25);
}

.modal-header {
  display: grid;
  grid-template-columns: 48px 1fr 36px;
  gap: 14px;
  align-items: start;
  padding: 22px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 46px;
  height: 46px;
  color: #ffffff;
  background: #2563eb;
  border-radius: 13px;
}

.modal-header-icon i {
  display: flex;
  font-size: 18px;
}

.modal-header h2 {
  margin: 0 0 5px;
  color: #1e293b;
  font-size: 20px;
}

.modal-header p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.modal-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  padding: 0;
  color: #64748b;
  background: transparent;
  border: 0;
  border-radius: 8px;
  cursor: pointer;
}

.modal-close:hover:not(:disabled) {
  color: #1e293b;
  background: #f1f5f9;
}

.user-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  padding: 22px;
}

.form-full {
  grid-column: 1 / -1;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.form-group label {
  color: #334155;
  font-size: 14px;
  font-weight: 600;
}

.form-group label span {
  color: #ef4444;
}

.form-group input,
.form-group select {
  box-sizing: border-box;
  width: 100%;
  min-height: 44px;
  padding: 10px 12px;
  color: #1e293b;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 9px;
  outline: none;
}

.form-group input::placeholder {
  color: #94a3b8;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.13);
}

.modal-actions {
  grid-column: 1 / -1;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 6px;
}

.btn-cancel,
.btn-submit {
  min-height: 42px;
  padding: 10px 18px;
  border-radius: 9px;
  cursor: pointer;
}

.btn-cancel {
  color: #475569;
  background: #ffffff;
  border: 1px solid #cbd5e1;
}

.btn-cancel:hover:not(:disabled) {
  background: #f8fafc;
}

.btn-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: #ffffff;
  background: #2563eb;
  border: 1px solid #2563eb;
}

.btn-submit:hover:not(:disabled) {
  background: #1d4ed8;
  border-color: #1d4ed8;
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.65;
}

.fi-rr-spinner {
  animation: spinner 0.8s linear infinite;
}

/* Animation de la fenêtre */

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease;
}

.modal-enter-active .user-modal,
.modal-leave-active .user-modal {
  transition: transform 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .user-modal,
.modal-leave-to .user-modal {
  transform: translateY(18px) scale(0.97);
}

@keyframes spinner {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 600px) {
  .user-form {
    grid-template-columns: 1fr;
  }

  .form-full,
  .modal-actions {
    grid-column: 1;
  }

  .modal-actions {
    flex-direction: column-reverse;
  }

  .btn-cancel,
  .btn-submit {
    width: 100%;
  }
}

@media (prefers-reduced-motion: reduce) {
  .modal-enter-active,
  .modal-leave-active,
  .modal-enter-active .user-modal,
  .modal-leave-active .user-modal {
    transition: none;
  }

  .fi-rr-spinner {
    animation: none;
  }
}
</style>