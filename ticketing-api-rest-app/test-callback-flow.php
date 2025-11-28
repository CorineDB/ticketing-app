<?php

/**
 * Test du flux callback FedaPay
 * Simule ce qui se passe quand un utilisateur est redirigé après paiement
 */

$baseUrl = $argv[1] ?? 'http://localhost:8000';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║           TEST DU FLUX CALLBACK FEDAPAY                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Vérifier si on a des données de test
if (!file_exists(__DIR__ . '/test-purchase-result.json')) {
    echo "⚠️  Aucun achat test trouvé. Création d'un achat test...\n\n";

    // Exécuter test-purchase-e2e.php
    passthru("php test-purchase-e2e.php $baseUrl");
    echo "\n";

    if (!file_exists(__DIR__ . '/test-purchase-result.json')) {
        echo "❌ Échec de la création de l'achat test\n";
        exit(1);
    }
}

$testData = json_decode(file_get_contents(__DIR__ . '/test-purchase-result.json'), true);
$transactionId = $testData['purchase']['transaction_id'];

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 1: INFORMATIONS DE LA TRANSACTION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Transaction ID: $transactionId\n";
echo "Nombre de tickets: " . count($testData['purchase']['tickets']) . "\n";
echo "Montant: {$testData['purchase']['total_amount']} {$testData['purchase']['currency']}\n";
echo "Lien de paiement: {$testData['purchase']['payment_url']}\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 2: SIMULATION DU CALLBACK APRÈS PAIEMENT\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Simuler différents scénarios de callback
$scenarios = [
    [
        'name' => 'Paiement approuvé',
        'params' => [
            'status' => 'approved',
            'id' => $transactionId,
            'reference' => 'REF-' . time(),
        ]
    ],
    [
        'name' => 'Paiement annulé',
        'params' => [
            'status' => 'canceled',
            'id' => $transactionId,
            'reference' => 'REF-' . time(),
        ]
    ],
    [
        'name' => 'Paiement refusé',
        'params' => [
            'status' => 'declined',
            'id' => $transactionId,
            'reference' => 'REF-' . time(),
        ]
    ],
];

foreach ($scenarios as $scenario) {
    echo "───────────────────────────────────────────────────────────────\n";
    echo "Scénario: {$scenario['name']}\n";
    echo "───────────────────────────────────────────────────────────────\n\n";

    $callbackUrl = $baseUrl . '/api/payment/callback?' . http_build_query($scenario['params']);

    echo "URL du callback:\n";
    echo "$callbackUrl\n\n";

    // Faire la requête et suivre la redirection
    $ch = curl_init($callbackUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Ne pas suivre automatiquement
    curl_setopt($ch, CURLOPT_HEADER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        echo "❌ Erreur: " . curl_error($ch) . "\n\n";
        curl_close($ch);
        continue;
    }

    curl_close($ch);

    echo "Code HTTP: $httpCode\n";

    // Extraire l'URL de redirection
    if (preg_match('/Location: (.+)/', $response, $matches)) {
        $redirectUrl = trim($matches[1]);
        echo "✅ Redirection vers:\n";
        echo "$redirectUrl\n\n";

        // Parser l'URL de redirection
        $parsedUrl = parse_url($redirectUrl);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $redirectParams);

            echo "Paramètres de redirection:\n";
            foreach ($redirectParams as $key => $value) {
                echo "  - $key: $value\n";
            }
            echo "\n";
        }

        // Afficher la page frontend qui sera affichée
        $frontendPath = $parsedUrl['path'] ?? '/';
        echo "Page frontend: $frontendPath\n";

        if (strpos($frontendPath, '/payment/result') !== false) {
            echo "📄 Type: Page de résultat générique\n";
            echo "   ⚠️  L'utilisateur voit juste le statut du paiement\n";
            echo "   ⚠️  Il doit chercher ses tickets dans son email\n";
        } elseif (strpos($frontendPath, '/my-tickets') !== false) {
            echo "🎫 Type: Page des tickets\n";
            echo "   ✅ L'utilisateur voit directement ses tickets\n";
            echo "   ✅ Il peut les télécharger immédiatement\n";
        }

    } else {
        echo "❌ Pas de redirection trouvée\n";
        echo "Réponse complète:\n";
        echo $response . "\n";
    }

    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 3: VÉRIFICATION DES LOGS LARAVEL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Pour voir les logs du callback:\n";
echo "  tail -f storage/logs/laravel.log | grep -A 10 'FedaPay payment callback'\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "RÉSUMÉ DU FLUX ACTUEL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ 1. Utilisateur termine le paiement sur FedaPay             │\n";
echo "│    ↓                                                        │\n";
echo "│ 2. FedaPay redirige vers /api/payment/callback             │\n";
echo "│    Paramètres: status, id, reference                       │\n";
echo "│    ↓                                                        │\n";
echo "│ 3. PaymentController reçoit le callback                    │\n";
echo "│    - Log les paramètres reçus                              │\n";
echo "│    - Extrait status, transaction_id, reference             │\n";
echo "│    ↓                                                        │\n";
echo "│ 4. Redirection vers le frontend                            │\n";
echo "│    URL: \$frontendUrl/payment/result?...                     │\n";
echo "│    ↓                                                        │\n";
echo "│ 5. Frontend affiche une page générique                     │\n";
echo "│    ⚠️  Utilisateur ne voit PAS ses tickets                 │\n";
echo "│    ⚠️  Doit chercher dans son email                        │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "💡 AMÉLIORATION POSSIBLE:\n";
echo "   Au lieu de rediriger vers /payment/result,\n";
echo "   rediriger vers /my-tickets avec les magic_link_tokens\n";
echo "   pour accès direct aux tickets.\n\n";

echo "   Voir: PROPOSITION_AMELIORATION_CALLBACK.md\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ TEST DU CALLBACK TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
