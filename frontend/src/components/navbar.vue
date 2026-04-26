<template>
  <nav class="navbar">
    <div class="logo">
      <span class="logo-icon">🤝</span>
      <router-link to="/">QuickHelp</router-link>
    </div>
    <div class="nav-links">
      <template v-if="authStore.isAuthenticated">
        <router-link to="/requests" class="nav-item">🔍 Trouver de l'aide</router-link>
        <router-link to="/requests/create" class="nav-item nav-create">+ Publier</router-link>
        <router-link to="/dashboard" class="nav-item">📋 Tableau de bord</router-link>
        <router-link to="/profile" class="nav-item nav-profile">
          <span class="avatar-xs">{{ authStore.user?.name?.charAt(0)?.toUpperCase() }}</span>
          {{ authStore.user?.name?.split(' ')[0] }}
        </router-link>
        <button @click="handleLogout" class="logout-btn">Déconnexion</button>
      </template>
      <template v-else>
        <router-link to="/login" class="nav-item">Se connecter</router-link>
        <router-link to="/register" class="nav-item signup-link">S'inscrire</router-link>
      </template>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
const authStore = useAuthStore();
const router = useRouter();
const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.navbar {
  position: sticky; top: 0; z-index: 100;
  display: flex; justify-content: space-between; align-items: center;
  padding: 0.85rem 2rem;
  background: white;
  box-shadow: 0 1px 0 #e5e7eb, 0 4px 16px -4px rgba(0,0,0,0.07);
}
.logo { display: flex; align-items: center; gap: 10px; font-size: 1.3rem; font-weight: 800; color: #16a34a; }
.logo-icon { font-size: 1.6rem; }
.logo a { text-decoration: none; color: inherit; }
.nav-links { display: flex; align-items: center; gap: 8px; }
.nav-item { text-decoration: none; color: #374151; font-weight: 500; font-size: 0.9rem; padding: 7px 14px; border-radius: 8px; transition: background 0.15s, color 0.15s; }
.nav-item:hover { background: #f0fdf4; color: #16a34a; }
.nav-create { background: #dcfce7; color: #16a34a; font-weight: 700; }
.nav-create:hover { background: #16a34a; color: white; }
.nav-profile { display: flex; align-items: center; gap: 7px; }
.avatar-xs { width: 26px; height: 26px; border-radius: 50%; background: #16a34a; color: white; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.signup-link { background: #16a34a; color: white !important; padding: 8px 20px; border-radius: 8px; font-weight: 700; }
.signup-link:hover { background: #15803d; }
.logout-btn { border: 1.5px solid #ef4444; background: white; color: #ef4444; padding: 7px 14px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.9rem; transition: all 0.15s; }
.logout-btn:hover { background: #ef4444; color: white; }
</style>
