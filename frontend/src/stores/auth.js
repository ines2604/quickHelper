import { defineStore } from 'pinia';
import api from '../api/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        isAuthenticated: !!localStorage.getItem('token'),
    }),
    actions: {
        async fetchUser() {
            if (!this.token) return;
            try {
                const response = await api.get('/me');
                this.user = response.data;
                this.isAuthenticated = true;
            } catch (error) {
                this.user = null;
                this.token = null;
                this.isAuthenticated = false;
                localStorage.removeItem('token');
            }
        },
        async login(credentials) {
            try {
                const response = await api.post('/login', credentials);
                this.user = response.data.user;
                this.token = response.data.token;
                this.isAuthenticated = true;
                if (this.token) localStorage.setItem('token', this.token);
                return { success: true };
            } catch (error) {
                const data = error.response?.data;
                if (data?.email_not_verified) {
                    return { success: false, emailNotVerified: true, message: data.message };
                }
                return { success: false, message: data?.message || 'Email ou mot de passe incorrect.' };
            }
        },
        async register(userData) {
            try {
                const response = await api.post('/register', userData);
                return { success: true, message: response.data.message };
            } catch (error) {
                let message = 'Erreur inconnue';
                if (error.response?.data?.message) {
                    message = error.response.data.message;
                } else if (error.response?.data?.errors) {
                    const errors = error.response.data.errors;
                    message = Object.values(errors).flat().join(' ');
                }
                return { success: false, message };
            }
        },
        async logout() {
            try { await api.post('/logout'); } catch (error) {}
            this.user = null;
            this.token = null;
            this.isAuthenticated = false;
            localStorage.removeItem('token');
        }
    }
});
