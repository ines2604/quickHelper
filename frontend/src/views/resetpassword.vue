<template>
  <div class="auth-container">
    <div class="auth-logo">🔒</div>
    <h2>Nouveau mot de passe</h2>
    <p class="subtitle">Choisissez un nouveau mot de passe sécurisé.</p>

    <div v-if="successMsg" class="alert alert-success">
      ✅ {{ successMsg }}
      <div style="margin-top: 12px;">
        <router-link to="/login" class="btn btn-primary btn-full">Se connecter</router-link>
      </div>
    </div>
    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <form v-if="!successMsg" @submit.prevent="handleSubmit">
      <div class="input-group">
        <span class="input-icon">🔒</span>
        <input
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          placeholder="Nouveau mot de passe (min. 8 caractères)"
          required
        />
        <button type="button" class="toggle-password" @click="showPassword = !showPassword">
          {{ showPassword ? '🙈' : '👁️' }}
        </button>
      </div>
      <div class="input-group">
        <span class="input-icon">🔒</span>
        <input
          v-model="form.password_confirmation"
          :type="showPassword ? 'text' : 'password'"
          placeholder="Confirmer le mot de passe"
          required
        />
      </div>
      <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
        <span v-if="loading" class="spinner"></span>
        {{ loading ? 'Réinitialisation...' : 'Réinitialiser le mot de passe' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../api/axios';

const route = useRoute();
const form = ref({ password: '', password_confirmation: '' });
const loading = ref(false);
const successMsg = ref(null);
const error = ref(null);
const showPassword = ref(false);

const handleSubmit = async () => {
  loading.value = true;
  error.value = null;
  try {
    const res = await api.post('/reset-password', {
      email: route.query.email,
      token: route.query.token,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    });
    successMsg.value = res.data.message;
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de la réinitialisation.';
  } finally {
    loading.value = false;
  }
};
</script>
