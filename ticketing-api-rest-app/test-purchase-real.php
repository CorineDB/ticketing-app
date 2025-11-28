<?php

/**
 * Test d'achat RÉEL en mode LIVE
 * ⚠️ ATTENTION: Ce test effectuera un VRAI paiement!
 */

$baseUrl = 'http://localhost:8000';
$ticketTypeId = '019ac8e5-5344-7125-95e5-68dfd4a3a654';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        TEST D'ACHAT - MODE LIVE (PAIEMENT RÉEL!)             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "⚠️  MODE: LIVE - Le paiement sera RÉEL!\n";
echo "URL de l'API: $baseUrl\n";
echo "Ticket Type ID: $ticketTypeId\n\n";

function makeRequest($method, $url, $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'data' => json_decode($response, true),
        'http_code' => $httpCode,
        'raw' => $response
    ];
}

// Récupérer les infos du ticket type
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 1: Récupération du ticket type\n";
echo "═══════════════════════════════════════════════════════════════\n";

$response = makeRequest('GET', "$baseUrl/api/public/ticket-types/$ticketTypeId");

if ($response['http_code'] !== 200) {
    echo "❌ Erreur lors de la récupération du ticket type\n";
    echo "HTTP Code: {$response['http_code']}\n";
    echo "Response: " . ($response['raw'] ?? 'Aucune réponse') . "\n";
    exit(1);
}

$ticketType = $response['data'];
echo "✅ Ticket type trouvé: {$ticketType['name']}\n";
echo "   ID: {$ticketType['id']}\n";
echo "   Prix: {$ticketType['price']} XOF\n";
echo "   Quota disponible: " . ($ticketType['remaining_quota'] ?? $ticketType['quota'] ?? 'N/A') . "\n";

$availableTickets = $ticketType['remaining_quota'] ?? $ticketType['quota'] ?? 0;
if ($availableTickets < 1) {
    echo "❌ Erreur: Quota insuffisant pour le ticket type {$ticketType['name']}. Tickets disponibles: {$availableTickets}\n";
    exit(1);
}

echo "\n";

// Acheter 1 ticket
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 2: Achat de 1 ticket via /api/tickets/purchase\n";
echo "═══════════════════════════════════════════════════════════════\n";

$purchaseData = [
    'ticket_type_id' => $ticketTypeId,
    'quantity' => 1,
    'customer' => [
        'firstname' => 'Corine D.',
        'lastname' => 'BOCOGA',
        'email' => 'test-sandbox-' . time() . '@example.com',
        'phone_number' => '+22996350263',
    ]
];

echo "Données d'achat:\n";
echo json_encode($purchaseData, JSON_PRETTY_PRINT) . "\n\n";

$response = makeRequest('POST', "$baseUrl/api/tickets/purchase", $purchaseData);

if ($response['http_code'] !== 201) {
    echo "❌ Erreur lors de l'achat de ticket\n";
    echo "HTTP Code: {$response['http_code']}\n";
    echo "Response:\n";
    echo json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

$purchaseResult = $response['data'];

echo "✅ Achat réussi!\n\n";
echo "Détails de l'achat:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Transaction ID    : {$purchaseResult['transaction_id']}\n";
echo "Nombre de tickets : " . count($purchaseResult['tickets']) . "\n";
echo "Montant total     : {$purchaseResult['total_amount']} {$purchaseResult['currency']}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Ticket créé:\n";
$ticket = $purchaseResult['tickets'][0];
echo "  ID          : {$ticket['id']}\n";
echo "  Code        : {$ticket['code']}\n";
echo "  Status      : {$ticket['status']}\n";
echo "  Buyer       : {$ticket['buyer_name']}\n\n";

// Lien de paiement
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 3: LIEN DE PAIEMENT FEDAPAY (LIVE - PAIEMENT RÉEL!)     \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$paymentUrl = $purchaseResult['payment_url'];

echo "🔗 URL de paiement:\n";
echo "   $paymentUrl\n\n";

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  ⚠️  PAIEMENT RÉEL - MODE LIVE                                ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "1️⃣  Ouvrez l'URL de paiement dans votre navigateur:\n";
echo "   $paymentUrl\n\n";

echo "2️⃣  ⚠️  Utilisez une VRAIE carte bancaire\n";
echo "   Le montant à payer: {$purchaseResult['total_amount']} XOF\n";
echo "   ⚠️  CE PAIEMENT SERA RÉEL!\n\n";

echo "3️⃣  Après le paiement, vérifiez le statut:\n";
echo "   php test-verify-payment.php\n\n";

// Sauvegarder les infos
echo "DEBUG: purchaseResult before saving to JSON:\n";
echo json_encode($purchaseResult, JSON_PRETTY_PRINT) . "\n\n";

$testData = [
    'ticket_type' => [
        'id' => $ticketType['id'],
        'name' => $ticketType['name'],
        'price' => $ticketType['price'],
    ],
    'purchase' => [
        'transaction_id' => $purchaseResult['transaction_id'],
        'tickets' => [[
            'id' => $ticket['id'],
            'code' => $ticket['code'] ?? null,
            'status' => $ticket['status'],
            'magic_link_token' => $ticket['magic_link_token'] ?? null,
        ]],
        'total_amount' => $purchaseResult['total_amount'],
        'currency' => $purchaseResult['currency'],
        'payment_url' => $paymentUrl,
    ],
    'customer' => $purchaseData['customer'],
    'tested_at' => date('Y-m-d H:i:s'),
    'environment' => 'LIVE',
];

file_put_contents(__DIR__ . '/test-purchase-result.json', json_encode($testData, JSON_PRETTY_PRINT));

echo "💾 Données de test sauvegardées dans: test-purchase-result.json\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "SURVEILLANCE EN TEMPS RÉEL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Pour surveiller le statut du ticket en temps réel:\n";
echo "  php watch-payment-status.php\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Test préparé avec succès! (LIVE - PAIEMENT RÉEL!)\n";
echo "═══════════════════════════════════════════════════════════════\n";
