# Plan d'Implémentation - Frontend Scan 2FA

## 📋 Contexte

Le backend du système de scan à 2 facteurs est **100% fonctionnel** avec les modifications suivantes :

### ✅ Backend Complété

1. **QR Code modifié** (`app/Services/TicketService.php`)
   - Pointe vers le frontend : `http://localhost:5173/dashboard/scan?t={ticket_id}&sig={signature}`
   - Testé et validé avec ZxingPHP

2. **ScanService amélioré** (`app/Services/ScanService.php`)
   - `/api/scan/request` retourne maintenant :
     - `scan_session_token`
     - `scan_nonce` ✅ (nouveau)
     - `expires_in`
     - `ticket` avec toutes les infos ✅ (nouveau)

3. **API Endpoints disponibles**
   - `POST /api/scan/request` (public, sans auth) ✅
   - `POST /api/scan/confirm` (authentifié, agent uniquement) ✅

### 🎯 Objectif

Implémenter le frontend Vue.js pour permettre aux agents de scanner les tickets avec 2 méthodes :
1. **Scanner externe** → Ouvre l'URL du QR dans le navigateur
2. **Widget intégré** → Scan via caméra de l'appareil dans l'interface

---

## 🏗️ Architecture Frontend

### Structure des Fichiers

```
ticketing-app/src/
├── composables/
│   └── useScan.ts              # Logique de scan réutilisable
├── components/
│   ├── scan/
│   │   ├── QrScanner.vue       # Widget scanner avec html5-qrcode
│   │   ├── TicketInfoCard.vue  # Carte d'affichage des infos ticket
│   │   ├── ScanResult.vue      # Écran de résultat (succès/erreur)
│   │   └── CountdownTimer.vue  # Timer 20 secondes
├── views/
│   ├── Dashboard/
│   │   ├── ScannerDashboard.vue   # Dashboard avec bouton scan
│   │   └── ScanPage.vue           # Page de scan (route directe)
├── services/
│   └── scanService.ts          # Service API pour les scans
└── types/
    └── scan.ts                 # Types TypeScript pour le scan
```

---

## 📦 Dépendances à Installer

```bash
npm install html5-qrcode
npm install @vueuse/core
```

**html5-qrcode** : Pour le widget de scan avec caméra
**@vueuse/core** : Pour useInterval, useTimeoutFn, etc.

---

## 🔧 Implémentation Détaillée

### 1. Types TypeScript

**Fichier:** `src/types/scan.ts`

```typescript
export interface ScanRequestPayload {
  ticket_id: string
  sig: string
}

export interface TicketInfo {
  id: string
  code: string
  status: 'issued' | 'paid' | 'in' | 'out'
  buyer_name: string
  buyer_email: string
  buyer_phone: string
  event: {
    id: string
    title: string
    start_datetime: string
    end_datetime: string
  }
  ticket_type: {
    id: string
    name: string
    price: string
  }
}

export interface ScanRequestResponse {
  scan_session_token: string
  scan_nonce: string
  expires_in: number
  ticket: TicketInfo
}

export interface ScanConfirmPayload {
  scan_session_token: string
  scan_nonce: string
  gate_id: string
  agent_id: string
  action: 'in' | 'out' | 'entry' | 'exit'
}

export interface ScanConfirmResponse {
  valid: boolean
  code: string
  message: string
  ticket: TicketInfo
  scan_log_id?: string
}

export type ScanStatus = 'idle' | 'scanning' | 'validating' | 'success' | 'error'
```

---

### 2. Service API

**Fichier:** `src/services/scanService.ts`

```typescript
import { api } from '@/config/api'
import type {
  ScanRequestPayload,
  ScanRequestResponse,
  ScanConfirmPayload,
  ScanConfirmResponse
} from '@/types/scan'

export const scanService = {
  /**
   * Niveau 1: Validation du QR code (public)
   */
  async scanRequest(payload: ScanRequestPayload): Promise<ScanRequestResponse> {
    const { data } = await api.post('/scan/request', payload)
    return data
  },

  /**
   * Niveau 2: Confirmation de l'entrée (authentifié)
   */
  async scanConfirm(payload: ScanConfirmPayload): Promise<ScanConfirmResponse> {
    const { data } = await api.post('/scan/confirm', payload)
    return data
  }
}
```

---

### 3. Composable Réutilisable

**Fichier:** `src/composables/useScan.ts`

```typescript
import { ref, computed } from 'vue'
import { scanService } from '@/services/scanService'
import type {
  TicketInfo,
  ScanRequestResponse,
  ScanConfirmResponse,
  ScanStatus
} from '@/types/scan'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

export const useScan = () => {
  const authStore = useAuthStore()
  const toast = useToast()

  // État
  const status = ref<ScanStatus>('idle')
  const ticketInfo = ref<TicketInfo | null>(null)
  const sessionData = ref<ScanRequestResponse | null>(null)
  const scanResult = ref<ScanConfirmResponse | null>(null)
  const expiresIn = ref(20)
  const error = ref<string | null>(null)

  // Computed
  const loading = computed(() => status.value === 'scanning' || status.value === 'validating')
  const hasTicketInfo = computed(() => !!ticketInfo.value)
  const isExpired = computed(() => expiresIn.value <= 0)

  /**
   * Niveau 1: Scanner le QR code
   */
  async function scanTicket(ticketId: string, signature: string) {
    status.value = 'scanning'
    error.value = null

    try {
      const response = await scanService.scanRequest({
        ticket_id: ticketId,
        sig: signature
      })

      sessionData.value = response
      ticketInfo.value = response.ticket
      expiresIn.value = response.expires_in

      // Démarrer le countdown
      startCountdown()

      status.value = 'idle'
      return response

    } catch (err: any) {
      status.value = 'error'
      error.value = err.response?.data?.message || 'Erreur lors du scan du QR code'
      toast.error(error.value)
      throw err
    }
  }

  /**
   * Niveau 2: Confirmer l'entrée
   */
  async function confirmEntry(action: 'in' | 'out' = 'in') {
    if (!sessionData.value) {
      throw new Error('Aucune session de scan active')
    }

    if (isExpired.value) {
      toast.error('Session expirée, veuillez scanner à nouveau')
      return
    }

    status.value = 'validating'
    error.value = null

    try {
      const currentUser = authStore.user
      const currentGate = localStorage.getItem('current_gate_id')

      if (!currentUser?.id || !currentGate) {
        throw new Error('Agent ou porte non configuré')
      }

      const result = await scanService.scanConfirm({
        scan_session_token: sessionData.value.scan_session_token,
        scan_nonce: sessionData.value.scan_nonce,
        gate_id: currentGate,
        agent_id: currentUser.id,
        action: action
      })

      scanResult.value = result

      if (result.valid) {
        status.value = 'success'
        toast.success(result.message || 'Entrée autorisée')
      } else {
        status.value = 'error'
        error.value = result.message
        toast.error(result.message)
      }

      return result

    } catch (err: any) {
      status.value = 'error'
      error.value = err.response?.data?.message || 'Erreur lors de la confirmation'
      toast.error(error.value)
      throw err
    }
  }

  /**
   * Démarrer le countdown de 20 secondes
   */
  function startCountdown() {
    const interval = setInterval(() => {
      expiresIn.value--

      if (expiresIn.value <= 0) {
        clearInterval(interval)
        toast.warning('Session expirée')
        status.value = 'error'
        error.value = 'Session expirée, veuillez scanner à nouveau'
      }
    }, 1000)
  }

  /**
   * Réinitialiser l'état
   */
  function reset() {
    status.value = 'idle'
    ticketInfo.value = null
    sessionData.value = null
    scanResult.value = null
    expiresIn.value = 20
    error.value = null
  }

  return {
    // État
    status,
    ticketInfo,
    sessionData,
    scanResult,
    expiresIn,
    error,

    // Computed
    loading,
    hasTicketInfo,
    isExpired,

    // Méthodes
    scanTicket,
    confirmEntry,
    reset
  }
}
```

---

### 4. Composant Widget Scanner

**Fichier:** `src/components/scan/QrScanner.vue`

```vue
<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Html5Qrcode } from 'html5-qrcode'

const emit = defineEmits<{
  (e: 'scanned', qrData: string): void
  (e: 'error', error: string): void
  (e: 'close'): void
}>()

const scanning = ref(false)
let html5QrCode: Html5Qrcode | null = null

async function startScanner() {
  scanning.value = true

  try {
    html5QrCode = new Html5Qrcode('qr-reader')

    await html5QrCode.start(
      { facingMode: 'environment' }, // Caméra arrière
      {
        fps: 10,
        qrbox: { width: 250, height: 250 }
      },
      onScanSuccess,
      onScanFailure
    )
  } catch (err: any) {
    emit('error', 'Impossible d\'accéder à la caméra')
    scanning.value = false
  }
}

function onScanSuccess(decodedText: string) {
  console.log('QR Code détecté:', decodedText)
  stopScanner()
  emit('scanned', decodedText)
}

function onScanFailure(error: string) {
  // Ignorer les erreurs de scan en cours
  // console.log('Scan en cours...', error)
}

async function stopScanner() {
  if (html5QrCode) {
    await html5QrCode.stop()
    html5QrCode.clear()
    html5QrCode = null
  }
  scanning.value = false
}

function close() {
  stopScanner()
  emit('close')
}

onMounted(() => {
  startScanner()
})

onUnmounted(() => {
  stopScanner()
})
</script>

<template>
  <div class="qr-scanner">
    <div class="scanner-header">
      <h2>Scanner un Ticket</h2>
      <button @click="close" class="btn-close">✕</button>
    </div>

    <div class="scanner-container">
      <div id="qr-reader"></div>
    </div>

    <div class="scanner-instructions">
      <p>📷 Pointez la caméra vers le QR code du ticket</p>
    </div>

    <button @click="close" class="btn-cancel">
      Annuler
    </button>
  </div>
</template>

<style scoped>
.qr-scanner {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #1a1a1a;
  z-index: 1000;
  display: flex;
  flex-direction: column;
}

.scanner-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #2a2a2a;
  color: white;
}

.scanner-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

#qr-reader {
  width: 100%;
  max-width: 500px;
}

.scanner-instructions {
  padding: 1rem;
  text-align: center;
  color: #999;
}

.btn-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
}

.btn-cancel {
  margin: 1rem;
  padding: 1rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
}
</style>
```

---

### 5. Composant Carte Ticket

**Fichier:** `src/components/scan/TicketInfoCard.vue`

```vue
<script setup lang="ts">
import type { TicketInfo } from '@/types/scan'

defineProps<{
  ticket: TicketInfo
}>()

const statusColors = {
  issued: '#ffc107',
  paid: '#28a745',
  in: '#17a2b8',
  out: '#6c757d'
}

const statusLabels = {
  issued: 'Émis',
  paid: 'Payé',
  in: 'À l\'intérieur',
  out: 'Sorti'
}
</script>

<template>
  <div class="ticket-card">
    <div class="ticket-header">
      <div class="status-badge" :style="{ background: statusColors[ticket.status] }">
        {{ statusLabels[ticket.status] }}
      </div>
    </div>

    <div class="buyer-info">
      <h2>{{ ticket.buyer_name }}</h2>
      <p>{{ ticket.buyer_email }}</p>
      <p>{{ ticket.buyer_phone }}</p>
    </div>

    <div class="ticket-details">
      <div class="detail-row">
        <span class="label">Code:</span>
        <span class="value">{{ ticket.code }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Type:</span>
        <span class="value">{{ ticket.ticket_type.name }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Prix:</span>
        <span class="value">{{ ticket.ticket_type.price }} XOF</span>
      </div>
    </div>

    <div class="event-info">
      <h3>{{ ticket.event.title }}</h3>
      <p>
        {{ new Date(ticket.event.start_datetime).toLocaleDateString() }}
        -
        {{ new Date(ticket.event.end_datetime).toLocaleDateString() }}
      </p>
    </div>
  </div>
</template>

<style scoped>
.ticket-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.status-badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.buyer-info {
  margin: 1.5rem 0;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.buyer-info h2 {
  margin: 0 0 0.5rem 0;
  color: #333;
}

.buyer-info p {
  margin: 0.25rem 0;
  color: #666;
}

.ticket-details {
  margin: 1rem 0;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
}

.label {
  color: #666;
  font-weight: 500;
}

.value {
  color: #333;
  font-weight: 600;
}

.event-info {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e0e0e0;
}

.event-info h3 {
  margin: 0 0 0.5rem 0;
  color: #333;
}

.event-info p {
  margin: 0;
  color: #666;
  font-size: 0.875rem;
}
</style>
```

---

### 6. Page de Scan (Route URL)

**Fichier:** `src/views/Dashboard/ScanPage.vue`

```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useScan } from '@/composables/useScan'
import TicketInfoCard from '@/components/scan/TicketInfoCard.vue'
import CountdownTimer from '@/components/scan/CountdownTimer.vue'
import ScanResult from '@/components/scan/ScanResult.vue'

const route = useRoute()
const router = useRouter()

const {
  status,
  ticketInfo,
  expiresIn,
  error,
  loading,
  hasTicketInfo,
  scanTicket,
  confirmEntry,
  reset
} = useScan()

onMounted(async () => {
  const ticketId = route.query.t as string
  const signature = route.query.sig as string

  if (!ticketId || !signature) {
    error.value = 'QR code invalide - Paramètres manquants'
    return
  }

  try {
    await scanTicket(ticketId, signature)
  } catch (err) {
    // Erreur déjà gérée dans useScan
  }
})

async function handleConfirm() {
  try {
    await confirmEntry('in')
  } catch (err) {
    // Erreur déjà gérée dans useScan
  }
}

function handleRefuse() {
  router.push('/dashboard')
}

function handleNewScan() {
  reset()
  router.push('/dashboard')
}
</script>

<template>
  <div class="scan-page">
    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Vérification du QR code...</p>
    </div>

    <!-- Erreur -->
    <div v-else-if="error && !hasTicketInfo" class="error-container">
      <div class="error-icon">❌</div>
      <h2>QR Code Invalide</h2>
      <p>{{ error }}</p>
      <button @click="handleNewScan" class="btn-primary">
        Retour au Dashboard
      </button>
    </div>

    <!-- Résultat du scan confirm -->
    <ScanResult
      v-else-if="status === 'success' || (status === 'error' && scanResult)"
      :result="scanResult"
      @new-scan="handleNewScan"
    />

    <!-- Infos du ticket (après scan request) -->
    <div v-else-if="hasTicketInfo" class="ticket-container">
      <div class="scan-header">
        <h1>✅ QR Code Valide</h1>
        <CountdownTimer :seconds="expiresIn" />
      </div>

      <TicketInfoCard :ticket="ticketInfo!" />

      <div class="actions">
        <button
          @click="handleConfirm"
          class="btn-success"
          :disabled="loading || expiresIn <= 0"
        >
          ✅ AUTORISER L'ENTRÉE
        </button>

        <button @click="handleRefuse" class="btn-danger">
          ❌ REFUSER
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.scan-page {
  min-height: 100vh;
  padding: 1rem;
  background: #f5f5f5;
}

.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 80vh;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e0e0e0;
  border-top-color: #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.error-container h2 {
  margin: 0.5rem 0;
  color: #dc3545;
}

.ticket-container {
  max-width: 600px;
  margin: 0 auto;
}

.scan-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.scan-header h1 {
  margin: 0 0 1rem 0;
  color: #28a745;
}

.actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-success,
.btn-danger,
.btn-primary {
  padding: 1rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-success:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-success:hover:not(:disabled),
.btn-danger:hover,
.btn-primary:hover {
  opacity: 0.9;
}
</style>
```

---

### 7. Dashboard avec Widget Scanner

**Fichier:** `src/views/Dashboard/ScannerDashboard.vue`

```vue
<script setup lang="ts">
import { ref } from 'vue'
import { useScan } from '@/composables/useScan'
import QrScanner from '@/components/scan/QrScanner.vue'
import TicketInfoCard from '@/components/scan/TicketInfoCard.vue'
import ScanResult from '@/components/scan/ScanResult.vue'

const showScanner = ref(false)
const showTicketModal = ref(false)

const {
  status,
  ticketInfo,
  expiresIn,
  scanTicket,
  confirmEntry,
  reset
} = useScan()

function openScanner() {
  showScanner.value = true
}

async function handleQrScanned(qrData: string) {
  showScanner.value = false

  try {
    // Parser l'URL du QR
    const url = new URL(qrData)
    const ticketId = url.searchParams.get('t')
    const signature = url.searchParams.get('sig')

    if (!ticketId || !signature) {
      throw new Error('QR code invalide')
    }

    // Appeler l'API
    await scanTicket(ticketId, signature)

    // Afficher le modal
    showTicketModal.value = true

  } catch (err: any) {
    console.error('Erreur scan:', err)
  }
}

async function handleConfirm() {
  try {
    await confirmEntry('in')
  } catch (err) {
    // Erreur gérée dans useScan
  }
}

function handleClose() {
  showTicketModal.value = false
  reset()
}

function handleNewScan() {
  handleClose()
  setTimeout(() => {
    openScanner()
  }, 300)
}
</script>

<template>
  <div class="scanner-dashboard">
    <div class="dashboard-header">
      <h1>Dashboard Agent</h1>
      <div class="agent-info">
        <p>👤 Agent: {{ $auth.user?.name }}</p>
        <p>🚪 Porte: Entrée Principale</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Entrées</div>
        <div class="stat-value">234</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Sorties</div>
        <div class="stat-value">45</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Présents</div>
        <div class="stat-value">189</div>
      </div>
    </div>

    <button @click="openScanner" class="btn-scan">
      📷 SCANNER UN TICKET
    </button>

    <div class="recent-scans">
      <h2>Historique récent</h2>
      <div class="scan-item">
        <span>15:30 - QVQLXE6Y</span>
        <span class="badge-success">Autorisé</span>
      </div>
      <div class="scan-item">
        <span>15:28 - ABC123XY</span>
        <span class="badge-danger">Refusé</span>
      </div>
    </div>

    <!-- Scanner Widget -->
    <QrScanner
      v-if="showScanner"
      @scanned="handleQrScanned"
      @close="showScanner = false"
    />

    <!-- Modal Infos Ticket -->
    <Teleport to="body">
      <div v-if="showTicketModal && status !== 'success'" class="modal-overlay" @click="handleClose">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>✅ QR Code Détecté</h2>
            <button @click="handleClose" class="btn-close">✕</button>
          </div>

          <div class="countdown-timer">
            ⏱️ Expire dans: {{ expiresIn }}s
          </div>

          <TicketInfoCard v-if="ticketInfo" :ticket="ticketInfo" />

          <div class="modal-actions">
            <button @click="handleConfirm" class="btn-success">
              ✅ AUTORISER L'ENTRÉE
            </button>
            <button @click="handleClose" class="btn-danger">
              ❌ REFUSER
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Résultat -->
      <div v-if="showTicketModal && status === 'success'" class="modal-overlay">
        <div class="modal-content" @click.stop>
          <ScanResult :result="scanResult" @new-scan="handleNewScan" />
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.scanner-dashboard {
  padding: 1.5rem;
  max-width: 800px;
  margin: 0 auto;
}

.dashboard-header h1 {
  margin: 0 0 1rem 0;
}

.agent-info p {
  margin: 0.25rem 0;
  color: #666;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin: 2rem 0;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-label {
  color: #666;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #333;
}

.btn-scan {
  width: 100%;
  padding: 1.5rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.25rem;
  font-weight: 600;
  cursor: pointer;
  margin: 2rem 0;
}

.recent-scans {
  margin-top: 2rem;
}

.recent-scans h2 {
  margin: 0 0 1rem 0;
  font-size: 1.25rem;
}

.scan-item {
  display: flex;
  justify-content: space-between;
  padding: 1rem;
  background: white;
  margin-bottom: 0.5rem;
  border-radius: 8px;
}

.badge-success {
  color: #28a745;
  font-weight: 600;
}

.badge-danger {
  color: #dc3545;
  font-weight: 600;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.modal-header h2 {
  margin: 0;
  color: #28a745;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
}

.countdown-timer {
  text-align: center;
  padding: 0.75rem;
  background: #fff3cd;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-weight: 600;
  color: #856404;
}

.modal-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-success,
.btn-danger {
  padding: 1rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-danger {
  background: #dc3545;
  color: white;
}
</style>
```

---

### 8. Routes

**Fichier:** `src/router/index.ts`

```typescript
{
  path: '/dashboard',
  component: () => import('@/layouts/DashboardLayout.vue'),
  meta: { requiresAuth: true, role: 'agent-de-controle' },
  children: [
    {
      path: '',
      name: 'ScannerDashboard',
      component: () => import('@/views/Dashboard/ScannerDashboard.vue')
    },
    {
      path: 'scan',
      name: 'ScanPage',
      component: () => import('@/views/Dashboard/ScanPage.vue')
    }
  ]
}
```

---

## 📋 Checklist d'Implémentation

### Phase 1: Setup (1h)
- [ ] Installer les dépendances (`html5-qrcode`, `@vueuse/core`)
- [ ] Créer la structure de fichiers
- [ ] Définir les types TypeScript (`src/types/scan.ts`)
- [ ] Créer le service API (`src/services/scanService.ts`)

### Phase 2: Composable (1h)
- [ ] Implémenter `useScan.ts`
- [ ] Tester la logique de scan request
- [ ] Tester la logique de scan confirm
- [ ] Implémenter le countdown de 20s

### Phase 3: Composants (2h)
- [ ] Créer `QrScanner.vue` (widget caméra)
- [ ] Créer `TicketInfoCard.vue`
- [ ] Créer `ScanResult.vue`
- [ ] Créer `CountdownTimer.vue`

### Phase 4: Pages (2h)
- [ ] Créer `ScanPage.vue` (route URL)
- [ ] Créer `ScannerDashboard.vue` (widget intégré)
- [ ] Configurer les routes
- [ ] Tester les 2 méthodes de scan

### Phase 5: Tests & Polish (1h)
- [ ] Tester scan avec QR externe
- [ ] Tester scan avec widget
- [ ] Tester gestion d'erreurs
- [ ] Tester countdown expiration
- [ ] Tester détection de doublons
- [ ] Responsive mobile
- [ ] Animations et transitions

---

## 🧪 Scénarios de Test

### Test 1: Scanner Externe
1. Générer un ticket
2. Télécharger le QR code
3. Utiliser une app scanner (ou scanner physique)
4. Scanner ouvre l'URL dans le navigateur
5. Vérifier affichage des infos
6. Cliquer "Autoriser l'entrée"
7. Vérifier succès

### Test 2: Widget Intégré
1. Ouvrir le dashboard agent
2. Cliquer "Scanner un ticket"
3. Pointer caméra vers QR
4. Vérifier détection automatique
5. Vérifier affichage modal
6. Confirmer l'entrée
7. Vérifier redémarrage automatique du scanner

### Test 3: Expiration de Session
1. Scanner un QR
2. Attendre 20 secondes sans confirmer
3. Essayer de confirmer
4. Vérifier message d'erreur

### Test 4: Doublon
1. Scanner un ticket
2. Confirmer l'entrée (succès)
3. Re-scanner le même ticket
4. Vérifier message "Already in"

### Test 5: Événement Terminé
1. Créer un événement avec end_datetime dans le passé
2. Scanner un ticket de cet événement
3. Vérifier message "Event has already ended"

---

## 🎯 Métriques de Succès

- [ ] Scanner externe fonctionne (URL ouverte)
- [ ] Widget scanner fonctionne (caméra activée)
- [ ] QR décodé correctement (paramètres extraits)
- [ ] API scan/request appelée avec succès
- [ ] Infos ticket affichées correctement
- [ ] Countdown de 20s fonctionne
- [ ] API scan/confirm appelée avec auth
- [ ] Résultat affiché (succès ou erreur)
- [ ] Détection de doublons fonctionne
- [ ] Responsive sur mobile et tablette

---

## 📱 Considérations UX

### Performance
- Le widget scanner doit se charger en < 1s
- La détection du QR doit être quasi-instantanée
- L'appel API scan/request doit prendre < 500ms

### Accessibilité
- Boutons avec bon contraste
- Textes lisibles (min 16px)
- Messages d'erreur clairs
- Feedback visuel et sonore

### Mobile-First
- Interface optimisée pour tablettes 7-10 pouces
- Boutons larges (min 48px de hauteur)
- Scanner plein écran sur mobile
- Gestion de l'orientation (portrait/paysage)

---

## 🔐 Sécurité

### Frontend
- Validation des paramètres QR avant appel API
- Stockage sécurisé du token agent (localStorage avec expiration)
- Nettoyage des données sensibles après scan
- Pas de log des tokens/signatures dans la console (en prod)

### Backend (Déjà implémenté ✅)
- Signature HMAC du QR
- Session de 20 secondes
- Nonce unique
- Authentification agent requise pour confirm
- Rate limiting

---

## 📚 Documentation à Créer

- [ ] Guide utilisateur pour les agents
- [ ] Guide de troubleshooting (caméra non accessible, etc.)
- [ ] Documentation API (Swagger/OpenAPI)
- [ ] README pour développeurs

---

## 🚀 Déploiement

### Variables d'Environnement

```env
# .env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_FRONTEND_URL=http://localhost:5173

# Production
VITE_API_BASE_URL=https://api.ticketing.com/api
VITE_FRONTEND_URL=https://app.ticketing.com
```

### Build
```bash
npm run build
```

### Permissions Caméra
Assurer que le site est en HTTPS en production pour accéder à la caméra.

---

## 📊 Estimation Totale

| Phase | Durée Estimée |
|-------|---------------|
| Setup | 1h |
| Composable | 1h |
| Composants | 2h |
| Pages | 2h |
| Tests | 1h |
| **TOTAL** | **7 heures** |

---

## ✅ Livrable Final

Un système de scan complet avec :

1. ✅ **2 méthodes de scan** (externe + widget)
2. ✅ **Interface agent complète** (dashboard + historique)
3. ✅ **Validation 2FA** (request public + confirm auth)
4. ✅ **Gestion erreurs** (QR invalide, session expirée, doublon)
5. ✅ **Responsive** (mobile, tablette, desktop)
6. ✅ **Sécurisé** (HMAC, auth, rate limiting)
7. ✅ **Performant** (< 1s pour scanner)

---

**Prêt pour soumission à Gemini! 🚀**
