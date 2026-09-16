import { ref } from 'vue'

const notifications = ref([])
let notificationId = 0

export function useNotification() {
  const removeNotification = (id) => {
    notifications.value = notifications.value.filter(
      (notification) => notification.id !== id
    )
  }

  const showNotification = ({
    type = 'info',
    title = 'Information',
    message,
    duration = 4000,
  }) => {
    const id = ++notificationId

    notifications.value.push({
      id,
      type,
      title,
      message,
      duration,
    })

    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, duration)
    }

    return id
  }

  const success = (message, title = 'Succès') => {
    return showNotification({
      type: 'success',
      title,
      message,
    })
  }

  const error = (message, title = 'Erreur') => {
    return showNotification({
      type: 'error',
      title,
      message,
    })
  }

  const warning = (message, title = 'Attention') => {
    return showNotification({
      type: 'warning',
      title,
      message,
    })
  }

  const info = (message, title = 'Information') => {
    return showNotification({
      type: 'info',
      title,
      message,
    })
  }

  return {
    notifications,
    showNotification,
    removeNotification,
    success,
    error,
    warning,
    info,
  }
}