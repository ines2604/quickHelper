<template>
  <div class="auth-container">
    <div class="auth-logo">🚀</div>
    <h2>QuickHelp</h2>
    <p class="subtitle">Bon retour parmi nous !</p>

    <div v-if="emailNotVerified" class="alert alert-warning">
      <p>⚠️ Votre email n'est pas encore vérifié.</p>
      <button class="btn-link" @click="resendVerification">Renvoyer l'email de vérification</button>
    </div>

    <div v-if="successMsg" class="alert alert-success">{{ successMsg }}</div>
    <div v-if="error && !emailNotVerified" class="alert alert-error">{{ error }}</div>

    <form @submit.prevent="handleLogin">
      <div class="input-group">
        <span class="input-icon">✉️</span>
        <input
          v-model="form.email"
          type="email"
          placeholder="votre@email.com"
          required
          autocomplete="email"
        />
      </div>
      <div class="input-group">
        <span class="input-icon">🔒</span>
        <input
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          placeholder="Mot de passe"
          required
          autocomplete="current-password"
        />
        <button type="button" class="toggle-password" @click="showPassword = !showPassword">
          {{ showPassword ? '🙈' : '👁️' }}
        </button>
      </div>

      <div class="forgot-link">
        <router-link to="/forgot-password">Mot de passe oublié ?</router-link>
      </div>

      <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
        <span v-if="loading" class="spinner"></span>
        {{ loading ? 'Connexion...' : 'Se connecter' }}
      </button>
    </form>

    <div class="auth-links">
      Pas encore de compte ? <router-link to="/register">Créer un compte</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../api/axios';

const router = useRouter();
const authStore = useAuthStore();
const form = ref({ email: '', password: '' });
const error = ref(null);
const successMsg = ref(null);
const loading = ref(false);
const emailNotVerified = ref(false);
const showPassword = ref(false);

const handleLogin = async () => {
  error.value = null;
  successMsg.value = null;
  emailNotVerified.value = false;
  loading.value = true;

  const result = await authStore.login(form.value);
  loading.value = false;

  if (result.success) {
    router.push('/dashboard');
  } else if (result.emailNotVerified) {
    emailNotVerified.value = true;
    error.value = result.message;
  } else {
    error.value = result.message;
  }
};

const resendVerification = async () => {
  try {
    await api.post('/resend-verification', { email: form.value.email });
    successMsg.value = 'Email de vérification renvoyé. Vérifiez votre boîte mail.';
  } catch (err) {
    error.value = 'Erreur lors de l\'envoi.';
  }
};
</script>
