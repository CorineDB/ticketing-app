# Ticketing API REST Application - Implementation

## Architecture

Cette application suit les principes SOLID et utilise les patterns **Repository** et **Service**.

### Structure

```
app/
├── Models/                      # Modèles Eloquent
│   ├── User.php
│   ├── Role.php
│   ├── Event.php
│   ├── TicketType.php
│   ├── Ticket.php
│   ├── Gate.php
│   ├── TicketScanLog.php
│   └── EventCounter.php
│
├── Repositories/                # Repository Pattern
│   ├── Core/
│   │   ├── Contracts/
│   │   │   └── BaseRepositoryInterface.php
│   │   └── Eloquent/
│   │       └── BaseRepository.php
│   ├── Contracts/              # Repository Interfaces
│   │   ├── RoleRepositoryContract.php
│   │   ├── EventRepositoryContract.php
│   │   ├── TicketRepositoryContract.php
│   │   ├── TicketTypeRepositoryContract.php
│   │   ├── GateRepositoryContract.php
│   │   ├── TicketScanLogRepositoryContract.php
│   │   └── EventCounterRepositoryContract.php
│   └── [Implementations]        # Repository Implementations
│
├── Services/                    # Service Pattern
│   ├── Core/
│   │   ├── Contracts/
│   │   │   └── BaseServiceInterface.php
│   │   └── Eloquent/
│   │       └── BaseService.php
│   ├── Contracts/              # Service Interfaces
│   │   ├── RoleServiceContract.php
│   │   ├── EventServiceContract.php
│   │   ├── TicketServiceContract.php
│   │   ├── TicketTypeServiceContract.php
│   │   ├── GateServiceContract.php
│   │   ├── ScanServiceContract.php
│   │   └── TicketScanLogServiceContract.php
│   └── [Implementations]        # Service Implementations
│
└── Http/
    ├── Controllers/Api/         # API Controllers
    │   ├── EventController.php
    │   ├── TicketController.php
    │   ├── GateController.php
    │   ├── ScanController.php
    │   ├── TicketTypeController.php
    │   └── RoleController.php
    └── Requests/Api/            # Form Requests
        ├── Events/
        ├── Tickets/
        ├── Gates/
        └── Scan/
```

## Fonctionnalités Implémentées

### ✅ 1. Système de Tickets avec QR Codes
- Génération automatique de QR codes pour chaque ticket
- Signature HMAC pour la sécurité
- QR codes statiques (ne changent pas après génération)
- **Stockage sécurisé** : Fichiers QR stockés sur disque local privé (pas d'accès direct)
- **Accès sécurisé** : Endpoint dédié `/tickets/{id}/qr/download` avec validation magic link ou authentification

### ✅ 2. Magic Links
- Tokens sécurisés pour accès sans compte
- Accès public aux tickets via token

### ✅ 3. Workflow de Scan en 2 Étapes
- **Étape 1**: `POST /scan/request` - Validation du QR et génération d'un session token
- **Étape 2**: `POST /scan/confirm` - Confirmation du scan avec authentification

### ✅ 4. Gestion des Événements
- CRUD complet pour les événements
- Création d'événements avec types de tickets
- Statistiques en temps réel

### ✅ 5. Système Anti-Fraude
- Vérification HMAC des QR codes
- Locks Redis pour éviter les doubles scans
- Compteurs atomiques pour la capacité
- Logs immutables de tous les scans

### ✅ 6. Règles Métier Complètes
- ✅ Vérification de la capacité de l'événement (current_in < capacity)
- ✅ Gestion des statuts de tickets (issued → reserved → paid → in → out)
- ✅ Usage limits et quotas par type de ticket
- ✅ Validation des périodes de validité (validity_from / validity_to)
- ✅ **Validation des dates d'événement** : Scan possible uniquement entre start_datetime et end_datetime
- ✅ **Allow Reentry** : Respect de la règle allow_reentry pour les re-entrées
- ✅ **Cooldown anti-fraude** : 60 secondes entre sortie et re-entrée
- ✅ Vérification du statut des gates (active/pause/inactive)
- ✅ Double-scan detection (already_in / already_out)
- ✅ Atomicité des compteurs avec locks Redis

## Installation

1. Installer les dépendances:
```bash
composer install
```

2. Configurer l'environnement:
```bash
cp .env.example .env
php artisan key:generate
```

3. Configurer la base de données dans `.env`

4. **Important**: Générer un secret HMAC pour les tickets:
```bash
# Ajouter dans .env
TICKET_HMAC_SECRET=votre_secret_tres_long_ici
```

5. Exécuter les migrations:
```bash
php artisan migrate
```

6. Créer le lien symbolique pour le storage:
```bash
php artisan storage:link
```

## Configuration Requise

### Dépendances PHP
- `simplesoftwareio/simple-qrcode` - Pour la génération de QR codes

Installer via:
```bash
composer require simplesoftwareio/simple-qrcode
```

### Cache (Redis recommandé)
Pour les locks de scan et les sessions temporaires, configurez Redis:
```
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## API Endpoints

### Routes Publiques

```
GET  /api/public/events                        # Liste des événements
GET  /api/public/events/{id}                   # Détails d'un événement
GET  /api/public/events/{id}/ticket-types      # Types de tickets d'un événement
GET  /api/public/tickets/{id}?token=xxx        # Voir un ticket avec magic link
GET  /api/public/tickets/{id}/qr?token=xxx     # Métadonnées QR code avec magic link
GET  /api/public/tickets/{id}/qr/download?token=xxx  # Télécharger fichier QR (PNG)
POST /api/scan/request                         # Demande de scan (QR validation)
```

### Routes Authentifiées

```
# Events
GET    /api/events                  # Liste des événements
POST   /api/events                  # Créer un événement
GET    /api/events/{id}             # Détails d'un événement
PUT    /api/events/{id}             # Modifier un événement
DELETE /api/events/{id}             # Supprimer un événement
GET    /api/events/{id}/stats       # Statistiques d'un événement

# Tickets
GET    /api/tickets                 # Liste des tickets
POST   /api/tickets                 # Générer un/des ticket(s)
GET    /api/tickets/{id}            # Détails d'un ticket
PUT    /api/tickets/{id}            # Modifier un ticket
DELETE /api/tickets/{id}            # Supprimer un ticket
GET    /api/tickets/{id}/qr         # Métadonnées du QR code
GET    /api/tickets/{id}/qr/download # Télécharger fichier QR (PNG)
POST   /api/tickets/{id}/mark-paid  # Marquer comme payé

# Gates
GET    /api/gates                   # Liste des portes
POST   /api/gates                   # Créer une porte
GET    /api/gates/{id}              # Détails d'une porte
PUT    /api/gates/{id}              # Modifier une porte
DELETE /api/gates/{id}              # Supprimer une porte

# Scan
POST   /api/scan/confirm            # Confirmer un scan

# Ticket Types
GET    /api/events/{id}/ticket-types        # Types de tickets d'un événement
POST   /api/events/{id}/ticket-types        # Créer un type de ticket
GET    /api/ticket-types/{id}               # Détails d'un type de ticket
PUT    /api/ticket-types/{id}               # Modifier un type de ticket
DELETE /api/ticket-types/{id}               # Supprimer un type de ticket

# Roles
GET    /api/roles                   # Liste des rôles
POST   /api/roles                   # Créer un rôle
GET    /api/roles/{id}              # Détails d'un rôle
PUT    /api/roles/{id}              # Modifier un rôle
DELETE /api/roles/{id}              # Supprimer un rôle
```

## Workflow de Scan

### 1. Scan Request (Public)
```bash
POST /api/scan/request
{
  "ticket_id": "uuid",
  "sig": "hmac_signature"
}
```

Réponse:
```json
{
  "scan_session_token": "token",
  "expires_in": 20
}
```

### 2. Scan Confirm (Authentifié)
```bash
POST /api/scan/confirm
Authorization: Bearer {token}
{
  "scan_session_token": "token",
  "scan_nonce": "nonce",
  "gate_id": "uuid",
  "agent_id": "uuid",
  "action": "in" // ou "out"
}
```

Réponse:
```json
{
  "valid": true,
  "code": "OK",
  "message": "Entry successful",
  "ticket": { ... },
  "scan_log_id": "uuid"
}
```

## Principes SOLID Appliqués

### Single Responsibility Principle (SRP)
- Chaque Repository gère uniquement l'accès aux données
- Chaque Service gère uniquement la logique métier
- Chaque Controller gère uniquement les requêtes HTTP

### Open/Closed Principle (OCP)
- Les classes de base (BaseRepository, BaseService) sont ouvertes à l'extension
- Les interfaces permettent d'ajouter de nouvelles implémentations sans modifier le code existant

### Liskov Substitution Principle (LSP)
- Toutes les implémentations de Repository peuvent remplacer BaseRepository
- Toutes les implémentations de Service peuvent remplacer BaseService

### Interface Segregation Principle (ISP)
- Interfaces spécifiques pour chaque entité
- Les contrats définissent uniquement les méthodes nécessaires

### Dependency Inversion Principle (DIP)
- Les Controllers dépendent des interfaces (Contracts), pas des implémentations
- L'injection de dépendances est configurée dans AppServiceProvider

## Sécurité

- ✅ Validation HMAC des QR codes
- ✅ Magic links avec tokens sécurisés
- ✅ Authentification via Sanctum
- ✅ Validation des données avec Form Requests
- ✅ Transactions DB pour les opérations critiques
- ✅ Locks Redis pour éviter les race conditions

## Tests

Pour tester l'API, vous pouvez utiliser Postman ou Insomnia en important les spécifications OpenAPI depuis:
- `docs/ticket-api-doc.yml`
- `docs/OpenAPI YAML complet.yml`
- `docs/API documentation.yml`

## Prochaines Étapes

1. Ajouter l'authentification OAuth2
2. Implémenter les webhooks de paiement
3. Ajouter les notifications (email/SMS)
4. Implémenter les tests unitaires et d'intégration
5. Ajouter un système de rate limiting
6. Implémenter l'export CSV des logs

## Support

Pour toute question ou problème, référez-vous à la documentation complète dans le dossier `docs/`.
