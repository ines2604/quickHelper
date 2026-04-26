<template>
  <div class="auth-container">
    <div class="auth-logo">🔑</div>
    <h2>Mot de passe oublié</h2>
    <p class="subtitle">Entrez votre email pour recevoir un lien de réinitialisation.</p>

    <div v-if="successMsg" class="alert alert-success">✅ {{ successMsg }}</div>
    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <form v-if="!successMsg" @submit.prevent="handleSubmit">
      <div class="input-group">
        <span class="input-icon">✉️</span>
        <input v-model="email" type="email" placeholder="votre@email.com" required />
      </div>
      <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
        <span v-if="loading" class="spinner"></span>
        {{ loading ? 'Envoi...' : 'Envoyer le lien' }}
      </button>
    </form>

    <div class="auth-links">
      <router-link to="/login">← Retour à la connexion</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../api/axios';

const email = ref('');
const loading = ref(false);
const successMsg = ref(null);
const error = ref(null);

const handleSubmit = async () => {
  loading.value = true;
  error.value = null;
  try {
    const res = await api.post('/forgot-password', { email: email.value });
    successMsg.value = res.data.message;
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de l\'envoi.';
  } finally {
    loading.value = false;
  }
};
</script>
