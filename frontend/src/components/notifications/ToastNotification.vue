<script setup>
import { useNotification } from '../../composables/useNotification.js'

const {
  notifications,
  removeNotification,
} = useNotification()

const notificationIcons = {
  success: 'fi-rr-check',
  error: 'fi-rr-cross',
  warning: 'fi-rr-exclamation',
  info: 'fi-rr-info',
}

const getIcon = (type) => {
  return notificationIcons[type] || notificationIcons.info
}
</script>

<template>
  <div
    class="toast-container"
    aria-live="polite"
    aria-atomic="true"
  >
    <TransitionGroup name="toast">
      <article
        v-for="notification in notifications"
        :key="notification.id"
        class="toast-notification"
        :class="`toast-${notification.type}`"
      >
        <div class="toast-icon">
          <i
            class="fi"
            :class="getIcon(notification.type)"
            aria-hidden="true"
          ></i>
        </div>

        <div class="toast-content">
          <strong class="toast-title">
            {{ notification.title }}
          </strong>

          <p class="toast-message">
            {{ notification.message }}
          </p>
        </div>

        <button
          type="button"
          class="toast-close"
          aria-label="Fermer la notification"
          @click="removeNotification(notification.id)"
        >
          <i class="fi fi-rr-cross-small"></i>
        </button>

        <div
          v-if="notification.duration > 0"
          class="toast-progress"
          :style="{
            animationDuration: `${notification.duration}ms`,
          }"
        ></div>
      </article>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-container {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 12px;
  pointer-events: none;
}

.toast-notification {
  --toast-color: #3b82f6;
  position: relative;
  display: grid;
  grid-template-columns: 40px 1fr 28px;
  gap: 12px;
  align-items: start;
  width: 370px;
  padding: 16px;
  overflow: hidden;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-left: 5px solid var(--toast-color);
  border-radius: 14px;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.16);
  pointer-events: auto;
}

.toast-success {
  --toast-color: #22c55e;
}

.toast-error {
  --toast-color: #ef4444;
}

.toast-warning {
  --toast-color: #f59e0b;
}

.toast-info {
  --toast-color: #3b82f6;
}

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  color: #ffffff;
  background: var(--toast-color);
  border-radius: 50%;
}

.toast-icon i {
  display: flex;
  font-size: 17px;
}

.toast-content {
  min-width: 0;
}

.toast-title {
  display: block;
  margin-bottom: 5px;
  color: #1e293b;
  font-size: 15px;
  font-weight: 600;
}

.toast-message {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.5;
}

.toast-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  padding: 0;
  color: #64748b;
  background: transparent;
  border: 0;
  border-radius: 6px;
  cursor: pointer;
}

.toast-close:hover {
  color: #1e293b;
  background: #f1f5f9;
}

.toast-progress {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 4px;
  background: var(--toast-color);
  transform-origin: left;
  animation-name: toast-progress;
  animation-timing-function: linear;
  animation-fill-mode: forwards;
}

/* دخول وخروج الإشعار */

.toast-enter-active,
.toast-leave-active {
  transition:
    opacity 0.35s ease,
    transform 0.35s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(110%);
}

/* حركة أيقونة النجاح */

.toast-success .toast-icon {
  animation: success-icon 0.65s ease;
}

/* حركة أيقونة الخطأ */

.toast-error .toast-icon {
  animation: error-icon 0.45s ease;
}

/* حركة أيقونة التحذير */

.toast-warning .toast-icon {
  animation: warning-icon 1.4s ease-in-out infinite;
}

/* حركة أيقونة المعلومات */

.toast-info .toast-icon {
  animation: info-icon 1.8s ease-in-out infinite;
}

@keyframes toast-progress {
  from {
    transform: scaleX(1);
  }

  to {
    transform: scaleX(0);
  }
}

@keyframes success-icon {
  0% {
    transform: scale(0.4) rotate(-20deg);
  }

  70% {
    transform: scale(1.12) rotate(5deg);
  }

  100% {
    transform: scale(1) rotate(0);
  }
}

@keyframes error-icon {
  0%,
  100% {
    transform: translateX(0);
  }

  30% {
    transform: translateX(-3px);
  }

  60% {
    transform: translateX(3px);
  }
}

@keyframes warning-icon {
  0%,
  100% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.08);
  }
}

@keyframes info-icon {
  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-2px);
  }
}

@media (max-width: 480px) {
  .toast-container {
    top: 12px;
    right: 12px;
    left: 12px;
  }

  .toast-notification {
    width: auto;
  }
}

@media (prefers-reduced-motion: reduce) {
  .toast-notification,
  .toast-icon,
  .toast-progress {
    animation: none !important;
    transition: none !important;
  }
}
</style>