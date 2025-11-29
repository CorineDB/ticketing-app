# Problèmes Identifiés - Intégration FedaPay

**Date d'analyse**: 27 novembre 2025
**Status**: En cours de test du flux complet

---

## 🔴 Problèmes Critiques Identifiés

### 1. **Base de Données - Module SQLite Manquant**

**Problème**:
```
could not find driver (Connection: sqlite, SQL: select exists...)
```

**Localisation**: `ticketing-api-rest-app/.env`
```
DB_CONNECTION=sqlite
```

**Cause**:
- Le module PHP `pdo_sqlite` n'est pas installé sur le serveur
- Modules disponibles: `pdo_mysql`, `pdo_pgsql`

**Solution**:
```bash
# Option 1: Installer SQLite
apt-get install php8.2-sqlite3

# Option 2: Utiliser MySQL (recommandé pour production)
# Modifier .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticketing
DB_USERNAME=root
DB_PASSWORD=
```

**Impact**: ⛔ **BLOQUANT** - L'application ne peut pas démarrer sans base de données

---

### 2. **Test du Flux Complet - Prérequis Manquants**

Pour tester le flux complet, il manque:

#### a) Données de Test
- ❌ Aucun événement en base
- ❌ Aucun type de ticket (ticket_type) en base
- ❌ Aucune donnée de test pour simuler un achat

**Solution**: Créer des seeders ou des migrations avec données de test

```bash
php artisan db:seed --class=EventSeeder
php artisan db:seed --class=TicketTypeSeeder
```

#### b) Base de Données Initialisée
- ❌ Tables non créées (migrations non exécutées)
- ❌ Base de données vide

**Solution**:
```bash
# Une fois le driver DB correct
php artisan migrate --seed
```

---

## ⚠️ Problèmes Potentiels (À Vérifier)

### 3. **Validation des Données d'Entrée**

**Fichier à vérifier**: `app/Http/Requests/Api/Tickets/TicketPurchaseRequest.php`

**Questions**:
1. ✓ Les règles de validation existent-elles?
2. ✓ Le format du numéro de téléphone est-il validé?
3. ✓ La quantité max est-elle limitée?

**Test à faire**:
```bash
curl -X POST http://localhost:8000/api/tickets/purchase \
  -H "Content-Type: application/json" \
  -d '{
    "ticket_type_id": "invalid-id",
    "quantity": 999,
    "customer": {
      "firstname": "",
      "lastname": "",
      "email": "invalid-email",
      "phone_number": "123"
    }
  }'
```

**Résultat attendu**: Erreurs de validation claires

---

### 4. **Gestion d'Erreur FedaPay**

**Scénarios à tester**:

#### a) Clés API Invalides
```php
// Si FEDAPAY_SECRET_KEY est invalide
// Que se passe-t-il?
```

**Code actuel** (`PaymentService.php:114-121`):
```php
catch (\Exception $e) {
    Log::error('FedaPay transaction creation failed', [
        'ticket_ids' => $ticketIds,
        'error' => $e->getMessage(),
    ]);

    throw new \Exception('Failed to create payment transaction: ' . $e->getMessage());
}
```

✅ **Bonne pratique**: Log + throw exception

#### b) Timeout Réseau FedaPay
- Que se passe-t-il si l'API FedaPay ne répond pas?
- Y a-t-il un timeout configuré?

**À vérifier**: Configuration du client HTTP FedaPay

---

### 5. **Gestion du Quota de Tickets**

**Code actuel** (`TicketController.php:74-79`):
```php
if (!$this->ticketService->checkQuotaAvailability($ticketTypeId, $quantity)) {
    return response()->json([
        'error' => 'Quota insuffisant',
        'message' => 'Il n\'y a pas assez de tickets disponibles pour ce type.'
    ], 400);
}
```

**Problème potentiel**: Race condition

**Scénario**:
1. Utilisateur A vérifie quota: 10 tickets disponibles ✓
2. Utilisateur B vérifie quota: 10 tickets disponibles ✓
3. Utilisateur A achète 10 tickets ✓
4. Utilisateur B achète 10 tickets ✓ (mais il n'en reste que 0!)

**Solution suggérée**: Transaction DB avec lock

```php
DB::transaction(function () use ($data) {
    // Lock sur le ticket_type pour éviter race condition
    $ticketType = TicketType::where('id', $ticketTypeId)
        ->lockForUpdate()
        ->first();

    if ($ticketType->quantity_available < $quantity) {
        throw new \Exception('Quota insuffisant');
    }

    // Créer les tickets...
});
```

---

### 6. **Webhook - Idempotence**

**Problème potentiel**: FedaPay peut envoyer le même webhook plusieurs fois

**Scénario**:
1. Webhook `transaction.approved` reçu → Ticket mis en statut "paid"
2. Webhook `transaction.approved` re-reçu (retry) → ???

**Code actuel** (`PaymentService.php:267-276`):
```php
$this->ticketRepository->update($ticket, [
    'status' => 'paid',
    'paid_at' => now(),
    'metadata' => array_merge($ticket->metadata ?? [], [
        'fedapay_transaction_id' => $entity['id'],
        'fedapay_reference' => $entity['reference'] ?? null,
        'payment_approved_at' => now()->toISOString(),
    ]),
]);
```

**Analyse**:
- ✅ Idempotent si le ticket est déjà "paid" (mise à jour, pas erreur)
- ⚠️ Risque: `paid_at` et `payment_approved_at` seront écrasés

**Solution suggérée**:
```php
// Ne mettre à jour que si le statut n'est pas déjà "paid"
if ($ticket->status !== 'paid') {
    $this->ticketRepository->update($ticket, [
        'status' => 'paid',
        'paid_at' => now(),
        // ...
    ]);
}
```

---

### 7. **Frontend - Page de Résultat**

**Fichier**: `ticketing-app/src/views/Payment/PaymentResultView.vue`

**Status**: Documentée dans le guide mais **non vérifiée si implémentée**

**Test à faire**:
1. Aller sur `/payment/result?status=approved&transaction_id=123&reference=ref123`
2. Vérifier si la page existe
3. Vérifier si le bon message s'affiche

---

### 8. **CORS - Callback Frontend**

**Problème potentiel**:
- FedaPay redirige vers `FRONTEND_URL` après paiement
- Si backend et frontend sont sur des domaines différents → CORS?

**Configuration actuelle** `.env`:
```
FRONTEND_URL=http://localhost:5173
```

**Code** (`PaymentController.php:28-29`):
```php
$frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'));
```

**⚠️ Problème**: `config('app.frontend_url')` n'existe pas dans `config/app.php`

**Correction suggérée**:
```php
// Option 1: Lire directement depuis .env
$frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

// Option 2: Ajouter dans config/app.php
'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
```

---

### 9. **Notification Client - Dépendance Externe**

**Code** (`PaymentService.php:281-287`):
```php
$this->notificationService->sendPaymentConfirmation($ticketId, [
    'transaction_id' => $entity['id'],
    // ...
]);
```

**Questions**:
1. Le `NotificationService` est-il implémenté?
2. Les emails partent-ils réellement?
3. Que se passe-t-il si l'envoi d'email échoue?

**Test à faire**:
```bash
# Vérifier la config mail
grep MAIL_ .env

# Tester l'envoi d'email
php artisan tinker
>>> Mail::raw('Test', function($message) { $message->to('test@example.com'); });
```

---

## 📝 Plan de Tests Complet

### Phase 1: Tests Unitaires (Backend)

```bash
# Test 1: Créer une transaction FedaPay
php artisan test --filter PaymentServiceTest::test_create_transaction

# Test 2: Vérifier webhook signature
php artisan test --filter PaymentServiceTest::test_verify_webhook_signature

# Test 3: Gérer webhook approved
php artisan test --filter PaymentServiceTest::test_handle_transaction_approved
```

### Phase 2: Tests d'Intégration

```bash
# Test 4: Flux complet d'achat
# 1. Créer événement + ticket_type
# 2. POST /api/tickets/purchase
# 3. Vérifier que payment_url est retournée
# 4. Simuler webhook FedaPay
# 5. Vérifier que les tickets sont marqués "paid"
```

### Phase 3: Tests End-to-End

```bash
# Test 5: Frontend → Backend → FedaPay → Webhook
# 1. Démarrer serveur backend
# 2. Démarrer serveur frontend
# 3. Ouvrir navigateur sur /events
# 4. Acheter un ticket
# 5. Payer sur FedaPay sandbox
# 6. Vérifier redirection vers /payment/result
# 7. Vérifier email reçu
# 8. Vérifier ticket en base = "paid"
```

---

## 🔧 Commandes de Débogage

### Vérifier la Configuration FedaPay
```bash
php artisan tinker
>>> config('services.fedapay')
```

### Tester la Connexion FedaPay
```php
use FedaPay\FedaPay;
use FedaPay\Transaction;

FedaPay::setApiKey(env('FEDAPAY_SECRET_KEY'));
FedaPay::setEnvironment('sandbox');

$transaction = Transaction::create([
    'description' => 'Test',
    'amount' => 100,
    'currency' => ['iso' => 'XOF'],
]);

dd($transaction->id); // Doit retourner un ID
```

### Vérifier les Routes API
```bash
php artisan route:list --path=api
```

### Tester Webhook Localement
```bash
curl -X POST http://localhost:8000/api/webhooks/fedapay \
  -H "Content-Type: application/json" \
  -H "X-FedaPay-Signature: test" \
  -d '{
    "name": "transaction.approved",
    "entity": {
      "id": "test123",
      "reference": "REF123",
      "amount": 5000,
      "currency": {"iso": "XOF"},
      "custom_metadata": {
        "ticket_ids": ["ticket-id-1"],
        "ticket_count": 1
      }
    }
  }'
```

---

## ✅ Ce qui Fonctionne Déjà (Confirmé)

1. ✅ **Connexion API FedaPay** - Tests manuels réussis (transaction 383505)
2. ✅ **Code Backend** - Bien structuré et conforme
3. ✅ **Configuration** - Clés API configurées
4. ✅ **Routes** - Endpoints définis correctement
5. ✅ **Sécurité** - Vérification signature webhook
6. ✅ **Documentation** - Guides complets

---

## 🎯 Prochaines Étapes Recommandées

### Étape 1: Réparer la Base de Données ⚡ URGENT
```bash
# Installer SQLite OU migrer vers MySQL
apt-get install php8.2-sqlite3

# Ou utiliser MySQL
# Modifier .env: DB_CONNECTION=mysql
# Créer la base: CREATE DATABASE ticketing;

# Puis:
php artisan migrate
php artisan db:seed
```

### Étape 2: Créer des Données de Test
```bash
# Créer un événement
php artisan tinker
>>> $event = Event::create([...]);
>>> $ticketType = TicketType::create([...]);
```

### Étape 3: Tester l'Endpoint Purchase
```bash
curl -X POST http://localhost:8000/api/tickets/purchase \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "ticket_type_id": "xxx",
    "quantity": 1,
    "customer": {
      "firstname": "Test",
      "lastname": "User",
      "email": "test@example.com",
      "phone_number": "+22997000000"
    }
  }'
```

### Étape 4: Corriger les Bugs Identifiés
1. Fix `config('app.frontend_url')` → `env('FRONTEND_URL')`
2. Ajouter vérification d'idempotence dans webhook handler
3. Ajouter lock sur quota check (race condition)

### Étape 5: Tester le Flux Complet
1. Démarrer backend: `php artisan serve`
2. Démarrer frontend: `npm run dev`
3. Acheter un ticket
4. Vérifier le paiement

---

## 📊 Résumé des Problèmes

| Problème | Gravité | Status | Solution |
|----------|---------|--------|----------|
| Module SQLite manquant | 🔴 Critique | Bloquant | Installer php-sqlite3 ou utiliser MySQL |
| Données de test absentes | 🟠 Important | Bloquant tests | Créer seeders |
| Config `app.frontend_url` | 🟡 Moyen | Fonctionnel mais incorrect | Lire depuis env() |
| Race condition quota | 🟡 Moyen | Potentiel | Ajouter lock DB |
| Idempotence webhook | 🟢 Faible | Fonctionnel | Amélioration recommandée |

---

## 🎓 Conclusion

L'intégration FedaPay est **BIEN CODÉE** et **CONFORME** aux bonnes pratiques, mais le système ne peut pas être testé actuellement à cause de:
1. ⛔ Module SQLite manquant
2. ⛔ Base de données vide

Une fois ces problèmes résolus, le flux devrait fonctionner correctement. Il y a quelques améliorations mineures à apporter (config, race conditions) mais rien de critique.

**Recommandation finale**: Installer SQLite ou migrer vers MySQL, puis exécuter les migrations et seeders.
