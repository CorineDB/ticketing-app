# Guide des Tests FedaPay

Ce guide explique comment utiliser les scripts de test FedaPay pour identifier et résoudre les problèmes d'intégration.

## 📋 Scripts Disponibles

| Script | Description | Usage |
|--------|-------------|-------|
| `check-fedapay-config.php` | Vérifie la configuration FedaPay | `php check-fedapay-config.php` |
| `test-fedapay.php` | Tests de base de l'API | `php test-fedapay.php` |
| `test-fedapay-advanced.php` | Tests avancés (formats, montants) | `php test-fedapay-advanced.php` |
| `test-fedapay-flow.php` | Simulation du flux complet | `php test-fedapay-flow.php` |
| `switch-fedapay-env.php` | Basculer entre sandbox/live | `php switch-fedapay-env.php [sandbox\|live]` |

---

## 🚀 Démarrage Rapide

### 1. Vérifier la Configuration

Avant tout test, vérifiez votre configuration:

```bash
php check-fedapay-config.php
```

**Ce script vérifie:**
- ✅ Présence de toutes les variables d'environnement
- ✅ Format des clés API (sandbox vs live)
- ✅ Cohérence entre l'environnement et les clés
- ✅ Connexion à l'API FedaPay

**Exemple de sortie:**
```
✅ FEDAPAY_PUBLIC_KEY configuré (SANDBOX)
✅ FEDAPAY_SECRET_KEY configuré (SANDBOX)
✅ FEDAPAY_ENVIRONMENT = sandbox (mode test)
✅ Connexion FedaPay réussie!
```

---

### 2. Basculer en Mode Sandbox (Recommandé pour les Tests)

⚠️ **Important:** Utilisez toujours le mode sandbox pour les tests!

```bash
php switch-fedapay-env.php sandbox
```

Cela configurera:
- `FEDAPAY_ENVIRONMENT=sandbox`
- `FEDAPAY_PUBLIC_KEY=pk_sandbox_...`
- `FEDAPAY_SECRET_KEY=sk_sandbox_...`

Puis redémarrez Laravel:
```bash
php artisan config:clear
php artisan serve
```

---

### 3. Exécuter les Tests

#### A. Tests de Base

Tests les fonctionnalités essentielles:

```bash
php test-fedapay.php
```

**Ce qui est testé:**
- ✅ Configuration API
- ✅ Création de client
- ✅ Création de transaction
- ✅ Génération de token de paiement

**Temps d'exécution:** ~3 secondes

---

#### B. Tests Avancés

Tests des cas limites et scénarios complexes:

```bash
php test-fedapay-advanced.php
```

**Ce qui est testé:**
- ✅ Différents formats de numéros de téléphone (Bénin, France, etc.)
- ✅ Différents montants (minimum, maximum, invalides)
- ✅ Métadonnées complexes
- ✅ Différents formats de callback URL
- ✅ Génération multiple de tokens

**Temps d'exécution:** ~10 secondes

---

#### C. Test du Flux Complet

Simule exactement le flux utilisé dans `PaymentService`:

```bash
php test-fedapay-flow.php
```

**Ce qui est testé:**
- ✅ Client Bénin (format local: 97123456)
- ✅ Client Bénin (format international: +22997123456)
- ✅ Client France (0612345678)
- ✅ Client sans numéro de téléphone
- ✅ Client avec numéro invalide (skip automatique)

**Temps d'exécution:** ~8 secondes

---

## 🔍 Interpréter les Résultats

### ✅ Succès

```
✅ Tous les tests ont réussi!
URL de paiement: https://sandbox-process.fedapay.com/...
```

**Signification:** L'intégration FedaPay fonctionne correctement!

---

### ❌ Erreur de Configuration

```
❌ FEDAPAY_SECRET_KEY n'est pas défini
```

**Solution:**
1. Vérifiez votre fichier `.env`
2. Ajoutez les variables manquantes
3. Relancez `php artisan config:clear`

---

### ❌ Erreur d'API

```
✗ Erreur lors de la création du client:
  Message: Invalid API key
```

**Solutions possibles:**
1. Vérifiez que vos clés API sont correctes
2. Vérifiez que l'environnement correspond aux clés (sandbox/live)
3. Vérifiez votre connexion internet

---

### ⚠️ Avertissement

```
⚠️  FEDAPAY_ENVIRONMENT = live (PRODUCTION)
```

**Action recommandée:**
- Si vous faites des tests, basculez en sandbox:
  ```bash
  php switch-fedapay-env.php sandbox
  ```

---

## 🛠️ Résolution de Problèmes

### Problème: "Failed to create payment transaction"

**Diagnostic:**
```bash
# Vérifier la configuration
php check-fedapay-config.php

# Tester l'API directement
php test-fedapay.php
```

**Causes possibles:**
1. Clés API invalides ou expirées
2. Environnement mal configuré
3. Problème de connexion réseau
4. Montant invalide (négatif, trop élevé)

---

### Problème: "Phone number format not recognized"

**Diagnostic:**
```bash
# Tester différents formats
php test-fedapay-advanced.php | grep "phone"
```

**Solution:**
Les formats supportés sont:
- Bénin: `97123456`, `22997123456`, `+22997123456`
- France: `0612345678`, `612345678`, `+33612345678`

Si le format n'est pas reconnu, le numéro est automatiquement ignoré (pas d'erreur).

---

### Problème: Incohérence sandbox/live

```
⚠️  INCOHÉRENCE: L'environnement et les clés ne correspondent pas!
   Environment: sandbox
   Public Key: live/invalide
```

**Solution:**
```bash
# Basculer tout en sandbox
php switch-fedapay-env.php sandbox

# OU basculer tout en live
php switch-fedapay-env.php live
```

---

## 📊 Comprendre les Résultats des Tests

### Transaction créée avec succès

```
✓ Transaction créée (ID: 383519, Status: pending)
✓ Token généré
URL: https://sandbox-process.fedapay.com/eyJ0eXAi...
```

**Signification:**
- Transaction créée dans FedaPay ✅
- Token de paiement généré ✅
- URL de paiement disponible ✅
- Status "pending" = en attente de paiement ✅

---

### Client créé avec succès

```
✓ Client créé (ID: 74192)
```

**Signification:**
- Le client a été enregistré dans FedaPay ✅
- Vous pouvez le voir dans le dashboard FedaPay
- L'ID peut être réutilisé pour d'autres transactions

---

## 🔐 Sécurité

### ⚠️ Ne committez JAMAIS vos clés API

Les scripts de test contiennent des clés en dur pour faciliter les tests.
En production:

1. **Utilisez uniquement `.env`** pour stocker les clés
2. **Ajoutez `.env` au `.gitignore`**
3. **Ne partagez jamais vos clés live**

### Mode Sandbox vs Live

| Aspect | Sandbox | Live |
|--------|---------|------|
| Argent réel | ❌ Non | ✅ Oui |
| Cartes de test | ✅ Oui | ❌ Non |
| Dashboard | sandbox.fedapay.com | dashboard.fedapay.com |
| Préfixe clés | `pk_sandbox_`, `sk_sandbox_` | `pk_live_`, `sk_live_` |

---

## 📝 Logs et Débogage

### Activer les logs détaillés

Dans `config/logging.php`, configurez le niveau de log:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'level' => 'debug', // Changez à 'debug' pour plus de détails
    ],
],
```

### Consulter les logs Laravel

```bash
tail -f storage/logs/laravel.log
```

### Consulter les logs FedaPay

Les scripts de test affichent les erreurs détaillées. Pour plus d'info:

1. Dashboard FedaPay: https://sandbox.fedapay.com (sandbox) ou https://dashboard.fedapay.com (live)
2. Section "Transactions" pour voir toutes les transactions
3. Section "Logs" pour voir les webhooks

---

## 🎯 Checklist Avant Production

Avant de passer en production, vérifiez:

- [ ] Tous les tests en sandbox réussissent
- [ ] Les webhooks sont configurés et testés
- [ ] Les clés live sont configurées dans `.env`
- [ ] L'environnement est défini sur `live`
- [ ] Le callback_url pointe vers votre domaine de production
- [ ] Les logs sont activés
- [ ] Le monitoring est en place
- [ ] Les montants min/max sont validés

### Commandes de vérification

```bash
# 1. Vérifier la config
php check-fedapay-config.php

# 2. Basculer en live (si prêt)
php switch-fedapay-env.php live

# 3. Vérifier que tout fonctionne
php artisan config:clear
php artisan cache:clear
```

---

## 📚 Ressources

- [Documentation FedaPay](https://docs.fedapay.com)
- [Dashboard Sandbox](https://sandbox.fedapay.com)
- [Dashboard Live](https://dashboard.fedapay.com)
- [Support FedaPay](https://fedapay.com/support)

---

## ❓ FAQ

### Q: Combien de temps les tokens sont-ils valides?

**R:** Les tokens FedaPay sont valides pendant 24 heures par défaut.

### Q: Puis-je réutiliser un token?

**R:** Non, chaque token est unique. Générez un nouveau token pour chaque tentative de paiement.

### Q: Que se passe-t-il si je génère plusieurs tokens pour la même transaction?

**R:** Chaque appel à `generateToken()` crée un nouveau token. Tous les tokens générés sont valides et pointent vers la même transaction.

### Q: Les tests créent-ils de vraies transactions?

**R:** En mode **sandbox**: Non, tout est simulé.
En mode **live**: Oui, les transactions sont réelles!

### Q: Comment supprimer les données de test?

**R:** Les données de test en sandbox sont automatiquement nettoyées périodiquement par FedaPay. Vous pouvez aussi les ignorer.

---

**Date de création:** 27 Novembre 2025
**Version:** 1.0
**Status:** ✅ Testé et validé
