<template>
  <div class="auth-container">
    <div class="auth-logo">📧</div>
    <h2>Vérification de l'email</h2>

    <div v-if="loading" class="loading-msg">Vérification en cours...</div>
    <div v-else-if="success" class="alert alert-success">
      ✅ {{ message }}
      <div style="margin-top: 12px;">
        <router-link to="/login" class="btn btn-primary btn-full">Se connecter</router-link>
      </div>
    </div>
    <div v-else class="alert alert-error">
      ❌ {{ message }}
      <div style="margin-top: 12px;">
        <router-link to="/login" class="btn-link">Retour à la connexion</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../api/axios';

const route = useRoute();
const loading = ref(true);
const success = ref(false);
const message = ref('');

onMounted(async () => {
  const token = route.query.token;
  const email = route.query.email;

  if (!token || !email) {
    loading.value = false;
    message.value = 'Lien de vérification invalide.';
    return;
  }

  try {
    const res = await api.post('/verify-email', { token, email });
    success.value = true;
    message.value = res.data.message;
  } catch (err) {
    message.value = err.response?.data?.message || 'Erreur lors de la vérification.';
  } finally {
    loading.value = false;
  }
});
</script>
