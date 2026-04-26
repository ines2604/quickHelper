<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Demandes disponibles</h1>
        <p class="page-subtitle">Trouvez une mission et proposez votre aide</p>
      </div>
      <router-link to="/requests/create" class="btn btn-primary">+ Publier une demande</router-link>
    </div>

    <!-- Filtres -->
    <div class="filters-bar">
      <input v-model="search" type="text" placeholder="🔍 Rechercher..." class="filter-input" />
      <select v-model="filterCategory" class="filter-select">
        <option value="">Toutes les catégories</option>
        <option value="general">Général</option>
        <option value="transport">Transport</option>
        <option value="education">Éducation</option>
        <option value="repair">Réparation</option>
        <option value="health">Santé</option>
        <option value="housekeeping">Ménage</option>
        <option value="it_support">Informatique</option>
        <option value="other">Autre</option>
      </select>
      <select v-model="filterUrgency" class="filter-select">
        <option value="">Toute urgence</option>
        <option value="low">🟢 Faible</option>
        <option value="medium">🟡 Moyenne</option>
        <option value="high">🔴 Haute</option>
      </select>
    </div>

    <div v-if="loading" class="loading-state">Chargement...</div>
    <div v-else-if="filteredRequests.length === 0" class="empty-state">
      <p>Aucune demande disponible pour le moment.</p>
    </div>
    <div class="cards-grid" v-else>
      <div v-for="req in filteredRequests" :key="req.id" class="card request-card">
        <div class="card-header">
          <span class="status-badge status-open">Disponible</span>
          <span class="urgency-badge" :class="'urgency-' + req.urgency">{{ urgencyLabel(req.urgency) }}</span>
        </div>
        <h3 class="card-title">{{ req.title }}</h3>
        <p class="card-desc">{{ req.description?.slice(0, 120) }}{{ req.description?.length > 120 ? '...' : '' }}</p>
        <div class="card-meta">
          <span>💰 {{ req.budget }} TND</span>
          <span>🏷️ {{ categoryLabel(req.category) }}</span>
          <span>📍 {{ req.location || 'Non précisé' }}</span>
          <span>📅 {{ formatDate(req.deadline) }}</span>
        </div>
        <div class="card-footer">
          <div class="requester-info">
            <span class="avatar-sm">{{ req.user?.name?.charAt(0)?.toUpperCase() }}</span>
            <span>{{ req.user?.name }}</span>
            <span v-if="req.user?.rating_avg > 0" class="rating-sm">⭐ {{ req.user.rating_avg.toFixed(1) }}</span>
          </div>
          <router-link :to="`/requests/${req.id}`" class="btn btn-sm btn-primary">Voir & Aider →</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../api/axios';

const authStore = useAuthStore();
const requests = ref([]);
const loading = ref(true);
const search = ref('');
const filterCategory = ref('');
const filterUrgency = ref('');

onMounted(async () => {
  try {
    const response = await api.get('/requests');
    // Extra client-side safety: exclude own requests (#7)
    requests.value = response.data.filter(r => r.user_id !== authStore.user?.id);
  } catch (error) {
    console.error("Erreur chargement demandes", error);
  } finally {
    loading.value = false;
  }
});

const filteredRequests = computed(() => {
  return requests.value.filter(req => {
    const matchSearch = !search.value ||
      req.title.toLowerCase().includes(search.value.toLowerCase()) ||
      req.description?.toLowerCase().includes(search.value.toLowerCase());
    const matchCategory = !filterCategory.value || req.category === filterCategory.value;
    const matchUrgency = !filterUrgency.value || req.urgency === filterUrgency.value;
    return matchSearch && matchCategory && matchUrgency;
  });
});

const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR') : 'N/A';
const urgencyLabel = (u) => ({ low: 'Faible', medium: 'Moyenne', high: 'Haute' }[u] || u);
const categoryLabel = (c) => ({
  general: 'Général', transport: 'Transport', education: 'Éducation',
  repair: 'Réparation', health: 'Santé', housekeeping: 'Ménage',
  it_support: 'Informatique', other: 'Autre'
}[c] || c);
</script>
