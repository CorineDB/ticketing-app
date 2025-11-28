<?php

/**
 * Script de test avancé pour FedaPay
 * Teste différents formats de numéros de téléphone et cas limites
 */

require __DIR__ . '/vendor/autoload.php';

use FedaPay\FedaPay;
use FedaPay\Customer;
use FedaPay\Transaction;

// Configuration
FedaPay::setApiKey('sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN');
FedaPay::setEnvironment('sandbox');

echo "=== Tests avancés FedaPay ===\n\n";

// Test avec différents formats de numéros de téléphone
$phoneTests = [
    'Benin format local' => [
        'number' => '97000000',
        'country' => 'BJ'
    ],
    'Benin format international' => [
        'number' => '22997000000',
        'country' => 'BJ'
    ],
    'Benin avec prefix +' => [
        'number' => '+22997000000',
        'country' => 'BJ'
    ],
    'France format local' => [
        'number' => '612345678',
        'country' => 'FR'
    ],
    'Sans numéro' => null,
];

foreach ($phoneTests as $testName => $phoneData) {
    echo "=== Test: $testName ===\n";

    try {
        $customerData = [
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test' . time() . rand(1000, 9999) . '@example.com',
        ];

        if ($phoneData !== null) {
            $customerData['phone_number'] = $phoneData;
        }

        echo "Données client:\n";
        print_r($customerData);

        $customer = Customer::create($customerData);
        echo "✓ Client créé avec succès (ID: {$customer->id})\n\n";

    } catch (\Exception $e) {
        echo "✗ Erreur:\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  Code: " . $e->getCode() . "\n";

        // Essayer d'obtenir plus de détails sur l'erreur
        if (method_exists($e, 'getHttpStatus')) {
            echo "  HTTP Status: " . $e->getHttpStatus() . "\n";
        }
        if (method_exists($e, 'getHttpBody')) {
            echo "  HTTP Body: " . $e->getHttpBody() . "\n";
        }
        echo "\n";
    }
}

// Test avec montants différents
echo "\n=== Tests avec différents montants ===\n";
$amountTests = [
    100,      // Minimum
    1000,     // Standard
    10000,    // Medium
    100000,   // Large
    999999999 // Maximum
];

foreach ($amountTests as $amount) {
    echo "Test montant: $amount XOF\n";

    try {
        $transaction = Transaction::create([
            'description' => "Test montant $amount",
            'amount' => $amount,
            'currency' => ['iso' => 'XOF'],
            'callback_url' => 'http://localhost:8000/api/payment/callback',
        ]);

        echo "  ✓ Transaction créée (ID: {$transaction->id}, Status: {$transaction->status})\n";

    } catch (\Exception $e) {
        echo "  ✗ Erreur: " . $e->getMessage() . "\n";
    }
}

// Test avec métadonnées complexes
echo "\n=== Test avec métadonnées complexes ===\n";
try {
    $transaction = Transaction::create([
        'description' => 'Test métadonnées complexes',
        'amount' => 5000,
        'currency' => ['iso' => 'XOF'],
        'callback_url' => 'http://localhost:8000/api/payment/callback',
        'custom_metadata' => [
            'ticket_ids' => ['uuid-1', 'uuid-2', 'uuid-3'],
            'ticket_count' => 3,
            'event_id' => 'event-123',
            'user_id' => 'user-456',
            'notes' => 'Test de métadonnées avec caractères spéciaux: àéèêç',
            'array_test' => ['key1' => 'value1', 'key2' => 'value2'],
        ],
    ]);

    echo "✓ Transaction avec métadonnées créée (ID: {$transaction->id})\n";
    echo "Métadonnées:\n";
    print_r($transaction->custom_metadata ?? 'N/A');
    echo "\n";

} catch (\Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test callback_url avec différents formats
echo "=== Test avec différentes callback_url ===\n";
$callbackTests = [
    'localhost' => 'http://localhost:8000/api/payment/callback',
    'localhost avec query' => 'http://localhost:8000/api/payment/callback?test=1',
    'IP' => 'http://127.0.0.1:8000/api/payment/callback',
    'domain' => 'https://example.com/api/payment/callback',
];

foreach ($callbackTests as $testName => $callbackUrl) {
    echo "Test callback: $testName\n";

    try {
        $transaction = Transaction::create([
            'description' => "Test callback $testName",
            'amount' => 1000,
            'currency' => ['iso' => 'XOF'],
            'callback_url' => $callbackUrl,
        ]);

        echo "  ✓ Transaction créée (ID: {$transaction->id})\n";

    } catch (\Exception $e) {
        echo "  ✗ Erreur: " . $e->getMessage() . "\n";
    }
}

// Test de génération de token multiple pour la même transaction
echo "\n=== Test génération multiple de tokens ===\n";
try {
    $transaction = Transaction::create([
        'description' => 'Test génération multiple tokens',
        'amount' => 1000,
        'currency' => ['iso' => 'XOF'],
        'callback_url' => 'http://localhost:8000/api/payment/callback',
    ]);

    echo "Transaction créée (ID: {$transaction->id})\n";

    // Générer premier token
    $token1 = $transaction->generateToken();
    echo "✓ Token 1 généré: {$token1->token}\n";

    // Essayer de générer un second token
    $token2 = $transaction->generateToken();
    echo "✓ Token 2 généré: {$token2->token}\n";

    echo "Les tokens sont " . ($token1->token === $token2->token ? "identiques" : "différents") . "\n\n";

} catch (\Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n\n";
}

echo "=== Fin des tests avancés ===\n";
