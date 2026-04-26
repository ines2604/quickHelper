import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8000/api', // URL de votre API Laravel
    withCredentials: true, // Important pour les sessions ou cookies Sanctum
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Intercepteur pour ajouter le token si vous utilisez l'auth par Token (Sanctum SPA)
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;