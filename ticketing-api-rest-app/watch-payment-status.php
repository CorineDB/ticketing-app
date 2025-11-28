<?php

/**
 * Surveille le statut des tickets en temps réel
 * À exécuter pendant que vous effectuez le paiement
 */

$baseUrl = $argv[1] ?? 'http://localhost:8000';

if (!file_exists(__DIR__ . '/test-purchase-result.json')) {
    echo "❌ Aucun test en cours. Exécutez d'abord: php test-purchase-e2e.php\n";
    exit(1);
}

$testData = json_decode(file_get_contents(__DIR__ . '/test-purchase-result.json'), true);

function makeRequest($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'data' => json_decode($response, true),
        'http_code' => $httpCode,
    ];
}

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║      SURVEILLANCE DU STATUT DES TICKETS EN TEMPS RÉEL        ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "Transaction FedaPay: #{$testData['purchase']['transaction_id']}\n";
echo "Montant: {$testData['purchase']['total_amount']} {$testData['purchase']['currency']}\n";
echo "Tickets à surveiller: " . count($testData['purchase']['tickets']) . "\n\n";

echo "🔗 Lien de paiement:\n";
echo "   {$testData['purchase']['payment_url']}\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Surveillance en cours... (Ctrl+C pour arrêter)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$iteration = 0;
$allPaid = false;
$lastStatuses = [];

while (!$allPaid) {
    $iteration++;
    $timestamp = date('H:i:s');

    echo "[$timestamp] Vérification #$iteration...\n";

    $currentStatuses = [];
    $paidCount = 0;

    foreach ($testData['purchase']['tickets'] as $index => $ticket) {
        $ticketNum = $index + 1;
        $response = makeRequest("$baseUrl/api/public/tickets/{$ticket['id']}");

        if ($response['http_code'] === 200) {
            $ticketData = $response['data'];
            $status = $ticketData['status'] ?? 'unknown';
            $currentStatuses[$ticket['id']] = $status;

            if ($status === 'paid') {
                $paidCount++;
            }

            // Afficher seulement si le statut a changé
            if (!isset($lastStatuses[$ticket['id']]) || $lastStatuses[$ticket['id']] !== $status) {
                $icon = $status === 'paid' ? '✅' : '⏳';
                echo "  $icon Ticket $ticketNum: $status";

                if ($status === 'paid' && isset($ticketData['paid_at'])) {
                    echo " (payé le " . date('H:i:s', strtotime($ticketData['paid_at'])) . ")";
                }

                echo "\n";

                // Afficher la référence FedaPay si disponible
                if ($status === 'paid' && !empty($ticketData['metadata']['fedapay_reference'])) {
                    echo "     Réf FedaPay: {$ticketData['metadata']['fedapay_reference']}\n";
                }
            }
        } else {
            echo "  ❌ Erreur lors de la récupération du ticket $ticketNum\n";
        }
    }

    $lastStatuses = $currentStatuses;

    if ($paidCount === count($testData['purchase']['tickets'])) {
        $allPaid = true;
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║  🎉 TOUS LES TICKETS SONT MAINTENANT PAYÉS!                  ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

        echo "✅ Paiement confirmé avec succès!\n";
        echo "✅ Le webhook FedaPay a bien fonctionné!\n";
        echo "✅ Les tickets ont été mis à jour automatiquement!\n\n";

        echo "Détails des tickets:\n";
        foreach ($testData['purchase']['tickets'] as $index => $ticket) {
            $ticketNum = $index + 1;
            $response = makeRequest("$baseUrl/api/public/tickets/{$ticket['id']}");
            $ticketData = $response['data'];

            echo "\n  Ticket $ticketNum:\n";
            echo "    ID: {$ticketData['id']}\n";
            echo "    Code: {$ticketData['code']}\n";
            echo "    Status: {$ticketData['status']}\n";
            echo "    Payé le: {$ticketData['paid_at']}\n";

            if (!empty($ticketData['metadata'])) {
                echo "    Transaction FedaPay: {$ticketData['metadata']['fedapay_transaction_id']}\n";
                if (!empty($ticketData['metadata']['fedapay_reference'])) {
                    echo "    Référence: {$ticketData['metadata']['fedapay_reference']}\n";
                }
            }
        }

        echo "\n";
        break;
    } else {
        echo "  ⏳ En attente... ($paidCount/" . count($testData['purchase']['tickets']) . " payés)\n\n";
        sleep(3); // Attendre 3 secondes avant la prochaine vérification
    }
}

// Sauvegarder les résultats
$verificationData = [
    'verified_at' => date('Y-m-d H:i:s'),
    'all_paid' => $allPaid,
    'paid_count' => $paidCount,
    'total_count' => count($testData['purchase']['tickets']),
    'iterations' => $iteration,
];

file_put_contents(__DIR__ . '/test-verification-result.json', json_encode($verificationData, JSON_PRETTY_PRINT));

echo "💾 Résultats sauvegardés dans: test-verification-result.json\n\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ TEST END-TO-END COMPLET RÉUSSI!\n";
echo "═══════════════════════════════════════════════════════════════\n";
