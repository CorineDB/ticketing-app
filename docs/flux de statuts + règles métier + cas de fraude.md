Voici le schéma complet des flux de statuts + règles métier + cas de fraude, y compris la protection anti-fraude liée aux scans out pendant l’événement.

Je te le donne en format texte structuré + tableaux complets, facile à mettre dans ton TDR ou Notion.

✅ 1. Schéma général des transitions du ticket
issued 
   ↓ (paiement initié)
reserved 
   ↓ (paiement validé)
paid 
   ↓ (scan entrée valide)
in 
   ↓ (scan sortie valide)
out


Transitions annexes :

any → invalid
any → refunded

✅ 2. Description des transitions + règles métier
Transition	Conditions	Règles métier	Logs	Erreurs
issued → reserved	paiement lancé	Génère ref paiement, verrouille ticket	log: reserved	paiement échoué = reste issued
reserved → paid	callback paiement OK	paid_at = now	log: paid	paiement frauduleux → invalid automatique
paid → in	scan gate entrée actif	incrément event.current_in; vérifier validité	log: entry	already_in, expired, invalid
in → out	scan gate sortie actif	décrément event.current_in	log: exit	already_out, invalid
any → invalid	annulé, expiré, fraude détectée	interdit scan	log: invalid	—
any → refunded	remboursement	désactivation QR/magic link	log: refund	—
✅ 3. Règles métier globales pour garantie anti-fraude
🔒 Règle #1 – Contrôle strict de l’état

Un ticket ne peut être réutilisé en entrée tant qu'il est dans l’état in.

🔒 Règle #2 – Chaque scan modifie l’état de façon atomique

Utilisation row-level lock SQL + Redis fallback :

SELECT ... FOR UPDATE

🔒 Règle #3 – Audit obligatoire pour chaque scan

Chaque scan crée obligatoirement un log (ticket_scan_logs), même en cas d’échec.

✅ 4. Cas particuliers + anti-fraude
⚠️ CAS 1 : Ticket scanné OUT pendant l’événement → tentative de fraude pour faire entrer quelqu’un d’autre

📌 Problème
Une personne sort, puis essaie de faire repasser son ticket (ou partage le QR à quelqu’un).

📌 Risque
Capacité non respectée + entrées frauduleuses.

🎯 Objectif


✅ 5. Scénarios complets incluant cas de fraude
🔰 Scénario A — Entrée valide

Ticket payé

Gate entrée actif

Scan → Status = in

Counter +1

Log event: result=ok

✔️ Aucun risque de fraude.
🔰 Scénario B — Sortie valide

Ticket était in

Gate sortie actif

Scan → Status = out

Counter –1
🔰 Scénario D — Tentative d’IN avec ticket déjà IN

Risque : copie du QR.

Condition	Règle	Résultat
status = in	Interdit	result=already_in
log fraud_attempt	Oui	-
possibilité invalidate ticket	optionnel	-
🔰 Scénario F — Ticket expiré / annulé
Condition	Règle	Résultat
validity_to < now	status automatically invalid	refused
event annulé	all tickets → invalid	refused

✅ 6. Résumé des protections anti-fraude

✔ Cooldown après sortie (out_locked)
✔ Double-scan detection (IN déjà utilisé)
✔ Détection scans IN simultanés
✔ Restrictions selon type de gate
✔ Option no-reentry pour événements fermés
✔ Audit complet et inviolable
✔ Détection multi-agent suspecte
✔ Possibilité d’invalidation automatique


📌 Résumé des transitions autorisées
États possibles :

unused

inside

outside

invalidated

banned (optionnel)

Transitions :
action	from	to	valid	notes
scan_in	unused	inside	✔️	première entrée
scan_in	outside	inside	✔️	ré-entrée
scan_in	inside	—	❌	tentative de double entrée
scan_out	inside	outside	✔️	sortie normale
scan_out	unused	—	❌	impossible
scan_out	outside	—	❌	impossible


