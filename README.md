# QuickHelp — Plateforme d'entraide

Application web fullstack permettant à des utilisateurs de publier des demandes d'aide et à d'autres de proposer leur assistance.

---

## Stack technique
- **Backend** : Laravel 11 + Sanctum (API REST)
- **Frontend** : Vue 3 + Vite + Pinia + Vue Router + Axios
- **Base de données** : SQLite (dev) / MySQL (prod)

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

## Fonctionnalités

### Utilisateur (Demandeur)
- ✅ S'inscrire / Se connecter / Se déconnecter
- ✅ Publier une demande d'aide (titre, description, budget, catégorie, urgence, deadline, lieu)
- ✅ Modifier sa demande (uniquement si statut "open")
- ✅ Supprimer sa demande
- ✅ Voir la liste des offres reçues
- ✅ Accepter une aide proposée
- ✅ Discuter (messagerie) avec l'aidant
- ✅ Marquer la demande comme terminée
- ✅ Évaluer l'aidant (note 1-5 + commentaire)
- ✅ Voir l'historique de ses demandes

### Utilisateur (Aidant)
- ✅ Consulter les demandes disponibles (avec filtres)
- ✅ Accepter une demande (envoyer une offre)
- ✅ Contacter le demandeur (messagerie)
- ✅ Marquer la tâche comme terminée
- ✅ Recevoir une évaluation
- ✅ Voir son historique de missions

### Profil
- ✅ Voir ses statistiques (demandes, missions)
- ✅ Consulter ses évaluations reçues
- ✅ Rating moyen affiché

---

## Routes API

| Méthode | Route | Description |
|---------|-------|-------------|
| POST | `/api/login` | Connexion |
| POST | `/api/register` | Inscription |
| POST | `/api/logout` | Déconnexion |
| GET | `/api/me` | Utilisateur connecté |
| GET | `/api/requests` | Demandes ouvertes |
| POST | `/api/requests` | Créer une demande |
| GET | `/api/requests/history` | Historique utilisateur |
| GET | `/api/requests/{id}` | Détail d'une demande |
| PUT | `/api/requests/{id}` | Modifier une demande |
| DELETE | `/api/requests/{id}` | Supprimer une demande |
| POST | `/api/requests/{id}/complete` | Marquer terminée |
| POST | `/api/offers/{requestId}` | Envoyer une offre |
| POST | `/api/offers/{id}/accept` | Accepter une offre |
| GET | `/api/messages/{requestId}` | Messages d'une demande |
| POST | `/api/messages` | Envoyer un message |
| POST | `/api/ratings` | Évaluer un aidant |
| GET | `/api/ratings/received` | Évaluations reçues |

---

## Corrections apportées

1. **`auth.js`** — Ajout de `fetchUser()`, `isAuthenticated` persistant au rechargement
2. **`router/index.js`** — Routes `/requests/:id/edit` et `/profile` ajoutées, guard corrigé
3. **`dashboard.vue`** — Méthodes PHP `.isOpen()` remplacées par comparaisons JS de statut
4. **`requestdetail.vue`** — Messagerie avec expéditeur, `canChat` correct, étoiles interactives
5. **`requestlist.vue`** — Filtres recherche/catégorie/urgence, rating affiché
6. **`RequestController`** — Ajout `destroy()`, `show()` avec `messages.sender`
7. **`RatingController`** — Ajout `received()`, `request_id` dans la création
8. **`Rating` model** — `request_id` ajouté au `fillable`
9. **Migration ratings** — `request_id` ajouté
10. **`api.php`** — Routes `DELETE /requests/{id}` et `GET /ratings/received` ajoutées
