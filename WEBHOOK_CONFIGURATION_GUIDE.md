# Guide de Configuration des Webhooks FedaPay

## 📡 Qu'est-ce qu'un Webhook ?

Un webhook est une notification automatique envoyée par FedaPay à votre serveur backend lorsqu'un événement se produit (paiement confirmé, annulé, etc.).

**C'est LA source de vérité pour confirmer les paiements** - ne faites JAMAIS confiance uniquement au callback URL.

---

## 🎯 Événements Écoutés par Votre Application

Votre backend écoute actuellement ces événements :

| Événement | Description | Action Backend |
|-----------|-------------|----------------|
| `transaction.approved` | ✅ Paiement réussi | Met TOUS les tickets en statut "paid" |
| `transaction.canceled` | ❌ Paiement annulé | Log l'annulation |
| `transaction.created` | 📝 Transaction créée | Log la création |

**Endpoint webhook :** `POST /api/webhooks/fedapay`

---

## 🔧 Configuration dans le Dashboard FedaPay

### **Étape 1 : Accéder aux Webhooks**

1. Connectez-vous à votre dashboard FedaPay : https://dashboard.fedapay.com/
2. Allez dans **Paramètres** → **Webhooks**
3. Cliquez sur **Ajouter un endpoint**

### **Étape 2 : Configurer l'Endpoint**

**Pour la PRODUCTION :**
```
URL: https://votre-domaine.com/api/webhooks/fedapay
```

**Pour les TESTS (avec ngrok) :**
```
URL: https://votre-url-ngrok.ngrok.io/api/webhooks/fedapay
```

### **Étape 3 : Sélectionner les Événements**

Cochez ces événements :
- ✅ `transaction.approved`
- ✅ `transaction.canceled`
- ✅ `transaction.created`

### **Étape 4 : Récupérer le Secret**

1. Après avoir créé l'endpoint, FedaPay génère un **Webhook Secret**
2. **Copiez ce secret** - vous en aurez besoin

### **Étape 5 : Configurer le Backend**

Ajoutez le secret dans votre fichier `.env` :

```bash
# FedaPay Configuration
FEDAPAY_PUBLIC_KEY=pk_sandbox_xxxxxxxxxx
FEDAPAY_SECRET_KEY=sk_sandbox_xxxxxxxxxx
FEDAPAY_WEBHOOK_SECRET=whsec_xxxxxxxxxx  # ← Le secret du webhook
FEDAPAY_ENVIRONMENT=sandbox  # ou 'live' pour production
FEDAPAY_CURRENCY=XOF
```

---

## 🧪 Tester les Webhooks en Local

### **Problème :** FedaPay ne peut pas atteindre `localhost:8000`

FedaPay a besoin d'une URL publique pour envoyer les webhooks. En développement local, utilisez **ngrok** ou **Expose**.

### **Solution 1 : Utiliser ngrok (Recommandé)**

#### **Installation de ngrok**

**MacOS :**
```bash
brew install ngrok
```

**Linux :**
```bash
wget https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-linux-amd64.tgz
tar -xvzf ngrok-v3-stable-linux-amd64.tgz
sudo mv ngrok /usr/local/bin/
```

**Windows :**
Téléchargez depuis https://ngrok.com/download

#### **Utilisation**

1. **Démarrez votre serveur Laravel :**
```bash
cd /home/unknow/Ticketing/ticketing-api-rest-app
php artisan serve
# Serveur démarré sur http://127.0.0.1:8000
```

2. **Lancez ngrok dans un autre terminal :**
```bash
ngrok http 8000
```

3. **Copiez l'URL générée :**
```
Forwarding  https://abc123.ngrok.io -> http://localhost:8000
```

4. **Configurez FedaPay avec cette URL :**
```
https://abc123.ngrok.io/api/webhooks/fedapay
```

5. **Testez !** Les webhooks de FedaPay arriveront maintenant à votre localhost via ngrok.

---

### **Solution 2 : Utiliser Laravel Expose**

```bash
# Installation
composer global require beyondcode/expose

# Démarrer expose
expose share http://127.0.0.1:8000

# Utilisez l'URL fournie dans FedaPay
```

---

## 🔍 Vérifier que les Webhooks Fonctionnent

### **Méthode 1 : Logs Laravel**

Surveillez les logs en temps réel :

```bash
cd /home/unknow/Ticketing/ticketing-api-rest-app
tail -f storage/logs/laravel.log
```

Vous devriez voir :
```
[2025-11-27 12:34:56] local.INFO: FedaPay webhook received {"event_type":"transaction.approved","entity_id":"12345"}
[2025-11-27 12:34:56] local.INFO: Tickets marked as paid {"ticket_ids":["abc","def"],"updated_count":2,"transaction_id":"12345"}
```

### **Méthode 2 : Dashboard FedaPay**

1. Allez dans **Paramètres** → **Webhooks**
2. Cliquez sur votre endpoint
3. Consultez l'onglet **Événements récents**
4. Vérifiez les statuts :
   - ✅ 200 : Webhook traité avec succès
   - ❌ 401/500 : Erreur (vérifiez les logs)

### **Méthode 3 : Base de Données**

Vérifiez que les tickets sont mis à jour :

```bash
php artisan tinker

# Chercher un ticket par email
$tickets = App\Models\Ticket::where('buyer_email', 'test@example.com')->get();

# Vérifier le statut
$tickets->pluck('status');
// Devrait afficher: ["paid", "paid", "paid"]

# Vérifier les metadata
$tickets->first()->metadata;
// Devrait contenir: fedapay_transaction_id, fedapay_reference, payment_approved_at
```

---

## 🐛 Dépannage

### **Erreur : "Invalid signature" (401)**

**Cause :** Le `FEDAPAY_WEBHOOK_SECRET` dans `.env` ne correspond pas au secret du dashboard.

**Solution :**
1. Vérifiez le secret dans le dashboard FedaPay
2. Copiez-le exactement dans `.env`
3. Redémarrez le serveur : `php artisan serve`

```bash
# Vérifier la configuration
php artisan tinker
config('services.fedapay.webhook_secret')
```

---

### **Erreur : "Transaction approved but no ticket_ids in metadata"**

**Cause :** Les metadata ne contiennent pas les IDs des tickets.

**Solution :** Vérifiez que `TicketController->purchase()` passe bien tous les ticket IDs :

```php
// TicketController.php ligne 101-106
$paymentData = $this->paymentService->createTransactionForTicket(
    $ticketIds,  // ← Doit être un array
    $customer,
    $totalAmount,
    "Achat de {$quantity} ticket(s) - {$ticketType->name}"
);
```

---

### **Erreur : Webhook reçu mais tickets pas mis à jour**

**Causes possibles :**

1. **Repository ne trouve pas les tickets**
   ```bash
   php artisan tinker
   $ticket = App\Models\Ticket::find('ticket-id-from-log');
   // Si null, le ticket n'existe pas
   ```

2. **Erreur dans la mise à jour**
   Vérifiez les logs Laravel :
   ```bash
   tail -f storage/logs/laravel.log | grep "Failed to update tickets"
   ```

3. **Problème de permissions**
   ```bash
   # Vérifier les permissions
   ls -la storage/logs/
   ```

---

### **Webhook n'arrive jamais**

**Vérifications :**

1. **ngrok est bien démarré**
   ```bash
   curl https://votre-url-ngrok.ngrok.io/api/webhooks/fedapay
   # Devrait retourner une erreur 405 (Method Not Allowed) - c'est normal, c'est un POST
   ```

2. **L'URL dans FedaPay est correcte**
   - Doit inclure `/api/webhooks/fedapay`
   - Doit être HTTPS (ngrok fournit automatiquement HTTPS)

3. **Le firewall n'est pas bloqué**
   ```bash
   # Tester depuis l'extérieur
   curl -X POST https://votre-url-ngrok.ngrok.io/api/webhooks/fedapay \
     -H "Content-Type: application/json" \
     -d '{"test": true}'
   ```

4. **Les routes sont bien chargées**
   ```bash
   php artisan route:list | grep webhook
   # Devrait afficher: POST api/webhooks/fedapay
   ```

---

## 🧪 Tester Manuellement un Webhook

Vous pouvez simuler un webhook pour tester votre code :

```bash
curl -X POST http://localhost:8000/api/webhooks/fedapay \
  -H "Content-Type: application/json" \
  -H "X-FedaPay-Signature: test" \
  -d '{
    "name": "transaction.approved",
    "entity": {
      "id": "txn_test_123",
      "reference": "REF123",
      "amount": 5000,
      "currency": {"iso": "XOF"},
      "custom_metadata": {
        "ticket_ids": ["019ac3e4-0536-71ab-9b99-845f80ee1def"],
        "ticket_count": 1
      }
    }
  }'
```

**Note :** Cela échouera à la vérification de signature, mais vous verrez l'erreur dans les logs, ce qui prouve que l'endpoint est accessible.

---

## 📊 Flux Complet de Test

### **Test End-to-End Complet**

1. **Démarrer le backend**
   ```bash
   php artisan serve
   ```

2. **Démarrer ngrok**
   ```bash
   ngrok http 8000
   ```

3. **Configurer FedaPay**
   - URL webhook : `https://xxx.ngrok.io/api/webhooks/fedapay`
   - Copier le webhook secret dans `.env`

4. **Surveiller les logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

5. **Faire un achat test depuis le frontend**
   - Aller sur un événement
   - Acheter des tickets (mode sandbox)
   - Payer avec les cartes de test FedaPay

6. **Vérifier le webhook arrive**
   - Consulter les logs : événement `transaction.approved` reçu
   - Vérifier DB : tickets en statut "paid"
   - Vérifier dashboard FedaPay : webhook 200 OK

---

## 🔒 Sécurité en Production

### **Checklist de Production**

- [ ] Utiliser `FEDAPAY_ENVIRONMENT=live`
- [ ] Configurer une vraie URL HTTPS (pas ngrok)
- [ ] Vérifier que le webhook secret est bien configuré
- [ ] Activer la vérification de signature (déjà fait ✅)
- [ ] Mettre en place un monitoring des webhooks ratés
- [ ] Logger tous les événements webhook

### **Rotation du Webhook Secret**

Si vous pensez que votre secret a été compromis :

1. Allez dans Dashboard FedaPay → Webhooks
2. Régénérez le secret
3. Mettez à jour `.env` immédiatement
4. Redéployez l'application

---

## 📈 Monitoring en Production

### **Métriques à Surveiller**

1. **Taux de succès des webhooks**
   ```sql
   -- Tickets créés mais pas payés après 1h
   SELECT COUNT(*) FROM tickets
   WHERE status = 'issued'
   AND created_at < NOW() - INTERVAL 1 HOUR;
   ```

2. **Webhooks ratés**
   ```bash
   grep "FedaPay webhook signature verification failed" storage/logs/laravel.log | wc -l
   ```

3. **Délai de traitement**
   ```bash
   grep "Tickets marked as paid" storage/logs/laravel.log
   ```

### **Alertes à Configurer**

- Webhook reçu mais signature invalide → Vérifier la configuration
- Webhook traité mais tickets non mis à jour → Problème de code
- Aucun webhook reçu depuis X minutes → Problème de réseau

---

## ✅ Checklist Finale

### **En Développement (Sandbox)**
- [ ] ngrok ou expose installé et configuré
- [ ] Webhook endpoint configuré dans FedaPay Sandbox
- [ ] `FEDAPAY_WEBHOOK_SECRET` dans `.env`
- [ ] Tests avec cartes de test FedaPay passés
- [ ] Logs confirmant la réception des webhooks

### **En Production (Live)**
- [ ] URL publique HTTPS configurée
- [ ] Webhook endpoint configuré dans FedaPay Live
- [ ] Secret de production dans `.env`
- [ ] Monitoring en place
- [ ] Tests avec vrais paiements effectués

---

## 🎉 Vous êtes prêt !

Votre système de webhooks est maintenant configuré pour :
- ✅ Recevoir les notifications FedaPay en temps réel
- ✅ Mettre à jour TOUS les tickets d'une transaction
- ✅ Logger tous les événements
- ✅ Gérer les erreurs proprement
- ✅ Fonctionner en local (ngrok) et en production

**Questions ?** Consultez la doc officielle : https://docs.fedapay.com/webhooks
