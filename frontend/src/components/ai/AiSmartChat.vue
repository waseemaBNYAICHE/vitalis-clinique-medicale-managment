<script setup>
import { nextTick, ref } from 'vue'
import api from '../../api.js'
import aiSmartRobot from '../../assets/ai-smart-robot.png'
const isOpen = ref(false)
const message = ref('')
const loading = ref(false)
const messagesContainer = ref(null)

const messages = ref([
  {
    role: 'assistant',
    text: 'Bonjour 👋 Je suis AI SMART, votre assistant Vitalis. Comment puis-je vous aider ?',
  },
])

const scrollToBottom = async () => {
  await nextTick()

  if (messagesContainer.value) {
    messagesContainer.value.scrollTop =
      messagesContainer.value.scrollHeight
  }
}

const sendMessage = async () => {
  const text = message.value.trim()

  if (!text || loading.value) return

  messages.value.push({
    role: 'user',
    text,
  })

  message.value = ''
  loading.value = true

  await scrollToBottom()

  try {
    const response = await api.post('/ai/chat', {
      message: text,
    })

    messages.value.push({
      role: 'assistant',
      text: response.data.reply,
    })
  } catch (error) {
    messages.value.push({
      role: 'assistant',
      text:
        error.response?.data?.message ||
        'AI SMART est temporairement indisponible. Veuillez réessayer.',
    })
  } finally {
    loading.value = false
    await scrollToBottom()
  }
}
</script>

<template>
  <div class="ai-smart">

    <!-- Bouton flottant AI SMART -->
    <button
      class="smart-button"
      type="button"
      aria-label="Ouvrir AI SMART"
      @click="isOpen = !isOpen"
    >
      <img
        :src="aiSmartRobot"
        alt="AI SMART"
        class="robot-image"
      />
    </button>

    <!-- Fenêtre de conversation -->
    <div
      v-if="isOpen"
      class="chat-window"
    >
      <header class="chat-header">
        <div class="identity">
          <div class="avatar">
            <img
              :src="aiSmartRobot"
              alt="AI SMART"
            />
          </div>

          <div>
            <strong>AI SMART</strong>
            <small>
              <span class="online-dot"></span>
              Assistant Vitalis
            </small>
          </div>
        </div>

        <button
          class="close-button"
          type="button"
          aria-label="Fermer"
          @click="isOpen = false"
        >
          ×
        </button>
      </header>

      <!-- Messages -->
      <div
        ref="messagesContainer"
        class="messages"
      >
        <div
          v-for="(item, index) in messages"
          :key="index"
          class="message-row"
          :class="item.role"
        >
          <div
            v-if="item.role === 'assistant'"
            class="mini-avatar"
          >
            <img
              :src="aiSmartRobot"
              alt=""
            />
          </div>

          <div class="bubble">
            {{ item.text }}
          </div>
        </div>

        <!-- AI SMART écrit -->
        <div
          v-if="loading"
          class="message-row assistant"
        >
          <div class="mini-avatar">
            <img
              :src="aiSmartRobot"
              alt=""
            />
          </div>

          <div class="bubble typing">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      </div>

      <!-- Avertissement médical -->
      <div class="medical-note">
        AI SMART fournit des informations générales et ne remplace pas un médecin.
      </div>

      <!-- Zone de saisie -->
      <form
        class="input-area"
        @submit.prevent="sendMessage"
      >
        <input
          v-model="message"
          type="text"
          maxlength="1000"
          placeholder="Écrivez votre question..."
          :disabled="loading"
        />

        <button
          type="submit"
          :disabled="loading || !message.trim()"
          aria-label="Envoyer"
        >
          ➤
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.ai-smart {
  --primary: #2447e8;
  --primary-dark: #1734bd;
  --turquoise: #32d5df;
  --navy: #10234a;
  --muted: #72809f;
  --border: #dfe6f2;
  --light-blue: #f4f8ff;

  position: fixed;
  right: 24px;
  bottom: 112px;
  z-index: 10000;
  color: var(--navy);
  font-family: inherit;
}

.smart-button {
  position: relative;
  display: grid;
  width: 68px;
  height: 68px;
  margin-left: auto;
  place-items: center;
  border: 4px solid white;
  border-radius: 50%;
  background: linear-gradient(
    135deg,
    var(--primary),
    var(--turquoise)
  );
  box-shadow: 0 12px 30px rgb(36 71 232 / 32%);
  cursor: pointer;
  transition: 0.25s ease;
}

.smart-button:hover {
  transform: translateY(-3px) scale(1.04);
}

.robot {
  font-size: 34px;
}

.doctor-badge {
  position: absolute;
  right: -2px;
  bottom: 1px;
  display: grid;
  width: 23px;
  height: 23px;
  place-items: center;
  border: 2px solid white;
  border-radius: 50%;
  color: white;
  background: #19b987;
  font-size: 16px;
  font-weight: 900;
}

.chat-window {
  position: absolute;

  /* النافذة تفتح إلى يسار الروبوت */
  right: 80px;
  bottom: -80px;

  display: flex;
  flex-direction: column;

  width: 370px;
  height: 520px;

  max-width: calc(100vw - 120px);
  max-height: calc(100vh - 40px);

  overflow: hidden;

  border: 1px solid rgb(36 71 232 / 15%);
  border-radius: 22px;

  background: white;
  box-shadow: 0 24px 70px rgb(11 35 75 / 28%);

  z-index: 10001;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 17px;
  color: white;
  background: linear-gradient(
    135deg,
    var(--primary-dark),
    var(--primary),
    var(--turquoise)
  );
}

.identity {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  display: grid;
  width: 42px;
  height: 42px;
  place-items: center;
  border: 2px solid rgb(255 255 255 / 60%);
  border-radius: 50%;
  background: white;
  font-size: 23px;
}

.identity strong {
  display: block;
  font-size: 15px;
}

.identity small {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-top: 3px;
  opacity: 0.9;
  font-size: 11px;
}

.online-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #73f1b8;
}

.close-button {
  border: 0;
  color: white;
  background: transparent;
  font-size: 27px;
  cursor: pointer;
}

.messages {
  flex: 1;
  overflow-y: auto;
  padding: 17px 14px;
  background: var(--light-blue);
}

.message-row {
  display: flex;
  align-items: flex-end;
  gap: 7px;
  margin-bottom: 13px;
}

.message-row.user {
  justify-content: flex-end;
}

.mini-avatar {
  display: grid;
  width: 28px;
  height: 28px;
  flex: 0 0 28px;
  place-items: center;
  border-radius: 50%;
  background: white;
  box-shadow: 0 3px 10px rgb(16 35 74 / 12%);
  font-size: 15px;
}

.bubble {
  max-width: 78%;
  padding: 10px 13px;
  border-radius: 15px 15px 15px 4px;
  background: white;
  box-shadow: 0 3px 12px rgb(16 35 74 / 7%);
  font-size: 13px;
  line-height: 1.55;
  white-space: pre-wrap;
}

.user .bubble {
  border-radius: 15px 15px 4px 15px;
  color: white;
  background: var(--primary);
}

.medical-note {
  padding: 7px 14px;
  color: var(--muted);
  background: #fff8df;
  font-size: 9px;
  text-align: center;
}

.input-area {
  display: flex;
  gap: 8px;
  padding: 12px;
  border-top: 1px solid var(--border);
  background: white;
}

.input-area input {
  min-width: 0;
  flex: 1;
  padding: 11px 13px;
  border: 1px solid var(--border);
  border-radius: 12px;
  outline: none;
  color: var(--navy);
  font: inherit;
  font-size: 12px;
}

.input-area input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgb(36 71 232 / 8%);
}

.input-area button {
  width: 42px;
  border: 0;
  border-radius: 12px;
  color: white;
  background: var(--primary);
  cursor: pointer;
  font-size: 17px;
}

.input-area button:disabled {
  cursor: not-allowed;
  opacity: 0.45;
}

.typing {
  display: flex;
  gap: 4px;
  padding: 14px;
}

.typing span {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--muted);
  animation: typing 1s infinite alternate;
}

.typing span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  to {
    opacity: 0.25;
    transform: translateY(-3px);
  }
}

@media (max-width: 600px) {
  .ai-smart {
    right: 14px;
    bottom: 100px;
  }

  .chat-window {
    position: fixed;
    right: 12px;
    bottom: 90px;
    left: 12px;
    top: auto;

    width: auto;
    height: 70vh;
    max-height: 600px;
  }
}
.robot-image {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.avatar img,
.mini-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}
</style>