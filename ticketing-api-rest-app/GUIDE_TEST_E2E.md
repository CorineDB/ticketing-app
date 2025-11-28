# Guide de Test End-to-End du Paiement FedaPay

## 🎯 Objectif

Tester le flux complet d'achat de ticket avec paiement FedaPay:
1. Achat de tickets via l'API
2. Paiement via le lien FedaPay
3. Réception du webhook
4. Mise à jour automatique du statut des tickets

---

## 📋 Prérequis

✅ Serveur Laravel en cours d'exécution (`php artisan serve`)
✅ Base de données configurée avec au moins un event et ticket type
✅ Configuration FedaPay dans `.env`

---

## 🚀 Procédure de Test

### Étape 1: Lancer le Test d'Achat

```bash
php test-purchase-e2e.php
```

**Ce script va:**
- Trouver un event avec des ticket types disponibles
- Créer 2 tickets en statut "issued"
- Générer un lien de paiement FedaPay
- Sauvegarder les données dans `test-purchase-result.json`

**Résultat attendu:**
```
✅ Achat réussi!
Transaction ID: 107724298
Lien de paiement: https://process.fedapay.com/...
```

---

### Étape 2: Surveiller les Tickets (Terminal 1)

Dans un nouveau terminal, lancez:

```bash
php watch-payment-status.php
```

**Ce script va:**
- Afficher le lien de paiement
- Vérifier toutes les 3 secondes le statut des tickets
- Afficher en temps réel quand les tickets passent de "issued" à "paid"

**Exemple de sortie:**
```
[05:10:15] Vérification #1...
  ⏳ Ticket 1: issued
  ⏳ Ticket 2: issued
  ⏳ En attente... (0/2 payés)

[05:10:18] Vérification #2...
  ✅ Ticket 1: paid (payé le 05:10:17)
  ✅ Ticket 2: paid (payé le 05:10:17)

🎉 TOUS LES TICKETS SONT MAINTENANT PAYÉS!
```

---

### Étape 3: Effectuer le Paiement

1. **Copiez le lien de paiement** affiché dans le terminal

2. **Ouvrez le lien dans votre navigateur**

3. **Complétez le paiement** sur la page FedaPay
   - Mode SANDBOX: Utilisez une carte de test
   - Mode LIVE: Utilisez une vraie carte (⚠️ PAIEMENT RÉEL!)

4. **Observez la mise à jour** dans le terminal de surveillance

---

## 📊 Vérification Manuelle

### Vérifier les Tickets Individuellement

```bash
# Remplacez {ticket_id} par l'ID du ticket
curl http://localhost:8000/api/public/tickets/{ticket_id} | python3 -m json.tool
```

**Avant paiement:**
```json
{
  "id": "019ac8db-5321-729c-bea4-6a26ed3e57af",
  "status": "issued",
  "paid_at": null,
  "metadata": null
}
```

**Après paiement:**
```json
{
  "id": "019ac8db-5321-729c-bea4-6a26ed3e57af",
  "status": "paid",
  "paid_at": "2025-11-28T05:10:17.000000Z",
  "metadata": {
    "fedapay_transaction_id": 107724298,
    "fedapay_reference": "REF-123456",
    "payment_approved_at": "2025-11-28T05:10:17Z"
  }
}
```

---

### Vérifier les Logs Laravel

```bash
tail -f storage/logs/laravel.log | grep -i fedapay
```

**Logs attendus:**
```
[2025-11-28 05:10:17] FedaPay webhook received
[2025-11-28 05:10:17] FedaPay webhook event: transaction.approved
[2025-11-28 05:10:17] Tickets marked as paid: 019ac8db-5321-...
```

---

### Vérifier le Dashboard FedaPay

1. Connectez-vous au dashboard FedaPay:
   - **Sandbox:** https://sandbox.fedapay.com
   - **Live:** https://dashboard.fedapay.com

2. Allez dans **Transactions**

3. Recherchez la transaction par ID (affiché dans le test)

4. Vérifiez:
   - ✅ Status: "approved" ou "completed"
   - ✅ Montant correct
   - ✅ Métadonnées (ticket_ids, ticket_count)
   - ✅ Webhook envoyé avec succès

---

## 🧪 Scripts de Test Disponibles

| Script | Description | Usage |
|--------|-------------|-------|
| `test-purchase-e2e.php` | Teste l'achat de tickets | `php test-purchase-e2e.php` |
| `watch-payment-status.php` | Surveille les tickets en temps réel | `php watch-payment-status.php` |
| `test-verify-payment.php` | Vérifie le statut après paiement | `php test-verify-payment.php` |
| `test-webhook-manual.php` | Teste le webhook (signature issue) | `php test-webhook-manual.php` |

---

## ✅ Critères de Succès

Le test end-to-end est réussi si:

1. ✅ L'endpoint `/api/tickets/purchase` crée les tickets avec succès
2. ✅ Un lien de paiement FedaPay est généré
3. ✅ Le lien de paiement s'ouvre et affiche la page FedaPay
4. ✅ Le paiement peut être complété
5. ✅ L'endpoint `/api/payment/callback` reçoit la redirection
6. ✅ Le webhook FedaPay est reçu et traité
7. ✅ Les tickets passent de "issued" à "paid"
8. ✅ Les métadonnées FedaPay sont ajoutées aux tickets
9. ✅ Le champ `paid_at` est renseigné

---

## ⚠️ Problèmes Courants

### Le lien de paiement ne s'ouvre pas

**Cause:** Transaction FedaPay invalide ou expirée

**Solution:**
- Vérifiez que les clés API sont correctes
- Relancez `php test-purchase-e2e.php` pour générer un nouveau lien

---

### Les tickets ne passent pas à "paid"

**Causes possibles:**

1. **Webhook non reçu**
   - Vérifiez les logs: `tail -f storage/logs/laravel.log`
   - Vérifiez le firewall/pare-feu
   - En local, FedaPay ne peut pas envoyer de webhook (localhost non accessible depuis Internet)

2. **Signature du webhook invalide**
   - Vérifiez `FEDAPAY_WEBHOOK_SECRET` dans `.env`
   - Doit correspondre à la configuration dans le dashboard FedaPay

3. **Erreur de traitement**
   - Consultez les logs Laravel pour les erreurs
   - Vérifiez que les ticket IDs sont corrects dans les métadonnées

**Solution pour test local:**
- Utilisez ngrok ou un tunnel pour exposer localhost
- Ou testez le webhook manuellement après paiement réel

---

### Mode Sandbox vs Live

**Vérifier l'environnement actuel:**
```bash
php check-fedapay-config.php
```

**Basculer en sandbox (recommandé pour tests):**
```bash
php switch-fedapay-env.php sandbox
php artisan config:clear
```

**Basculer en live (production uniquement):**
```bash
php switch-fedapay-env.php live
php artisan config:clear
```

---

## 🔒 Test en Production

### Checklist Avant de Tester en Live

- [ ] Tous les tests en sandbox ont réussi
- [ ] Les clés API live sont configurées
- [ ] L'URL de callback pointe vers le domaine de production
- [ ] Les webhooks sont configurés dans le dashboard FedaPay
- [ ] Le serveur est accessible publiquement (pas localhost)
- [ ] Les logs sont activés et surveillés
- [ ] Un montant de test minimum est utilisé (ex: 100 XOF)

### Procédure en Live

1. Basculez en mode live:
   ```bash
   php switch-fedapay-env.php live
   php artisan config:clear
   ```

2. Créez un ticket type avec un prix minimal (ex: 100 XOF)

3. Lancez le test:
   ```bash
   php test-purchase-e2e.php
   ```

4. **⚠️ ATTENTION:** Le paiement sera RÉEL!

5. Effectuez le paiement avec une vraie carte

6. Vérifiez que tout fonctionne

7. Rebasculez en sandbox après le test:
   ```bash
   php switch-fedapay-env.php sandbox
   php artisan config:clear
   ```

---

## 📈 Données de Test Sauvegardées

Après chaque test, des fichiers JSON sont créés:

| Fichier | Contenu |
|---------|---------|
| `test-purchase-result.json` | Données de l'achat (IDs tickets, lien de paiement) |
| `test-verification-result.json` | Résultat de la vérification des tickets |

---

## 🎯 Flux Complet Testé

```
┌─────────────────────┐
│  1. POST /tickets/  │
│     purchase        │
│                     │
│  - Crée tickets     │
│  - Génère lien      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  2. Lien FedaPay    │
│                     │
│  Utilisateur paye   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  3. Redirection     │
│     Callback        │
│                     │
│  GET /payment/      │
│      callback       │
└─────────────────────┘
           │
           ▼
┌─────────────────────┐
│  4. Webhook FedaPay │
│                     │
│  POST /webhooks/    │
│       fedapay       │
│                     │
│  - Vérifie signature│
│  - Met à jour       │
│    tickets → "paid" │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  5. Tickets Payés   │
│                     │
│  status: "paid"     │
│  paid_at: ...       │
│  metadata: {...}    │
└─────────────────────┘
```

---

**Date:** 27 Novembre 2025
**Version:** 1.0
**Status:** ✅ Testé et validé
