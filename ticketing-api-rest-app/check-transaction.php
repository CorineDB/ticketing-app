<?php

/**
 * Vérifie le statut d'une transaction FedaPay
 */

require __DIR__ . '/vendor/autoload.php';

use FedaPay\FedaPay;
use FedaPay\Transaction;

$transactionId = $argv[1] ?? '383614';

FedaPay::setApiKey('sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN');
FedaPay::setEnvironment('sandbox');

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        VÉRIFICATION TRANSACTION FEDAPAY                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "Transaction ID: $transactionId\n";
echo "Environment: sandbox\n\n";

try {
    echo "Récupération de la transaction...\n";
    $transaction = Transaction::retrieve($transactionId);

    echo "\n✅ Transaction trouvée!\n\n";
    echo "Détails:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ID                : {$transaction->id}\n";
    echo "Status            : {$transaction->status}\n";
    echo "Amount            : {$transaction->amount} {$transaction->currency->iso}\n";
    echo "Description       : {$transaction->description}\n";
    echo "Created at        : {$transaction->created_at}\n";
    echo "Updated at        : {$transaction->updated_at}\n";

    if (isset($transaction->reference)) {
        echo "Reference         : {$transaction->reference}\n";
    }

    if (isset($transaction->approved_at)) {
        echo "Approved at       : {$transaction->approved_at}\n";
    }

    if (isset($transaction->canceled_at)) {
        echo "Canceled at       : {$transaction->canceled_at}\n";
    }

    if (isset($transaction->reason)) {
        echo "Reason            : {$transaction->reason}\n";
    }

    if (isset($transaction->custom_metadata) && !empty($transaction->custom_metadata)) {
        echo "\nMétadonnées:\n";
        foreach ($transaction->custom_metadata as $key => $value) {
            echo "  - $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    }

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    // Interpréter le statut
    echo "Interprétation:\n";
    switch ($transaction->status) {
        case 'pending':
            echo "  ⏳ Transaction en attente de paiement\n";
            break;
        case 'approved':
        case 'transferred':
            echo "  ✅ Transaction approuvée/complétée\n";
            break;
        case 'canceled':
            echo "  ❌ Transaction annulée\n";
            break;
        case 'declined':
            echo "  ❌ Transaction refusée\n";
            break;
        default:
            echo "  ⚠️  Statut inconnu: {$transaction->status}\n";
    }

    echo "\n";

} catch (\Exception $e) {
    echo "\n❌ Erreur lors de la récupération de la transaction\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n\n";

    if (method_exists($e, 'getHttpBody')) {
        echo "HTTP Body: " . $e->getHttpBody() . "\n";
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
