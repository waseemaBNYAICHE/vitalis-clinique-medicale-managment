<template>
  <section class="patients-page">
    <div class="patients-header">
      <div>
        <p class="breadcrumb">Accueil / Patients</p>
        <h1>Patients</h1>
        <p class="subtitle">Gérez les informations et le suivi de vos patients.</p>
      </div>
      <button class="btn-primary" type="button">
        <i class="fi fi-rr-plus"></i>
        Ajouter un patient
      </button>
    </div>

    <div class="quick-actions">
      <article class="quick-card">
        <span class="quick-icon"><i class="fi fi-rr-calendar"></i></span>
        <div><strong>Prendre rendez-vous</strong><small>Planifier une visite</small></div>
      </article>
      <article class="quick-card">
        <span class="quick-icon"><i class="fi fi-rr-stethoscope"></i></span>
        <div><strong>Nouvelle consultation</strong><small>Démarrer une consultation</small></div>
      </article>
      <article class="quick-card">
        <span class="quick-icon"><i class="fi fi-rr-document"></i></span>
        <div><strong>Historique médical</strong><small>Consulter le dossier</small></div>
      </article>
    </div>

    <div class="patients-panel">
      <div class="filters">
        <label class="search">
          <i class="fi fi-rr-search"></i>
          <input type="search" placeholder="Rechercher par nom, CIN ou téléphone..." />
        </label>
        <select><option>Tous les sexes</option><option>Femme</option><option>Homme</option></select>
        <select><option>Tous les groupes sanguins</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>AB+</option><option>AB-</option><option>O+</option><option>O-</option></select>
        <button class="btn-filter" type="button"><i class="fi fi-rr-search"></i> Rechercher</button>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Patient</th><th>CIN</th><th>Date de naissance</th><th>Sexe</th><th>Téléphone</th><th>Groupe sanguin</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <tr class="empty-row">
              <td colspan="8">
                <i class="fi fi-rr-users"></i>
                <strong>Liste des patients</strong>
                <span>Les données réelles seront chargées par la liaison Backend / Frontend de SCRUM-750.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>

<script setup>
// SCRUM-749 : finalisation visuelle uniquement.
// Les appels API et le CRUD réel sont volontairement laissés à SCRUM-750.
</script>

<style scoped src="../../styles/patients.css"></style>
