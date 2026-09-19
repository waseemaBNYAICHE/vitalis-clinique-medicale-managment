<template>
  <div class="facturation-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->
    <div class="page-header">

      <div class="header-left">

        <div class="page-title">

          <div class="page-title-icon">
            <i class="fi fi-rr-receipt"></i>
          </div>

          <div>
            <h1>Facturation</h1>

            <p>
              Gérez les factures et les paiements des patients
              en toute simplicité.
            </p>
          </div>

        </div>

      </div>


      <!-- BOUTON EN HAUT COMME HOSPITALISATIONS -->
      <button
        class="primary-btn header-create-btn"
        @click="openCreateModal"
      >
        <i class="fi fi-rr-plus"></i>
        Nouvelle facture
      </button>

    </div>


    <!-- =====================================================
         STATISTIQUES
    ====================================================== -->
    <div class="stats-grid">

      <div class="stat-card">

        <div class="stat-icon blue">
          <i class="fi fi-rr-receipt"></i>
        </div>

        <div class="stat-content">
          <strong>{{ factures.length }}</strong>
          <span>Factures totales</span>
        </div>

      </div>


      <div class="stat-card">

        <div class="stat-icon green">
          <i class="fi fi-rr-check-circle"></i>
        </div>

        <div class="stat-content">
          <strong>{{ paidCount }}</strong>
          <span>Factures payées</span>
        </div>

      </div>


      <div class="stat-card">

        <div class="stat-icon orange">
          <i class="fi fi-rr-clock"></i>
        </div>

        <div class="stat-content">
          <strong>{{ pendingCount }}</strong>
          <span>En attente</span>
        </div>

      </div>


      <div class="stat-card">

        <div class="stat-icon red">
          <i class="fi fi-rr-money-bill-wave"></i>
        </div>

        <div class="stat-content">
          <strong>{{ formatMoney(totalRemaining) }}</strong>
          <span>Reste à payer</span>
        </div>

      </div>

    </div>


    <!-- =====================================================
         TABLE CARD
    ====================================================== -->
    <div class="facturation-card">


      <!-- ===================================================
           RECHERCHE
           Même disposition que Hospitalisations
      ==================================================== -->
      <div class="search-section">

        <div class="search-box">

          <i class="fi fi-rr-search"></i>

          <input
            v-model="search"
            type="text"
            placeholder="Rechercher une facture, un patient..."
          />

        </div>


        <!-- FILTRES -->
        <div class="filters">

          <select v-model="statusFilter">
            <option value="">
              Tous les statuts
            </option>

            <option value="Payée">
              Payée
            </option>

            <option value="Partiellement payée">
              Partiellement payée
            </option>

            <option value="En attente">
              En attente
            </option>
          </select>


          <select v-model="paymentFilter">
            <option value="">
              Tous les paiements
            </option>

            <option value="Espèces">
              Espèces
            </option>

            <option value="Carte bancaire">
              Carte bancaire
            </option>

            <option value="Virement">
              Virement
            </option>

            <option value="Chèque">
              Chèque
            </option>
          </select>


          <button
            class="search-btn"
            @click="currentPage = 1"
          >
            <i class="fi fi-rr-search"></i>
            Rechercher
          </button>


          <button
            class="reset-btn"
            @click="resetFilters"
          >
            <i class="fi fi-rr-refresh"></i>
            Réinitialiser
          </button>

        </div>

      </div>


      <!-- ===================================================
           TABLE
      ==================================================== -->
      <div class="table-wrapper">

        <table>

          <thead>
            <tr>
              <th>#</th>
              <th>N° FACTURE</th>
              <th>PATIENT</th>
              <th>DATE</th>
              <th>MONTANT</th>
              <th>PAYÉ</th>
              <th>RESTE</th>
              <th>STATUT</th>
              <th>ACTIONS</th>
            </tr>
          </thead>


          <tbody>

            <tr
              v-for="(facture, index) in paginatedFactures"
              :key="facture.id"
            >

              <!-- NUMERO -->
              <td class="row-number">
                {{ (currentPage - 1) * perPage + index + 1 }}
              </td>


              <!-- FACTURE -->
              <td>

                <div class="facture-cell">

                  <div class="small-facture-icon">
                    <i class="fi fi-rr-receipt"></i>
                  </div>

                  <div class="facture-info">

                    <strong>
                      {{ facture.numero }}
                    </strong>

                    <span>
                      {{ facture.description }}
                    </span>

                  </div>

                </div>

              </td>


              <!-- PATIENT -->
              <td>

                <div class="patient-cell">

                  <div class="avatar">
                    {{ initials(facture.patient) }}
                  </div>

                  <div>

                    <strong>
                      {{ facture.patient }}
                    </strong>

                    <span>
                      {{ facture.reference }}
                    </span>

                  </div>

                </div>

              </td>


              <!-- DATE -->
              <td>
                {{ formatDate(facture.date) }}
              </td>


              <!-- MONTANT -->
              <td>

                <strong class="money">
                  {{ formatMoney(facture.montant) }}
                </strong>

              </td>


              <!-- PAYE -->
              <td>

                <span class="paid-money">
                  {{ formatMoney(facture.paye) }}
                </span>

              </td>


              <!-- RESTE -->
              <td>

                <span
                  class="remaining-money"
                  :class="{ zero: remaining(facture) === 0 }"
                >
                  {{ formatMoney(remaining(facture)) }}
                </span>

              </td>


              <!-- STATUT -->
              <td>

                <span
                  class="status"
                  :class="statusClass(facture.statut)"
                >

                  <span class="status-dot"></span>

                  {{ facture.statut }}

                </span>

              </td>


              <!-- ACTIONS -->
              <td>

                <div class="actions">

                  <button
                    class="action-btn view"
                    title="Voir"
                    @click="openViewModal(facture)"
                  >
                    <i class="fi fi-rr-eye"></i>
                  </button>


                  <button
                    class="action-btn edit"
                    title="Modifier"
                    @click="openEditModal(facture)"
                  >
                    <i class="fi fi-rr-pencil"></i>
                  </button>


                  <button
                    v-if="facture.statut !== 'Payée'"
                    class="action-btn payment"
                    title="Enregistrer un paiement"
                    @click="openPaymentModal(facture)"
                  >
                    <i class="fi fi-rr-credit-card"></i>
                  </button>


                  <button
                    class="action-btn delete"
                    title="Supprimer"
                    @click="openDeleteModal(facture)"
                  >
                    <i class="fi fi-rr-trash"></i>
                  </button>

                </div>

              </td>

            </tr>


            <!-- EMPTY STATE -->
            <tr v-if="filteredFactures.length === 0">

              <td colspan="9">

                <div class="empty-state">

                  <div class="empty-state-icon">
                    <i class="fi fi-rr-receipt"></i>
                  </div>

                  <h3>Aucune facture trouvée</h3>

                  <p>
                    Aucune facture ne correspond à votre recherche.
                  </p>

                  <button
                    class="primary-btn"
                    @click="resetFilters"
                  >
                    Réinitialiser les filtres
                  </button>

                </div>

              </td>

            </tr>

          </tbody>

        </table>

      </div>


      <!-- ===================================================
           FOOTER / PAGINATION
      ==================================================== -->
      <div
        v-if="filteredFactures.length"
        class="table-footer"
      >

        <div class="table-info">

          Affichage de

          <strong>{{ firstItem }}</strong>

          à

          <strong>{{ lastItem }}</strong>

          sur

          <strong>{{ filteredFactures.length }}</strong>

          factures

        </div>


        <div class="pagination">

          <button
            :disabled="currentPage === 1"
            @click="previousPage"
          >
            <i class="fi fi-rr-angle-small-left"></i>
          </button>


          <button
            v-for="page in totalPages"
            :key="page"
            :class="{ active: currentPage === page }"
            @click="currentPage = page"
          >
            {{ page }}
          </button>


          <button
            :disabled="currentPage === totalPages"
            @click="nextPage"
          >
            <i class="fi fi-rr-angle-small-right"></i>
          </button>

        </div>

      </div>

    </div>


    <!-- =====================================================
         INFO BACKEND
    ====================================================== -->
    <div class="backend-info">

      <i class="fi fi-rr-info"></i>

      <div>

        <strong>Interface Frontend prête</strong>

        <span>
          La liaison avec les données réelles du Backend Laravel
          sera finalisée dans SCRUM-750 lorsque les API
          Facturation seront disponibles.
        </span>

      </div>

    </div>


    <!-- =====================================================
         MODAL CREATE / EDIT
    ====================================================== -->
    <div
      v-if="showFormModal"
      class="modal-overlay"
      @click.self="closeFormModal"
    >

      <div class="modal">

        <div class="modal-header">

          <div class="modal-title">

            <div class="modal-title-icon">
              <i class="fi fi-rr-receipt"></i>
            </div>

            <div>

              <span>FACTURATION</span>

              <h2>
                {{
                  editingFacture
                    ? 'Modifier la facture'
                    : 'Nouvelle facture'
                }}
              </h2>

              <p>
                Renseignez les informations de facturation.
              </p>

            </div>

          </div>


          <button
            class="modal-close"
            @click="closeFormModal"
          >
            <i class="fi fi-rr-cross-small"></i>
          </button>

        </div>


        <form
          class="modal-body"
          @submit.prevent="saveFacture"
        >

          <div class="form-grid">


            <!-- PATIENT -->
            <div class="form-group">

              <label>
                Patient
                <span>*</span>
              </label>

              <input
                v-model="form.patient"
                type="text"
                required
                placeholder="Nom du patient"
              />

            </div>


            <!-- REFERENCE -->
            <div class="form-group">

              <label>Référence patient</label>

              <input
                v-model="form.reference"
                type="text"
                placeholder="PAT-001"
              />

            </div>


            <!-- DATE -->
            <div class="form-group">

              <label>
                Date
                <span>*</span>
              </label>

              <input
                v-model="form.date"
                type="date"
                required
              />

            </div>


            <!-- MONTANT -->
            <div class="form-group">

              <label>
                Montant total
                <span>*</span>
              </label>

              <div class="input-money">

                <input
                  v-model.number="form.montant"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                />

                <span>DH</span>

              </div>

            </div>


            <!-- PAYE -->
            <div class="form-group">

              <label>Montant payé</label>

              <div class="input-money">

                <input
                  v-model.number="form.paye"
                  type="number"
                  min="0"
                  step="0.01"
                />

                <span>DH</span>

              </div>

            </div>


            <!-- PAIEMENT -->
            <div class="form-group">

              <label>Mode de paiement</label>

              <select v-model="form.modePaiement">

                <option value="">
                  Non défini
                </option>

                <option value="Espèces">
                  Espèces
                </option>

                <option value="Carte bancaire">
                  Carte bancaire
                </option>

                <option value="Virement">
                  Virement
                </option>

                <option value="Chèque">
                  Chèque
                </option>

              </select>

            </div>


            <!-- DESCRIPTION -->
            <div class="form-group full">

              <label>
                Description
                <span>*</span>
              </label>

              <input
                v-model="form.description"
                type="text"
                required
                placeholder="Consultation, hospitalisation, examens..."
              />

            </div>


            <!-- NOTES -->
            <div class="form-group full">

              <label>Notes</label>

              <textarea
                v-model="form.notes"
                placeholder="Informations complémentaires..."
              ></textarea>

            </div>

          </div>


          <!-- SUMMARY -->
          <div class="form-summary">

            <div>

              <span>Total</span>

              <strong>
                {{ formatMoney(form.montant) }}
              </strong>

            </div>


            <div>

              <span>Payé</span>

              <strong class="green-text">
                {{ formatMoney(form.paye) }}
              </strong>

            </div>


            <div>

              <span>Reste</span>

              <strong class="red-text">
                {{ formatMoney(formRemaining) }}
              </strong>

            </div>

          </div>


          <!-- FOOTER -->
          <div class="modal-footer">

            <button
              type="button"
              class="secondary-btn"
              @click="closeFormModal"
            >
              Annuler
            </button>


            <button
              type="submit"
              class="primary-btn"
            >

              <i class="fi fi-rr-check"></i>

              {{
                editingFacture
                  ? 'Enregistrer'
                  : 'Créer la facture'
              }}

            </button>

          </div>

        </form>

      </div>

    </div>


    <!-- =====================================================
         MODAL DETAILS
    ====================================================== -->
    <div
      v-if="viewFacture"
      class="modal-overlay"
      @click.self="viewFacture = null"
    >

      <div class="modal detail-modal">

        <div class="modal-header">

          <div class="modal-title">

            <div class="modal-title-icon">
              <i class="fi fi-rr-receipt"></i>
            </div>

            <div>

              <span>DÉTAILS DE LA FACTURE</span>

              <h2>
                {{ viewFacture.numero }}
              </h2>

              <p>
                Informations complètes de la facture.
              </p>

            </div>

          </div>


          <button
            class="modal-close"
            @click="viewFacture = null"
          >
            <i class="fi fi-rr-cross-small"></i>
          </button>

        </div>


        <div class="detail-body">

          <div class="patient-detail-card">

            <div class="detail-avatar">
              {{ initials(viewFacture.patient) }}
            </div>

            <div>

              <span>Patient</span>

              <h3>
                {{ viewFacture.patient }}
              </h3>

              <p>
                {{ viewFacture.reference }}
              </p>

            </div>

          </div>


          <div class="details-grid">

            <div class="detail-item">

              <span>Date</span>

              <strong>
                {{ formatDate(viewFacture.date) }}
              </strong>

            </div>


            <div class="detail-item">

              <span>Montant total</span>

              <strong>
                {{ formatMoney(viewFacture.montant) }}
              </strong>

            </div>


            <div class="detail-item">

              <span>Montant payé</span>

              <strong class="green-text">
                {{ formatMoney(viewFacture.paye) }}
              </strong>

            </div>


            <div class="detail-item">

              <span>Reste à payer</span>

              <strong class="red-text">
                {{ formatMoney(remaining(viewFacture)) }}
              </strong>

            </div>


            <div class="detail-item">

              <span>Mode de paiement</span>

              <strong>
                {{ viewFacture.modePaiement || 'Non défini' }}
              </strong>

            </div>


            <div class="detail-item">

              <span>Statut</span>

              <span
                class="status"
                :class="statusClass(viewFacture.statut)"
              >

                <span class="status-dot"></span>

                {{ viewFacture.statut }}

              </span>

            </div>

          </div>


          <div class="detail-description">

            <span>Description</span>

            <p>
              {{ viewFacture.description }}
            </p>

          </div>


          <div
            v-if="viewFacture.notes"
            class="detail-description"
          >

            <span>Notes</span>

            <p>
              {{ viewFacture.notes }}
            </p>

          </div>

        </div>

      </div>

    </div>


    <!-- =====================================================
         MODAL PAIEMENT
    ====================================================== -->
    <div
      v-if="paymentFacture"
      class="modal-overlay"
      @click.self="closePaymentModal"
    >

      <div class="small-modal">

        <div class="payment-modal-icon">
          <i class="fi fi-rr-credit-card"></i>
        </div>

        <h2>Enregistrer un paiement</h2>

        <p class="small-modal-description">
          {{ paymentFacture.numero }}
          ·
          {{ paymentFacture.patient }}
        </p>


        <div class="payment-summary">

          <div>

            <span>Montant total</span>

            <strong>
              {{ formatMoney(paymentFacture.montant) }}
            </strong>

          </div>


          <div>

            <span>Déjà payé</span>

            <strong class="green-text">
              {{ formatMoney(paymentFacture.paye) }}
            </strong>

          </div>


          <div>

            <span>Reste</span>

            <strong class="red-text">
              {{ formatMoney(remaining(paymentFacture)) }}
            </strong>

          </div>

        </div>


        <div class="payment-form">

          <div class="form-group">

            <label>
              Montant du paiement
              <span>*</span>
            </label>

            <div class="input-money">

              <input
                v-model.number="paymentAmount"
                type="number"
                min="0.01"
                step="0.01"
              />

              <span>DH</span>

            </div>

          </div>


          <div class="form-group">

            <label>
              Mode de paiement
              <span>*</span>
            </label>

            <select v-model="paymentMethod">

              <option value="">
                Sélectionner
              </option>

              <option value="Espèces">
                Espèces
              </option>

              <option value="Carte bancaire">
                Carte bancaire
              </option>

              <option value="Virement">
                Virement
              </option>

              <option value="Chèque">
                Chèque
              </option>

            </select>

          </div>

        </div>


        <div class="small-modal-footer">

          <button
            class="secondary-btn"
            @click="closePaymentModal"
          >
            Annuler
          </button>


          <button
            class="payment-btn"
            @click="confirmPayment"
          >
            <i class="fi fi-rr-check"></i>
            Confirmer le paiement
          </button>

        </div>

      </div>

    </div>


    <!-- =====================================================
         DELETE MODAL
    ====================================================== -->
    <div
      v-if="deleteTarget"
      class="modal-overlay"
      @click.self="deleteTarget = null"
    >

      <div class="small-modal">

        <div class="delete-modal-icon">
          <i class="fi fi-rr-trash"></i>
        </div>

        <h2>Supprimer la facture ?</h2>

        <p class="small-modal-description">
          La facture
          <strong>{{ deleteTarget.numero }}</strong>
          sera définitivement supprimée.
        </p>


        <div class="small-modal-footer">

          <button
            class="secondary-btn"
            @click="deleteTarget = null"
          >
            Annuler
          </button>


          <button
            class="danger-btn"
            @click="deleteFacture"
          >
            <i class="fi fi-rr-trash"></i>
            Supprimer
          </button>

        </div>

      </div>

    </div>


    <!-- =====================================================
         NOTIFICATION
    ====================================================== -->
    <transition name="toast">

      <div
        v-if="notification.show"
        class="notification"
        :class="notification.type"
      >

        <div class="notification-icon">

          <i
            :class="
              notification.type === 'success'
                ? 'fi fi-rr-check-circle'
                : 'fi fi-rr-exclamation'
            "
          ></i>

        </div>


        <div>

          <strong>
            {{
              notification.type === 'success'
                ? 'Succès'
                : 'Erreur'
            }}
          </strong>

          <span>
            {{ notification.message }}
          </span>

        </div>

      </div>

    </transition>

  </div>
</template>


<script setup>
import {
  ref,
  computed,
  watch
} from 'vue'


/* =========================================================
   DONNÉES LOCALES - SCRUM 749
========================================================= */

const factures = ref([
  {
    id: 1,
    numero: 'FAC-2026-001',
    patient: 'Sara Benali',
    reference: 'PAT-001',
    date: '2026-09-18',
    montant: 850,
    paye: 850,
    modePaiement: 'Carte bancaire',
    statut: 'Payée',
    description: 'Consultation et examens médicaux',
    notes: ''
  },

  {
    id: 2,
    numero: 'FAC-2026-002',
    patient: 'Yassine Amrani',
    reference: 'PAT-002',
    date: '2026-09-18',
    montant: 1500,
    paye: 500,
    modePaiement: 'Espèces',
    statut: 'Partiellement payée',
    description: 'Hospitalisation',
    notes: ''
  },

  {
    id: 3,
    numero: 'FAC-2026-003',
    patient: 'Nadia El Mansouri',
    reference: 'PAT-003',
    date: '2026-09-19',
    montant: 650,
    paye: 0,
    modePaiement: '',
    statut: 'En attente',
    description: 'Consultation spécialisée',
    notes: ''
  },

  {
    id: 4,
    numero: 'FAC-2026-004',
    patient: 'Omar Alaoui',
    reference: 'PAT-004',
    date: '2026-09-19',
    montant: 1200,
    paye: 1200,
    modePaiement: 'Virement',
    statut: 'Payée',
    description: 'Examens médicaux',
    notes: ''
  }
])


/* =========================================================
   SEARCH / FILTER
========================================================= */

const search = ref('')
const statusFilter = ref('')
const paymentFilter = ref('')

const currentPage = ref(1)
const perPage = 6


/* =========================================================
   MODALS
========================================================= */

const showFormModal = ref(false)

const editingFacture = ref(null)

const viewFacture = ref(null)

const paymentFacture = ref(null)

const deleteTarget = ref(null)


/* =========================================================
   PAYMENT
========================================================= */

const paymentAmount = ref('')

const paymentMethod = ref('')


/* =========================================================
   FORM
========================================================= */

const createEmptyForm = () => ({
  patient: '',
  reference: '',
  date: new Date().toISOString().slice(0, 10),
  montant: 0,
  paye: 0,
  modePaiement: '',
  description: '',
  notes: ''
})


const form = ref(createEmptyForm())


/* =========================================================
   NOTIFICATION
========================================================= */

const notification = ref({
  show: false,
  type: 'success',
  message: ''
})


let notificationTimer = null


const showNotification = (
  message,
  type = 'success'
) => {

  clearTimeout(notificationTimer)

  notification.value = {
    show: true,
    type,
    message
  }

  notificationTimer = setTimeout(() => {

    notification.value.show = false

  }, 3000)
}


/* =========================================================
   HELPERS
========================================================= */

const remaining = facture => {

  return Math.max(
    0,
    Number(facture?.montant || 0) -
    Number(facture?.paye || 0)
  )
}


const formatMoney = value => {

  const number = Number(value || 0)

  return `${number.toLocaleString('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })} DH`
}


const formatDate = value => {

  if (!value) {
    return '—'
  }

  const date =
    new Date(`${value}T00:00:00`)

  return date.toLocaleDateString('fr-FR')
}


const initials = name => {

  if (!name) {
    return '--'
  }

  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
}


const calculateStatus = facture => {

  const total =
    Number(facture.montant || 0)

  const paid =
    Number(facture.paye || 0)

  if (
    total > 0 &&
    paid >= total
  ) {
    return 'Payée'
  }

  if (paid > 0) {
    return 'Partiellement payée'
  }

  return 'En attente'
}


const statusClass = status => {

  if (status === 'Payée') {
    return 'paid'
  }

  if (status === 'Partiellement payée') {
    return 'partial'
  }

  return 'pending'
}


/* =========================================================
   STATISTIQUES
========================================================= */

const paidCount = computed(() => {

  return factures.value.filter(
    facture =>
      facture.statut === 'Payée'
  ).length
})


const pendingCount = computed(() => {

  return factures.value.filter(
    facture =>
      facture.statut !== 'Payée'
  ).length
})


const totalRemaining = computed(() => {

  return factures.value.reduce(
    (total, facture) =>
      total + remaining(facture),
    0
  )
})


/* =========================================================
   FORM REMAINING
========================================================= */

const formRemaining = computed(() => {

  return Math.max(
    0,
    Number(form.value.montant || 0) -
    Number(form.value.paye || 0)
  )
})


/* =========================================================
   FILTERED DATA
========================================================= */

const filteredFactures = computed(() => {

  const query =
    search.value
      .trim()
      .toLowerCase()


  return factures.value.filter(
    facture => {

      const searchableText = `
        ${facture.numero}
        ${facture.patient}
        ${facture.reference}
        ${facture.description}
        ${facture.modePaiement}
      `.toLowerCase()


      const matchesSearch =
        !query ||
        searchableText.includes(query)


      const matchesStatus =
        !statusFilter.value ||
        facture.statut ===
          statusFilter.value


      const matchesPayment =
        !paymentFilter.value ||
        facture.modePaiement ===
          paymentFilter.value


      return (
        matchesSearch &&
        matchesStatus &&
        matchesPayment
      )
    }
  )
})


/* =========================================================
   PAGINATION
========================================================= */

const totalPages = computed(() => {

  return Math.max(
    1,
    Math.ceil(
      filteredFactures.value.length /
      perPage
    )
  )
})


const paginatedFactures = computed(() => {

  const start =
    (currentPage.value - 1) *
    perPage

  return filteredFactures.value.slice(
    start,
    start + perPage
  )
})


const firstItem = computed(() => {

  if (
    filteredFactures.value.length === 0
  ) {
    return 0
  }

  return (
    (currentPage.value - 1) *
    perPage +
    1
  )
})


const lastItem = computed(() => {

  return Math.min(
    currentPage.value * perPage,
    filteredFactures.value.length
  )
})


const previousPage = () => {

  if (currentPage.value > 1) {
    currentPage.value--
  }
}


const nextPage = () => {

  if (
    currentPage.value <
    totalPages.value
  ) {
    currentPage.value++
  }
}


watch(
  [
    search,
    statusFilter,
    paymentFilter
  ],
  () => {

    currentPage.value = 1

  }
)


/* =========================================================
   RESET FILTERS
========================================================= */

const resetFilters = () => {

  search.value = ''

  statusFilter.value = ''

  paymentFilter.value = ''

  currentPage.value = 1
}


/* =========================================================
   CREATE
========================================================= */

const openCreateModal = () => {

  editingFacture.value = null

  form.value =
    createEmptyForm()

  showFormModal.value = true
}


/* =========================================================
   EDIT
========================================================= */

const openEditModal = facture => {

  editingFacture.value = facture

  form.value = {
    ...facture
  }

  showFormModal.value = true
}


/* =========================================================
   CLOSE FORM
========================================================= */

const closeFormModal = () => {

  showFormModal.value = false

  editingFacture.value = null

  form.value =
    createEmptyForm()
}


/* =========================================================
   SAVE
========================================================= */

const saveFacture = () => {

  const total =
    Number(form.value.montant || 0)

  const paid =
    Number(form.value.paye || 0)


  if (total <= 0) {

    showNotification(
      'Le montant total doit être supérieur à 0.',
      'error'
    )

    return
  }


  if (
    paid < 0 ||
    paid > total
  ) {

    showNotification(
      'Le montant payé est invalide.',
      'error'
    )

    return
  }


  const data = {

    ...form.value,

    montant: total,

    paye: paid
  }


  data.statut =
    calculateStatus(data)


  /* EDIT */

  if (editingFacture.value) {

    const index =
      factures.value.findIndex(
        facture =>
          facture.id ===
          editingFacture.value.id
      )


    if (index !== -1) {

      factures.value[index] = {

        ...factures.value[index],

        ...data
      }
    }


    showNotification(
      'Facture modifiée avec succès.'
    )

  }

  /* CREATE */

  else {

    const newId =
      Math.max(
        0,
        ...factures.value.map(
          facture => facture.id
        )
      ) + 1


    const newFacture = {

      id: newId,

      numero:
        `FAC-2026-${String(newId).padStart(3, '0')}`,

      ...data
    }


    factures.value.unshift(
      newFacture
    )


    showNotification(
      'Facture créée avec succès.'
    )
  }


  closeFormModal()
}


/* =========================================================
   VIEW
========================================================= */

const openViewModal = facture => {

  viewFacture.value = facture
}


/* =========================================================
   PAYMENT
========================================================= */

const openPaymentModal = facture => {

  paymentFacture.value = facture

  paymentAmount.value = ''

  paymentMethod.value =
    facture.modePaiement || ''
}


const closePaymentModal = () => {

  paymentFacture.value = null

  paymentAmount.value = ''

  paymentMethod.value = ''
}


const confirmPayment = () => {

  if (!paymentFacture.value) {
    return
  }


  const amount =
    Number(paymentAmount.value || 0)


  const reste =
    remaining(paymentFacture.value)


  if (
    amount <= 0 ||
    amount > reste
  ) {

    showNotification(
      'Le montant du paiement est invalide.',
      'error'
    )

    return
  }


  if (!paymentMethod.value) {

    showNotification(
      'Veuillez sélectionner un mode de paiement.',
      'error'
    )

    return
  }


  const facture =
    factures.value.find(
      item =>
        item.id ===
        paymentFacture.value.id
    )


  if (facture) {

    facture.paye =
      Number(facture.paye || 0) +
      amount


    facture.modePaiement =
      paymentMethod.value


    facture.statut =
      calculateStatus(facture)
  }


  closePaymentModal()


  showNotification(
    'Paiement enregistré avec succès.'
  )
}


/* =========================================================
   DELETE
========================================================= */

const openDeleteModal = facture => {

  deleteTarget.value = facture
}


const deleteFacture = () => {

  if (!deleteTarget.value) {
    return
  }


  factures.value =
    factures.value.filter(
      facture =>
        facture.id !==
        deleteTarget.value.id
    )


  deleteTarget.value = null


  if (
    currentPage.value >
    totalPages.value
  ) {
    currentPage.value =
      totalPages.value
  }


  showNotification(
    'Facture supprimée avec succès.'
  )
}


/*
=========================================================
SCRUM-750

Quand le Backend Facturation sera disponible :

import api from '../../api'

GET    /factures
POST   /factures
PUT    /factures/{id}
DELETE /factures/{id}
POST   /factures/{id}/paiements

Les données locales ci-dessus seront alors remplacées
par les données Laravel / PostgreSQL.
=========================================================
*/
</script>


<style
  scoped
  src="../../styles/facturation.css"
></style>