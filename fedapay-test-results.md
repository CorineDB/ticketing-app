# Résultats des Tests API FedaPay

## Informations de Test
- **Environnement**: SANDBOX
- **Date**: 27 novembre 2025, 21:30 UTC
- **Clé API**: `sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN`

---

## ✅ ÉTAPE 1: Création de la Transaction

### Requête
```bash
POST https://sandbox-api.fedapay.com/v1/transactions
```

### Données Envoyées
```json
{
  "description": "Test Ticketing App - Achat de billet",
  "amount": 5000,
  "currency": {"iso": "XOF"},
  "callback_url": "https://example.com/callback",
  "customer": {
    "firstname": "Jean",
    "lastname": "Dupont",
    "email": "jean.dupont@example.com",
    "phone_number": {
      "number": "+22997000000",
      "country": "bj"
    }
  }
}
```

### Réponse Reçue
```json
{
  "id": 383505,
  "reference": "trx__tU_1764279000929",
  "amount": 5000,
  "description": "Test Ticketing App - Achat de billet",
  "callback_url": "https://example.com/callback",
  "status": "pending",
  "customer_id": 74190,
  "currency_id": 1,
  "operation": "payment",
  "created_at": "2025-11-27T21:30:00.929Z",
  "updated_at": "2025-11-27T21:30:01.031Z"
}
```

### Résultat
✅ **Transaction créée avec succès**
- **ID de transaction**: `383505`
- **Référence**: `trx__tU_1764279000929`
- **Montant**: `5000 XOF` (soit environ 8.33 USD)
- **Statut**: `pending`

---

## ✅ ÉTAPE 2: Token et URL de Paiement

### Informations de Paiement
La réponse de création de transaction contient directement:

- **Token de paiement**:
```
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjM4MzUwNSwiZXhwIjoxNzY0MzY1NDAxfQ.KUGTpPxjdqqtJvQBtEXStkcegd1GMjxkEoaWV5xBY9g
```

- **URL de paiement complète**:
```
https://sandbox-process.fedapay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjM4MzUwNSwiZXhwIjoxNzY0MzY1NDAxfQ.KUGTpPxjdqqtJvQBtEXStkcegd1GMjxkEoaWV5xBY9g
```

### Résultat
✅ **URL de paiement générée**
- L'utilisateur peut maintenant être redirigé vers cette URL pour effectuer le paiement

---

## 📝 ÉTAPE 3: Instructions pour Tester le Paiement

### Option 1: Navigateur Web
1. Copiez l'URL de paiement ci-dessus
2. Ouvrez-la dans un navigateur
3. Vous serez redirigé vers la page de paiement FedaPay sandbox
4. Effectuez un paiement de test avec les moyens de paiement disponibles

### Option 2: Vérifier le Statut
Pour vérifier le statut de la transaction après paiement:

```bash
curl -X GET "https://sandbox-api.fedapay.com/v1/transactions/383505" \
  -H "Authorization: Bearer sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN" \
  -H "Content-Type: application/json"
```

### États Possibles de la Transaction
- `pending`: En attente de paiement (statut actuel)
- `approved`: Paiement réussi ✅
- `declined`: Annulé par l'utilisateur
- `canceled`: Échec du paiement
- `expired`: Lien expiré

---

## 🔧 ÉTAPE 4: Récupération par GET

### Requête
```bash
GET https://sandbox-api.fedapay.com/v1/transactions/383505
```

### Résultat
✅ **Détails récupérés avec succès**

**Note importante**: Le `payment_token` et `payment_url` ne sont retournés que lors de la création initiale de la transaction. Ils ne sont pas présents dans les requêtes GET ultérieures.

---

## 📊 Résumé des Tests

| Test | Statut | Résultat |
|------|--------|----------|
| Création de transaction | ✅ | ID: 383505 |
| Génération du token | ✅ | Token JWT généré |
| URL de paiement | ✅ | URL sandbox accessible |
| Récupération GET | ✅ | Détails récupérés |

---

## 🎯 Points Clés pour l'Intégration

### ✅ Fonctionnalités Validées
1. **Création de transaction**: L'API accepte correctement les données de transaction
2. **Génération automatique**: Le token et l'URL sont générés automatiquement lors de la création
3. **Référence unique**: Chaque transaction reçoit une référence unique
4. **Statut temps réel**: Le statut peut être vérifié à tout moment

### ⚠️ Points d'Attention
1. **Token unique**: Le `payment_token` et `payment_url` ne sont retournés qu'une seule fois lors de la création
2. **Stockage**: Il faut stocker le token/URL si vous en avez besoin plus tard
3. **Callback**: Implémenter un webhook pour recevoir les notifications de changement de statut
4. **Sécurité**: Ne jamais se fier au statut dans l'URL de callback, toujours vérifier via l'API

### 🔄 Flux Recommandé pour l'Application
```
1. Utilisateur achète un billet
   ↓
2. Backend crée une transaction FedaPay
   ↓
3. Backend stocke transaction_id et payment_url en DB
   ↓
4. Backend redirige l'utilisateur vers payment_url
   ↓
5. Utilisateur paie sur FedaPay
   ↓
6. FedaPay envoie webhook à notre callback_url
   ↓
7. Backend vérifie le statut via GET /transactions/:id
   ↓
8. Backend met à jour la commande et envoie le billet
```

---

## 🚀 Prochaines Étapes

1. **Implémenter dans l'application**:
   - Service FedaPay pour gérer les transactions
   - Contrôleur pour créer des paiements
   - Webhook pour recevoir les notifications

2. **Tester le paiement complet**:
   - Ouvrir l'URL de paiement dans un navigateur
   - Effectuer un paiement de test
   - Vérifier la mise à jour du statut

3. **Fonctionnalités avancées**:
   - Paiement sans redirection (MTN, Moov)
   - Métadonnées personnalisées (ticket_id, event_id)
   - Référence marchande (merchant_reference)

---

## 🔗 URL de Test Active

**URL de paiement à tester**:
```
https://sandbox-process.fedapay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjM4MzUwNSwiZXhwIjoxNzY0MzY1NDAxfQ.KUGTpPxjdqqtJvQBtEXStkcegd1GMjxkEoaWV5xBY9g
```

**Transaction ID**: `383505`

Vous pouvez maintenant ouvrir cette URL dans votre navigateur pour tester le processus de paiement complet !
