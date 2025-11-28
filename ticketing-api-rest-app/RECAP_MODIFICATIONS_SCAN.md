# Récapitulatif des Modifications - Système de Scan

## 📋 Modifications Effectuées

### 1. Modification du QR Code (TicketService.php)

**Avant:**
```php
$qrData = config('app.url') . "/t/{$ticket->id}?sig={$signature}";
// Résultat: http://192.168.8.106:8000/t/019ac9fc-b13d-72c0-b27e-c1295d21b7a3?sig=...
```

**Après:**
```php
$frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'));
$qrData = $frontendUrl . "/dashboard/scan?t={$ticket->id}&sig={$signature}";
// Résultat: http://localhost:5173/dashboard/scan?t=019ac9fc-b13d-72c0-b27e-c1295d21b7a3&sig=...
```

**Impact:**
- ✅ Le QR code pointe maintenant vers le frontend Vue.js
- ✅ Le frontend gère l'UI de scan
- ✅ Le frontend appelle les API de scan (request + confirm)

**Fichier modifié:** `app/Services/TicketService.php` ligne 97-99

---

### 2. Script de Test Complet (test-scan-system.php)

**Fonctionnalités:**
- ✅ Utilise les vraies données de test (test-scan-requirements-details.txt)
- ✅ Teste POST /api/scan/request
- ✅ Récupère automatiquement le nonce depuis le cache Laravel
- ✅ Teste POST /api/scan/confirm avec authentification
- ✅ Affiche le flux complet et la documentation

**Données de test utilisées:**
```
Ticket ID: 019ac9fc-b13d-72c0-b27e-c1295d21b7a3
Signature: 16a0f30637122c6f4fb031c93e3da1712aaccfe8929cd2aff39fc38df588ae59
Agent ID: 9d518178-44e1-4f6c-92f4-13bf0d899d79
Gate ID: 5939d63e-3ede-440e-bc30-413b896c0eb2
Bearer Token: 9|GT5eSHw0dNeybuhfjEtklwmWWAE3TDhqPwP9Kila2e542b32
```

**Fichier:** `test-scan-system.php`

---

## 🎯 Flux Frontend → Backend

### Page Frontend: `/dashboard/scan`

**URL du QR Code:**
```
http://localhost:5173/dashboard/scan?t=019ac9fc-b13d-72c0-b27e-c1295d21b7a3&sig=16a0f30637122c6f4fb031c93e3da1712aaccfe8929cd2aff39fc38df588ae59
```

### Étapes dans le Frontend (Vue.js)

```vue
<template>
  <div class="scan-page">
    <!-- Scanner UI -->
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const ticketId = ref('')
const signature = ref('')
const scanResult = ref(null)

onMounted(async () => {
  // 1. Extraire les paramètres du QR code
  ticketId.value = route.query.t
  signature.value = route.query.sig

  // 2. Appeler l'API de scan request (public - sans auth)
  const requestResponse = await fetch('http://localhost:8000/api/scan/request', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      ticket_id: ticketId.value,
      sig: signature.value
    })
  })

  const requestData = await requestResponse.json()

  if (!requestData.scan_session_token) {
    // Gérer l'erreur (signature invalide, ticket non trouvé, etc.)
    return
  }

  // 3. Récupérer le nonce (stocké dans les données de session)
  // Note: Le nonce est côté serveur dans le cache
  // Le frontend affiche les infos du ticket et demande à l'agent de confirmer

  // 4. Quand l'agent confirme, appeler l'API de scan confirm (authentifié)
  const confirmResponse = await fetch('http://localhost:8000/api/scan/confirm', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('token')}`  // Token de l'agent
    },
    body: JSON.stringify({
      scan_session_token: requestData.scan_session_token,
      scan_nonce: requestData.scan_nonce,  // À obtenir via une route dédiée
      gate_id: 'gate-id-from-context',
      agent_id: 'agent-id-from-auth',
      action: 'in'
    })
  })

  scanResult.value = await confirmResponse.json()

  // 5. Afficher le résultat (OK, INVALID, etc.)
})
</script>
```

---

## 🔐 Sécurité - Problème du Nonce

### ⚠️ Problème Actuel

Le nonce est stocké **côté serveur** dans le cache avec la clé `scan_session:{token}`. Le frontend ne peut pas y accéder directement.

### ✅ Solutions Possibles

#### Option 1: Retourner le Nonce dans /scan/request

**Modification de ScanService.php:**
```php
public function requestScan(string $ticketId, string $signature): array
{
    $ticket = $this->ticketRepository->find($ticketId);

    if (!$ticket) {
        throw new \Exception('TICKET_NOT_FOUND', 404);
    }

    if (!$this->validateTicketSignature($ticketId, $signature)) {
        throw new \Exception('QR_SIGNATURE_MISMATCH: Invalid QR signature', 400);
    }

    $sessionToken = Str::random(64);
    $nonce = Str::random(32);  // Générer le nonce
    $expiresIn = 20;

    Cache::put("scan_session:{$sessionToken}", [
        'ticket_id' => $ticketId,
        'nonce' => $nonce,
    ], now()->addSeconds($expiresIn));

    return [
        'scan_session_token' => $sessionToken,
        'scan_nonce' => $nonce,  // ✅ Retourner le nonce
        'expires_in' => $expiresIn,
        'ticket' => $ticket,  // ✅ Optionnel: infos du ticket
    ];
}
```

**Avantages:**
- ✅ Frontend a directement le nonce
- ✅ Pas besoin d'endpoint supplémentaire
- ✅ Simplifie le flux

**Inconvénients:**
- ⚠️ Le nonce est exposé au client (mais ce n'est pas grave car il est unique et éphémère)

#### Option 2: Endpoint GET /scan/session/{token}

**Nouvelle route:**
```php
Route::get('/scan/session/{token}', [ScanController::class, 'getSession'])
    ->middleware('auth:sanctum');
```

**Nouveau controller:**
```php
public function getSession(string $token)
{
    $sessionData = Cache::get("scan_session:{$token}");

    if (!$sessionData) {
        return response()->json(['error' => 'Session expired'], 404);
    }

    return response()->json([
        'scan_nonce' => $sessionData['nonce'],
        'ticket_id' => $sessionData['ticket_id'],
    ]);
}
```

**Avantages:**
- ✅ Nonce protégé par authentification
- ✅ Séparation des concerns

**Inconvénients:**
- ⚠️ Requête supplémentaire
- ⚠️ Complexité accrue

### 💡 Recommandation

**Utiliser Option 1** - Retourner le nonce dans /scan/request

Raisons:
1. Le nonce est déjà temporaire (20 secondes)
2. Il est unique et ne peut être réutilisé
3. Simplifie grandement le flux frontend
4. La sécurité est déjà assurée par la signature HMAC du QR

---

## 📝 Modifications Recommandées

### 1. Modifier ScanService.php (Option 1)

```php
// app/Services/ScanService.php ligne 53-64

return [
    'scan_session_token' => $sessionToken,
    'scan_nonce' => $nonce,  // AJOUTER cette ligne
    'expires_in' => $expiresIn,
];
```

### 2. Modifier ScanService.php pour retourner les infos du ticket

```php
// app/Services/ScanService.php ligne 41-64

public function requestScan(string $ticketId, string $signature): array
{
    $ticket = $this->ticketRepository->find($ticketId);

    if (!$ticket) {
        throw new \Exception('TICKET_NOT_FOUND', 404);
    }

    if (!$this->validateTicketSignature($ticketId, $signature)) {
        throw new \Exception('QR_SIGNATURE_MISMATCH: Invalid QR signature', 400);
    }

    $sessionToken = Str::random(64);
    $nonce = Str::random(32);
    $expiresIn = 20;

    Cache::put("scan_session:{$sessionToken}", [
        'ticket_id' => $ticketId,
        'nonce' => $nonce,
    ], now()->addSeconds($expiresIn));

    return [
        'scan_session_token' => $sessionToken,
        'scan_nonce' => $nonce,  // ✅ AJOUTÉ
        'expires_in' => $expiresIn,
        'ticket' => [  // ✅ AJOUTÉ - Infos du ticket pour l'UI
            'id' => $ticket->id,
            'code' => $ticket->code,
            'status' => $ticket->status,
            'buyer_name' => $ticket->buyer_name,
            'buyer_email' => $ticket->buyer_email,
            'event' => $ticket->event,
            'ticket_type' => $ticket->ticketType,
        ],
    ];
}
```

---

## 📊 Résultat Final

### Nouveau Flux Complet

```
┌─────────────────────────────────────────────────────────┐
│ 1. Utilisateur scanne le QR code                       │
│    QR → http://localhost:5173/dashboard/scan?t=...&sig=│
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 2. Frontend extrait t (ticket_id) et sig (signature)   │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 3. Frontend → POST /api/scan/request (PUBLIC)          │
│    {                                                    │
│      "ticket_id": "...",                                │
│      "sig": "..."                                       │
│    }                                                    │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 4. Backend valide signature et retourne                │
│    {                                                    │
│      "scan_session_token": "...",                       │
│      "scan_nonce": "...",         ← ✅ NOUVEAU          │
│      "expires_in": 20,                                  │
│      "ticket": {...}               ← ✅ NOUVEAU         │
│    }                                                    │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 5. Frontend affiche les infos du ticket                │
│    - Nom de l'acheteur                                  │
│    - Événement                                          │
│    - Statut du ticket                                   │
│    - Bouton "Confirmer l'entrée"                        │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 6. Agent clique "Confirmer"                             │
│    Frontend → POST /api/scan/confirm (AUTHENTIFIÉ)      │
│    Header: Authorization: Bearer {agent_token}          │
│    {                                                    │
│      "scan_session_token": "...",                       │
│      "scan_nonce": "...",                               │
│      "gate_id": "...",                                  │
│      "agent_id": "...",                                 │
│      "action": "in"                                     │
│    }                                                    │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 7. Backend traite le scan                               │
│    - Valide nonce                                       │
│    - Applique règles métier                             │
│    - Met à jour le ticket                               │
│    - Retourne résultat                                  │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 8. Frontend affiche le résultat                         │
│    ✅ "Entrée autorisée" (status: in)                   │
│    ❌ "Ticket invalide"                                 │
│    ❌ "Capacité atteinte"                               │
│    etc.                                                 │
└─────────────────────────────────────────────────────────┘
```

---

## ✅ Checklist d'Implémentation

### Backend
- [x] Modifier TicketService.php pour QR frontend
- [ ] Modifier ScanService.php pour retourner le nonce
- [ ] Modifier ScanService.php pour retourner les infos du ticket
- [ ] Tester avec test-scan-system.php

### Frontend
- [ ] Créer la page `/dashboard/scan`
- [ ] Extraire les paramètres du QR (t, sig)
- [ ] Appeler POST /api/scan/request au mounted
- [ ] Afficher les infos du ticket
- [ ] Bouton "Confirmer l'entrée"
- [ ] Appeler POST /api/scan/confirm avec auth
- [ ] Afficher le résultat

---

**Date:** 28 Novembre 2025
**Status:** ✅ Backend modifié, Frontend à implémenter
