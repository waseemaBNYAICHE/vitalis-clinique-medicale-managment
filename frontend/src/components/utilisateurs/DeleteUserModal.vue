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

const emit = defineEmits([
  'close',
  'confirm',
])
</script>

<template>
  <Teleport to="body">
    <Transition name="delete-modal">
      <div
        v-if="open"
        class="delete-overlay"
        @click.self="
          !loading && emit('close')
        "
      >
        <div
          class="delete-modal"
          role="alertdialog"
          aria-modal="true"
          aria-labelledby="delete-title"
          aria-describedby="delete-description"
        >
          <div class="delete-icon">
            <i class="fi fi-rr-trash"></i>
          </div>

          <h2 id="delete-title">
            Supprimer l’utilisateur ?
          </h2>

          <p id="delete-description">
            Voulez-vous vraiment supprimer
            <strong>{{ user?.name }}</strong> ?
          </p>

          <p class="delete-warning">
            Cette action est irréversible.
          </p>

          <div class="delete-actions">
            <button
              type="button"
              class="cancel-button"
              :disabled="loading"
              @click="emit('close')"
            >
              Annuler
            </button>

            <button
              type="button"
              class="confirm-button"
              :disabled="loading"
              @click="emit('confirm')"
            >
              <i
                class="fi"
                :class="
                  loading
                    ? 'fi-rr-spinner'
                    : 'fi-rr-trash'
                "
              ></i>

              {{
                loading
                  ? 'Suppression...'
                  : 'Supprimer'
              }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.delete-overlay {
  position: fixed;
  inset: 0;
  z-index: 9500;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(4px);
}

.delete-modal {
  width: min(440px, 100%);
  padding: 28px;
  text-align: center;
  background: #ffffff;
  border-radius: 18px;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.25);
}

.delete-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 62px;
  height: 62px;
  margin: 0 auto 18px;
  color: #ffffff;
  font-size: 24px;
  background: #ef4444;
  border-radius: 50%;
  animation: delete-icon 0.6s ease;
}

.delete-modal h2 {
  margin: 0 0 10px;
  color: #1e293b;
  font-size: 21px;
}

.delete-modal p {
  margin: 0;
  color: #64748b;
  line-height: 1.6;
}

.delete-modal p strong {
  color: #1e293b;
}

.delete-warning {
  margin-top: 8px !important;
  color: #ef4444 !important;
  font-size: 13px;
}

.delete-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 24px;
}

.cancel-button,
.confirm-button {
  min-width: 125px;
  min-height: 43px;
  padding: 10px 18px;
  border-radius: 9px;
  cursor: pointer;
}

.cancel-button {
  color: #475569;
  background: #ffffff;
  border: 1px solid #cbd5e1;
}

.confirm-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: #ffffff;
  background: #ef4444;
  border: 1px solid #ef4444;
}

.confirm-button:hover:not(:disabled) {
  background: #dc2626;
  border-color: #dc2626;
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.65;
}

.fi-rr-spinner {
  animation: spinner 0.8s linear infinite;
}

.delete-modal-enter-active,
.delete-modal-leave-active {
  transition: opacity 0.25s ease;
}

.delete-modal-enter-active .delete-modal,
.delete-modal-leave-active .delete-modal {
  transition: transform 0.25s ease;
}

.delete-modal-enter-from,
.delete-modal-leave-to {
  opacity: 0;
}

.delete-modal-enter-from .delete-modal,
.delete-modal-leave-to .delete-modal {
  transform: translateY(16px) scale(0.96);
}

@keyframes delete-icon {
  0% {
    opacity: 0;
    transform: scale(0.4) rotate(-15deg);
  }

  70% {
    transform: scale(1.1) rotate(5deg);
  }

  100% {
    opacity: 1;
    transform: scale(1) rotate(0);
  }
}

@keyframes spinner {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 480px) {
  .delete-actions {
    flex-direction: column-reverse;
  }

  .cancel-button,
  .confirm-button {
    width: 100%;
  }
}
</style>