<?php

/**
 * Simule un paiement complet en mettant à jour directement les tickets
 * Bypasse le webhook pour tester le flux complet
 */

require __DIR__ . '/vendor/autoload.php';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        SIMULATION DE PAIEMENT COMPLET                        ║\n";
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
echo "  Tickets: " . count($testData['purchase']['tickets']) . "\n";
echo "  Montant: {$testData['purchase']['total_amount']} {$testData['purchase']['currency']}\n\n";

// Utiliser Eloquent pour mettre à jour les tickets
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 1: Chargement de Laravel\n";
echo "═══════════════════════════════════════════════════════════════\n";

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "✅ Laravel chargé\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 2: Mise à jour des tickets\n";
echo "═══════════════════════════════════════════════════════════════\n";

$now = now();
$transactionId = $testData['purchase']['transaction_id'];
$reference = 'REF-SIMULATED-' . time();

foreach ($testData['purchase']['tickets'] as $index => $ticketData) {
    $ticketNum = $index + 1;
    $ticketId = $ticketData['id'];

    echo "Ticket $ticketNum (ID: $ticketId)...\n";

    // Récupérer le ticket
    $ticket = \App\Models\Ticket::find($ticketId);

    if (!$ticket) {
        echo "  ❌ Ticket non trouvé\n";
        continue;
    }

    echo "  Statut actuel: {$ticket->status}\n";

    // Mettre à jour le ticket comme si le webhook l'avait fait
    $ticket->update([
        'status' => 'paid',
        'paid_at' => $now,
        'metadata' => array_merge($ticket->metadata ?? [], [
            'fedapay_transaction_id' => $transactionId,
            'fedapay_reference' => $reference,
            'payment_approved_at' => $now->toISOString(),
            'payment_method' => 'simulated',
        ]),
    ]);

    echo "  ✅ Ticket mis à jour:\n";
    echo "     Nouveau statut: paid\n";
    echo "     Payé le: {$ticket->paid_at}\n";
    echo "     Transaction: $transactionId\n";
    echo "     Référence: $reference\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 3: Vérification des tickets mis à jour\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$allPaid = true;
foreach ($testData['purchase']['tickets'] as $index => $ticketData) {
    $ticketNum = $index + 1;
    $ticket = \App\Models\Ticket::find($ticketData['id']);

    echo "Ticket $ticketNum:\n";
    echo "  Status: {$ticket->status}\n";
    echo "  Code: {$ticket->code}\n";
    echo "  Buyer: {$ticket->buyer_name}\n";
    echo "  Paid at: {$ticket->paid_at}\n";

    if (!empty($ticket->metadata)) {
        echo "  Metadata:\n";
        foreach ($ticket->metadata as $key => $value) {
            echo "    - $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    }

    echo "\n";

    if ($ticket->status !== 'paid') {
        $allPaid = false;
    }
}

if ($allPaid) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  🎉 TOUS LES TICKETS SONT MAINTENANT PAYÉS!                  ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    echo "✅ Simulation de paiement réussie!\n";
    echo "✅ Les tickets ont été mis à jour comme après un webhook FedaPay\n\n";

    // Sauvegarder les résultats
    $simulationData = [
        'simulated_at' => date('Y-m-d H:i:s'),
        'transaction_id' => $transactionId,
        'reference' => $reference,
        'tickets_updated' => count($testData['purchase']['tickets']),
        'all_paid' => true,
    ];

    file_put_contents(__DIR__ . '/test-simulation-result.json', json_encode($simulationData, JSON_PRETTY_PRINT));
    echo "💾 Résultats sauvegardés dans: test-simulation-result.json\n\n";
} else {
    echo "❌ Certains tickets n'ont pas été mis à jour\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "VÉRIFICATION VIA L'API\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Vous pouvez maintenant vérifier via l'API:\n\n";

foreach ($testData['purchase']['tickets'] as $ticketData) {
    echo "  curl http://localhost:8000/api/public/tickets/{$ticketData['id']}\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ SIMULATION TERMINÉE AVEC SUCCÈS!\n";
echo "═══════════════════════════════════════════════════════════════\n";
