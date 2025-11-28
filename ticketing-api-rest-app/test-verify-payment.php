<?php

/**
 * Vérifie le statut des tickets après paiement
 * Utilise les données sauvegardées par test-purchase-e2e.php
 */

$baseUrl = $argv[1] ?? 'http://localhost:8000';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        VÉRIFICATION DU STATUT APRÈS PAIEMENT                 ║\n";
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
echo "  Nombre de tickets: " . count($testData['purchase']['tickets']) . "\n";
echo "  Montant: {$testData['purchase']['total_amount']} {$testData['purchase']['currency']}\n";
echo "  Testé le: {$testData['tested_at']}\n\n";

// Fonction pour faire une requête HTTP
function makeRequest($method, $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

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
    ];
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "VÉRIFICATION DU STATUT DES TICKETS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$allPaid = true;
$ticketStatuses = [];

foreach ($testData['purchase']['tickets'] as $index => $ticket) {
    $num = $index + 1;
    echo "Ticket $num (ID: {$ticket['id']}):\n";

    $response = makeRequest('GET', "$baseUrl/api/public/tickets/{$ticket['id']}");

    if ($response['http_code'] !== 200) {
        echo "  ❌ Erreur lors de la récupération\n";
        echo "     HTTP Code: {$response['http_code']}\n";
        continue;
    }

    $ticketData = $response['data'];
    $status = $ticketData['status'] ?? 'unknown';

    $ticketStatuses[] = [
        'id' => $ticket['id'],
        'code' => $ticket['code'],
        'status' => $status,
        'paid_at' => $ticketData['paid_at'] ?? null,
    ];

    $statusIcon = $status === 'paid' ? '✅' : '⚠️ ';
    echo "  $statusIcon Status: $status\n";
    echo "     Code: {$ticketData['ticket_code']}\n";
    echo "     Buyer: {$ticketData['buyer_name']}\n";

    if ($status === 'paid') {
        echo "     Payé le: " . ($ticketData['paid_at'] ?? 'N/A') . "\n";
    } else {
        $allPaid = false;
    }

    // Afficher les métadonnées si présentes
    if (!empty($ticketData['metadata'])) {
        echo "     Métadonnées FedaPay:\n";
        if (isset($ticketData['metadata']['fedapay_transaction_id'])) {
            echo "       - Transaction: {$ticketData['metadata']['fedapay_transaction_id']}\n";
        }
        if (isset($ticketData['metadata']['fedapay_reference'])) {
            echo "       - Référence: {$ticketData['metadata']['fedapay_reference']}\n";
        }
    }

    echo "\n";
}

// Résumé
echo "═══════════════════════════════════════════════════════════════\n";
echo "RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$paidCount = count(array_filter($ticketStatuses, fn($t) => $t['status'] === 'paid'));
$totalCount = count($ticketStatuses);

echo "Tickets payés: $paidCount / $totalCount\n\n";

if ($allPaid) {
    echo "✅ TOUS LES TICKETS SONT PAYÉS!\n";
    echo "Le webhook FedaPay a fonctionné correctement.\n\n";
} else {
    echo "⚠️  Certains tickets ne sont pas encore payés\n\n";
    echo "Vérifications possibles:\n";
    echo "1. Avez-vous complété le paiement sur FedaPay?\n";
    echo "2. Le webhook a-t-il été reçu?\n";
    echo "   Vérifiez les logs: tail -f storage/logs/laravel.log\n";
    echo "3. Le webhook est-il configuré dans FedaPay?\n";
    echo "4. Testez le webhook manuellement: php test-webhook-manual.php\n\n";

    echo "URL de paiement (si besoin de refaire):\n";
    echo "  {$testData['purchase']['payment_url']}\n\n";
}

// Tableau récapitulatif
echo "Statuts détaillés:\n";
echo "┌─────────────────────────┬──────────────┬──────────────────────┐\n";
echo "│ Ticket ID               │ Status       │ Paid At              │\n";
echo "├─────────────────────────┼──────────────┼──────────────────────┤\n";
foreach ($ticketStatuses as $ticket) {
    $statusDisplay = str_pad($ticket['status'], 12);
    $paidAt = $ticket['paid_at'] ? substr($ticket['paid_at'], 0, 19) : 'N/A';
    $paidAtDisplay = str_pad($paidAt, 20);
    echo "│ " . substr($ticket['id'], 0, 23) . " │ $statusDisplay │ $paidAtDisplay │\n";
}
echo "└─────────────────────────┴──────────────┴──────────────────────┘\n\n";

// Sauvegarder les résultats de vérification
$verificationData = [
    'verified_at' => date('Y-m-d H:i:s'),
    'tickets' => $ticketStatuses,
    'all_paid' => $allPaid,
    'paid_count' => $paidCount,
    'total_count' => $totalCount,
];

file_put_contents(__DIR__ . '/test-verification-result.json', json_encode($verificationData, JSON_PRETTY_PRINT));
echo "💾 Résultats sauvegardés dans: test-verification-result.json\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo $allPaid ? "✅ VÉRIFICATION TERMINÉE AVEC SUCCÈS\n" : "⚠️  VÉRIFICATION INCOMPLÈTE\n";
echo "═══════════════════════════════════════════════════════════════\n";

exit($allPaid ? 0 : 1);
