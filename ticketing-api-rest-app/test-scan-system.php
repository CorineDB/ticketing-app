<?php

/**
 * Test complet du système de scan de tickets
 *
 * Système en 2 étapes :
 * 1. POST /api/scan/request (public) - Demande de scan avec QR
 * 2. POST /api/scan/confirm (authentifié) - Confirmation du scan
 */

$baseUrl = $argv[1] ?? 'http://localhost:8000';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         TEST COMPLET DU SYSTÈME DE SCAN                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Fonction pour faire une requête HTTP
function makeRequest($method, $url, $headers = [], $data = null)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if (!empty($headers)) {
        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = "$key: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
    }

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $formattedHeaders[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        curl_close($ch);
        return ['error' => curl_error($ch), 'http_code' => 0];
    }

    curl_close($ch);

    return [
        'data' => json_decode($response, true),
        'raw' => $response,
        'http_code' => $httpCode,
    ];
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 1: RÉCUPÉRATION D'UN TICKET DE TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Utiliser les données du fichier test-scan-requirements-details.txt
// Ticket ID et signature extraits du QR code
$ticketId = '019ac9fc-b13d-72c0-b27e-c1295d21b7a3';
$signature = '16a0f30637122c6f4fb031c93e3da1712aaccfe8929cd2aff39fc38df588ae59';

echo "Ticket de test (depuis test-scan-requirements-details.txt):\n";
echo "  ID: $ticketId\n";
echo "  Signature: " . substr($signature, 0, 40) . "...\n\n";

// Récupérer les détails du ticket pour vérifier qu'il existe
echo "Vérification de l'existence du ticket...\n";

// On peut chercher le ticket via la recherche publique ou utiliser l'ID directement
$testTicket = null;

// Essayer de récupérer via l'endpoint public si on a le magic token
// Sinon on utilisera juste l'ID pour le scan
if (file_exists(__DIR__ . '/test-purchase-result.json')) {
    $testData = json_decode(file_get_contents(__DIR__ . '/test-purchase-result.json'), true);
    foreach ($testData['purchase']['tickets'] ?? [] as $ticket) {
        if ($ticket['id'] === $ticketId) {
            $testTicket = $ticket;
            break;
        }
    }
}

if ($testTicket && isset($testTicket['magic_link_token'])) {
    $ticketResponse = makeRequest('GET', "$baseUrl/api/public/tickets/$ticketId?token={$testTicket['magic_link_token']}");

    if ($ticketResponse['http_code'] === 200) {
        $ticketData = $ticketResponse['data'];
        echo "✅ Ticket trouvé:\n";
        echo "  Code: {$ticketData['code']}\n";
        echo "  Status: {$ticketData['status']}\n";
        echo "  Event: {$ticketData['event']['title']}\n";
        echo "  Event ID: {$ticketData['event_id']}\n\n";
    }
} else {
    echo "⚠️  Ticket sera utilisé directement avec l'ID et la signature\n";
    echo "   (Les détails seront vérifiés lors du scan)\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 2: CONTENU DU QR CODE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Le QR code contient maintenant un lien frontend (après modification)
$frontendUrl = 'http://localhost:5173';
$qrContent = "$frontendUrl/dashboard/scan?t=$ticketId&sig=$signature";

echo "Nouveau format du QR Code (Frontend):\n";
echo "  URL: $qrContent\n\n";

echo "Paramètres extraits du QR:\n";
echo "  - t (ticket_id): $ticketId\n";
echo "  - sig (signature): " . substr($signature, 0, 40) . "...\n\n";

echo "💡 Le frontend (Vue.js) appellera les endpoints de scan:\n";
echo "   1. POST /api/scan/request → Obtenir scan_session_token\n";
echo "   2. POST /api/scan/confirm → Valider l'entrée\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 3: REQUEST SCAN (Public - Étape 1)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "POST /api/scan/request\n";
echo "Paramètres:\n";
echo "  - ticket_id: $ticketId\n";
echo "  - sig: $signature\n\n";

$requestData = [
    'ticket_id' => $ticketId,
    'sig' => $signature,
];

$scanRequestResponse = makeRequest('POST', "$baseUrl/api/scan/request", [
    'Content-Type' => 'application/json',
], $requestData);

if ($scanRequestResponse['http_code'] === 200) {
    $scanRequestData = $scanRequestResponse['data'];
    echo "✅ Requête de scan acceptée\n\n";
    echo "Réponse:\n";
    echo "  - scan_session_token: " . substr($scanRequestData['scan_session_token'], 0, 20) . "...\n";
    echo "  - expires_in: {$scanRequestData['expires_in']} secondes\n\n";

    $scanSessionToken = $scanRequestData['scan_session_token'];

    echo "⏱️  Session de scan créée (expire dans {$scanRequestData['expires_in']}s)\n\n";

} else {
    echo "❌ Erreur lors de la requête de scan\n";
    echo "Code HTTP: {$scanRequestResponse['http_code']}\n";
    echo "Réponse: " . json_encode($scanRequestResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

    if (isset($scanRequestResponse['data']['message'])) {
        echo "Message: {$scanRequestResponse['data']['message']}\n\n";

        if (strpos($scanRequestResponse['data']['message'], 'QR_SIGNATURE_MISMATCH') !== false) {
            echo "💡 La signature du QR code ne correspond pas.\n";
            echo "   Cela peut être dû à une clé APP_KEY différente.\n\n";
        }
    }

    echo "⚠️  Impossible de continuer le test sans scan_session_token\n";
    exit(1);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 4: RÉCUPÉRATION DU NONCE DEPUIS LE CACHE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Le nonce est stocké dans le cache avec la clé scan_session:{token}
// On doit le récupérer via Laravel pour confirmer le scan

echo "Récupération du nonce depuis le cache Laravel...\n";

// Utiliser artisan tinker ou une commande pour récupérer le nonce
$getCacheCommand = "php artisan tinker --execute=\"echo json_encode(Cache::get('scan_session:$scanSessionToken'));\"";
$cacheOutput = shell_exec($getCacheCommand);

if ($cacheOutput) {
    $sessionData = json_decode(trim($cacheOutput), true);
    if ($sessionData && isset($sessionData['nonce'])) {
        $scanNonce = $sessionData['nonce'];
        echo "✅ Nonce récupéré: " . substr($scanNonce, 0, 20) . "...\n\n";
    } else {
        echo "❌ Session expirée ou nonce non trouvé\n";
        echo "La session de 20 secondes a peut-être expiré.\n\n";

        // Afficher quand même comment faire le test manuellement
        echo "⚠️  Pour tester manuellement:\n";
        echo "   1. Exécuter scan request pour obtenir un nouveau token\n";
        echo "   2. Dans les 20 secondes, récupérer le nonce:\n";
        echo "      php artisan tinker --execute=\"echo json_encode(Cache::get('scan_session:TOKEN'));\"\n\n";
        exit(1);
    }
} else {
    echo "⚠️  Impossible de récupérer le nonce automatiquement\n";
    echo "   Continuons avec des données de test connues...\n\n";

    // Utiliser les données du fichier test-scan-requirements-details.txt
    $scanNonce = "nonce_from_cache"; // Placeholder
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 5: CONFIRMATION DU SCAN (Authentifié)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Données d'authentification depuis test-scan-requirements-details.txt
$agentId = '9d518178-44e1-4f6c-92f4-13bf0d899d79';  // Control Agent
$gateId = 'acac322c-97a5-4887-b33a-6296cbd57060';   // Gate ID
$bearerToken = '9|GT5eSHw0dNeybuhfjEtklwmWWAE3TDhqPwP9Kila2e542b32';

echo "Agent: Control Agent\n";
echo "  ID: $agentId\n";
echo "  Email: agent@example.com\n\n";

echo "Gate ID: $gateId\n\n";

if (isset($scanNonce) && $scanNonce !== 'nonce_from_cache') {
    echo "POST /api/scan/confirm\n";
    echo "Authorization: Bearer $bearerToken\n\n";

    $confirmData = [
        'scan_session_token' => $scanSessionToken,
        'scan_nonce' => $scanNonce,
        'gate_id' => $gateId,
        'agent_id' => $agentId,
        'action' => 'in',
    ];

    echo "Paramètres:\n";
    echo "  - scan_session_token: " . substr($scanSessionToken, 0, 30) . "...\n";
    echo "  - scan_nonce: " . substr($scanNonce, 0, 20) . "...\n";
    echo "  - gate_id: $gateId\n";
    echo "  - agent_id: $agentId\n";
    echo "  - action: in\n\n";

    $scanConfirmResponse = makeRequest('POST', "$baseUrl/api/scan/confirm", [
        'Authorization' => "Bearer $bearerToken",
        'Content-Type' => 'application/json',
    ], $confirmData);

    if ($scanConfirmResponse['http_code'] === 200) {
        $confirmResult = $scanConfirmResponse['data'];
        echo "✅ SCAN CONFIRMÉ AVEC SUCCÈS!\n\n";

        echo "Résultat:\n";
        echo "  - Valid: " . ($confirmResult['valid'] ? 'OUI ✅' : 'NON ❌') . "\n";
        echo "  - Code: {$confirmResult['code']}\n";
        echo "  - Message: {$confirmResult['message']}\n\n";

        if (isset($confirmResult['ticket'])) {
            $ticketResult = $confirmResult['ticket'];
            echo "Ticket mis à jour:\n";
            echo "  - ID: {$ticketResult['id']}\n";
            echo "  - Status: {$ticketResult['status']}\n";
            echo "  - Used count: {$ticketResult['used_count']}\n";
            echo "  - Last used: {$ticketResult['last_used_at']}\n";
            echo "  - Gate in: " . ($ticketResult['gate_in'] ?? 'N/A') . "\n\n";
        }

        if (isset($confirmResult['scan_log_id'])) {
            echo "Scan enregistré avec ID: {$confirmResult['scan_log_id']}\n\n";
        }

    } else {
        echo "❌ Erreur lors de la confirmation du scan\n";
        echo "Code HTTP: {$scanConfirmResponse['http_code']}\n";
        echo "Réponse: " . json_encode($scanConfirmResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

        if (isset($scanConfirmResponse['data']['message'])) {
            $errorMessage = $scanConfirmResponse['data']['message'];
            echo "Message: $errorMessage\n\n";

            if (strpos($errorMessage, 'Session expired') !== false) {
                echo "💡 La session de 20 secondes a expiré.\n";
                echo "   Relancez le test et confirmez dans les 20 secondes.\n\n";
            } elseif (strpos($errorMessage, 'Invalid nonce') !== false) {
                echo "💡 Le nonce ne correspond pas.\n";
                echo "   Vérifiez que vous utilisez le bon nonce du cache.\n\n";
            }
        }
    }
} else {
    echo "⚠️  CONFIRMATION NON TESTÉE (nonce non récupéré)\n\n";
    echo "Pour tester manuellement:\n";
    echo "1. Exécuter: curl -X POST $baseUrl/api/scan/request \\\n";
    echo "     -H 'Content-Type: application/json' \\\n";
    echo "     -d '{\"ticket_id\":\"$ticketId\",\"sig\":\"$signature\"}'\n\n";
    echo "2. Récupérer le scan_session_token de la réponse\n\n";
    echo "3. Dans les 20 secondes, récupérer le nonce:\n";
    echo "   php artisan tinker --execute=\"echo json_encode(Cache::get('scan_session:TOKEN'));\"\n\n";
    echo "4. Confirmer le scan:\n";
    echo "   curl -X POST $baseUrl/api/scan/confirm \\\n";
    echo "     -H 'Authorization: Bearer $bearerToken' \\\n";
    echo "     -H 'Content-Type: application/json' \\\n";
    echo "     -d '{\n";
    echo "       \"scan_session_token\":\"TOKEN\",\n";
    echo "       \"scan_nonce\":\"NONCE\",\n";
    echo "       \"gate_id\":\"$gateId\",\n";
    echo "       \"agent_id\":\"$agentId\",\n";
    echo "       \"action\":\"in\"\n";
    echo "     }'\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "FLUX COMPLET DU SYSTÈME DE SCAN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ 📱 ÉTAPE 1: UTILISATEUR PRÉSENTE SON QR CODE                │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";
echo "│                                                             │\n";
echo "│ • QR code contient: ticket_id + signature HMAC             │\n";
echo "│ • Signature = HMAC(ticket_id|event_id, APP_KEY)            │\n";
echo "│                                                             │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ 🔍 ÉTAPE 2: SCANNER DEMANDE LA VALIDATION (Public)          │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";
echo "│                                                             │\n";
echo "│ POST /api/scan/request                                     │\n";
echo "│ {                                                           │\n";
echo "│   \"ticket_id\": \"xxx\",                                     │\n";
echo "│   \"sig\": \"yyy\"                                            │\n";
echo "│ }                                                           │\n";
echo "│                                                             │\n";
echo "│ Réponse (si valide):                                        │\n";
echo "│ {                                                           │\n";
echo "│   \"scan_session_token\": \"token_temporaire\",              │\n";
echo "│   \"expires_in\": 20                                         │\n";
echo "│ }                                                           │\n";
echo "│                                                             │\n";
echo "│ ⚡ Crée une session de 20 secondes dans le cache           │\n";
echo "│                                                             │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ ✅ ÉTAPE 3: AGENT CONFIRME LE SCAN (Authentifié)            │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";
echo "│                                                             │\n";
echo "│ POST /api/scan/confirm                                     │\n";
echo "│ Header: Authorization: Bearer <token>                      │\n";
echo "│ {                                                           │\n";
echo "│   \"scan_session_token\": \"token_temporaire\",              │\n";
echo "│   \"scan_nonce\": \"nonce_from_cache\",                      │\n";
echo "│   \"gate_id\": \"porte_id\",                                 │\n";
echo "│   \"agent_id\": \"agent_id\",                                │\n";
echo "│   \"action\": \"in\" ou \"out\"                               │\n";
echo "│ }                                                           │\n";
echo "│                                                             │\n";
echo "│ ⚡ Vérifie et traite le scan                               │\n";
echo "│                                                             │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ 🔐 RÈGLES DE VALIDATION                                     │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";
echo "│                                                             │\n";
echo "│ ✓ Événement en cours (entre start et end datetime)         │\n";
echo "│ ✓ Porte (gate) active                                      │\n";
echo "│ ✓ Ticket status = paid/in/out                              │\n";
echo "│ ✓ Ticket dans la période de validité                       │\n";
echo "│ ✓ Capacité de l'événement non atteinte                     │\n";
echo "│ ✓ Limite d'utilisation non dépassée                        │\n";
echo "│ ✓ Cooldown de 60s après sortie (anti-fraude)               │\n";
echo "│ ✓ Re-entry autorisée si configuré                          │\n";
echo "│                                                             │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ 📊 ACTIONS EFFECTUÉES                                       │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";
echo "│                                                             │\n";
echo "│ • Met à jour le statut du ticket (paid→in→out)             │\n";
echo "│ • Incrémente/décrémente le compteur d'entrées              │\n";
echo "│ • Enregistre le scan dans les logs                         │\n";
echo "│ • Envoie une notification                                  │\n";
echo "│ • Vérifie les locks distribués (anti-concurrence)          │\n";
echo "│                                                             │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "SÉCURITÉ DU SYSTÈME\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Signature HMAC sur le QR code (anti-contrefaçon)\n";
echo "✅ Session éphémère de 20 secondes\n";
echo "✅ Nonce unique (anti-replay)\n";
echo "✅ Lock distribué (anti-concurrence)\n";
echo "✅ Rate limiting:\n";
echo "   - 60 requêtes/min pour /scan/request\n";
echo "   - 30 requêtes/min pour /scan/confirm\n";
echo "✅ Authentification requise pour la confirmation\n";
echo "✅ Logging complet de tous les scans\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "RÉSULTAT DU TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ ÉTAPE 1: Scan request (public) → TESTÉ ET FONCTIONNEL\n";
echo "⚠️  ÉTAPE 2: Scan confirm (authentifié) → NON TESTÉ\n";
echo "   (Nécessite un compte authentifié avec les droits)\n\n";

echo "💡 POUR TESTER COMPLÈTEMENT:\n";
echo "   1. Créer un utilisateur avec rôle 'scanner' ou 'agent'\n";
echo "   2. S'authentifier pour obtenir un Bearer token\n";
echo "   3. Créer une porte (gate) active pour l'événement\n";
echo "   4. Utiliser le scan_session_token reçu à l'étape 1\n";
echo "   5. Récupérer le nonce depuis le cache (ou logs)\n";
echo "   6. Appeler POST /api/scan/confirm\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ TEST PARTIEL TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
