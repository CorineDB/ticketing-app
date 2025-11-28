<?php

/**
 * Test Manuel du Webhook FedaPay
 *
 * Simule un webhook "transaction.approved" pour tester la mise à jour des tickets
 * Utilise les données sauvegardées par test-purchase-e2e.php
 */

require __DIR__ . '/vendor/autoload.php';

use FedaPay\Webhook;

$baseUrl = $argv[1] ?? 'http://localhost:8000';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        TEST MANUEL DU WEBHOOK FEDAPAY                        ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Charger les données de test
if (!file_exists(__DIR__ . '/test-purchase-result.json')) {
    echo "❌ Fichier test-purchase-result.json non trouvé\n";
    echo "Veuillez d'abord exécuter: php test-purchase-e2e.php\n";
    exit(1);
}

$testData = json_decode(file_get_contents(__DIR__ . '/test-purchase-result.json'), true);

echo "Données du test:\n";
echo "  Transaction ID: {$testData['purchase']['transaction_id']}\n";
echo "  Tickets: " . count($testData['purchase']['tickets']) . "\n\n";

// Créer le payload du webhook (simuler une transaction approuvée)
$webhookPayload = [
    'name' => 'transaction.approved',
    'entity' => [
        'id' => $testData['purchase']['transaction_id'],
        'reference' => 'REF-TEST-' . time(),
        'amount' => $testData['purchase']['total_amount'],
        'status' => 'approved',
        'currency' => [
            'iso' => $testData['purchase']['currency']
        ],
        'custom_metadata' => [
            'ticket_ids' => array_column($testData['purchase']['tickets'], 'id'),
            'ticket_count' => count($testData['purchase']['tickets']),
        ],
        'approved_at' => date('c'),
    ],
];

$payloadJson = json_encode($webhookPayload);

echo "═══════════════════════════════════════════════════════════════\n";
echo "PAYLOAD DU WEBHOOK\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo json_encode($webhookPayload, JSON_PRETTY_PRINT) . "\n\n";

// Générer la signature du webhook
$webhookSecret = getenv('FEDAPAY_WEBHOOK_SECRET') ?: 'ticketing';

echo "Génération de la signature...\n";
echo "  Secret: " . substr($webhookSecret, 0, 8) . "...\n";

$signature = hash_hmac('sha256', $payloadJson, $webhookSecret);

echo "  Signature: " . substr($signature, 0, 32) . "...\n\n";

// Envoyer le webhook
echo "═══════════════════════════════════════════════════════════════\n";
echo "ENVOI DU WEBHOOK\n";
echo "═══════════════════════════════════════════════════════════════\n";

$ch = curl_init("$baseUrl/api/webhooks/fedapay");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-FedaPay-Signature: ' . $signature,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "❌ Erreur cURL: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit(1);
}

curl_close($ch);

echo "URL: $baseUrl/api/webhooks/fedapay\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode === 200) {
    $responseData = json_decode($response, true);

    if ($responseData && isset($responseData['status']) && $responseData['status'] === 'success') {
        echo "✅ Webhook traité avec succès!\n\n";

        echo "═══════════════════════════════════════════════════════════════\n";
        echo "VÉRIFICATION DES TICKETS\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

        echo "Les tickets devraient maintenant avoir le statut 'paid'.\n";
        echo "Vérifiez avec:\n\n";
        echo "  php test-verify-payment.php\n\n";

        echo "Ou vérifiez les logs Laravel:\n";
        echo "  tail -f storage/logs/laravel.log | grep -i fedapay\n\n";
    } else {
        echo "⚠️  Réponse inattendue du webhook\n";
    }
} elseif ($httpCode === 401) {
    echo "❌ Signature invalide!\n\n";
    echo "Vérifications:\n";
    echo "1. Le secret webhook est-il correct dans .env?\n";
    echo "   FEDAPAY_WEBHOOK_SECRET=$webhookSecret\n\n";
    echo "2. Le secret dans config/services.php correspond-il?\n\n";
} else {
    echo "❌ Erreur lors du traitement du webhook\n";
    echo "Vérifiez les logs Laravel pour plus de détails\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "INFORMATIONS UTILES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Tickets concernés:\n";
foreach ($testData['purchase']['tickets'] as $ticket) {
    echo "  - ID: {$ticket['id']}\n";
    echo "    Code: {$ticket['code']}\n";
    echo "    Status initial: {$ticket['status']}\n";
    echo "    Vérifier: GET $baseUrl/api/public/tickets/{$ticket['id']}\n\n";
}

echo "Transaction FedaPay:\n";
echo "  ID: {$testData['purchase']['transaction_id']}\n";
echo "  Dashboard: https://sandbox.fedapay.com/transactions/{$testData['purchase']['transaction_id']}\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo $httpCode === 200 ? "✅ TEST DU WEBHOOK TERMINÉ\n" : "❌ TEST DU WEBHOOK ÉCHOUÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";

exit($httpCode === 200 ? 0 : 1);
