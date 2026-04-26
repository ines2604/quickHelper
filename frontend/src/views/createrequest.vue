<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Publier une demande d'aide</h1>
        <p class="page-subtitle">Décrivez ce dont vous avez besoin et trouvez rapidement de l'aide</p>
      </div>
    </div>

    <div class="form-container">
      <form @submit.prevent="submitRequest" class="form-card">
        <div class="form-group">
          <label>Titre de la demande *</label>
          <input v-model="form.title" type="text" placeholder="Ex: Aide pour déménagement ce week-end" required />
        </div>

        <div class="form-group">
          <label>Description détaillée *</label>
          <textarea v-model="form.description" placeholder="Décrivez précisément votre besoin..." rows="5" required></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Budget (TND) *</label>
            <input v-model.number="form.budget" type="number" min="0" placeholder="0" required />
          </div>
          <div class="form-group">
            <label>Date limite *</label>
            <input v-model="form.deadline" type="date" required :min="today" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Catégorie *</label>
            <select v-model="form.category" required>
              <option value="">Choisir une catégorie</option>
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
              <option value="">Niveau d'urgence</option>
              <option value="low">🟢 Faible - Pas pressé</option>
              <option value="medium">🟡 Moyenne - Dans la semaine</option>
              <option value="high">🔴 Haute - Urgent</option>
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
            {{ loading ? 'Publication...' : '📢 Publier la demande' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/axios';

const router = useRouter();
const loading = ref(false);
const error = ref(null);

const today = new Date().toISOString().split('T')[0];

const form = ref({
  title: '',
  description: '',
  budget: '',
  category: '',
  urgency: '',
  deadline: '',
  location: ''
});

const submitRequest = async () => {
  loading.value = true;
  error.value = null;
  try {
    await api.post('/requests', form.value);
    router.push('/dashboard');
  } catch (err) {
    const errors = err.response?.data?.errors;
    if (errors) {
      error.value = Object.values(errors).flat().join(', ');
    } else {
      error.value = err.response?.data?.message || "Erreur lors de la publication";
    }
  } finally {
    loading.value = false;
  }
};
</script>
