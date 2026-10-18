# Guide de déploiement — EC2 Native (Ubuntu 24.04) - api.ticketing.corinebocoga.com

Objectif : déployer le backend Laravel sur AWS EC2 (installation native : PHP 8.3, Nginx, PostgreSQL 16, systemd, Certbot) accessible via `https://api.ticketing.corinebocoga.com/`. Frontend déjà sur Vercel (`https://ticketing.corinebocoga.com/`).

---

## 1. AWS EC2 — Création de l'instance

### Compte AWS
- Compte AWS (nouveau) → Free plan = crédits $100–200 (couvert ~6 mois pour t3.small)
- À surveiller : Billing → Charges après épuisement des crédits

### Création de l'instance
1. Aller sur [console.aws.amazon.com/ec2](https://console.aws.amazon.com/ec2) → `Launch Instance`
2. **Name & tags** : `ticketing-api-prod`
3. **AMI** : Ubuntu Server 24.04 LTS (ami-*) — Architecture `x86_64`
4. **Instance type** : `t3.small` (2 vCPU / 2 GiB RAM) — recommandé
5. **Key pair** : créer une nouvelle clé PEM (ex. `ticketing-ec2.pem`) → télécharger et stocker en lieu sûr
6. **Network** :
   - VPC par défaut
   - Auto-assign public IP : activé
7. **Storage** : gp3 20 Go
8. **Security Group (pare-feu)** : créer → nom `ticketing-api-sg`
   - SSH (22) : `My IP` uniquement (limiter à ton IP actuelle)
   - HTTP (80) : `0.0.0.0/0`
   - HTTPS (443) : `0.0.0.0/0`
9. `Launch Instance`

---

## 2. Elastic IP (IP publique fixe)

1. EC2 → Elastic IPs → Allocate Elastic IP address (VPC, Amazon's pool)
2. Actions → Associate Elastic IP → sélectionner l'instance `ticketing-api-prod`
3. Noter l'**Elastic IP** : `X.X.X.X`

---

## 3. DNS Hostinger (corinebocoga.com)

1. [hostinger.com](https://hostinger.com) → Domain Portfolio → `corinebocoga.com` → DNS Zone
2. Ajouter un enregistrement **A** :

| Type | Nom | Valeur | TTL |
|---|---|---|---|
| A | `api.ticketing` | `<Elastic IP EC2>` | (default/auto) |

3. **Vérification** : après ~1–10 min (DNS propagation), tester :
```bash
nslookup api.ticketing.corinebocoga.com
# ou
dig +short api.ticketing.corinebocoga.com
```
Doit retourner l'Elastic IP.

*(Le domaine `ticketing.corinebocoga.com` reste sur Vercel — inchangé)*

---

## 4. Connexion SSH à l'instance

Sur ton PC (Linux/macOS) :

```bash
chmod 600 ~/Downloads/ticketing-ec2.pem   # adapter le chemin
ssh -i ~/Downloads/ticketing-ec2.pem ubuntu@<ELASTIC_IP>
```

Si première connexion : accepter le host key.

---

## 5. Déployer le backend sur le serveur (méthode rapide avec setup-server.sh)

Sur l'instance EC2 (SSH) :

```bash
# 1. Bootstrap complet (swap + PHP+Nginx+Postgres+systemd+Certbot)
sudo apt update && sudo apt install -y git
cd /var/www
sudo mkdir -p /var/www && sudo chown ubuntu:ubuntu /var/www
git clone https://github.com/CorineDB/ticketing-app.git
cd /var/www/ticketing-app/ticketing-api-rest-app

# 2. Lancer le bootstrap
chmod +x deploy/setup-server.sh deploy/backup.sh deploy.sh
sudo ./deploy/setup-server.sh
```

Le script :
- Crée un swap 1 Go
- Installe : Nginx, PostgreSQL 16, PHP 8.3 (fpm+extensions), Composer, Certbot
- Crée l'utilisateur/db `ticketing` PostgreSQL
- Installe les dépendances Composer (`--no-dev`)
- Génère `APP_KEY` si manquant, configure `.env` depuis template
- Copie nginx conf + 2 units systemd (worker + reverb)
- Active systemd + cron Laravel `schedule:run` + cron backup quotidien
- Active UFW (22/80/443)

À la fin, le script t'indique les actions manuelles à faire (3 étapes).

---

## 6. Finaliser le `.env` de production

Sur l'instance EC2 :

```bash
cd /var/www/ticketing-app/ticketing-api-rest-app
nano .env
```

Vérifie / complète obligatoirement :

- `APP_URL=https://api.ticketing.corinebocoga.com`
- `FRONTEND_URL=https://ticketing.corinebocoga.com`
- `DB_PASSWORD` (remplace `changeme` si présent)
- `BROADCAST_CONNECTION=reverb`
- `REVERB_HOST=api.ticketing.corinebocoga.com`, `REVERB_PORT=443`, `REVERB_SCHEME=https`
- `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET` → réutilise les valeurs de ton `.env` local (ou génère)
- `FEDAPAY_PUBLIC_KEY/SECRET_KEY/WEBHOOK_SECRET` (live)
- `MAIL_*`, `SMS_*` à jour
- `TOKEN_ENCRYPTION_KEY` (fort, 32+ chars)

Puis appliquer :

```bash
php artisan optimize
sudo systemctl restart ticketing-worker ticketing-reverb
```

---

## 7. SSL HTTPS avec Certbot (après pointage DNS OK)

DNS doit déjà répondre à l'Elastic IP (`dig api.ticketing.corinebocoga.com` donne l'IP).

```bash
sudo certbot --nginx -d api.ticketing.corinebocoga.com
```

- Sélectionner l'option `Redirect HTTP to HTTPS` (recommandé)
- Renouvellement auto : `systemctl status certbot.timer` doit être `active`

Test :

```bash
curl -I https://api.ticketing.corinebocoga.com
curl https://api.ticketing.corinebocoga.com/api/health
```

Attendu : JSON avec `status`.

---

## 8. Vercel (frontend) — mise à jour des variables

Sur [vercel.com](https://vercel.com) → projet `ticketing-app` (frontend) → Settings → Environment Variables :

| Variable | Valeur | Environnements |
|---|---|---|
| `VITE_API_URL` | `https://api.ticketing.corinebocoga.com/api` | Production (+ Preview si souhaité) |
| `VITE_REVERB_HOST` | `api.ticketing.corinebocoga.com` | Production |
| `VITE_REVERB_PORT` | `443` | Production |
| `VITE_REVERB_SCHEME` | `https` | Production |

Redéployer : Deployments → dernière production → Redeploy.

---

## 9. FedaPay (webhook)

[FedaPay Dashboard](https://dashboard.fedapay.com/) → Webhooks → créer/éditer :

- **URL** : `https://api.ticketing.corinebocoga.com/api/webhooks/fedapay`
- **Événements** : sélectionner les événements utilisés (transaction_created/transaction_success/transaction_failure)
- **Secret** : utiliser `FEDAPAY_WEBHOOK_SECRET` de ton `.env` (même valeur)

Tester le front à l'URL [https://ticketing.corinebocoga.com](https://ticketing.corinebocoga.com)

---

## 10. Déploiement automatique (GitHub Actions)

Le repo contient `.github/workflows/deploy-backend.yml`. Ajouter les secrets dans GitHub :

Repo → Settings → Secrets and variables → Actions → New repository secret :

| Secret | Valeur |
|---|---|
| `EC2_HOST` | `<Elastic IP>` |
| `EC2_SSH_KEY` | Contenu complet de `ticketing-ec2.pem` (y compris `-----BEGIN... END RSA PRIVATE KEY-----`) |
| `EC2_USER` | `ubuntu` (défaut utilisé) |

Désormais, chaque **push sur `main`** touchant `ticketing-api-rest-app/**` déclenche un déploiement auto :
- `git pull` → `composer install --no-dev` → `migrate --force` → `optimize` → redémarrage `worker` + `reverb`
- Smoke test `http://${EC2_HOST}/api/health`

Déploiement manuel : Actions → `Deploy backend (EC2)` → `Run workflow`

Sur le serveur, `deploy.sh` fait la même chose.

---

## 11. Sécurité & Backups

### Sécurité immédiate
- [ ] Révoquer le token GitHub `ghp_...` visible dans `.git/config` (sur [github.com/settings/tokens](https://github.com/settings/tokens))
- [ ] Clé SSH de deploy = paire dédiée (jamais ta clé perso)
- [ ] SG EC2 : SSH restreint à **ton IP unique** (pas 0.0.0.0/0)
- [ ] `APP_DEBUG=false` confirmé
- [ ] UFW actif (22/80/443)

### Backups
`deploy/backup.sh` tourne tous les jours à **03h15** (crontab www-data) :
- `pg_dump -Fc ticketing_db` → `/var/backups/ticketing/db_YYYY-MM-DD.dump`
- `tar storage/app` → `/var/backups/ticketing/storage_YYYY-MM-DD.tgz`
- Rotation 7 jours

Test manuel :
```bash
bash /var/www/ticketing-app/ticketing-api-rest-app/deploy/backup.sh
ls -lh /var/backups/ticketing/
```

**Optionnel (gratuit)** : copier vers Oracle Object Storage (20 Go) ou AWS S3 Free Tier (5 Go) plus tard.

### Surveillance
```bash
# Statut services
systemctl status nginx php8.3-fpm ticketing-worker ticketing-reverb

# Logs
journalctl -u ticketing-worker -n 20
journalctl -u ticketing-reverb -n 20
tail -n 20 /var/log/nginx/ticketing-api.error.log
tail -n 30 /var/www/ticketing-app/ticketing-api-rest-app/storage/logs/laravel.log
```

---

## 12. Check-list finale

- [ ] Instance t3.small UP + Elastic IP attachée
- [ ] DNS Hostinger A `api.ticketing` → Elastic IP (résolu)
- [ ] `setup-server.sh` exécuté avec succès
- [ ] `.env` prod complet + `php artisan optimize` + restart services
- [ ] Certbot SSL OK → `https://api.ticketing.corinebocoga.com/api/health` répond
- [ ] Front Vercel vars mises à jour + redeploy → connexion API OK
- [ ] FedaPay webhook mis à jour
- [ ] GitHub Secrets ajoutés → workflow déploy OK
- [ ] Backups testés + cron présent (`crontab -l -u www-data`)
- [ ] UFW + SG restreints OK

---

**Coût** : $0/mois pendant ~6 mois (crédits AWS) → ~$18/mois après. Domaine Hostinger déjà payé. Vercel free OK.

**Fichiers créés dans le repo** : `deploy/*`, `deploy.sh`, `.env.production.example`, `bootstrap/app.php` (trustProxies), `.github/workflows/deploy-backend.yml`
