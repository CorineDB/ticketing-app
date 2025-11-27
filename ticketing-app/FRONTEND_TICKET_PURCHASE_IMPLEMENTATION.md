# Guide d'Implémentation - Flux d'Achat de Tickets (Frontend)

## 📋 Vue d'ensemble

Ce guide décrit le processus complet pour implémenter le flux d'achat de tickets avec paiement FedaPay dans votre application Vue 3.

---

## 🎯 Objectifs

1. Permettre aux utilisateurs d'acheter des tickets pour un événement
2. Collecter les informations client
3. Initier le paiement FedaPay
4. Gérer le retour après paiement

---

## 📊 Architecture du Flux

```
Page Événement → Checkout → API Backend → FedaPay → Confirmation
```

---

## 🚀 Étapes d'Implémentation

### **Étape 1 : Créer le Service API**

**Fichier :** `src/services/ticketService.ts`

**Actions à faire :**
1. Créer/ouvrir le fichier `ticketService.ts`
2. Ajouter l'interface `PurchaseTicketRequest`
3. Ajouter l'interface `PurchaseTicketResponse`
4. Créer la méthode `purchase()` qui appelle `POST /api/tickets/purchase`

**Code à ajouter :**

```typescript
import { api } from './api'

// Interface pour la requête d'achat
export interface PurchaseTicketRequest {
  ticket_type_id: string
  quantity: number
  customer: {
    firstname: string
    lastname: string
    email: string
    phone_number: string
  }
}

// Interface pour la réponse d'achat
export interface PurchaseTicketResponse {
  tickets: Array<{
    id: string
    event_id: string
    ticket_type_id: string
    buyer_name: string
    buyer_email: string
    buyer_phone: string
    status: string
    qr_code: string
  }>
  payment_url: string
  transaction_id: string
  total_amount: number
  currency: string
}

export const ticketService = {
  /**
   * Acheter des tickets et obtenir l'URL de paiement
   */
  async purchase(data: PurchaseTicketRequest): Promise<PurchaseTicketResponse> {
    const response = await api.post('/tickets/purchase', data)
    return response.data
  },

  // Autres méthodes existantes...
}
```

**Tests à faire :**
- Vérifier que l'import de `api` fonctionne
- Tester l'appel avec Postman/curl avant d'intégrer au frontend

---

### **Étape 2 : Mettre à Jour les Types TypeScript**

**Fichier :** `src/types/api.ts` (ou équivalent)

**Actions à faire :**
1. Ajouter les propriétés liées au paiement dans l'interface `TicketType`
2. Vérifier que l'interface `Ticket` a tous les champs nécessaires

**Code à vérifier/ajouter :**

```typescript
export interface TicketType {
  id: string
  event_id: string
  name: string
  description?: string
  price: number
  quota: number
  sold_count: number
  // ... autres champs
}

export interface Ticket {
  id: string
  event_id: string
  ticket_type_id: string
  buyer_name: string
  buyer_email: string
  buyer_phone: string
  status: 'pending' | 'paid' | 'issued' | 'reserved' | 'in' | 'out' | 'invalid' | 'refunded'
  qr_code: string
  created_at: string
  // ... autres champs
}
```

---

### **Étape 3 : Créer le Composable `useTicketPurchase`**

**Fichier :** `src/composables/useTicketPurchase.ts`

**Actions à faire :**
1. Créer le fichier `useTicketPurchase.ts`
2. Implémenter la logique de gestion d'état pour l'achat
3. Gérer les erreurs et le loading

**Code à créer :**

```typescript
import { ref } from 'vue'
import { ticketService, type PurchaseTicketRequest } from '@/services/ticketService'

export function useTicketPurchase() {
  const loading = ref(false)
  const error = ref<string | null>(null)

  const purchaseTickets = async (data: PurchaseTicketRequest) => {
    loading.value = true
    error.value = null

    try {
      const response = await ticketService.purchase(data)

      // Rediriger vers FedaPay
      window.location.href = response.payment_url

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erreur lors de l\'achat'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    purchaseTickets
  }
}
```

---

### **Étape 4 : Créer le Modal de Checkout**

**Fichier :** `src/components/tickets/TicketCheckoutModal.vue`

**Actions à faire :**
1. Créer le composant modal pour le formulaire de checkout
2. Ajouter validation des champs
3. Calculer le montant total dynamiquement
4. Gérer l'affichage des erreurs

**Structure du composant :**

```vue
<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="close">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header">
        <h2>Achat de tickets</h2>
        <button @click="close" class="close-btn">&times;</button>
      </div>

      <!-- Info Ticket -->
      <div class="ticket-info">
        <h3>{{ ticketType.name }}</h3>
        <p>{{ ticketType.description }}</p>
        <p class="price">Prix unitaire: {{ ticketType.price }} XOF</p>
      </div>

      <!-- Formulaire -->
      <form @submit.prevent="handleSubmit" class="checkout-form">
        <!-- Quantité -->
        <div class="form-group">
          <label for="quantity">Quantité *</label>
          <input
            id="quantity"
            v-model.number="formData.quantity"
            type="number"
            min="1"
            max="10"
            required
          />
          <small>Maximum 10 tickets par commande</small>
        </div>

        <!-- Prénom -->
        <div class="form-group">
          <label for="firstname">Prénom *</label>
          <input
            id="firstname"
            v-model="formData.customer.firstname"
            type="text"
            required
          />
        </div>

        <!-- Nom -->
        <div class="form-group">
          <label for="lastname">Nom *</label>
          <input
            id="lastname"
            v-model="formData.customer.lastname"
            type="text"
            required
          />
        </div>

        <!-- Email -->
        <div class="form-group">
          <label for="email">Email *</label>
          <input
            id="email"
            v-model="formData.customer.email"
            type="email"
            required
          />
          <small>Vos tickets seront envoyés à cette adresse</small>
        </div>

        <!-- Téléphone -->
        <div class="form-group">
          <label for="phone">Téléphone *</label>
          <input
            id="phone"
            v-model="formData.customer.phone_number"
            type="tel"
            required
            placeholder="+229XXXXXXXX ou 01XXXXXXXX"
          />
        </div>

        <!-- Total -->
        <div class="total-section">
          <div class="total-line">
            <span>Total:</span>
            <strong>{{ totalAmount }} XOF</strong>
          </div>
        </div>

        <!-- Erreur -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <!-- Boutons -->
        <div class="form-actions">
          <button type="button" @click="close" :disabled="loading">
            Annuler
          </button>
          <button type="submit" :disabled="loading" class="primary">
            {{ loading ? 'Traitement...' : 'Procéder au paiement' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTicketPurchase } from '@/composables/useTicketPurchase'
import type { TicketType } from '@/types/api'

interface Props {
  isOpen: boolean
  ticketType: TicketType
}

interface Emits {
  (e: 'close'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const { loading, error, purchaseTickets } = useTicketPurchase()

const formData = ref({
  quantity: 1,
  customer: {
    firstname: '',
    lastname: '',
    email: '',
    phone_number: ''
  }
})

const totalAmount = computed(() => {
  return props.ticketType.price * formData.value.quantity
})

const close = () => {
  if (!loading.value) {
    emit('close')
  }
}

const handleSubmit = async () => {
  try {
    await purchaseTickets({
      ticket_type_id: props.ticketType.id,
      quantity: formData.value.quantity,
      customer: formData.value.customer
    })
    // La redirection vers FedaPay se fait dans purchaseTickets()
  } catch (err) {
    // L'erreur est déjà gérée dans le composable
    console.error('Erreur lors de l\'achat:', err)
  }
}
</script>

<style scoped>
/* Ajouter vos styles ici */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  padding: 24px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 4px;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-group small {
  display: block;
  margin-top: 4px;
  color: #666;
  font-size: 12px;
}

.total-section {
  margin: 24px 0;
  padding: 16px;
  background: #f5f5f5;
  border-radius: 4px;
}

.total-line {
  display: flex;
  justify-content: space-between;
  font-size: 18px;
}

.error-message {
  padding: 12px;
  background: #fee;
  color: #c00;
  border-radius: 4px;
  margin-bottom: 16px;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

button {
  padding: 10px 20px;
  border-radius: 4px;
  border: 1px solid #ddd;
  cursor: pointer;
}

button.primary {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
```

---

### **Étape 5 : Modifier la Page de Détails d'Événement**

**Fichier :** `src/views/Events/EventDetailView.vue` et src/views/Events/EventPublicView.vue

**Actions à faire :**
1. Ajouter un état pour gérer l'ouverture du modal
2. Ajouter le composant `TicketCheckoutModal`
3. Ajouter un bouton "Acheter" pour chaque type de ticket
4. Gérer les tickets épuisés (quota atteint)

**Modifications à apporter :**

```vue
<template>
  <div class="event-detail">
    <!-- ... Autres sections de l'événement ... -->

    <!-- Section Types de Tickets -->
    <section class="ticket-types-section">
      <h2>Tickets Disponibles</h2>

      <div class="ticket-types-grid">
        <div
          v-for="ticketType in event.ticketTypes"
          :key="ticketType.id"
          class="ticket-card"
          :class="{ 'sold-out': isSoldOut(ticketType) }"
        >
          <h3>{{ ticketType.name }}</h3>
          <p class="description">{{ ticketType.description }}</p>

          <div class="ticket-info">
            <div class="price">{{ ticketType.price }} XOF</div>
            <div class="quota">
              {{ ticketType.sold_count }} / {{ ticketType.quota }} vendus
            </div>
          </div>

          <button
            @click="openCheckout(ticketType)"
            :disabled="isSoldOut(ticketType)"
            class="buy-button"
          >
            {{ isSoldOut(ticketType) ? 'Épuisé' : 'Acheter' }}
          </button>
        </div>
      </div>
    </section>

    <!-- Modal de Checkout -->
    <TicketCheckoutModal
      :is-open="checkoutModal.isOpen"
      :ticket-type="checkoutModal.selectedTicketType!"
      @close="closeCheckout"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import TicketCheckoutModal from '@/components/tickets/TicketCheckoutModal.vue'
import type { TicketType } from '@/types/api'

// ... Votre code existant pour charger l'événement ...

const checkoutModal = ref<{
  isOpen: boolean
  selectedTicketType: TicketType | null
}>({
  isOpen: false,
  selectedTicketType: null
})

const isSoldOut = (ticketType: TicketType) => {
  return ticketType.sold_count >= ticketType.quota
}

const openCheckout = (ticketType: TicketType) => {
  checkoutModal.value = {
    isOpen: true,
    selectedTicketType: ticketType
  }
}

const closeCheckout = () => {
  checkoutModal.value = {
    isOpen: false,
    selectedTicketType: null
  }
}
</script>

<style scoped>
/* Ajouter vos styles */
.ticket-types-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.ticket-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  transition: transform 0.2s;
}

.ticket-card:hover:not(.sold-out) {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.ticket-card.sold-out {
  opacity: 0.6;
  background: #f5f5f5;
}

.buy-button {
  width: 100%;
  padding: 12px;
  background: #28a745;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
}

.buy-button:disabled {
  background: #ccc;
  cursor: not-allowed;
}
</style>
```

---

### **Étape 6 : Créer la Page de Résultat de Paiement**

**Fichier :** `src/views/Payment/PaymentResultView.vue`

**Actions à faire :**
1. Créer le dossier `src/views/Payment/` si nécessaire
2. Créer le composant `PaymentResultView.vue`
3. Lire les query params (status, transaction_id, reference)
4. Afficher le bon message selon le statut

**Code à créer :**

```vue
<template>
  <div class="payment-result-container">
    <!-- Succès -->
    <div v-if="status === 'approved'" class="result-card success">
      <div class="icon">✅</div>
      <h1>Paiement réussi !</h1>
      <p>Votre paiement a été confirmé avec succès.</p>

      <div class="details">
        <div class="detail-row">
          <span class="label">Transaction:</span>
          <span class="value">{{ transactionId }}</span>
        </div>
        <div v-if="reference" class="detail-row">
          <span class="label">Référence:</span>
          <span class="value">{{ reference }}</span>
        </div>
      </div>

      <div class="info-box">
        <p>📧 Vos tickets ont été envoyés à votre adresse email.</p>
        <p>Consultez votre boîte de réception et vos spams.</p>
      </div>

      <div class="actions">
        <button @click="goToHome" class="primary-btn">
          Retour à l'accueil
        </button>
        <button @click="goToEvents" class="secondary-btn">
          Voir les événements
        </button>
      </div>
    </div>

    <!-- Annulé / Refusé -->
    <div v-else-if="status === 'declined' || status === 'canceled'" class="result-card error">
      <div class="icon">❌</div>
      <h1>Paiement annulé</h1>
      <p>Votre paiement n'a pas été complété.</p>

      <div class="info-box">
        <p>Aucun montant n'a été débité de votre compte.</p>
        <p>Vous pouvez réessayer à tout moment.</p>
      </div>

      <div class="actions">
        <button @click="goToEvents" class="primary-btn">
          Retour aux événements
        </button>
      </div>
    </div>

    <!-- En attente / Statut inconnu -->
    <div v-else class="result-card pending">
      <div class="icon">⏳</div>
      <h1>Vérification en cours...</h1>
      <p>Nous vérifions le statut de votre paiement.</p>

      <div class="info-box">
        <p>Cela peut prendre quelques instants.</p>
        <p>Vous recevrez un email de confirmation.</p>
      </div>

      <div class="actions">
        <button @click="checkStatus" class="primary-btn">
          Vérifier le statut
        </button>
        <button @click="goToHome" class="secondary-btn">
          Retour à l'accueil
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const status = ref(route.query.status as string)
const transactionId = ref(route.query.transaction_id as string)
const reference = ref(route.query.reference as string)

const goToHome = () => {
  router.push('/')
}

const goToEvents = () => {
  router.push('/events')
}

const checkStatus = () => {
  // Recharger la page pour récupérer un statut à jour
  window.location.reload()
}

onMounted(() => {
  // Log pour débuggage
  console.log('Payment result:', { status: status.value, transactionId: transactionId.value, reference: reference.value })

  // ⚠️ IMPORTANT: Ne JAMAIS faire confiance uniquement à ces paramètres
  // Le webhook backend est la source de vérité
  // Ces informations sont uniquement pour l'UX utilisateur
})
</script>

<style scoped>
.payment-result-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
  background: #f5f5f5;
}

.result-card {
  background: white;
  border-radius: 12px;
  padding: 40px;
  max-width: 500px;
  width: 100%;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.icon {
  font-size: 64px;
  margin-bottom: 20px;
}

h1 {
  margin-bottom: 12px;
  color: #333;
}

.result-card.success h1 {
  color: #28a745;
}

.result-card.error h1 {
  color: #dc3545;
}

.result-card.pending h1 {
  color: #ffc107;
}

.details {
  margin: 24px 0;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  text-align: left;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.label {
  font-weight: 500;
  color: #666;
}

.value {
  font-family: monospace;
  color: #333;
}

.info-box {
  margin: 24px 0;
  padding: 16px;
  background: #e7f3ff;
  border-left: 4px solid #007bff;
  border-radius: 4px;
}

.info-box p {
  margin: 8px 0;
}

.actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
  flex-wrap: wrap;
  justify-content: center;
}

button {
  padding: 12px 24px;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.primary-btn {
  background: #007bff;
  color: white;
}

.primary-btn:hover {
  background: #0056b3;
}

.secondary-btn {
  background: #6c757d;
  color: white;
}

.secondary-btn:hover {
  background: #545b62;
}
</style>
```

---

### **Étape 7 : Configurer les Routes**

**Fichier :** `src/router/index.ts`

**Actions à faire :**
1. Ajouter la route pour la page de résultat de paiement
2. S'assurer que la route est accessible sans authentification

**Code à ajouter :**

```typescript
// Dans votre configuration de routes
{
  path: '/payment/result',
  name: 'payment-result',
  component: () => import('@/views/Payment/PaymentResultView.vue'),
  meta: {
    requiresAuth: false // Accessible sans authentification
  }
}
```

---

## ✅ Checklist de Validation

### Backend
- [ ] Variable `FRONTEND_URL` configurée dans `.env`
- [ ] Route `POST /api/tickets/purchase` accessible
- [ ] Route `GET /api/payment/callback` accessible
- [ ] Webhook FedaPay configuré dans le dashboard FedaPay

### Frontend
- [ ] Service `ticketService.purchase()` créé
- [ ] Types TypeScript définis
- [ ] Composable `useTicketPurchase` créé
- [ ] Modal `TicketCheckoutModal` créé
- [ ] Page `EventDetailView` modifiée avec bouton "Acheter"
- [ ] Page `PaymentResultView` créée
- [ ] Routes configurées dans le router

---

## 🧪 Tests à Effectuer

### Test 1 : Affichage des tickets
1. Aller sur la page d'un événement
2. Vérifier que tous les types de tickets s'affichent
3. Vérifier que le bouton "Épuisé" apparaît si quota atteint

### Test 2 : Ouverture du modal
1. Cliquer sur "Acheter"
2. Vérifier que le modal s'ouvre
3. Vérifier que les informations du ticket sont correctes

### Test 3 : Validation du formulaire
1. Essayer de soumettre le formulaire vide → Doit afficher les erreurs
2. Remplir tous les champs
3. Vérifier le calcul du total (quantité × prix)

### Test 4 : Achat de tickets
1. Remplir le formulaire avec des vraies informations
2. Soumettre
3. Vérifier la redirection vers FedaPay
4. Compléter le paiement sur FedaPay (mode sandbox)
5. Vérifier le retour sur `/payment/result`

### Test 5 : Gestion des erreurs
1. Tester avec un `ticket_type_id` invalide
2. Tester avec une quantité supérieure au quota disponible
3. Vérifier que les messages d'erreur s'affichent

---

## 🔒 Sécurité & Bonnes Pratiques

### ✅ À FAIRE
- Toujours valider les données côté backend (déjà fait)
- Ne jamais faire confiance au statut dans l'URL du callback
- Le webhook est la source de vérité pour la confirmation de paiement
- Logger toutes les erreurs pour le débogage

### ❌ À NE PAS FAIRE
- Ne jamais marquer un paiement comme confirmé basé sur l'URL
- Ne jamais exposer de clés API dans le code frontend
- Ne jamais contourner la validation backend

---

## 📞 Support & Débogage

### Erreurs Communes

**Erreur : "Route [payment.callback] not defined"**
- ✅ Résolu ! La route a été créée

**Erreur : "Quota insuffisant"**
- Vérifier que le `ticket_type.quota` > `ticket_type.sold_count`

**Erreur : "Failed to create payment transaction"**
- Vérifier la configuration FedaPay dans `.env`
- Vérifier que `FEDAPAY_SECRET_KEY` est correcte
- Vérifier que vous êtes en mode `sandbox` pour les tests

**Redirection ne fonctionne pas après paiement**
- Vérifier que `FRONTEND_URL` est correctement configurée dans `.env`
- Vérifier que la route `/payment/result` existe dans le router

### Logs à Consulter

**Backend :**
```bash
tail -f storage/logs/laravel.log
```

**Frontend :**
- Console du navigateur (F12)
- Network tab pour voir les requêtes API

---

## 🎉 Conclusion

Une fois toutes ces étapes complétées, votre flux d'achat de tickets sera fonctionnel !

**Flux final :**
```
Utilisateur → Choisit tickets → Remplit formulaire → Paie sur FedaPay → Reçoit tickets
```

**Questions ?** Consultez la documentation FedaPay : https://docs.fedapay.com/
