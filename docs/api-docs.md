3) Catalogue d’erreurs (codes standardisés) — exhaustive

Chaque erreur retourne le schéma Error : { code, message, details? }.

Code	HTTP	Signification	Quand
UNAUTHORIZED	401	Auth manquante / invalide	Token absent ou expiré
FORBIDDEN	403	Permissions insuffisantes	Role non autorisé
NOT_FOUND	404	Ressource introuvable	ticket/event inexistant
VALIDATION_ERROR	422	Erreur de validation	champ manquant / format
QR_SIGNATURE_MISMATCH	400	Signature QR invalide	QR falsifié
TICKET_NOT_FOUND	404	Ticket introuvable	id invalide
TICKET_ALREADY_INSIDE	422	Déjà à l'intérieur	tentative scan_in alors que inside
TICKET_NOT_INSIDE	422	Pas à l'intérieur	tentative scan_out injustifiée
CONFLICT_SCAN	409	Conflit de scan	token utilisé / lock en place
CAPACITY_FULL	422	Capacité atteinte	current_in >= capacity
QUOTA_EXCEEDED	422	Quota tickets dépassé	ticket_type quota atteint
TICKET_EXPIRED	422	Ticket hors validité	validity_to dépassée
FRAUD_SUSPECT	403	Activité suspecte	détection pattern fraude
PAYMENT_FAILED	400	Paiement échoué	webhook payment error
INTERNAL_ERROR	500	Erreur serveur	logs nécessaires
4) Champs, formats, valeurs par défaut, validations principales

UUID : format xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx. (ex. tickets.id)

date-time : RFC3339 (ISO8601) 2025-11-26T12:00:00Z.

status (ticket) possible values (string, validated in app): issued, reserved, paid, inside, outside, invalid, refunded.

Default : issued

type possible values: entrance, exit, vip, other. (string)

Default : entrance

gate.status: active, pause, inactive. Default active.

event.allow_reentry: boolean default true.

ticket.usage_limit: integer >= 1 default 1.

ticket.quota: integer >= 0 or null for unlimited.

Scan session TTL : configurable (ex: 20 seconds).

Scan lock Redis TTL: short (ex: 3 seconds).

5) Quels endpoints nécessitent auth / lesquels sont publics

Public (no auth) :

GET /events (liste)

GET /events/{id} (détail)

GET /events/{id}/ticket-types

POST /scan/request (la requête QR inclut la signature HMAC, donc scan/request peut être public)

GET /tickets/{id} with token query (magic link)

GET /tickets/{id}/qr with token query (magic link)

POST /webhooks/payment (webhook - validate HMAC from provider)

Requires OAuth2 (organizer/agent/superadmin/caissier):

CRUD events, ticket-types, tickets (creation/génération), scan/confirm, gates, stats, ticket put/delete, exports.

Notes:

POST /scan/request is intentionally public to allow scanners without prior auth (mobile scanner). However POST /scan/confirm requires the operator to be authenticated (agent) to confirm in/out. This reduces risk: the scanner device must call /scan/request (public) then an authenticated device/operator calls /scan/confirm.

6) Exemples d’utilisation (brefs)

Contrôleur scanne QR (URL includes ticket_id + sig) → frontend calls POST /scan/request with payload {ticket_id, sig} → API validates sig, returns {scan_session_token, expires_in}.

Contrôleur confirme action (in or out) via POST /scan/confirm with {scan_session_token, scan_nonce, gate_id, agent_id, action} (auth Bearer). API checks token validity, atomic locks, ticket state, capacity, usage_limit, then writes ticket_scan_logs and returns ScanResult.

Participant reçoit email avec https://app.example.com/ticket/{ticket_id}?token=<magic_link_token> → front calls GET /tickets/{id}?token=... → returns ticket data without auth.

7) Remarques d’implémentation & opérations critiques

HMAC secret : stocker dans env (TICKET_HMAC_SECRET), rotation planifiée (avec migration des tickets signés si rotation).

Atomicité : utiliser transactions DB + Redis locks (SETNX + expire) autour d’opérations current_in et used_count.

Logs : tous les scans doivent écrire sur ticket_scan_logs (immutable).

Rate limits : protéger endpoints scan/request avec rate-limit par IP et par ticket_id.

Monitoring / Alerting : notifier lors de FRAUD_SUSPECT ou tentatives répétées.

Tests : automatiser Gherkin scenarios pour in/out, re-entry, duplicate scan, token expiry, capacity full, payment webhooks.

Storage : QR images stockées sur storage privé (S3) ; qr_path contient URL signée si nécessaire.
