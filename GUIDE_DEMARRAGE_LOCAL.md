# Guide de Démarrage Local - Application Ticketing

**Date**: 27 novembre 2025
**Base de données**: PostgreSQL

---

## 📋 Prérequis

- ✅ PHP 8.2+ installé
- ✅ Composer installé
- ✅ Node.js et npm installés
- ✅ PostgreSQL installé et démarré
- ✅ Git installé

---

## 🗄️ Étape 1: Configuration PostgreSQL

### Installer PostgreSQL (si pas déjà fait)

**Ubuntu/Debian**:
```bash
sudo apt-get update
sudo apt-get install postgresql postgresql-contrib
```

**macOS**:
```bash
brew install postgresql
brew services start postgresql
```

**Windows**:
Télécharger depuis https://www.postgresql.org/download/windows/

### Créer la Base de Données

```bash
# Se connecter à PostgreSQL
sudo -u postgres psql

# Créer la base de données
CREATE DATABASE ticketing;

# Créer un utilisateur (optionnel, sinon utiliser postgres)
CREATE USER ticketing_user WITH PASSWORD 'votre_password';
GRANT ALL PRIVILEGES ON DATABASE ticketing TO ticketing_user;

# Quitter
\q
```

### Configuration dans .env

Le fichier `.env` est déjà configuré pour PostgreSQL :

```bash
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ticketing
DB_USERNAME=postgres
DB_PASSWORD=           # ← Ajouter votre mot de passe PostgreSQL ici
```

**⚠️ Important**: Modifier `DB_PASSWORD` avec votre mot de passe PostgreSQL

---

## 🚀 Étape 2: Installation Backend (Laravel)

```bash
# Aller dans le dossier backend
cd ticketing-api-rest-app

# Les dépendances sont déjà installées (composer install fait)
# La clé Laravel est déjà générée (APP_KEY configuré)

# Exécuter les migrations
php artisan migrate

# Créer des données de test (seeders)
php artisan db:seed

# OU créer des données manuellement via tinker
php artisan tinker
```

### Créer des Données de Test Manuellement

```php
// Dans tinker (php artisan tinker)

// 1. Créer un utilisateur admin
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@ticketing.com',
    'password' => bcrypt('password123'),
]);

// 2. Créer un événement
$event = App\Models\Event::create([
    'name' => 'Concert Test',
    'description' => 'Un super concert de test',
    'location' => 'Cotonou, Bénin',
    'start_date' => now()->addDays(30),
    'end_date' => now()->addDays(30)->addHours(5),
    'organizer_id' => $user->id,
    'status' => 'published',
]);

// 3. Créer un type de ticket
$ticketType = App\Models\TicketType::create([
    'event_id' => $event->id,
    'name' => 'VIP',
    'description' => 'Accès VIP avec boissons incluses',
    'price' => 5000,
    'quota' => 100,
    'sold_count' => 0,
]);

// Noter l'ID du ticket type
echo "Ticket Type ID: " . $ticketType->id;
// ← Copier cet ID pour les tests
```

---

## 🎨 Étape 3: Installation Frontend (Vue.js)

```bash
# Aller dans le dossier frontend
cd ../ticketing-app

# Installer les dépendances
npm install

# Configurer le .env frontend (si nécessaire)
cp .env.example .env

# Vérifier que l'URL API pointe vers le backend
# Dans .env ou dans src/config/api.ts:
# VITE_API_URL=http://localhost:8000/api
```

---

## 🔥 Étape 4: Démarrer l'Application

### Terminal 1: Backend Laravel

```bash
cd ticketing-api-rest-app
php artisan serve

# L'application démarre sur: http://localhost:8000
```

**Logs en temps réel** (Terminal supplémentaire):
```bash
cd ticketing-api-rest-app
tail -f storage/logs/laravel.log
```

### Terminal 2: Frontend Vue.js

```bash
cd ticketing-app
npm run dev

# L'application démarre sur: http://localhost:5173
```

---

## 🧪 Étape 5: Tester le Flux d'Achat FedaPay

### Test 1: Vérifier les Routes

```bash
# Backend
curl http://localhost:8000/api/health
# Devrait retourner: {"status":"ok"}

# Lister les événements
curl http://localhost:8000/api/events
```

### Test 2: Acheter un Ticket

```bash
# Remplacer TICKET_TYPE_ID par l'ID obtenu plus haut
curl -X POST http://localhost:8000/api/tickets/purchase \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "ticket_type_id": "TICKET_TYPE_ID_ICI",
    "quantity": 1,
    "customer": {
      "firstname": "Jean",
      "lastname": "Dupont",
      "email": "jean.dupont@test.com",
      "phone_number": "+22997000000"
    }
  }'
```

**Réponse attendue**:
```json
{
  "tickets": [...],
  "payment_url": "https://sandbox-process.fedapay.com/...",
  "transaction_id": "123456",
  "total_amount": 5000,
  "currency": "XOF"
}
```

### Test 3: Ouvrir l'URL de Paiement

1. Copier l'URL `payment_url` de la réponse ci-dessus
2. Ouvrir dans un navigateur
3. Vous serez redirigé vers la page de paiement FedaPay sandbox
4. Utiliser une carte de test FedaPay pour payer

**Cartes de test FedaPay** :
- Visa : `4111 1111 1111 1111`
- Expiration : n'importe quelle date future
- CVV : `123`

### Test 4: Vérifier le Webhook (Optionnel)

Pour tester les webhooks en local, utiliser **ngrok**:

```bash
# Terminal 3: Démarrer ngrok
ngrok http 8000

# Copier l'URL HTTPS générée (ex: https://abc123.ngrok.io)

# Configurer le webhook dans FedaPay Dashboard:
# URL: https://abc123.ngrok.io/api/webhooks/fedapay
```

---

## 🎯 Flux Complet à Tester

### Via le Frontend (Recommandé)

1. **Ouvrir le navigateur** : http://localhost:5173
2. **Aller sur les événements** : `/events`
3. **Cliquer sur un événement**
4. **Cliquer sur "Acheter un billet"**
5. **Remplir le formulaire** :
   - Prénom : Jean
   - Nom : Dupont
   - Email : jean.dupont@test.com
   - Téléphone : +22997000000
   - Quantité : 1
6. **Cliquer sur "Procéder au paiement"**
7. **Redirection vers FedaPay**
8. **Payer avec carte de test**
9. **Retour sur l'application** : `/payment/result`
10. **Vérifier le statut** : `approved`

### Vérifier en Base de Données

```bash
# Se connecter à PostgreSQL
psql -U postgres -d ticketing

# Vérifier les tickets créés
SELECT id, buyer_name, buyer_email, status, paid_at FROM tickets;

# Devrait afficher le ticket avec status='paid' et paid_at rempli
```

---

## 🔧 Commandes Utiles

### Backend

```bash
# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Voir les routes
php artisan route:list

# Accéder au tinker (console interactive)
php artisan tinker

# Lancer les tests
php artisan test

# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Créer une migration
php artisan make:migration nom_de_la_migration

# Créer un seeder
php artisan make:seeder NomDuSeeder
```

### Frontend

```bash
# Lancer en mode dev
npm run dev

# Build pour production
npm run build

# Linter
npm run lint

# Vérifier les types TypeScript
npm run type-check
```

### PostgreSQL

```bash
# Se connecter à la base
psql -U postgres -d ticketing

# Lister les tables
\dt

# Voir la structure d'une table
\d tickets

# Compter les tickets
SELECT COUNT(*) FROM tickets;

# Voir les tickets payés
SELECT * FROM tickets WHERE status = 'paid';

# Quitter
\q
```

---

## 🐛 Dépannage

### Erreur: "Connection refused" (PostgreSQL)

```bash
# Vérifier que PostgreSQL tourne
sudo service postgresql status

# Démarrer PostgreSQL
sudo service postgresql start

# Vérifier le port
sudo netstat -plunt | grep 5432
```

### Erreur: "SQLSTATE[08006] password authentication failed"

```bash
# Vérifier le mot de passe dans .env
cat ticketing-api-rest-app/.env | grep DB_

# Réinitialiser le mot de passe postgres
sudo -u postgres psql
ALTER USER postgres PASSWORD 'nouveau_password';
\q

# Mettre à jour .env avec le nouveau password
```

### Erreur: "Base de données ticketing n'existe pas"

```bash
sudo -u postgres psql
CREATE DATABASE ticketing;
\q
```

### Erreur: "Class 'Event' not found"

```bash
# Vérifier que vous êtes dans tinker
php artisan tinker

# Utiliser le namespace complet
$event = App\Models\Event::create([...]);
```

### Erreur: "Route [payment.callback] not defined"

```bash
# Vérifier que la route existe
php artisan route:list | grep callback

# Effacer le cache des routes
php artisan route:clear
```

### Frontend ne charge pas les événements

```bash
# Vérifier l'URL de l'API dans le frontend
# Fichier: ticketing-app/src/config/api.ts
# Doit pointer vers: http://localhost:8000/api

# Vérifier les CORS dans le backend
# Le package fruitcake/php-cors devrait gérer automatiquement
```

---

## 📊 Checklist de Démarrage

### Première Installation

- [ ] PostgreSQL installé et démarré
- [ ] Base de données `ticketing` créée
- [ ] Fichier `.env` configuré avec mot de passe DB
- [ ] `composer install` exécuté (déjà fait ✅)
- [ ] `php artisan key:generate` exécuté (déjà fait ✅)
- [ ] `php artisan migrate` exécuté
- [ ] Données de test créées (seeders ou tinker)
- [ ] `npm install` exécuté dans le frontend
- [ ] Clés FedaPay configurées dans `.env` (déjà fait ✅)

### Démarrage Quotidien

- [ ] PostgreSQL démarré : `sudo service postgresql start`
- [ ] Backend démarré : `php artisan serve`
- [ ] Frontend démarré : `npm run dev`
- [ ] Logs ouverts : `tail -f storage/logs/laravel.log` (optionnel)

---

## 🔐 Clés FedaPay Configurées

Les clés sandbox sont déjà configurées dans `.env` :

```bash
FEDAPAY_PUBLIC_KEY=pk_sandbox_TcBc9d1JPwbOlDzCYf7rjJCL
FEDAPAY_SECRET_KEY=sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN
FEDAPAY_ENVIRONMENT=sandbox
FEDAPAY_CURRENCY=XOF
```

✅ **Prêt pour les tests !**

---

## 🎉 Vous êtes Prêt !

Une fois toutes les étapes complétées :

1. **Backend** : http://localhost:8000
2. **Frontend** : http://localhost:5173
3. **API** : http://localhost:8000/api
4. **Logs** : `storage/logs/laravel.log`

**Bon développement ! 🚀**

---

## 📞 Commandes Rapides (Copy-Paste)

```bash
# Démarrage complet (3 terminaux)

# Terminal 1: Backend
cd ticketing-api-rest-app && php artisan serve

# Terminal 2: Frontend
cd ticketing-app && npm run dev

# Terminal 3: Logs
cd ticketing-api-rest-app && tail -f storage/logs/laravel.log
```

**Accès rapide** :
- Frontend : http://localhost:5173
- Backend API : http://localhost:8000/api
- Backend Admin : http://localhost:8000

Enjoy! 🎟️
