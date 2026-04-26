<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Modifier la demande</h1>
        <p class="page-subtitle">Mettez à jour les informations de votre demande</p>
      </div>
    </div>

    <div class="form-container" v-if="form.title !== undefined">
      <form @submit.prevent="submitUpdate" class="form-card">
        <div class="form-group">
          <label>Titre *</label>
          <input v-model="form.title" type="text" required />
        </div>
        <div class="form-group">
          <label>Description *</label>
          <textarea v-model="form.description" rows="5" required></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Budget (TND) *</label>
            <input v-model.number="form.budget" type="number" min="0" required />
          </div>
          <div class="form-group">
            <label>Date limite *</label>
            <input v-model="form.deadline" type="date" required />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Catégorie *</label>
            <select v-model="form.category" required>
              <option value="general">Général</option>
              <option value="transport">Transport / Déménagement</option>
              <option value="education">Cours / Éducation</option>
              <option value="repair">Réparation / Bricolage</option>
              <option value="health">Santé / Aide médicale</option>
              <option value="housekeeping">Ménage / Nettoyage</option>
              <option value="it_support">Informatique / Tech</option>
              <option value="other">Autre</option>
            </select>
          </div>
          <div class="form-group">
            <label>Urgence *</label>
            <select v-model="form.urgency" required>
              <option value="low">🟢 Faible</option>
              <option value="medium">🟡 Moyenne</option>
              <option value="high">🔴 Haute</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Localisation (optionnel)</label>
          <input v-model="form.location" type="text" placeholder="Ex: Tunis, La Marsa..." />
        </div>
        <div v-if="error" class="error">{{ error }}</div>
        <div class="form-actions">
          <router-link to="/dashboard" class="btn btn-outline">Annuler</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            {{ loading ? 'Enregistrement...' : '💾 Enregistrer les modifications' }}
          </button>
        </div>
      </form>
    </div>
    <div v-else class="loading-state">Chargement...</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api/axios';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const error = ref(null);

const form = ref({
  title: undefined,
  description: '',
  budget: 0,
  category: '',
  urgency: '',
  deadline: '',
  location: ''
});

onMounted(async () => {
  try {
    const res = await api.get(`/requests/${route.params.id}`);
    const req = res.data;
    form.value = {
      title: req.title,
      description: req.description,
      budget: req.budget,
      category: req.category,
      urgency: req.urgency,
      deadline: req.deadline ? req.deadline.split('T')[0] : '',
      location: req.location || ''
    };
  } catch (err) {
    error.value = 'Impossible de charger la demande.';
  }
});

const submitUpdate = async () => {
  loading.value = true;
  error.value = null;
  try {
    await api.put(`/requests/${route.params.id}`, form.value);
    router.push('/dashboard');
  } catch (err) {
    const errors = err.response?.data?.errors;
    if (errors) {
      error.value = Object.values(errors).flat().join(', ');
    } else {
      error.value = err.response?.data?.message || 'Erreur lors de la modification';
    }
  } finally {
    loading.value = false;
  }
};
</script>
