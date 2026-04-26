<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import Navbar from './components/navbar.vue'

const authStore = useAuthStore()
onMounted(() => {
  authStore.fetchUser()
})
</script>

<template>
  <div id="app">
    <Navbar />
    <main>
      <router-view />
    </main>
  </div>
</template>

<style>
@import './assets/main.css';
</style>
