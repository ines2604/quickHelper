<template>
  <div class="page-container" v-if="request">
    <!-- Header -->
    <div class="detail-header">
      <router-link to="/requests" class="back-link">← Retour</router-link>
      <div class="detail-title-row">
        <h1 class="page-title">{{ request.title }}</h1>
        <span class="status-badge" :class="'status-' + request.status">{{ statusLabel(request.status) }}</span>
      </div>
      <div class="detail-meta">
        <span>💰 {{ request.budget }} TND</span>
        <span>🏷️ {{ categoryLabel(request.category) }}</span>
        <span>📍 {{ request.location || 'Non précisé' }}</span>
        <span>📅 Deadline: {{ formatDate(request.deadline) }}</span>
        <span>👤 Publié par: <strong>{{ request.user?.name }}</strong></span>
      </div>
      <p class="detail-description">{{ request.description }}</p>

      <!-- Status change for owner (#5) -->
      <div v-if="isOwner && request.status !== 'open'" class="action-bar">
        <label class="action-label">Changer le statut :</label>
        <div class="status-actions">
          <button v-if="request.status !== 'open'" @click="changeStatus('open')" class="btn btn-sm btn-outline">🔓 Réouvrir</button>
          <button v-if="request.status === 'in_progress'" @click="changeStatus('completed')" class="btn btn-sm btn-success">✅ Marquer terminé</button>
        </div>
      </div>

      <!-- Action: Marquer terminé si aidant en cours -->
      <div v-if="isHelper && request.status === 'in_progress'" class="action-bar">
        <button @click="markCompleted" class="btn btn-success">✅ Marquer comme terminé</button>
      </div>
    </div>

    <!-- Onglets -->
    <div class="tabs">
      <button @click="activeTab = 'chat'" :class="['tab-btn', activeTab === 'chat' ? 'active' : '']">
        💬 Discussion
        <span v-if="messages.length" class="tab-count">{{ messages.length }}</span>
      </button>
      <button @click="activeTab = 'offers'" :class="['tab-btn', activeTab === 'offers' ? 'active' : '']">
        🙋 Offres
        <span v-if="offers.length" class="tab-count">{{ offers.length }}</span>
      </button>
      <button
        v-if="isOwner && request.status === 'completed' && request.accepted_helper_id && !alreadyRated"
        @click="activeTab = 'rating'"
        :class="['tab-btn', activeTab === 'rating' ? 'active' : '']"
      >
        ⭐ Noter l'aidant
      </button>
    </div>

    <!-- Chat -->
    <div v-if="activeTab === 'chat'" class="tab-content">
      <div v-if="!canChat" class="info-box">
        💡 La messagerie est disponible une fois qu'une offre a été envoyée ou acceptée.
      </div>
      <div v-else>
        <div class="chat-box" ref="chatBox">
          <div v-if="messages.length === 0" class="empty-chat">Aucun message. Commencez la discussion !</div>
          <div v-for="msg in messages" :key="msg.id" :class="['message', isMe(msg) ? 'message-me' : 'message-other']">
            <div class="message-author">{{ isMe(msg) ? 'Vous' : (msg.sender?.name || 'Aidant') }}</div>
            <div class="message-bubble">
              <span v-if="msg.content">{{ msg.content }}</span>
              <!-- File attachment in message (#8) -->
              <div v-if="msg.file_url" class="message-file">
                <a v-if="msg.file_type?.startsWith('image/')" :href="msg.file_url" target="_blank">
                  <img :src="msg.file_url" class="message-image" :alt="msg.file_name" />
                </a>
                <a v-else :href="msg.file_url" target="_blank" class="file-link">
                  📎 {{ msg.file_name }}
                </a>
              </div>
            </div>
            <div class="message-time">{{ formatTime(msg.created_at) }}</div>
          </div>
        </div>
        <form @submit.prevent="sendMessage" class="chat-form">
          <div class="chat-input-area">
            <input v-model="newMessage" placeholder="Votre message..." class="chat-input" />
            <label class="file-btn" title="Joindre un fichier">
              📎
              <input type="file" @change="onFileSelected" accept="image/*,.pdf" style="display:none" />
            </label>
          </div>
          <div v-if="selectedFile" class="file-preview">
            📎 {{ selectedFile.name }}
            <button type="button" @click="selectedFile = null" class="remove-file">✕</button>
          </div>
          <button type="submit" class="btn btn-primary" :disabled="!newMessage.trim() && !selectedFile">Envoyer</button>
        </form>
      </div>
    </div>

    <!-- Offres -->
    <div v-if="activeTab === 'offers'" class="tab-content">
      <!-- Propriétaire voit les offres reçues -->
      <div v-if="isOwner && request.status === 'open'">
        <h3 class="section-subtitle">Offres reçues</h3>
        <div v-if="offers.length === 0" class="empty-state">Aucune offre reçue pour l'instant.</div>
        <div v-for="offer in offers" :key="offer.id" class="offer-card">
          <div class="offer-header">
            <div class="requester-info">
              <span class="avatar-sm">{{ offer.helper?.name?.charAt(0)?.toUpperCase() }}</span>
              <div>
                <strong>{{ offer.helper?.name }}</strong>
                <div v-if="offer.helper?.rating_avg > 0" class="rating-sm">⭐ {{ offer.helper.rating_avg?.toFixed(1) }} ({{ offer.helper.rating_count }} avis)</div>
              </div>
            </div>
            <span class="offer-budget">{{ offer.proposed_budget }} TND</span>
          </div>
          <p class="offer-message">{{ offer.message }}</p>
          <!-- Offer file attachment (#8) -->
          <div v-if="offer.file_path" class="offer-file">
            <a v-if="offer.file_type?.startsWith('image/')" :href="fileUrl(offer.file_path)" target="_blank">
              <img :src="fileUrl(offer.file_path)" class="offer-image" :alt="offer.file_name" />
            </a>
            <a v-else :href="fileUrl(offer.file_path)" target="_blank" class="file-link">
              📎 {{ offer.file_name }}
            </a>
          </div>
          <div class="offer-actions">
            <span v-if="offer.status === 'accepted'" class="badge-accepted">✅ Acceptée</span>
            <span v-else-if="offer.status === 'rejected'" class="badge-rejected">❌ Refusée</span>
            <template v-else>
              <button @click="acceptOffer(offer.id)" class="btn btn-success btn-sm">✅ Accepter</button>
              <!-- Reject button (#4) -->
              <button @click="rejectOffer(offer.id)" class="btn btn-danger btn-sm">❌ Refuser</button>
            </template>
          </div>
        </div>
      </div>

      <!-- Non-propriétaire peut faire une offre -->
      <div v-else-if="!isOwner && request.status === 'open'">
        <div v-if="myOffer">
          <div class="info-box success-box">
            ✅ Vous avez déjà soumis une offre ({{ myOffer.proposed_budget }} TND). En attente de réponse.
          </div>
        </div>
        <div v-else>
          <h3 class="section-subtitle">Proposer votre aide</h3>
          <form @submit.prevent="makeOffer" class="offer-form">
            <label>Message (pourquoi vous êtes le bon aidant ?)</label>
            <textarea v-model="offerMessage" placeholder="Décrivez votre expérience et motivation..." rows="4" required></textarea>
            <label>Prix proposé (TND)</label>
            <input v-model.number="offerBudget" type="number" min="0" placeholder="Votre prix" />
            <!-- File attachment for offer (#8) -->
            <label>Joindre un fichier (image ou PDF, optionnel)</label>
            <div class="file-upload-zone">
              <input type="file" @change="onOfferFileSelected" accept="image/*,.pdf" id="offer-file" style="display:none" />
              <label for="offer-file" class="file-upload-label">
                📎 {{ offerFile ? offerFile.name : 'Choisir un fichier...' }}
              </label>
              <button v-if="offerFile" type="button" @click="offerFile = null" class="remove-file">✕ Supprimer</button>
            </div>
            <button type="submit" class="btn btn-primary" :disabled="sendingOffer">
              {{ sendingOffer ? 'Envoi...' : 'Envoyer mon offre' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Si déjà en cours ou terminée -->
      <div v-else>
        <div class="info-box">
          <p v-if="request.status === 'in_progress'">
            Mission en cours avec <strong>{{ request.accepted_helper?.name || 'un aidant' }}</strong>.
          </p>
          <p v-else-if="request.status === 'completed'">Cette demande est terminée.</p>
          <p v-else>Les offres ne sont plus disponibles.</p>
        </div>
      </div>
    </div>

    <!-- Notation (#6) -->
    <div v-if="activeTab === 'rating'" class="tab-content">
      <h3 class="section-subtitle">⭐ Évaluer l'aidant</h3>
      <div v-if="alreadyRated" class="info-box success-box">Vous avez déjà noté cet aidant. Merci !</div>
      <form v-else @submit.prevent="submitRating" class="rating-form">
        <label>Note (1 à 5 étoiles)</label>
        <div class="star-rating">
          <button
            v-for="star in 5" :key="star" type="button"
            @click="ratingForm.rating = star"
            :class="['star-btn', star <= ratingForm.rating ? 'star-active' : '']"
          >★</button>
        </div>
        <span class="rating-label">{{ ratingLabel(ratingForm.rating) }}</span>
        <label>Commentaire (optionnel)</label>
        <textarea v-model="ratingForm.comment" placeholder="Partagez votre expérience..." rows="3"></textarea>
        <button type="submit" class="btn btn-primary">Envoyer l'évaluation</button>
      </form>
    </div>
  </div>

  <div v-else class="page-container">
    <div class="loading-state">Chargement...</div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../api/axios';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const request = ref(null);
const messages = ref([]);
const offers = ref([]);
const activeTab = ref('offers');
const newMessage = ref('');
const offerMessage = ref('');
const offerBudget = ref(0);
const ratingForm = ref({ rating: 5, comment: '' });
const alreadyRated = ref(false);
const chatBox = ref(null);
const selectedFile = ref(null);
const offerFile = ref(null);
const sendingOffer = ref(false);

const BACKEND_URL = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8000';

const isOwner = computed(() => authStore.user && request.value && authStore.user.id === request.value.user_id);
const isHelper = computed(() => authStore.user && request.value && authStore.user.id === request.value.accepted_helper_id);
const myOffer = computed(() => offers.value.find(o => o.helper_id === authStore.user?.id));
const canChat = computed(() => {
  if (!request.value || !authStore.user) return false;
  if (isOwner.value || isHelper.value) return true;
  return offers.value.some(o => o.helper_id === authStore.user.id);
});

const isMe = (msg) => authStore.user && msg.sender_id === authStore.user.id;
const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR') : 'N/A';
const formatTime = (date) => date ? new Date(date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : '';
const statusLabel = (s) => ({ open: 'Disponible', in_progress: 'En cours', completed: 'Terminée', accepted: 'Acceptée' }[s] || s);
const categoryLabel = (c) => ({ general: 'Général', transport: 'Transport', education: 'Éducation', repair: 'Réparation', health: 'Santé', housekeeping: 'Ménage', it_support: 'Informatique', other: 'Autre' }[c] || c);
const ratingLabel = (r) => (['', 'Mauvais 😞', 'Passable 😐', 'Bien 🙂', 'Très bien 😊', 'Excellent 🌟'])[r] || '';
const fileUrl = (path) => `${BACKEND_URL}/storage/${path}`;

const onFileSelected = (e) => { selectedFile.value = e.target.files[0] || null; };
const onOfferFileSelected = (e) => { offerFile.value = e.target.files[0] || null; };

onMounted(async () => { await loadRequest(); });

const loadRequest = async () => {
  try {
    const res = await api.get(`/requests/${route.params.id}`);
    request.value = res.data;
    offers.value = res.data.help_offers || [];
    await loadMessages();
    // Check if already rated
    if (isOwner.value && res.data.status === 'completed') {
      try {
        const ratings = await api.get('/ratings/received');
        alreadyRated.value = false; // server-side check is handled
      } catch {}
    }
  } catch (error) {
    console.error('Erreur chargement demande', error);
  }
};

const loadMessages = async () => {
  try {
    const res = await api.get(`/messages/${route.params.id}`);
    messages.value = res.data;
    await nextTick();
    if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight;
  } catch {
    messages.value = [];
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim() && !selectedFile.value) return;
  const receiverId = isOwner.value ? request.value.accepted_helper_id : request.value.user_id;
  if (!receiverId) { alert('Impossible d\'envoyer: pas d\'interlocuteur désigné.'); return; }

  const formData = new FormData();
  formData.append('request_id', request.value.id);
  formData.append('receiver_id', receiverId);
  if (newMessage.value.trim()) formData.append('content', newMessage.value);
  if (selectedFile.value) formData.append('file', selectedFile.value);

  await api.post('/messages', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
  newMessage.value = '';
  selectedFile.value = null;
  await loadMessages();
};

const makeOffer = async () => {
  sendingOffer.value = true;
  try {
    const formData = new FormData();
    formData.append('message', offerMessage.value);
    formData.append('proposed_budget', offerBudget.value);
    if (offerFile.value) formData.append('file', offerFile.value);

    await api.post(`/offers/${request.value.id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
    await loadRequest();
    activeTab.value = 'chat';
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur lors de l\'envoi de l\'offre');
  } finally {
    sendingOffer.value = false;
  }
};

const acceptOffer = async (offerId) => {
  if (!confirm('Accepter cette offre d\'aide ?')) return;
  try {
    await api.post(`/offers/${offerId}/accept`);
    await loadRequest();
    activeTab.value = 'chat';
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur');
  }
};

const rejectOffer = async (offerId) => {
  if (!confirm('Refuser cette offre d\'aide ?')) return;
  try {
    await api.post(`/offers/${offerId}/reject`);
    await loadRequest();
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur');
  }
};

const markCompleted = async () => {
  if (!confirm('Marquer cette mission comme terminée ?')) return;
  try {
    await api.post(`/requests/${request.value.id}/complete`);
    await loadRequest();
    activeTab.value = 'rating';
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur');
  }
};

const changeStatus = async (status) => {
  if (!confirm(`Changer le statut en "${statusLabel(status)}" ?`)) return;
  try {
    await api.patch(`/requests/${request.value.id}/status`, { status });
    await loadRequest();
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur');
  }
};

const submitRating = async () => {
  try {
    await api.post('/ratings', {
      request_id: request.value.id,
      to_user_id: request.value.accepted_helper_id,
      rating: ratingForm.value.rating,
      comment: ratingForm.value.comment
    });
    alreadyRated.value = true;
    alert('Merci pour votre évaluation ! ⭐');
    await loadRequest();
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur lors de l\'évaluation');
  }
};
</script>
