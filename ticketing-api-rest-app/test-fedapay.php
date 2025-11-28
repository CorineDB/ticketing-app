<?php

/**
 * Script de test simple pour FedaPay
 * Ce script teste directement l'API FedaPay pour identifier les erreurs réelles
 */

require __DIR__ . '/vendor/autoload.php';

use FedaPay\FedaPay;
use FedaPay\Customer;
use FedaPay\Transaction;

// Configuration
echo "=== Configuration FedaPay ===\n";
$publicKey = 'pk_sandbox_TcBc9d1JPwbOlDzCYf7rjJCL';
$secretKey = 'sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN';
$environment = 'sandbox';

FedaPay::setApiKey($secretKey);
FedaPay::setEnvironment($environment);

echo "✓ API Key configurée\n";
echo "✓ Environment: $environment\n\n";

// Test 1: Créer un client
echo "=== Test 1: Création d'un client ===\n";
try {
    $customerData = [
        'firstname' => 'Test',
        'lastname' => 'User',
        'email' => 'test@example.com',
        'phone_number' => [
            'number' => '97000000',
            'country' => 'BJ'
        ]
    ];

    echo "Données client:\n";
    print_r($customerData);

    $customer = Customer::create($customerData);
    echo "✓ Client créé avec succès\n";
    echo "  ID: {$customer->id}\n";
    echo "  Email: {$customer->email}\n\n";

    $customerId = $customer->id;
} catch (\Exception $e) {
    echo "✗ Erreur lors de la création du client:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getCode() . "\n";
    if (method_exists($e, 'getResponse')) {
        echo "  Response: " . print_r($e->getResponse(), true) . "\n";
    }
    echo "\n";
    exit(1);
}

// Test 2: Créer une transaction sans client
echo "=== Test 2: Création d'une transaction simple ===\n";
try {
    $transactionData = [
        'description' => 'Test transaction simple',
        'amount' => 1000,
        'currency' => ['iso' => 'XOF'],
        'callback_url' => 'http://localhost:8000/api/payment/callback',
    ];

    echo "Données transaction:\n";
    print_r($transactionData);

    $transaction = Transaction::create($transactionData);
    echo "✓ Transaction créée avec succès\n";
    echo "  ID: {$transaction->id}\n";
    echo "  Amount: {$transaction->amount}\n";
    echo "  Status: {$transaction->status}\n\n";
} catch (\Exception $e) {
    echo "✗ Erreur lors de la création de la transaction:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getCode() . "\n";
    if (method_exists($e, 'getResponse')) {
        echo "  Response: " . print_r($e->getResponse(), true) . "\n";
    }
    echo "\n";
}

// Test 3: Créer une transaction avec client
echo "=== Test 3: Création d'une transaction avec client ===\n";
try {
    $transactionData = [
        'description' => 'Test transaction avec client',
        'amount' => 2000,
        'currency' => ['iso' => 'XOF'],
        'callback_url' => 'http://localhost:8000/api/payment/callback',
        'customer' => ['id' => $customerId],
        'merchant_reference' => 'test-' . time(),
        'custom_metadata' => [
            'ticket_ids' => ['test-1', 'test-2'],
            'ticket_count' => 2,
        ],
    ];

    echo "Données transaction:\n";
    print_r($transactionData);

    $transaction = Transaction::create($transactionData);
    echo "✓ Transaction créée avec succès\n";
    echo "  ID: {$transaction->id}\n";
    echo "  Amount: {$transaction->amount}\n";
    echo "  Status: {$transaction->status}\n";
    echo "  Customer ID: " . ($transaction->customer->id ?? 'N/A') . "\n\n";

    // Test 4: Générer un token de paiement
    echo "=== Test 4: Génération du token de paiement ===\n";
    try {
        $token = $transaction->generateToken();
        echo "✓ Token généré avec succès\n";
        echo "  Token: {$token->token}\n";
        echo "  URL: {$token->url}\n";
        echo "  Created at: {$token->created_at}\n\n";

        echo "=== Résultat final ===\n";
        echo "✓ Tous les tests ont réussi!\n";
        echo "URL de paiement: {$token->url}\n";

    } catch (\Exception $e) {
        echo "✗ Erreur lors de la génération du token:\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  Code: " . $e->getCode() . "\n";
        if (method_exists($e, 'getResponse')) {
            echo "  Response: " . print_r($e->getResponse(), true) . "\n";
        }
        echo "\n";
    }

} catch (\Exception $e) {
    echo "✗ Erreur lors de la création de la transaction avec client:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getCode() . "\n";
    if (method_exists($e, 'getResponse')) {
        echo "  Response: " . print_r($e->getResponse(), true) . "\n";
    }
    echo "\n";
}

echo "\n=== Fin des tests ===\n";
