<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Mon Profil</h1>
      <p class="page-subtitle">Vos informations et votre réputation sur QuickHelp</p>
    </div>

    <div v-if="user" class="profile-layout">
      <!-- Carte profil -->
      <div class="profile-card">
        <div class="profile-avatar">{{ user.name?.charAt(0)?.toUpperCase() }}</div>
        <h2 class="profile-name">{{ user.name }}</h2>
        <p class="profile-email">{{ user.email }}</p>
        <p v-if="user.phone" class="profile-phone">📞 {{ user.phone }}</p>
        <div class="profile-rating">
          <div class="stars">
            <span v-for="i in 5" :key="i" :class="['star', i <= Math.round(user.rating_avg) ? 'star-filled' : '']">★</span>
          </div>
          <span class="rating-text">{{ user.rating_avg ? user.rating_avg.toFixed(1) : 'Pas encore noté' }}</span>
          <span class="rating-count" v-if="user.rating_count">({{ user.rating_count }} avis)</span>
        </div>
        <div class="profile-stats">
          <div class="stat">
            <span class="stat-value">{{ stats.totalRequests }}</span>
            <span class="stat-label">Demandes</span>
          </div>
          <div class="stat">
            <span class="stat-value">{{ stats.completedHelp }}</span>
            <span class="stat-label">Missions</span>
          </div>
          <div class="stat">
            <span class="stat-value">{{ stats.completedRequests }}</span>
            <span class="stat-label">Résolues</span>
          </div>
        </div>
      </div>

      <!-- Historique & Avis -->
      <div class="profile-content">
        <div class="tabs">
          <button @click="activeTab = 'history'" :class="['tab-btn', activeTab === 'history' ? 'active' : '']">📋 Historique</button>
          <button @click="activeTab = 'ratings'" :class="['tab-btn', activeTab === 'ratings' ? 'active' : '']">⭐ Évaluations reçues</button>
        </div>

        <!-- Historique -->
        <div v-if="activeTab === 'history'" class="tab-content">
          <h3 class="section-subtitle">Mes demandes</h3>
          <div v-if="myRequests.length === 0" class="empty-state">Aucune demande publiée.</div>
          <div v-for="req in myRequests" :key="'req-' + req.id" class="history-item">
            <div class="history-info">
              <span class="status-badge" :class="'status-' + req.status">{{ statusLabel(req.status) }}</span>
              <strong>{{ req.title }}</strong>
              <span class="history-date">{{ formatDate(req.created_at) }}</span>
            </div>
            <router-link :to="`/requests/${req.id}`" class="btn btn-sm btn-outline">Voir</router-link>
          </div>

          <h3 class="section-subtitle" style="margin-top:2rem">Missions effectuées</h3>
          <div v-if="helpedRequests.length === 0" class="empty-state">Aucune mission effectuée.</div>
          <div v-for="req in helpedRequests" :key="'help-' + req.id" class="history-item">
            <div class="history-info">
              <span class="status-badge" :class="'status-' + req.status">{{ statusLabel(req.status) }}</span>
              <strong>{{ req.title }}</strong>
              <span class="history-date">{{ formatDate(req.created_at) }}</span>
            </div>
            <router-link :to="`/requests/${req.id}`" class="btn btn-sm btn-outline">Voir</router-link>
          </div>
        </div>

        <!-- Avis reçus -->
        <div v-if="activeTab === 'ratings'" class="tab-content">
          <div v-if="ratings.length === 0" class="empty-state">Aucune évaluation reçue pour l'instant.</div>
          <div v-for="r in ratings" :key="r.id" class="rating-item">
            <div class="rating-header">
              <div class="rating-stars">
                <span v-for="i in 5" :key="i" :class="['star', i <= r.rating ? 'star-filled' : '']">★</span>
              </div>
              <span class="rating-from">Par {{ r.from_user?.name || 'Utilisateur' }}</span>
              <span class="rating-date">{{ formatDate(r.created_at) }}</span>
            </div>
            <p v-if="r.comment" class="rating-comment">"{{ r.comment }}"</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="loading-state">Chargement du profil...</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../api/axios';

const authStore = useAuthStore();
const user = ref(null);
const myRequests = ref([]);
const helpedRequests = ref([]);
const ratings = ref([]);
const activeTab = ref('history');

const stats = computed(() => ({
  totalRequests: myRequests.value.length,
  completedRequests: myRequests.value.filter(r => r.status === 'completed').length,
  completedHelp: helpedRequests.value.filter(r => r.status === 'completed').length,
}));

onMounted(async () => {
  user.value = authStore.user;
  if (!user.value) {
    await authStore.fetchUser();
    user.value = authStore.user;
  }
  try {
    const histRes = await api.get('/requests/history');
    myRequests.value = histRes.data.my_requests || [];
    helpedRequests.value = histRes.data.helped_requests || [];
  } catch (e) {}
  try {
    const ratingsRes = await api.get('/ratings/received');
    ratings.value = ratingsRes.data || [];
  } catch (e) {
    ratings.value = [];
  }
});

const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR') : '';
const statusLabel = (s) => ({ open: 'Ouverte', in_progress: 'En cours', completed: 'Terminée', accepted: 'Acceptée' }[s] || s);
</script>
