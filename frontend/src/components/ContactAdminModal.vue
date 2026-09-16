<script setup>
import { reactive, ref } from 'vue'

// SCRUM-546 : interface uniquement. Aucun appel réseau.
const dialog = ref(null)
const feedback = ref('')
const form = reactive({ fullName: '', email: '', subject: '', message: '' })

function open() {
  feedback.value = ''
  if (!dialog.value.open) dialog.value.showModal()
}
function close() {
  dialog.value.close()
}
function submit() {
  // La validation HTML vérifie aussi le format de l'adresse e-mail.
  if (Object.values(form).some(value => !value.trim())) {
    feedback.value = 'Veuillez compléter tous les champs, sans utiliser uniquement des espaces.'
    return
  }
  // À remplacer par le résultat du service lorsque le backend sera disponible.
  // Ne pas simuler une confirmation d'envoi.
  feedback.value = "L'envoi est momentanément indisponible. Votre message n'a pas été envoyé. Vous pouvez contacter admin@vitalis.ma depuis votre messagerie."
}
defineExpose({ open })
</script>

<template>
  <Teleport to="body">
    <dialog ref="dialog" class="contact-admin" aria-labelledby="contact-admin-title"
      aria-describedby="contact-admin-description">
      <div class="contact-admin__header">
        <div>
          <span class="contact-admin__eyebrow">VITALIS · Assistance</span>
          <h2 id="contact-admin-title">Contacter l'administrateur</h2>
        </div>
        <button type="button" class="contact-admin__close" aria-label="Fermer" @click="close">×</button>
      </div>
      <p id="contact-admin-description">Besoin d'un compte ou d'aide pour vous connecter ? Écrivez à notre administrateur.</p>
      <p class="contact-admin__recipient">Destinataire : <strong>admin@vitalis.ma</strong></p>
      <form @submit.prevent="submit">
        <p class="contact-admin__required">Tous les champs sont obligatoires.</p>
        <div class="contact-admin__field">
          <label for="contact-admin-name">Nom complet</label>
          <input id="contact-admin-name" v-model="form.fullName" name="fullName" autocomplete="name"
            maxlength="120" placeholder="Votre nom et prénom" required autofocus />
        </div>
        <div class="contact-admin__field">
          <label for="contact-admin-email">Adresse e-mail</label>
          <input id="contact-admin-email" v-model.trim="form.email" name="email" type="email"
            autocomplete="email" maxlength="254" placeholder="vous@exemple.ma" required />
        </div>
        <div class="contact-admin__field">
          <label for="contact-admin-subject">Objet</label>
          <input id="contact-admin-subject" v-model="form.subject" name="subject" maxlength="160"
            placeholder="Ex. : Demande de création de compte" required />
        </div>
        <div class="contact-admin__field">
          <label for="contact-admin-message">Message</label>
          <textarea id="contact-admin-message" v-model="form.message" name="message" rows="4"
            maxlength="2000" placeholder="Décrivez votre demande…" required />
          <span class="contact-admin__counter">{{ form.message.length }} / 2000</span>
        </div>
        <p v-if="feedback" class="contact-admin__feedback" role="alert">{{ feedback }}</p>
        <div class="contact-admin__actions">
          <button type="button" class="contact-admin__cancel" @click="close">Annuler</button>
          <button type="submit" class="contact-admin__send">Envoyer</button>
        </div>
      </form>
    </dialog>
  </Teleport>
</template>

<style scoped>
.contact-admin {
  box-sizing: border-box;
  width: min(540px, calc(100vw - 32px));
  max-height: calc(100dvh - 32px);
  overflow-y: auto;
  margin: auto;
  padding: 28px;
  border: 0;
  border-radius: 18px;
  background: #fff;
  color: #243b35;
  font-family: Arial, Helvetica, sans-serif;
  box-shadow: 0 24px 80px #102e3540;
}
.contact-admin::backdrop { background: #102e3599; }
.contact-admin__header { display: flex; align-items: flex-start; gap: 16px; justify-content: space-between; }
.contact-admin__eyebrow { color: #176b56; font-size: 12px; font-weight: 700; letter-spacing: .05em; }
.contact-admin h2 { font-size: 23px; line-height: 1.3; margin: 8px 0 0; }
.contact-admin p { line-height: 1.5; }
.contact-admin__close { flex-shrink: 0; border: 0; background: #f0f5f3; border-radius: 50%; width: 36px; height: 36px; font-size: 26px; color: #243b35; cursor: pointer; }
.contact-admin__recipient { background: #edf7f3; padding: 12px; border-radius: 8px; font-size: 14px; overflow-wrap: anywhere; }
.contact-admin__required { font-size: 12px; color: #526660; }
.contact-admin__field { display: grid; gap: 7px; margin: 16px 0; }
.contact-admin label { font-size: 14px; font-weight: 600; }
.contact-admin input, .contact-admin textarea { box-sizing: border-box; width: 100%; padding: 12px; border: 1px solid #9bafa7; border-radius: 8px; font: inherit; background: #fff; color: #243b35; }
.contact-admin textarea { resize: vertical; min-height: 104px; }
.contact-admin__counter { text-align: right; font-size: 12px; color: #526660; }
.contact-admin__feedback { background: #fff4df; color: #69470c; padding: 12px; border-radius: 8px; font-size: 14px; }
.contact-admin__actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; flex-wrap: wrap; }
.contact-admin__actions button { padding: 11px 22px; border: 1px solid #176b56; border-radius: 8px; font: inherit; font-weight: 600; cursor: pointer; }
.contact-admin__cancel { color: #176b56; background: #fff; }
.contact-admin__send { color: #fff; background: #176b56; }
.contact-admin :focus-visible { outline: 3px solid #3586ba; outline-offset: 3px; }
@media (max-width: 480px) { .contact-admin { padding: 20px; } .contact-admin h2 { font-size: 20px; } }
.contact-admin input,
.contact-admin textarea {
  background: #e8f0fe;
  border: 1px solid #d3ddef;
  border-radius: 7px;
  padding: 16px;
  color: #142b4a;
}

.contact-admin label {
  color: #142b4a;
}

.contact-admin__send {
  background: linear-gradient(180deg, #0875ed, #0751bc);
  border-color: #0864d3;
  box-shadow: 0 6px 16px #0968db26;
}

.contact-admin__cancel {
  color: #0864d3;
  border-color: #0864d3;
}

.contact-admin__eyebrow {
  color: #0864d3;
}

.contact-admin__recipient {
  background: #e8f0fe;
}
</style>
