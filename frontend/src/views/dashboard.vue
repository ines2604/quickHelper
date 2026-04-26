<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Mon Tableau de Bord</h1>
        <p class="page-subtitle">Gérez vos demandes et missions</p>
      </div>
      <router-link to="/requests/create" class="btn btn-primary">
        + Nouvelle demande
      </router-link>
    </div>

    <!-- Mes Demandes -->
    <section class="section">
      <h2 class="section-title">
        <span class="section-icon">📋</span> Mes Demandes
        <span class="badge-count">{{ myRequests.length }}</span>
      </h2>
      <div v-if="myRequests.length === 0" class="empty-state">
        <p>Vous n'avez aucune demande. <router-link to="/requests/create">Publiez-en une !</router-link></p>
      </div>
      <div class="cards-grid">
        <div v-for="req in myRequests" :key="req.id" class="card">
          <div class="card-header">
            <span class="status-badge" :class="'status-' + req.status">{{ statusLabel(req.status) }}</span>
            <span class="urgency-badge" :class="'urgency-' + req.urgency">{{ urgencyLabel(req.urgency) }}</span>
          </div>
          <h3 class="card-title">{{ req.title }}</h3>
          <p class="card-desc">{{ req.description?.slice(0, 100) }}{{ req.description?.length > 100 ? '...' : '' }}</p>
          <div class="card-meta">
            <span>💰 {{ req.budget }} TND</span>
            <span>📍 {{ req.location || 'Non précisé' }}</span>
            <span>📅 {{ formatDate(req.deadline) }}</span>
          </div>
          <div class="card-actions">
            <router-link :to="`/requests/${req.id}`" class="btn btn-sm btn-outline">Voir détails</router-link>
            <template v-if="req.status === 'open'">
              <router-link :to="`/requests/${req.id}/edit`" class="btn btn-sm btn-secondary">Modifier</router-link>
              <button @click="deleteRequest(req.id)" class="btn btn-sm btn-danger">Supprimer</button>
            </template>
            <!-- Status change button (#5) -->
            <template v-if="req.status === 'in_progress'">
              <button @click="changeStatus(req.id, 'completed')" class="btn btn-sm btn-success">✅ Terminer</button>
            </template>
            <!-- Rating button (#6) -->
            <template v-if="req.status === 'completed' && req.accepted_helper_id && !req.rated">
              <router-link :to="`/requests/${req.id}#rating`" class="btn btn-sm btn-warning">⭐ Noter l'aidant</router-link>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- Missions en cours (Aidant) -->
    <section class="section">
      <h2 class="section-title">
        <span class="section-icon">🤝</span> Mes Missions (Aidant)
        <span class="badge-count">{{ helpedRequests.length }}</span>
      </h2>
      <div v-if="helpedRequests.length === 0" class="empty-state">
        <p>Aucune mission acceptée. <router-link to="/requests">Trouvez des demandes !</router-link></p>
      </div>
      <div class="cards-grid">
        <div v-for="req in helpedRequests" :key="req.id" class="card">
          <div class="card-header">
            <span class="status-badge" :class="'status-' + req.status">{{ statusLabel(req.status) }}</span>
          </div>
          <h3 class="card-title">{{ req.title }}</h3>
          <p class="card-desc">{{ req.description?.slice(0, 100) }}{{ req.description?.length > 100 ? '...' : '' }}</p>
          <div class="card-meta">
            <span>💰 {{ req.budget }} TND</span>
            <span>👤 {{ req.user?.name }}</span>
          </div>
          <div class="card-actions">
            <router-link :to="`/requests/${req.id}`" class="btn btn-sm btn-outline">Discussion</router-link>
            <button v-if="req.status === 'in_progress'" @click="markCompleted(req.id)" class="btn btn-sm btn-success">
              ✅ Marquer terminé
            </button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/axios';

const router = useRouter();
const myRequests = ref([]);
const helpedRequests = ref([]);

onMounted(async () => {
  try {
    const res = await api.get('/requests/history');
    myRequests.value = res.data.my_requests || [];
    helpedRequests.value = res.data.helped_requests || [];
  } catch (error) {
    console.error('Erreur chargement historique', error);
  }
});

const statusLabel = (status) => {
  const map = { open: 'Ouverte', in_progress: 'En cours', completed: 'Terminée', accepted: 'Acceptée' };
  return map[status] || status;
};

const urgencyLabel = (urgency) => {
  const map = { low: 'Faible', medium: 'Moyenne', high: 'Haute' };
  return map[urgency] || urgency;
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('fr-FR');
};

const deleteRequest = async (id) => {
  if (!confirm('Supprimer cette demande ?')) return;
  try {
    await api.delete(`/requests/${id}`);
    myRequests.value = myRequests.value.filter(r => r.id !== id);
  } catch (error) {
    alert('Erreur lors de la suppression');
  }
};

const markCompleted = async (id) => {
  try {
    await api.post(`/requests/${id}/complete`);
    const req = helpedRequests.value.find(r => r.id === id);
    if (req) req.status = 'completed';
  } catch (error) {
    alert('Erreur');
  }
};

const changeStatus = async (id, status) => {
  if (!confirm(`Marquer cette demande comme "${statusLabel(status)}" ?`)) return;
  try {
    await api.patch(`/requests/${id}/status`, { status });
    const req = myRequests.value.find(r => r.id === id);
    if (req) req.status = status;
  } catch (error) {
    alert('Erreur lors du changement de statut');
  }
};
</script>
