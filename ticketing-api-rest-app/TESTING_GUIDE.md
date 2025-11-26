# Guide de Test - API de Ticketing avec FedaPay

## Configuration initiale

### 1. Variables d'environnement

Copiez `.env.example` vers `.env` et configurez:

```bash
cp .env.example .env
```

**Configuration FedaPay (Sandbox):**
```env
FEDAPAY_PUBLIC_KEY=pk_sandbox_TcBc9d1JPwbOlDzCYf7rjJCL
FEDAPAY_SECRET_KEY=sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN
FEDAPAY_WEBHOOK_SECRET=ticketing
FEDAPAY_ENVIRONMENT=sandbox
FEDAPAY_CURRENCY=XOF
```

**Configuration de la queue:**
```env
QUEUE_CONNECTION=database
```

**Configuration email (pour tests locaux):**
```env
MAIL_MAILER=log
# OU pour tester avec Mailtrap:
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### 2. Installation des dépendances

```bash
cd ticketing-api-rest-app
composer install
composer require fedapay/fedapay-php
composer require simplesoftwareio/simple-qrcode
```

### 3. Configuration de la base de données

```bash
# Générer la clé application
php artisan key:generate

# Lancer les migrations
php artisan migrate

# Créer la table jobs pour la queue
php artisan queue:table
php artisan migrate
```

### 4. Configuration du webhook avec ngrok

```bash
# Installer ngrok: https://ngrok.com/download

# Démarrer votre serveur Laravel
php artisan serve

# Dans un autre terminal, exposer avec ngrok
ngrok http 8000
```

**Configurer le webhook FedaPay:**
1. Connectez-vous à https://dashboard.fedapay.com (sandbox)
2. Allez dans **Paramètres** → **Webhooks**
3. Ajoutez l'URL: `https://votre-url.ngrok-free.app/api/webhooks/fedapay`
4. Événements à activer:
   - `transaction.approved`
   - `transaction.canceled`
   - `transaction.created`

### 5. Lancer le worker de queue

```bash
# Dans un terminal séparé
php artisan queue:work
```

---

## Tests du flux complet

### Étape 1: Créer un rôle et un utilisateur

```bash
# POST /api/roles
curl -X POST http://localhost:8000/api/roles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "Organisateur",
    "slug": "organizer"
  }'

# POST /api/register (si vous avez un endpoint d'inscription)
# Sinon créer directement en base de données
```

### Étape 2: Créer un événement

```bash
# POST /api/events
curl -X POST http://localhost:8000/api/events \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "Festival Culturel 2024",
    "description": "Un événement culturel incroyable",
    "location": "Stade de l'\''Amitié, Cotonou",
    "start_datetime": "2024-12-15 18:00:00",
    "end_datetime": "2024-12-15 23:59:59",
    "capacity": 1000,
    "allow_reentry": true
  }'
```

### Étape 3: Créer un type de ticket (génération auto du lien de paiement)

```bash
# POST /api/events/{eventId}/ticket-types
curl -X POST http://localhost:8000/api/events/{EVENT_ID}/ticket-types \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "VIP",
    "price": 5000,
    "quota": 50,
    "validity_from": "2024-12-15 00:00:00",
    "validity_to": "2024-12-15 23:59:59",
    "usage_limit": 1
  }'
```

**Réponse attendue:**
```json
{
  "id": "uuid-du-ticket-type",
  "name": "VIP",
  "price": 5000,
  "payment_url": "https://checkout.fedapay.com/...",
  "payment_transaction_id": "123456",
  "payment_token": "tok_...",
  ...
}
```

### Étape 4: Générer un billet

```bash
# POST /api/tickets
curl -X POST http://localhost:8000/api/tickets \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "event_id": "EVENT_UUID",
    "ticket_type_id": "TICKET_TYPE_UUID",
    "email": "participant@example.com",
    "phone": "+22997000000",
    "first_name": "Jean",
    "last_name": "Dupont"
  }'
```

**Ce qui se passe automatiquement:**
- ✅ Génération du code unique
- ✅ Génération du QR code avec signature HMAC
- ✅ Stockage sécurisé du QR sur disque local
- ✅ Génération du magic link
- ✅ Email de confirmation envoyé (avec QR en pièce jointe)
- ✅ Job ajouté à la queue

**Réponse attendue:**
```json
{
  "id": "uuid-du-ticket",
  "code": "EVT-001-000001",
  "status": "issued",
  "qr_path": "tickets/qr/uuid.png",
  "magic_link_token": "...",
  ...
}
```

### Étape 5: Effectuer un paiement (simulation)

1. Récupérer le `payment_url` du TicketType
2. Ouvrir dans un navigateur
3. Utiliser les cartes de test FedaPay:
   - **Succès**: `4000000000000002`
   - **Échec**: `4000000000000036`

**Ce qui se passe lors du webhook `transaction.approved`:**
- ✅ Ticket marqué comme "paid"
- ✅ Champ `paid_at` renseigné
- ✅ Metadata mis à jour avec transaction FedaPay
- ✅ Email de confirmation de paiement envoyé
- ✅ Log dans `storage/logs/laravel.log`

### Étape 6: Scanner le billet (2 étapes)

**6.1. Requête de scan (publique):**
```bash
# POST /api/scan/request
curl -X POST http://localhost:8000/api/scan/request \
  -H "Content-Type: application/json" \
  -d '{
    "ticket_id": "TICKET_UUID",
    "signature": "HMAC_SIGNATURE_FROM_QR"
  }'
```

**Réponse:**
```json
{
  "session_token": "random-64-chars",
  "ticket": {
    "code": "EVT-001-000001",
    "status": "paid",
    ...
  },
  "expires_in": 20
}
```

**6.2. Confirmation du scan (authentifiée):**
```bash
# POST /api/scan/confirm
curl -X POST http://localhost:8000/api/scan/confirm \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer AGENT_TOKEN" \
  -d '{
    "session_token": "token-from-step-1",
    "action": "in",
    "gate_id": "GATE_UUID",
    "agent_id": "AGENT_USER_UUID"
  }'
```

**Ce qui se passe lors d'une entrée réussie:**
- ✅ Vérification des règles métier (statut, dates, capacité, etc.)
- ✅ Ticket mis à jour (status = "in", used_count++, last_used_at)
- ✅ Compteur event_counter incrémenté (current_in++)
- ✅ Log de scan créé (immuable)
- ✅ Email de notification envoyé
- ✅ Respect du cooldown de 60 secondes
- ✅ Respect de la règle allow_reentry

### Étape 7: Accéder au billet via Magic Link

```bash
# GET /api/public/tickets/{id}?token={magic_link_token}
curl http://localhost:8000/api/public/tickets/TICKET_UUID?token=MAGIC_LINK_TOKEN
```

**Télécharger le QR code:**
```bash
# GET /api/public/tickets/{id}/qr/download?token={magic_link_token}
curl http://localhost:8000/api/public/tickets/TICKET_UUID/qr/download?token=MAGIC_LINK_TOKEN \
  --output ticket-qr.png
```

---

## Vérification du rate limiting

### Test du rate limiter scan-request (60/minute par IP)

```bash
# Lancer 70 requêtes rapidement
for i in {1..70}; do
  curl -X POST http://localhost:8000/api/scan/request \
    -H "Content-Type: application/json" \
    -d '{"ticket_id": "xxx", "signature": "xxx"}'
done

# Après la 60ème requête, vous devriez recevoir:
# HTTP 429 Too Many Requests
```

### Test du rate limiter scan-confirm (30/minute par user)

```bash
# Lancer 35 requêtes authentifiées rapidement
for i in {1..35}; do
  curl -X POST http://localhost:8000/api/scan/confirm \
    -H "Authorization: Bearer YOUR_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"session_token": "xxx", "action": "in", "gate_id": "xxx", "agent_id": "xxx"}'
done

# Après la 30ème requête: HTTP 429
```

---

## Vérification des emails

### Avec MAIL_MAILER=log

Consultez le fichier: `storage/logs/laravel.log`

### Avec Mailtrap

1. Créez un compte sur https://mailtrap.io
2. Configurez les credentials dans `.env`
3. Consultez la boîte mail dans l'interface Mailtrap

### Emails envoyés automatiquement:

1. **Confirmation de billet** → Après génération du ticket
   - Sujet: "Votre billet est prêt!"
   - Pièce jointe: QR code PNG
   - Magic link inclus

2. **Confirmation de paiement** → Après webhook FedaPay
   - Sujet: "Paiement confirmé"
   - Détails: montant, transaction ID

3. **Notification de scan** → Après entrée/sortie
   - Sujet: "Entrée confirmée" ou "Sortie confirmée"
   - Détails: porte, heure

---

## Vérification des logs

```bash
# Logs généraux
tail -f storage/logs/laravel.log

# Logs des jobs (queue)
# Visibles dans laravel.log avec le tag [job]

# Logs FedaPay
# Chercher "FedaPay" dans laravel.log
grep "FedaPay" storage/logs/laravel.log
```

---

## Résolution des problèmes courants

### Problème: Webhook ne reçoit rien

**Vérifications:**
1. ngrok est bien démarré et pointe vers le bon port
2. URL webhook dans FedaPay dashboard est correcte
3. Route `/api/webhooks/fedapay` est bien accessible (pas de 404)
4. Webhook secret correspond dans `.env`

**Test manuel du webhook:**
```bash
curl -X POST https://votre-url.ngrok-free.app/api/webhooks/fedapay \
  -H "Content-Type: application/json" \
  -H "X-FedaPay-Signature: test" \
  -d '{"name": "transaction.approved", "entity": {"id": 123}}'
```

### Problème: Emails non envoyés

**Vérifications:**
1. Queue worker est lancé: `php artisan queue:work`
2. Table jobs existe: `php artisan queue:table && php artisan migrate`
3. QUEUE_CONNECTION=database dans `.env`
4. Vérifier les jobs échoués: `php artisan queue:failed`

**Relancer un job échoué:**
```bash
php artisan queue:retry all
```

### Problème: QR code non généré

**Vérifications:**
1. Package installé: `composer require simplesoftwareio/simple-qrcode`
2. Dossier storage/app/tickets/qr/ existe et est writable
3. Permissions: `chmod -R 775 storage`

### Problème: Rate limiting ne fonctionne pas

**Vérifications:**
1. CACHE_STORE configuré (database ou redis)
2. Table cache existe pour database driver
3. Middleware throttle appliqué aux routes

---

## Architecture des fonctionnalités

### 1. Paiement FedaPay

**Fichiers impliqués:**
- `app/Services/PaymentService.php` - Logique FedaPay
- `app/Services/TicketTypeService.php` - Génération auto lien paiement
- `app/Http/Controllers/Api/WebhookController.php` - Réception webhooks
- `app/Http/Middleware/VerifyCsrfToken.php` - Exclusion webhook
- `database/migrations/*_add_payment_fields_to_ticket_types_table.php`

**Flux:**
```
Création TicketType (price > 0)
  → PaymentService::createPaymentLinkForTicketType()
  → FedaPay API: Transaction::create()
  → FedaPay API: generateToken()
  → Retour: payment_url, payment_transaction_id, payment_token

Paiement effectué
  → FedaPay envoie webhook
  → WebhookController::fedapayWebhook()
  → PaymentService::handleWebhookEvent()
  → Si approved: Ticket.status = 'paid'
  → NotificationService::sendPaymentConfirmation()
```

### 2. Notifications

**Fichiers impliqués:**
- `app/Services/NotificationService.php` - Orchestration
- `app/Jobs/SendTicketEmail.php` - Job async ticket
- `app/Jobs/SendPaymentConfirmationEmail.php` - Job async paiement
- `app/Jobs/SendScanNotificationEmail.php` - Job async scan
- `app/Mail/TicketConfirmationMail.php` - Mailable ticket
- `app/Mail/PaymentConfirmationMail.php` - Mailable paiement
- `app/Mail/ScanNotificationMail.php` - Mailable scan
- `resources/views/emails/*.blade.php` - Templates HTML

**Flux:**
```
Génération ticket
  → NotificationService::sendTicketConfirmation()
  → Dispatch SendTicketEmail job
  → Queue worker traite
  → Mail::send() avec QR en attachement

Webhook paiement
  → NotificationService::sendPaymentConfirmation()
  → Dispatch SendPaymentConfirmationEmail job
  → Queue worker traite

Scan réussi
  → NotificationService::sendScanNotification()
  → Dispatch SendScanNotificationEmail job
  → Queue worker traite
```

### 3. Rate Limiting

**Fichiers impliqués:**
- `app/Providers/AppServiceProvider.php` - Définition rate limiters
- `routes/api.php` - Application middleware throttle

**Configuration:**
```php
'scan-request' → 60/min par IP
'scan-confirm' → 30/min par user (ou 10/min par IP)
'api' → 100/min général
```

---

## Commandes utiles

```bash
# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Voir les routes
php artisan route:list

# Voir les jobs dans la queue
php artisan queue:monitor

# Relancer tous les jobs échoués
php artisan queue:retry all

# Vider la queue
php artisan queue:flush

# Tester l'envoi d'email
php artisan tinker
> Mail::raw('Test email', fn($m) => $m->to('test@test.com')->subject('Test'));
```

---

## Conclusion

L'API est complète avec:
- ✅ Architecture SOLID (Repository/Service/Controller)
- ✅ Paiement automatique via FedaPay
- ✅ Webhooks sécurisés avec signature
- ✅ Notifications email/SMS asynchrones
- ✅ Rate limiting anti-spam
- ✅ QR codes sécurisés (HMAC + stockage privé)
- ✅ Magic links pour accès sans auth
- ✅ Règles métier complètes (cooldown, reentry, capacity)
- ✅ Logs immuables pour audit

**Prêt pour production après:**
1. Configuration des clés FedaPay en mode "live"
2. Configuration d'un vrai serveur SMTP
3. Déploiement sur un serveur avec domaine
4. Configuration supervisord pour queue worker
5. Monitoring et alertes
