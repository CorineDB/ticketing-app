# API de Ticketing avec Paiement FedaPay

API REST complète pour la gestion de billets d'événements avec paiement intégré, notifications et contrôle d'accès.

## 🎯 Fonctionnalités

### ✅ Gestion des événements et billets
- Création et gestion d'événements avec capacité, dates et localisation
- Différents types de billets par événement (VIP, Standard, etc.)
- Génération automatique de codes QR sécurisés avec signature HMAC
- Magic links pour accès sans authentification
- Stockage sécurisé des QR codes sur disque local privé

### 💳 Paiement automatique (FedaPay)
- **Génération automatique de liens de paiement** pour chaque type de billet
- Intégration complète avec FedaPay API (sandbox et production)
- Webhooks sécurisés pour confirmation de paiement
- Mise à jour automatique du statut des billets après paiement
- Support de la monnaie XOF (et autres devises FedaPay)

### 📧 Système de notifications
- **Emails automatiques** avec templates HTML professionnels:
  - Confirmation de billet (avec QR code en pièce jointe)
  - Confirmation de paiement
  - Notifications de scan (entrée/sortie)
- Infrastructure SMS prête (Twilio, Vonage)
- Traitement asynchrone via Laravel Queue
- Retry automatique (3 tentatives) en cas d'échec

### 🔐 Contrôle d'accès et sécurité
- Scan en 2 étapes: requête publique + confirmation authentifiée
- Validation HMAC des QR codes
- Règles métier strictes:
  - Vérification des dates d'événement
  - Respect de la capacité maximum
  - Gestion de allow_reentry
  - Cooldown anti-fraude de 60 secondes
  - Détection de double-scan
  - Limites d'utilisation par billet
- Logs de scan immuables pour audit
- Compteurs atomiques avec Redis locks

### 🚦 Rate limiting
- **scan-request**: 60 requêtes/minute par IP
- **scan-confirm**: 30 requêtes/minute par utilisateur
- **api générale**: 100 requêtes/minute
- Protection contre spam et abus

### 🏗️ Architecture SOLID
- **Repository Pattern**: Séparation de la logique d'accès aux données
- **Service Pattern**: Logique métier centralisée
- **Dependency Injection**: Tous les services injectés via constructeur
- **Contracts/Interfaces**: Abstraction pour faciliter les tests
- **Single Responsibility**: Chaque classe a une responsabilité unique

## 📋 Prérequis

- PHP 8.1+
- Composer
- PostgreSQL ou SQLite
- Redis (optionnel, pour le cache)
- Compte FedaPay (sandbox pour tests)

## 🚀 Installation rapide

```bash
# 1. Cloner et installer
git clone <repository-url>
cd ticketing-api-rest-app
composer install

# 2. Installer les packages spécifiques
composer require fedapay/fedapay-php
composer require simplesoftwareio/simple-qrcode

# 3. Configuration
cp .env.example .env
php artisan key:generate

# 4. Configurer FedaPay dans .env
FEDAPAY_PUBLIC_KEY=pk_sandbox_...
FEDAPAY_SECRET_KEY=sk_sandbox_...
FEDAPAY_WEBHOOK_SECRET=your_secret
FEDAPAY_ENVIRONMENT=sandbox
FEDAPAY_CURRENCY=XOF

# 5. Base de données
php artisan migrate
php artisan queue:table
php artisan migrate

# 6. Lancer l'application
php artisan serve

# 7. Lancer le worker de queue (dans un autre terminal)
php artisan queue:work
```

## 📖 Documentation complète

**Pour un guide de test détaillé, consultez:** [TESTING_GUIDE.md](TESTING_GUIDE.md)

Le guide de test contient:
- Configuration détaillée de FedaPay
- Configuration des webhooks avec ngrok
- Tests du flux complet (événement → ticket → paiement → scan)
- Vérification des emails et notifications
- Tests du rate limiting
- Résolution des problèmes courants
- Architecture technique détaillée

## 🔑 Configuration FedaPay

### Clés sandbox (test)
```env
FEDAPAY_PUBLIC_KEY=pk_sandbox_TcBc9d1JPwbOlDzCYf7rjJCL
FEDAPAY_SECRET_KEY=sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN
FEDAPAY_WEBHOOK_SECRET=ticketing
FEDAPAY_ENVIRONMENT=sandbox
```

### Configuration du webhook

Votre URL de webhook: `https://votre-domaine.com/api/webhooks/fedapay`

Pour tester en local avec ngrok:
```bash
ngrok http 8000
# URL webhook: https://xxxx.ngrok-free.app/api/webhooks/fedapay
```

Dans FedaPay Dashboard → Webhooks, activez:
- `transaction.approved`
- `transaction.canceled`
- `transaction.created`

## 📁 Structure du projet

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── EventController.php
│   │   ├── TicketController.php
│   │   ├── ScanController.php
│   │   ├── WebhookController.php
│   │   └── ...
│   └── Middleware/
│       └── VerifyCsrfToken.php (webhook exclusion)
│
├── Models/
│   ├── Event.php
│   ├── Ticket.php
│   ├── TicketType.php
│   ├── Gate.php
│   └── ...
│
├── Repositories/
│   ├── Contracts/ (interfaces)
│   └── [Repository implementations]
│
├── Services/
│   ├── Contracts/
│   │   ├── PaymentServiceContract.php
│   │   ├── NotificationServiceContract.php
│   │   └── ...
│   ├── PaymentService.php (FedaPay)
│   ├── NotificationService.php
│   ├── TicketService.php
│   ├── ScanService.php
│   └── ...
│
├── Jobs/
│   ├── SendTicketEmail.php
│   ├── SendPaymentConfirmationEmail.php
│   └── SendScanNotificationEmail.php
│
├── Mail/
│   ├── TicketConfirmationMail.php
│   ├── PaymentConfirmationMail.php
│   └── ScanNotificationMail.php
│
└── Providers/
    └── AppServiceProvider.php (DI bindings + rate limiting)

database/migrations/
├── 2024_11_26_000001_create_roles_table.php
├── 2024_11_26_000002_update_users_table.php
├── 2024_11_26_000003_create_events_table.php
├── 2024_11_26_000004_create_ticket_types_table.php
├── 2024_11_26_000005_create_gates_table.php
├── 2024_11_26_000006_create_tickets_table.php
├── 2024_11_26_000007_create_ticket_scan_logs_table.php
├── 2024_11_26_000008_create_event_counters_table.php
└── 2024_11_26_100001_add_payment_fields_to_ticket_types_table.php

resources/views/emails/
├── ticket-confirmation.blade.php
├── payment-confirmation.blade.php
└── scan-notification.blade.php

routes/
└── api.php (tous les endpoints)
```

## 🛣️ Routes principales

### Publiques
```
GET    /api/public/events
GET    /api/public/events/{id}
GET    /api/public/tickets/{id}?token={magic_link}
GET    /api/public/tickets/{id}/qr/download?token={magic_link}
POST   /api/scan/request
POST   /api/webhooks/fedapay
```

### Authentifiées (Bearer token)
```
POST   /api/events
GET    /api/events/{id}
POST   /api/events/{id}/ticket-types
POST   /api/tickets
POST   /api/scan/confirm
POST   /api/gates
```

## 🔄 Flux de paiement automatique

```
1. Création d'un TicketType avec price > 0
   ↓
2. TicketTypeService détecte price > 0
   ↓
3. PaymentService::createPaymentLinkForTicketType()
   ↓
4. Appel FedaPay API → Création transaction
   ↓
5. Génération du token de paiement
   ↓
6. Mise à jour TicketType:
   - payment_url
   - payment_transaction_id
   - payment_token
   ↓
7. Retour de l'URL de paiement au client
   ↓
8. Client effectue le paiement sur FedaPay
   ↓
9. FedaPay envoie webhook transaction.approved
   ↓
10. WebhookController vérifie la signature
   ↓
11. PaymentService::handleWebhookEvent()
   ↓
12. Mise à jour Ticket: status = 'paid'
   ↓
13. NotificationService envoie email de confirmation
```

## 📊 Statuts des billets

```
issued   → Billet créé, en attente de paiement
reserved → Billet réservé temporairement
paid     → Paiement confirmé
in       → Participant à l'intérieur de l'événement
out      → Participant sorti de l'événement
invalid  → Billet invalidé (fraude, remboursement, etc.)
expired  → Billet expiré
```

## 🧪 Tests

```bash
# Tester un endpoint
curl -X POST http://localhost:8000/api/events \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mon événement",
    "start_datetime": "2024-12-15 18:00:00",
    "end_datetime": "2024-12-15 23:59:59",
    "capacity": 1000
  }'

# Voir les logs
tail -f storage/logs/laravel.log

# Voir les jobs dans la queue
php artisan queue:monitor

# Vider le cache
php artisan cache:clear
php artisan config:clear
```

## 🔒 Sécurité

- ✅ Signatures HMAC pour QR codes
- ✅ Webhook signature verification (FedaPay)
- ✅ Magic links avec tokens aléatoires 64 caractères
- ✅ QR codes stockés en privé (pas d'accès direct)
- ✅ Rate limiting sur tous les endpoints sensibles
- ✅ CSRF protection (webhook exclu)
- ✅ Validation des données avec Form Requests
- ✅ Transactions DB pour opérations atomiques
- ✅ Redis locks pour compteurs d'événement

## 📝 Logs et audit

- **Logs de scan immuables** (`ticket_scan_logs`)
- **Logs FedaPay** dans `storage/logs/laravel.log`
- **Jobs queue** tracés avec retry automatique
- **Metadata JSON** sur les tickets pour historique complet

## 🌐 Production

Pour déployer en production:

1. **Configuration FedaPay live:**
   ```env
   FEDAPAY_ENVIRONMENT=live
   FEDAPAY_PUBLIC_KEY=pk_live_...
   FEDAPAY_SECRET_KEY=sk_live_...
   ```

2. **Serveur SMTP réel:**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.your-provider.com
   ```

3. **Queue worker avec supervisord:**
   ```ini
   [program:laravel-worker]
   command=php /path/to/artisan queue:work --tries=3
   autostart=true
   autorestart=true
   ```

4. **URL webhook FedaPay:**
   ```
   https://api.your-domain.com/api/webhooks/fedapay
   ```

5. **Redis pour cache et sessions** (recommandé)

## 🤝 Support

Pour toute question ou problème:
- Consultez [TESTING_GUIDE.md](TESTING_GUIDE.md)
- Vérifiez les logs: `storage/logs/laravel.log`
- Documentation FedaPay: https://docs.fedapay.com

## 📜 Licence

Ce projet est sous licence MIT.

---

**Développé avec ❤️ suivant les principes SOLID**
