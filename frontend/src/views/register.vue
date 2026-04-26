<template>
  <div class="auth-container">
    <div class="auth-logo">🤝</div>
    <h2>Rejoindre QuickHelp</h2>
    <p class="subtitle">Créez votre compte gratuitement.</p>

    <div v-if="successMsg" class="alert alert-success">
      ✅ {{ successMsg }}
      <div style="margin-top: 8px;">
        <router-link to="/login" class="btn-link">Aller à la connexion</router-link>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <form v-if="!successMsg" @submit.prevent="handleRegister">
      <div class="input-group">
        <span class="input-icon">👤</span>
        <input v-model="form.name" type="text" placeholder="Votre nom complet" required autocomplete="name" />
      </div>
      <div class="input-group">
        <span class="input-icon">✉️</span>
        <input v-model="form.email" type="email" placeholder="votre@email.com" required autocomplete="email" />
      </div>
      <div class="input-group">
        <span class="input-icon">📞</span>
        <input v-model="form.phone" type="tel" placeholder="Téléphone (optionnel)" />
      </div>
      <div class="input-group">
        <span class="input-icon">🔒</span>
        <input
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          placeholder="Mot de passe (min. 8 caractères)"
          required
          autocomplete="new-password"
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
          autocomplete="new-password"
        />
      </div>

      <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
        <span v-if="loading" class="spinner"></span>
        {{ loading ? 'Création...' : "S'inscrire maintenant" }}
      </button>
    </form>

    <div class="auth-links" v-if="!successMsg">
      Déjà inscrit ? <router-link to="/login">Se connecter</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: ''
});
const error = ref(null);
const successMsg = ref(null);
const loading = ref(false);
const showPassword = ref(false);

const handleRegister = async () => {
  error.value = null;
  loading.value = true;

  const result = await authStore.register(form.value);
  loading.value = false;

  if (result.success) {
    successMsg.value = result.message || 'Compte créé ! Vérifiez votre email pour activer votre compte.';
  } else {
    error.value = result.message || 'Une erreur est survenue lors de l\'inscription.';
  }
};
</script>
