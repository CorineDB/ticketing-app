Voici la règle métier complète, robuste et exploitable en production pour la génération du QR code d’un ticket événementiel, adaptée à ton architecture, ton MCD, et tes contraintes anti-fraude.

✅ RÈGLE MÉTIER — Génération du QR Code d’un ticket
🎯 Objectif

Créer un QR code unique, infalsifiable, persistent, utilisable pour toute la durée de l'événement, et compatible avec le mécanisme anti-fraude basé sur la signature HMAC.

🌐 1. Le QR n’encode jamais des données sensibles

Il encode uniquement un identifiant unique + une signature cryptographique.

❌ Interdit dans le QR :

email

nom

numéro de téléphone

prix du ticket

date de validité

statut (in/out)

informations d’événement

👉 Pour éviter les manipulations, clonage, divulgation et RGPD.

🔐 2. Payload minimal du QR

Le QR doit encoder :

https://<domain>/t/<ticket_id>?sig=<signature>

Format :

ticket_id = UUID v4

signature = HMAC_SHA256(ticket_id + event_id, SECRET_KEY)

⚙️ 3. Méthode de signature cryptographique
Pseudo-code :
$signature = hash_hmac(
    'sha256',
    $ticket_id . '|' . $event_id,
    env('TICKET_HMAC_SECRET')
);


Utiliser une clé secrète longue (32 bytes).

Ne jamais sauvegarder la clé dans la DB.

Modifier régulièrement la clé invalide instantanément toutes les copies frauduleuses.

🔁 4. Le QR est statique et doit rester le même

Pourquoi ?

Le participant peut l’imprimer, le garder en PDF, WhatsApp, email.

Il ne doit pas changer après validation, sortie, re-entrée.

👉 Le QR est une clé d’accès, pas un état dynamique.

🧠 5. Validation côté serveur

Quand l’agent scanne, l’API :

1. Vérifie la signature

Si la signature ≠ HMAC(ticket_id+event_id) → QR falsifié → REFUS AUTOMATIQUE

2. Vérifie l’existence du ticket

ticket not found → tentative de fraude → REFUS

3. Vérifie l’état métier

unused → OK pour entry

inside → OK pour exit

outside → OK pour entry

invalidated → refus

refunded → refus

banned → refus

used_count >= usage_limit → refus

4. Logge le scan

Dans ticket_scan_logs avec agent_id, gate_id, scan_time.

🛡️ 6. Protection anti-clonage

Le QR peut être copié (photo), donc protection côté API :

6.1. Unicité et atomicité du scan

Deux personnes scannant le même QR à 2 portes :

→ L’API bloque le second scan car l’état change immédiatement (outside → inside).

6.2. Session token temporaire (anti-replay)

Pour empêcher l’usage simultané :

Workflow :

scan QR → l’API valide signature

l’API génère un session_token valable 30 sec

seul ce token permet d’exécuter entry/out

→ Une photo du QR seul est inutilisable sans session_token.

🧩 7. Contenu final du QR Code

Tu peux l’encoder soit en URL soit en JSON compacté :

Option A — URL (recommandé)
https://yourdomain.com/t/123e4567-e89b-12d3-a456-426614174000?sig=ab45dcef9...

Option B — JSON (si app native)
{
  "tid": "uuid-ticket",
  "sig": "hmac256"
}


Mais l’URL est compatible web + PDF + WhatsApp.

✨ 8. Chronologie métier complète

Voici la règle de génération du QR code étape par étape :

1. Création du ticket

créer ticket_id

déterminer event_id, ticket_type_id

enregistrer en DB

2. Génération de la signature HMAC
sig = HMAC_SHA256(ticket_id + event_id, SECRET)

3. Génération du lien de validation
/t/<ticket_id>?sig=<sig>

4. Génération de l’image QR

format PNG

taille 300x300

correction L ou M

uploaded into S3 or local/private storage : /tickets/qr/<ticket_id>.png

5. Enregistrement dans DB

Dans la table tickets :

colonne	description
qr_path	chemin du fichier
qr_hmac	signature calculée
magic_link_token	pour l’accès client (email/SMS)
🧨 9. Règle métier en cas de fraude

Si QR cloné :

➡️ premier scan devient inside
➡️ second scan (copie photo) = refus automatique
car état = inside, horodatage incohérent

🎯 Résumé final de la règle métier QR
Élément	Règle
QR statique	Oui
Données contenues	ticket_id + signature
Signature	HMAC_SHA256(ticket_id + event_id)
Secret	32 bytes minimum
Anti-fraude	via serveur + session_token
Modifiable ?	Jamais
Données sensibles dans QR	Non
Validation	Vérification HMAC + état + logs
Clonage	Impossible grâce aux états + session_token
