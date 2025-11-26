1) Fonctionnalités attendues (exhaustif — priorisé)
A. Front / Utilisateur public

Page publique événements : liste filtrable (date, lieu, status). (PUBLIC)

Page détail d’événement : types de tickets, capacité, dates, lieu. (PUBLIC)

Bouton redirection vers payment_url (externe). (PUBLIC)

Téléchargement du ticket PDF/QR via magic_link (token) — sans compte. (PUBLIC)

B. Opérations organisateur / admin

CRUD Événements (title, description, start_datetime, end_datetime, location, capacity, timezone, dress_code, allow_reentry [bool]). (AUTH)

CRUD TicketTypes (name unique per event, price, quota, usage_limit, validity_from/to). (AUTH)

Génération en masse de Tickets (codes, QR, magic links, HMAC). (AUTH)

Export participants CSV / logs. (AUTH)

Dashboard ventes / occupancy / scans (Auth). (AUTH: organizer/superadmin)

C. Contrôle d’accès (Opérateur / Agent)

Front scan QR (Vue minimal) pour scan/request (création token) et scan/confirm (in/out). (AUTH)

Logs scannés affichés par Gate (agent voit uniquement sa gate). (AUTH)

Visualisation instantanée résultat scan. (AUTH)

D. Participant / Acheteur

Achat via lien de paiement externe. (PUBLIC)

Magic link / téléchargement ticket (PDF/PNG QR). (PUBLIC with token)

Voir statut du ticket (issued/reserved/paid/inside/outside/invalid/refunded). (AUTH optional)

E. Paiement & Caissier

Support paiement externe (webhooks). (PUBLIC webhook + AUTH validation)

Caissier peut créer ticket manuel et marquer payé. (AUTH: caissier/organizer)

F. Notifications & Queue

Jobs asynchrones pour emails/SMS (queue). (AUTH backend)

Notifications : ticket created, payment success, refunded, suspicious activity.

G. Sécurité & Audits

OAuth2 (token endpoint /oauth/token) pour API. (AUTH)

HMAC signature QR pour intégrité (qr_hmac). (No sensitive in QR)

Scan workflow 2-steps (request → confirm) avec scan_session_token TTL. (AUTH)

Redis locks / DB transactions pour event_counters atomicity.

H. Administration & CI/CD

Seeders (roles, superadmin).

Swagger/OpenAPI auto généré.

Tests unitaires & intégration pour workflows scan, payments, reentry, quotas.

Scripts simple de charge pour validation.
