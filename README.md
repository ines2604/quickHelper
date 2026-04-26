# QuickHelp — Plateforme d'entraide

Application web fullstack permettant à des utilisateurs de publier des demandes d'aide et à d'autres de proposer leur assistance.

---

## Stack technique
- **Backend** : Laravel 11 + Sanctum (API REST)
- **Frontend** : Vue 3 + Vite + Pinia + Vue Router + Axios
- **Base de données** : MySQL 

---

## Installation

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```
> API disponible sur `http://localhost:8000`

### Frontend

```bash
cd frontend
npm install
npm run dev
```
> App disponible sur `http://localhost:5173`

---

## 🚀 Fonctionnalités

### 🔐 Authentification
- Inscription
- Connexion
- Déconnexion

### 👤 Profil
- Consulter son profil
- Voir ses statistiques (demandes, missions)
- Voir la note moyenne ⭐
- Consulter les évaluations reçues

### 📌 Gestion des demandes
- Créer une demande (titre, description, budget, catégorie, urgence, deadline, lieu)
- Modifier une demande (si statut = open)
- Supprimer une demande (si aucune offre acceptée)
- Consulter ses demandes
- Voir les détails d’une demande

### 🔍 Consultation
- Voir toutes les demandes disponibles
- Filtrer les demandes (catégorie, budget, urgence, lieu)

### 📩 Offres
- Envoyer une offre pour une demande
- Consulter les offres d’une demande
- Accepter une offre
- Refuser une offre

### 💬 Messagerie
- Envoyer et recevoir des messages
- Discuter entre utilisateurs

### ✅ Suivi
- Marquer une demande comme terminée
- Consulter l’historique des demandes
- Consulter l’historique des participations

### ⭐ Évaluations
- Donner une note (1 à 5) avec commentaire
- Recevoir des évaluations
- Une seule évaluation par demande

---
