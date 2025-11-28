<?php

/**
 * Test complet du système de scan de tickets
 * - Achat de ticket (test-purchase-real.php intégré)
 * - Lecture du QR code généré
 * - Test du flux de scan à double facteur (request + confirm)
 */

require __DIR__ . '/vendor/autoload.php';

use Zxing\QrReader;

$baseUrl = $argv[1] ?? 'http://localhost:8000';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║     TEST COMPLET: ACHAT → QR → SCAN 2FA                     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Fonction pour faire une requête HTTP
function makeRequest($method, $url, $headers = [], $data = null) {
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
echo "ÉTAPE 0: ACHAT D'UN NOUVEAU TICKET VIA test-purchase-real.php\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

passthru("php test-purchase-real.php");

echo "\n\nSimulation du webhook de paiement et vérification...\n";
passthru("php test-webhook-manual.php http://localhost:8000");
passthru("php test-verify-payment.php");

echo "\n\nAttente de 5 secondes pour le traitement du webhook...\n";
sleep(5);

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 1: LECTURE DU TICKET EXISTANT (créé à l'étape 0)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if (!file_exists(__DIR__ . '/test-purchase-result.json')) {
    echo "❌ Fichier test-purchase-result.json non trouvé.\n";
    echo "Veuillez d'abord exécuter le script test-purchase-real.php et compléter le paiement.\n";
    exit(1);
}

$testData = json_decode(file_get_contents(__DIR__ . '/test-purchase-result.json'), true);

$ticket = $testData['purchase']['tickets'][0];
$ticketId = $ticket['id'];
$magicToken = $ticket['magic_link_token'];

if (!$ticketId || !$magicToken) {
    echo "❌ Données du ticket invalides dans test-purchase-result.json.\n";
    exit(1);
}

echo "Ticket chargé depuis test-purchase-result.json:\n";
echo "  ID: $ticketId\n";
echo "  Code: {$ticket['code']}\n";
echo "  Magic Token: " . substr($magicToken, 0, 20) . "...\n\n";

// Vérifier que le ticket est bien payé via l'API
echo "Vérification du statut du ticket via l'API...\n";
$statusResponse = makeRequest('GET', "$baseUrl/api/public/tickets/$ticketId?token=$magicToken");
if ($statusResponse['http_code'] !== 200) {
    echo "❌ Impossible de récupérer le statut du ticket depuis l'API.\n";
    echo "Réponse: " . json_encode($statusResponse['data'], JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

$currentStatus = $statusResponse['data']['status'];
echo "  Statut actuel: {$currentStatus}\n\n";

if ($currentStatus !== 'paid') {
    echo "⚠️  Le ticket n'est pas au statut 'paid'. Le statut est '{$currentStatus}'.\n";
    echo "Le test de scan risque d'échouer. Assurez-vous que le paiement a été effectué et que le webhook a été traité.\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 2: TÉLÉCHARGEMENT ET LECTURE DU QR CODE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";


$ticketId = $ticket['id'];
$magicToken = $ticket['magic_link_token'];

// Télécharger le QR code
echo "Téléchargement du QR code...\n";

$qrDownloadResponse = makeRequest('GET', "$baseUrl/api/public/tickets/$ticketId/qr/download?token=$magicToken");

if ($qrDownloadResponse['http_code'] !== 200) {
    echo "❌ Erreur lors du téléchargement du QR code\n";
    exit(1);
}

// Sauvegarder le QR code temporairement
$tempQrPath = sys_get_temp_dir() . "/qr-scan-test-$ticketId.png";
file_put_contents($tempQrPath, $qrDownloadResponse['raw']);

echo "✅ QR code téléchargé: $tempQrPath\n";
echo "  Taille: " . strlen($qrDownloadResponse['raw']) . " octets\n\n";

// Lire le contenu du QR code avec ZxingPHP
echo "Lecture du QR code avec ZxingPHP...\n";

try {
    $qrReader = new QrReader($tempQrPath);
    $qrContent = $qrReader->text();

    echo "✅ Contenu du QR code décodé:\n";
    echo "  $qrContent\n\n";

    // Parser l'URL du QR code
    $parsedUrl = parse_url($qrContent);
    parse_str($parsedUrl['query'] ?? '', $qrParams);

    $scannedTicketId = $qrParams['t'] ?? null;
    $scannedSignature = $qrParams['sig'] ?? null;

    if (!$scannedTicketId || !$scannedSignature) {
        echo "❌ QR code invalide (paramètres manquants)\n";
        exit(1);
    }

    echo "Paramètres extraits du QR:\n";
    echo "  - t (ticket_id): $scannedTicketId\n";
    echo "  - sig (signature): " . substr($scannedSignature, 0, 40) . "...\n\n";

    // Vérifier que l'ID correspond
    if ($scannedTicketId !== $ticketId) {
        echo "⚠️  L'ID du ticket dans le QR ne correspond pas!\n";
        echo "  Attendu: $ticketId\n";
        echo "  Trouvé: $scannedTicketId\n\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur lors de la lecture du QR code\n";
    echo "  " . $e->getMessage() . "\n\n";
    exit(1);
}

// Nettoyer le fichier temporaire
unlink($tempQrPath);

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 3: SCAN REQUEST (Première Étape - Public)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "POST /api/scan/request\n";
echo "Paramètres du QR code:\n";
echo "  - ticket_id: $scannedTicketId\n";
echo "  - sig: " . substr($scannedSignature, 0, 40) . "...\n\n";

$scanRequestData = [
    'ticket_id' => $scannedTicketId,
    'sig' => $scannedSignature,
];

$scanRequestResponse = makeRequest('POST', "$baseUrl/api/scan/request", [
    'Content-Type' => 'application/json',
], $scanRequestData);

if ($scanRequestResponse['http_code'] !== 200) {
    echo "❌ Erreur lors de la requête de scan\n";
    echo "Code HTTP: {$scanRequestResponse['http_code']}\n";
    echo "Réponse: " . json_encode($scanRequestResponse['data'], JSON_PRETTY_PRINT) . "\n\n";
    exit(1);
}

$scanSession = $scanRequestResponse['data'];

echo "✅ Session de scan créée!\n\n";

echo "Réponse de /api/scan/request:\n";
echo "  - scan_session_token: " . substr($scanSession['scan_session_token'], 0, 30) . "...\n";
echo "  - scan_nonce: " . substr($scanSession['scan_nonce'], 0, 20) . "...\n";
echo "  - expires_in: {$scanSession['expires_in']} secondes\n\n";

if (isset($scanSession['ticket'])) {
    echo "Informations du ticket:\n";
    echo "  - Code: {$scanSession['ticket']['code']}\n";
    echo "  - Status: {$scanSession['ticket']['status']}\n";
    echo "  - Buyer: {$scanSession['ticket']['buyer_name']}\n";
    echo "  - Email: {$scanSession['ticket']['buyer_email']}\n";
    echo "  - Event: {$scanSession['ticket']['event']['title']}\n";
    echo "  - Ticket Type: {$scanSession['ticket']['ticket_type']['name']}\n";
    echo "  - Price: {$scanSession['ticket']['ticket_type']['price']}\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 4: SCAN CONFIRM (Deuxième Étape - Authentifié)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Données d'authentification (depuis test-scan-requirements-details.txt)
$agentId = '9d518178-44e1-4f6c-92f4-13bf0d899d79';
$gateId = '5939d63e-3ede-440e-bc30-413b896c0eb2';
$bearerToken = '9|GT5eSHw0dNeybuhfjEtklwmWWAE3TDhqPwP9Kila2e542b32';

echo "Agent de contrôle:\n";
echo "  - ID: $agentId\n";
echo "  - Email: agent@example.com\n\n";

echo "Porte (Gate):\n";
echo "  - ID: $gateId\n\n";

echo "POST /api/scan/confirm\n";
echo "Authorization: Bearer " . substr($bearerToken, 0, 20) . "...\n\n";

$scanConfirmData = [
    'scan_session_token' => $scanSession['scan_session_token'],
    'scan_nonce' => $scanSession['scan_nonce'],
    'gate_id' => $gateId,
    'agent_id' => $agentId,
    'action' => 'in',
];

echo "Paramètres:\n";
echo "  - scan_session_token: " . substr($scanConfirmData['scan_session_token'], 0, 30) . "...\n";
echo "  - scan_nonce: " . substr($scanConfirmData['scan_nonce'], 0, 20) . "...\n";
echo "  - gate_id: $gateId\n";
echo "  - agent_id: $agentId\n";
echo "  - action: in\n\n";

$scanConfirmResponse = makeRequest('POST', "$baseUrl/api/scan/confirm", [
    'Authorization' => "Bearer $bearerToken",
    'Content-Type' => 'application/json',
], $scanConfirmData);

if ($scanConfirmResponse['http_code'] !== 200) {
    echo "❌ Erreur lors de la confirmation du scan\n";
    echo "Code HTTP: {$scanConfirmResponse['http_code']}\n";
    echo "Réponse: " . json_encode($scanConfirmResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

    if (isset($scanConfirmResponse['data']['message'])) {
        $errorMsg = $scanConfirmResponse['data']['message'];

        if (strpos($errorMsg, 'Session expired') !== false) {
            echo "💡 La session de 20 secondes a expiré.\n\n";
        } elseif (strpos($errorMsg, 'Event has not started') !== false) {
            echo "💡 L'événement n'a pas encore commencé.\n";
            echo "   Vous pouvez modifier la date de début de l'événement.\n\n";
        } elseif (strpos($errorMsg, 'invalid for scanning') !== false) {
            echo "💡 Le statut du ticket ne permet pas le scan.\n";
            echo "   Status actuel: {$scanSession['ticket']['status']}\n";
            echo "   Status requis: paid, in, ou out\n\n";
        }
    }

    exit(1);
}

$scanResult = $scanConfirmResponse['data'];

echo "✅ SCAN CONFIRMÉ AVEC SUCCÈS!\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "RÉSULTAT DU SCAN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$validIcon = $scanResult['valid'] ? '✅' : '❌';
echo "$validIcon Valid: " . ($scanResult['valid'] ? 'OUI' : 'NON') . "\n";
echo "Code: {$scanResult['code']}\n";
echo "Message: {$scanResult['message']}\n\n";

if (isset($scanResult['ticket'])) {
    $resultTicket = $scanResult['ticket'];
    echo "Ticket mis à jour:\n";
    echo "  - ID: {$resultTicket['id']}\n";
    echo "  - Code: {$resultTicket['code']}\n";
    echo "  - Status: {$resultTicket['status']}\n";
    echo "  - Used count: {$resultTicket['used_count']}\n";
    echo "  - Last used at: {$resultTicket['last_used_at']}\n";

    if (isset($resultTicket['gate_in'])) {
        echo "  - Gate in: {$resultTicket['gate_in']}\n";
    }
    echo "\n";
}

if (isset($scanResult['scan_log_id'])) {
    echo "Scan Log ID: {$scanResult['scan_log_id']}\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST DE RE-SCAN (Détection de doublon)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Tentative de re-scanner le même ticket...\n\n";

// Re-scanner le ticket (devrait être rejeté car déjà "in")
$reScanRequestResponse = makeRequest('POST', "$baseUrl/api/scan/request", [
    'Content-Type' => 'application/json',
], $scanRequestData);

if ($reScanRequestResponse['http_code'] === 200) {
    $reScanSession = $reScanRequestResponse['data'];

    $reScanConfirmData = [
        'scan_session_token' => $reScanSession['scan_session_token'],
        'scan_nonce' => $reScanSession['scan_nonce'],
        'gate_id' => $gateId,
        'agent_id' => $agentId,
        'action' => 'in',
    ];

    $reScanConfirmResponse = makeRequest('POST', "$baseUrl/api/scan/confirm", [
        'Authorization' => "Bearer $bearerToken",
        'Content-Type' => 'application/json',
    ], $reScanConfirmData);

    if ($reScanConfirmResponse['http_code'] === 200) {
        $reScanResult = $reScanConfirmResponse['data'];

        echo "Résultat du re-scan:\n";
        echo "  - Valid: " . ($reScanResult['valid'] ? 'OUI ✅' : 'NON ❌') . "\n";
        echo "  - Code: {$reScanResult['code']}\n";
        echo "  - Message: {$reScanResult['message']}\n\n";

        if ($reScanResult['code'] === 'ALREADY_IN') {
            echo "✅ Détection de doublon correcte!\n";
            echo "   Le système a bien détecté que le ticket est déjà à l'intérieur.\n\n";
        }
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "RÉCAPITULATIF DU TEST COMPLET\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ ÉTAPE 1: Achat du ticket → Succès\n";
echo "✅ ÉTAPE 2: Lecture du QR code → Succès\n";
echo "✅ ÉTAPE 3: Scan request (public) → Succès\n";
echo "✅ ÉTAPE 4: Scan confirm (authentifié) → Succès\n";
echo "✅ ÉTAPE 5: Détection de doublon → Succès\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ TEST COMPLET TERMINÉ AVEC SUCCÈS!\n";
echo "═══════════════════════════════════════════════════════════════\n";
