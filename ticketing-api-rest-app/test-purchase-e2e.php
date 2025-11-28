<?php

/**
 * Test End-to-End de l'Achat de Ticket
 *
 * Ce script teste le flux complet:
 * 1. Récupère un event et un ticket type
 * 2. Appelle l'endpoint /api/tickets/purchase
 * 3. Affiche le lien de paiement pour test manuel
 */

$baseUrl = $argv[1] ?? 'http://localhost:8000';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        TEST END-TO-END: ACHAT DE TICKET + PAIEMENT           ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "URL de l'API: $baseUrl\n\n";

// Fonction pour faire une requête HTTP
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

    if (curl_errno($ch)) {
        curl_close($ch);
        return ['error' => curl_error($ch), 'http_code' => 0];
    }

    curl_close($ch);

    return [
        'data' => json_decode($response, true),
        'http_code' => $httpCode,
        'raw' => $response
    ];
}

// Étape 1: Récupérer les events disponibles
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 1: Récupération des events disponibles\n";
echo "═══════════════════════════════════════════════════════════════\n";

$response = makeRequest('GET', "$baseUrl/api/public/events");

if ($response['http_code'] !== 200) {
    echo "❌ Erreur lors de la récupération des events\n";
    echo "HTTP Code: {$response['http_code']}\n";
    echo "Response: " . ($response['raw'] ?? 'Aucune réponse') . "\n";
    exit(1);
}

$events = $response['data']['data'] ?? [];

if (empty($events)) {
    echo "❌ Aucun event trouvé dans la base de données\n";
    echo "Veuillez créer un event d'abord via l'interface admin\n";
    exit(1);
}

$event = $events[0];
echo "✅ Event trouvé: {$event['title']}\n";
echo "   ID: {$event['id']}\n";
echo "   Date: {$event['date']}\n\n";

// Étape 2: Récupérer les ticket types de cet event
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 2: Récupération des types de tickets\n";
echo "═══════════════════════════════════════════════════════════════\n";

$response = makeRequest('GET', "$baseUrl/api/public/events/{$event['id']}/ticket-types");

if ($response['http_code'] !== 200) {
    echo "❌ Erreur lors de la récupération des ticket types\n";
    echo "HTTP Code: {$response['http_code']}\n";
    exit(1);
}

$ticketTypes = $response['data']['data'] ?? [];

if (empty($ticketTypes)) {
    echo "❌ Aucun ticket type trouvé pour cet event\n";
    echo "Veuillez créer un ticket type d'abord\n";
    exit(1);
}

$ticketType = $ticketTypes[0];
echo "✅ Ticket type trouvé: {$ticketType['name']}\n";
echo "   ID: {$ticketType['id']}\n";
echo "   Prix: {$ticketType['price']} XOF\n";
echo "   Quota disponible: " . ($ticketType['remaining_quota'] ?? 'N/A') . "\n\n";

// Étape 3: Acheter un ticket
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 3: Achat de ticket via /api/tickets/purchase\n";
echo "═══════════════════════════════════════════════════════════════\n";

$purchaseData = [
    'ticket_type_id' => $ticketType['id'],
    'quantity' => 2, // Acheter 2 tickets
    'customer' => [
        'firstname' => 'Test',
        'lastname' => 'E2E',
        'email' => 'test-e2e-' . time() . '@example.com',
        'phone_number' => '+22997123456', // Numéro Bénin valide
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

echo "Tickets créés:\n";
foreach ($purchaseResult['tickets'] as $index => $ticket) {
    $num = $index + 1;
    echo "  Ticket $num:\n";
    echo "    ID          : {$ticket['id']}\n";
    echo "    Code        : {$ticket['ticket_code']}\n";
    echo "    Status      : {$ticket['status']}\n";
    echo "    Buyer       : {$ticket['buyer_name']}\n";
    echo "\n";
}

// Étape 4: Afficher le lien de paiement
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 4: LIEN DE PAIEMENT FEDAPAY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$paymentUrl = $purchaseResult['payment_url'];

echo "🔗 URL de paiement:\n";
echo "   $paymentUrl\n\n";

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  ACTION REQUISE: TESTEZ LE PAIEMENT MANUELLEMENT             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "1️⃣  Ouvrez l'URL de paiement dans votre navigateur:\n";
echo "   $paymentUrl\n\n";

echo "2️⃣  Complétez le paiement sur la page FedaPay\n";
echo "   (utilisez une carte de test en mode sandbox)\n\n";

echo "3️⃣  Après le paiement, vous serez redirigé vers:\n";
echo "   {$baseUrl}/api/payment/callback\n\n";

echo "4️⃣  Le webhook FedaPay mettra à jour automatiquement le statut\n";
echo "   du ticket de 'issued' à 'paid'\n\n";

// Sauvegarder les infos pour vérification ultérieure
$testData = [
    'event' => [
        'id' => $event['id'],
        'title' => $event['title'],
    ],
    'ticket_type' => [
        'id' => $ticketType['id'],
        'name' => $ticketType['name'],
        'price' => $ticketType['price'],
    ],
    'purchase' => [
        'transaction_id' => $purchaseResult['transaction_id'],
        'tickets' => array_map(function($t) {
            return [
                'id' => $t['id'],
                'code' => $t['ticket_code'],
                'status' => $t['status'],
            ];
        }, $purchaseResult['tickets']),
        'total_amount' => $purchaseResult['total_amount'],
        'currency' => $purchaseResult['currency'],
        'payment_url' => $paymentUrl,
    ],
    'customer' => $purchaseData['customer'],
    'tested_at' => date('Y-m-d H:i:s'),
];

file_put_contents(__DIR__ . '/test-purchase-result.json', json_encode($testData, JSON_PRETTY_PRINT));

echo "💾 Données de test sauvegardées dans: test-purchase-result.json\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "PROCHAINES ÉTAPES POUR VÉRIFICATION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Après avoir effectué le paiement, vérifiez:\n\n";

echo "1. Vérifier le statut des tickets:\n";
foreach ($purchaseResult['tickets'] as $ticket) {
    echo "   GET $baseUrl/api/public/tickets/{$ticket['id']}\n";
}
echo "\n";

echo "2. Vérifier les logs Laravel:\n";
echo "   tail -f storage/logs/laravel.log\n\n";

echo "3. Vérifier le dashboard FedaPay:\n";
echo "   https://sandbox.fedapay.com/transactions/{$purchaseResult['transaction_id']}\n\n";

echo "4. Tester le webhook manuellement:\n";
echo "   php test-webhook-e2e.php\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Test end-to-end préparé avec succès!\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
