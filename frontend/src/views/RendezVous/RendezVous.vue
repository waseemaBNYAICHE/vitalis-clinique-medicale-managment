<script setup>
import { nextTick, onMounted, ref } from 'vue'
import gsap from 'gsap'
import api, { messageErreur } from '../../api'
import { peutAction } from '../../actions.js'
import { useNotification } from '../../composables/useNotification.js'
import RendezVousFormModal from './RendezVousFormModal.vue'
import RendezVousConfirmModal from './RendezVousConfirmModal.vue'
import '../../styles/rendez-vous.css'

const { success, error: notifierErreur } = useNotification()

const search = ref('')
const selectedStatus = ref('')

const rendezVousListe = ref([])
const loading = ref(false)

const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const from = ref(0)
const to = ref(0)

const modalOuverte = ref(false)
const modalMode = ref('view')
const rendezVousSelectionne = ref(null)

// SCRUM-614 - Confirmation de suppression/annulation, sur le meme patron
// que DeleteUserModal : plus de window.confirm() natif.
const confirmModalOuverte = ref(false)
const confirmAction = ref(null) // 'delete' | 'annuler'
const confirmCible = ref(null)
const confirmEnCours = ref(false)

/**
 * SCRUM-614 - GET /rendez-vous ne prend en charge que la pagination
 * (?page=N), pas de filtres serveur (recherche, medecin, date, statut).
 * La recherche et le filtre statut ci-dessous ne s'appliquent donc qu'a la
 * page actuellement chargee, pas a l'ensemble des rendez-vous - limitation
 * connue, a lever cote backend dans un ticket ulterieur.
 */
async function chargerRendezVous(page = 1) {
  loading.value = true

  try {
    const reponse = await api.get('/rendez-vous', { params: { page } })
    const donnees = reponse.data?.rendez_vous

    rendezVousListe.value = donnees?.data ?? []
    currentPage.value = donnees?.current_page ?? 1
    lastPage.value = donnees?.last_page ?? 1
    total.value = donnees?.total ?? 0
    from.value = donnees?.from ?? 0
    to.value = donnees?.to ?? 0
  } catch (error) {
    notifierErreur(messageErreur(error, 'Impossible de charger les rendez-vous.'))
  } finally {
    loading.value = false
  }
}

function rendezVousAffiches() {
  const mot = search.value.trim().toLowerCase()

  return rendezVousListe.value.filter((rdv) => {
    const nomPatient = `${rdv.patient?.nom ?? ''} ${rdv.patient?.prenom ?? ''}`.toLowerCase()
    const nomMedecin = `${rdv.medecin?.nom ?? ''} ${rdv.medecin?.prenom ?? ''}`.toLowerCase()

    const matchRecherche =
      !mot ||
      nomPatient.includes(mot) ||
      nomMedecin.includes(mot) ||
      (rdv.motif ?? '').toLowerCase().includes(mot)

    const matchStatut = !selectedStatus.value || rdv.statut === selectedStatus.value

    return matchRecherche && matchStatut
  })
}

function goToPage(page) {
  if (page >= 1 && page <= lastPage.value) {
    chargerRendezVous(page)
  }
}

function resetFilters() {
  search.value = ''
  selectedStatus.value = ''
}

function statusClass(status) {
  if (status === 'Confirmé') return 'status-confirmed'
  if (status === 'En attente') return 'status-pending'
  if (status === 'En cours') return 'status-progress'
  if (status === 'Annulé') return 'status-cancelled'
  return ''
}

function initiales(nom, prenom) {
  return `${(nom || '').charAt(0)}${(prenom || '').charAt(0)}`.toUpperCase()
}

// --- Actions ---

function createAppointment() {
  rendezVousSelectionne.value = null
  modalMode.value = 'create'
  modalOuverte.value = true
}

function viewAppointment(item) {
  rendezVousSelectionne.value = item
  modalMode.value = 'view'
  modalOuverte.value = true
}

function editAppointment(item) {
  rendezVousSelectionne.value = item
  modalMode.value = 'edit'
  modalOuverte.value = true
}

function fermerModal() {
  modalOuverte.value = false
  rendezVousSelectionne.value = null
}

function sauvegarde() {
  const estCreation = modalMode.value === 'create'
  fermerModal()
  chargerRendezVous(currentPage.value)
  success(estCreation ? 'Rendez-vous créé avec succès.' : 'Rendez-vous modifié avec succès.')
}

function demanderSuppression(item) {
  confirmAction.value = 'delete'
  confirmCible.value = item
  confirmModalOuverte.value = true
}

function demanderAnnulation(item) {
  confirmAction.value = 'annuler'
  confirmCible.value = item
  confirmModalOuverte.value = true
}

function fermerConfirmModal() {
  if (confirmEnCours.value) return
  confirmModalOuverte.value = false
  confirmAction.value = null
  confirmCible.value = null
}

async function executerConfirmation() {
  const item = confirmCible.value
  confirmEnCours.value = true

  try {
    if (confirmAction.value === 'delete') {
      await api.delete(`/rendez-vous/${item.id_rendez_vous}`)
      success('Rendez-vous supprimé avec succès.')
    } else if (confirmAction.value === 'annuler') {
      await api.patch(`/rendez-vous/${item.id_rendez_vous}/annuler`)
      success('Rendez-vous annulé avec succès.')
    }

    confirmModalOuverte.value = false
    confirmAction.value = null
    confirmCible.value = null
    chargerRendezVous(currentPage.value)
  } catch (error) {
    const secours = confirmAction.value === 'delete'
      ? 'Impossible de supprimer ce rendez-vous.'
      : "Impossible d'annuler ce rendez-vous."

    notifierErreur(messageErreur(error, secours))
  } finally {
    confirmEnCours.value = false
  }
}

onMounted(async () => {
  await chargerRendezVous(1)

  await nextTick()

  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })

  tl.from('.rv-page-title', { y: 18, opacity: 0, duration: 0.55 })
    .from('.rv-filter-card', { y: 18, opacity: 0, duration: 0.45 }, '-=0.25')
    .from('.rv-table-card', { y: 20, opacity: 0, duration: 0.5 }, '-=0.2')
})
</script>

<template>
  <section class="rendezvous-page">
    <div class="rv-page-title">
      <div>
        <h1>Gestion des rendez-vous</h1>
        <p>Planifiez et suivez tous les rendez-vous de la clinique en toute simplicité.</p>
      </div>
    </div>

    <!-- FILTRES -->
    <div class="rv-filter-card">
      <div class="rv-filter-group search-group">
        <label>Rechercher (page actuelle)</label>
        <div class="rv-input-with-icon">
          <i class="fi fi-rr-search"></i>
          <input
            v-model="search"
            type="text"
            placeholder="Nom du patient, médecin, motif..."
          />
        </div>
      </div>

      <div class="rv-filter-group">
        <label>Statut</label>
        <select v-model="selectedStatus">
          <option value="">Tous les statuts</option>
          <option value="Confirmé">Confirmé</option>
          <option value="En attente">En attente</option>
          <option value="En cours">En cours</option>
          <option value="Annulé">Annulé</option>
        </select>
      </div>

      <button class="rv-reset-button" @click="resetFilters">
        <i class="fi fi-rr-refresh"></i>
      </button>
    </div>

    <!-- LISTE -->
    <div class="rv-table-card">
      <div class="rv-table-heading">
        <div>
          <h2>Liste des rendez-vous</h2>
          <p>Consultez et gérez les rendez-vous programmés</p>
        </div>

        <button
          v-if="peutAction('rendezVous.creer')"
          class="btn-new-appointment"
          @click="createAppointment"
        >
          <i class="fi fi-rr-plus"></i>
          Nouveau rendez-vous
        </button>
      </div>

      <div v-if="loading" class="rv-empty">Chargement...</div>

      <div v-else class="rv-table-wrapper">
        <table class="rv-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Patient</th>
              <th>Médecin</th>
              <th>Date et heure</th>
              <th>Motif</th>
              <th>Statut</th>
              <th class="action-column">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="rdv in rendezVousAffiches()" :key="rdv.id_rendez_vous">
              <td class="appointment-number">{{ rdv.id_rendez_vous }}</td>

              <td>
                <div class="person-cell">
                  <div class="person-avatar patient-avatar">
                    {{ initiales(rdv.patient?.nom, rdv.patient?.prenom) }}
                  </div>
                  <div class="person-details">
                    <strong>{{ rdv.patient?.nom }} {{ rdv.patient?.prenom }}</strong>
                    <span>ID: {{ rdv.id_patient }}</span>
                  </div>
                </div>
              </td>

              <td>
                <div class="person-cell">
                  <div class="person-avatar doctor-avatar">
                    {{ initiales(rdv.medecin?.nom, rdv.medecin?.prenom) }}
                  </div>
                  <div class="person-details">
                    <strong>Dr. {{ rdv.medecin?.nom }} {{ rdv.medecin?.prenom }}</strong>
                  </div>
                </div>
              </td>

              <td>
                <div class="date-time">
                  <div><i class="fi fi-rr-calendar"></i> {{ rdv.date_rendez_vous }}</div>
                  <span><i class="fi fi-rr-clock"></i> {{ (rdv.heure_debut || '').slice(0, 5) }}</span>
                </div>
              </td>

              <td class="motif-cell">{{ rdv.motif }}</td>

              <td>
                <span class="status-badge" :class="statusClass(rdv.statut)">
                  {{ rdv.statut }}
                </span>
              </td>

              <td>
                <div class="rv-actions">
                  <button class="action-button action-view" title="Voir" @click="viewAppointment(rdv)">
                    <i class="fi fi-rr-eye"></i>
                  </button>

                  <button
                    v-if="peutAction('rendezVous.modifier')"
                    class="action-button action-edit"
                    title="Modifier"
                    @click="editAppointment(rdv)"
                  >
                    <i class="fi fi-rr-pencil"></i>
                  </button>

                  <button
                    v-if="peutAction('rendezVous.annuler') && !['Annulé', 'Terminé'].includes(rdv.statut)"
                    class="action-button action-edit"
                    title="Annuler"
                    @click="demanderAnnulation(rdv)"
                  >
                    <i class="fi fi-rr-cross-circle"></i>
                  </button>

                  <button
                    v-if="peutAction('rendezVous.supprimer')"
                    class="action-button action-delete"
                    title="Supprimer"
                    @click="demanderSuppression(rdv)"
                  >
                    <i class="fi fi-rr-trash"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!loading && rendezVousAffiches().length === 0">
              <td colspan="7" class="rv-empty">Aucun rendez-vous trouvé.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="rv-table-footer">
        <span>
          Affichage de <strong>{{ from }}</strong> à <strong>{{ to }}</strong>
          sur <strong>{{ total }}</strong> rendez-vous
        </span>

        <div class="rv-pagination">
          <button class="page-button" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">‹</button>
          <button
            v-for="page in lastPage"
            :key="page"
            class="page-button"
            :class="{ active: page === currentPage }"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
          <button class="page-button" :disabled="currentPage === lastPage" @click="goToPage(currentPage + 1)">›</button>
        </div>
      </div>
    </div>

    <RendezVousFormModal
      v-if="modalOuverte"
      :mode="modalMode"
      :rendez-vous="rendezVousSelectionne"
      @close="fermerModal"
      @saved="sauvegarde"
    />

    <RendezVousConfirmModal
      :open="confirmModalOuverte"
      :loading="confirmEnCours"
      :title="confirmAction === 'delete' ? 'Supprimer le rendez-vous ?' : 'Annuler le rendez-vous ?'"
      :message="`${confirmAction === 'delete' ? 'Voulez-vous vraiment supprimer' : 'Voulez-vous vraiment annuler'} le rendez-vous de ${confirmCible?.patient?.nom ?? ''} ${confirmCible?.patient?.prenom ?? ''} ?`"
      :warning-text="confirmAction === 'delete' ? 'Cette action est irréversible.' : 'Le patient et le médecin seront notifiés.'"
      :confirm-label="confirmAction === 'delete' ? 'Supprimer' : 'Annuler le RDV'"
      @close="fermerConfirmModal"
      @confirm="executerConfirmation"
    />
  </section>
</template>