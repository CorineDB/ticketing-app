# Déploiement Docker + GHCR sur EC2 (api.ticketing.corinebocoga.com)

Ce mode construit l'image Docker dans GitHub Actions, la pousse sur GHCR, puis pull + up sur EC2 via SSH.

## Différence vs natif
- Natif (actuel) : SSH + git pull + composer + systemd
- Docker+GHCR (ci-dessous) : SSH + docker compose pull + up (zero-downtime-friendly, image immuable)

## Étape 1 - Préparer EC2 (Docker)
SSH sur EC2:
```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker ubuntu
newgrp docker || true
mkdir -p ~/app
cd ~/app
```

## Étape 2 - Copier fichiers nécessaires sur EC2
Depuis ton repo local ou git pull:
```bash
cd /var/www/ticketing-app/ticketing-api-rest-app
scp docker-compose.yml ubuntu@<IP>:/home/ubuntu/app/
scp docker/Caddyfile ubuntu@<IP>:/home/ubuntu/app/
scp .env.production.example ubuntu@<IP>:/home/ubuntu/app/.env  # puis éditer
```

## Étape 3 - .env sur EC2
Même vars (DB_PASSWORD, REVERB_*, FEDAPAY_*, MAIL_*). DB_HOST peut rester postgres (service compose).

## Étape 4 - First launch
```bash
cd ~/app
docker compose up -d postgres
docker compose up -d web worker broadcasting scheduler caddy
docker compose exec -T web php artisan migrate --force
docker compose exec -T web php artisan storage:link || true
```

## Étape 5 - GitHub Secrets (déjà requis)
EC2_HOST, EC2_SSH_KEY, EC2_USER=ubuntu (GITHUB_TOKEN auto pour GHCR)

## Étape 6 - Déclencher
Push sur main (paths ticketing-api-rest-app/**) → workflow docker-deploy.yml (build GHCR + deploy SSH)
