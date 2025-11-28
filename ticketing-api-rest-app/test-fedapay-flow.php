<?php

/**
 * Script de test du flux complet FedaPay
 * Simule le flux exact utilisé dans PaymentService
 */

require __DIR__ . '/vendor/autoload.php';

use FedaPay\FedaPay;
use FedaPay\Customer;
use FedaPay\Transaction;

// Configuration identique à PaymentService
FedaPay::setApiKey('sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN');
FedaPay::setEnvironment('sandbox');

echo "=== Test du flux complet PaymentService ===\n\n";

// Simuler createTransactionForTicket
function simulateCreateTransactionForTicket($ticketIds, $customerData, $amount, $description)
{
    echo "=== Simulation createTransactionForTicket ===\n";
    echo "Ticket IDs: " . implode(', ', $ticketIds) . "\n";
    echo "Amount: $amount XOF\n";
    echo "Customer: {$customerData['firstname']} {$customerData['lastname']}\n\n";

    try {
        // Prepare customer data (comme dans PaymentService.php:48-85)
        $fedapayCustomerData = [
            'firstname' => $customerData['firstname'],
            'lastname' => $customerData['lastname'],
            'email' => $customerData['email'],
        ];

        // Phone number processing
        if (!empty($customerData['phone_number'])) {
            $phoneNumber = $customerData['phone_number'];
            $country = 'BJ'; // Default

            echo "Processing phone: $phoneNumber\n";

            // Benin numbers
            if (preg_match('/^\+?229[0-9]{8}$/', $phoneNumber) || preg_match('/^[69][0-9]{7}$/', $phoneNumber)) {
                $country = 'BJ';
                $phoneNumber = preg_replace('/^\+?229/', '', $phoneNumber);
                echo "  → Detected as Benin: $phoneNumber\n";
            }
            // French numbers
            elseif (preg_match('/^\+?33[0-9]{9}$/', $phoneNumber) || preg_match('/^0[1-9][0-9]{8}$/', $phoneNumber)) {
                $country = 'FR';
                $phoneNumber = preg_replace('/^0/', '', $phoneNumber);
                echo "  → Detected as France: $phoneNumber\n";
            } else {
                echo "  → Format not recognized, skipping\n";
                $phoneNumber = null;
            }

            if ($phoneNumber) {
                $fedapayCustomerData['phone_number'] = [
                    'number' => $phoneNumber,
                    'country' => $country,
                ];
            }
        }

        echo "\nCreating FedaPay customer...\n";
        $customer = Customer::create($fedapayCustomerData);
        echo "✓ Customer created (ID: {$customer->id})\n\n";

        // Create transaction (comme dans PaymentService.php:91-102)
        echo "Creating FedaPay transaction...\n";
        $transaction = Transaction::create([
            'description' => $description,
            'amount' => $amount,
            'currency' => ['iso' => 'XOF'],
            'callback_url' => 'http://localhost:8000/api/payment/callback',
            'customer' => ['id' => $customer->id],
            'merchant_reference' => 'tickets-' . implode('-', $ticketIds),
            'custom_metadata' => [
                'ticket_ids' => $ticketIds,
                'ticket_count' => count($ticketIds),
            ],
        ]);
        echo "✓ Transaction created (ID: {$transaction->id})\n\n";

        // Generate token (comme dans PaymentService.php:105)
        echo "Generating payment token...\n";
        $token = $transaction->generateToken();
        echo "✓ Token generated\n\n";

        return [
            'transaction_id' => $transaction->id,
            'token' => $token->token,
            'payment_url' => $token->url,
            'amount' => $amount,
            'currency' => 'XOF',
        ];

    } catch (\Exception $e) {
        echo "✗ ERREUR: " . $e->getMessage() . "\n";
        if (method_exists($e, 'getHttpBody')) {
            echo "HTTP Body: " . $e->getHttpBody() . "\n";
        }
        throw $e;
    }
}

// Test 1: Flux complet avec numéro Bénin format local
echo "═══════════════════════════════════════════════════════\n";
echo "TEST 1: Client Bénin (format local)\n";
echo "═══════════════════════════════════════════════════════\n";
try {
    $result = simulateCreateTransactionForTicket(
        ['ticket-uuid-1', 'ticket-uuid-2'],
        [
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'email' => 'jean.dupont' . time() . '@example.com',
            'phone_number' => '97123456',
        ],
        5000,
        'Achat de 2 tickets pour Concert Test'
    );

    echo "✅ SUCCÈS!\n";
    echo "URL de paiement: {$result['payment_url']}\n\n";
} catch (\Exception $e) {
    echo "❌ ÉCHEC\n\n";
}

// Test 2: Flux complet avec numéro Bénin format international
echo "═══════════════════════════════════════════════════════\n";
echo "TEST 2: Client Bénin (format international)\n";
echo "═══════════════════════════════════════════════════════\n";
try {
    $result = simulateCreateTransactionForTicket(
        ['ticket-uuid-3'],
        [
            'firstname' => 'Marie',
            'lastname' => 'Kossou',
            'email' => 'marie.kossou' . time() . '@example.com',
            'phone_number' => '+22997123456',
        ],
        2500,
        'Achat de 1 ticket pour Concert Test'
    );

    echo "✅ SUCCÈS!\n";
    echo "URL de paiement: {$result['payment_url']}\n\n";
} catch (\Exception $e) {
    echo "❌ ÉCHEC\n\n";
}

// Test 3: Flux complet avec numéro France
echo "═══════════════════════════════════════════════════════\n";
echo "TEST 3: Client France\n";
echo "═══════════════════════════════════════════════════════\n";
try {
    $result = simulateCreateTransactionForTicket(
        ['ticket-uuid-4'],
        [
            'firstname' => 'Pierre',
            'lastname' => 'Martin',
            'email' => 'pierre.martin' . time() . '@example.com',
            'phone_number' => '0612345678',
        ],
        3000,
        'Achat de 1 ticket pour Concert Test'
    );

    echo "✅ SUCCÈS!\n";
    echo "URL de paiement: {$result['payment_url']}\n\n";
} catch (\Exception $e) {
    echo "❌ ÉCHEC\n\n";
}

// Test 4: Flux complet sans numéro de téléphone
echo "═══════════════════════════════════════════════════════\n";
echo "TEST 4: Client sans numéro de téléphone\n";
echo "═══════════════════════════════════════════════════════\n";
try {
    $result = simulateCreateTransactionForTicket(
        ['ticket-uuid-5', 'ticket-uuid-6', 'ticket-uuid-7'],
        [
            'firstname' => 'Sophie',
            'lastname' => 'Bernard',
            'email' => 'sophie.bernard' . time() . '@example.com',
        ],
        7500,
        'Achat de 3 tickets pour Concert Test'
    );

    echo "✅ SUCCÈS!\n";
    echo "URL de paiement: {$result['payment_url']}\n\n";
} catch (\Exception $e) {
    echo "❌ ÉCHEC\n\n";
}

// Test 5: Flux complet avec numéro invalide
echo "═══════════════════════════════════════════════════════\n";
echo "TEST 5: Client avec numéro invalide (devrait skip)\n";
echo "═══════════════════════════════════════════════════════\n";
try {
    $result = simulateCreateTransactionForTicket(
        ['ticket-uuid-8'],
        [
            'firstname' => 'Ahmed',
            'lastname' => 'Ali',
            'email' => 'ahmed.ali' . time() . '@example.com',
            'phone_number' => '123',
        ],
        1500,
        'Achat de 1 ticket pour Concert Test'
    );

    echo "✅ SUCCÈS!\n";
    echo "URL de paiement: {$result['payment_url']}\n\n";
} catch (\Exception $e) {
    echo "❌ ÉCHEC\n\n";
}

echo "═══════════════════════════════════════════════════════\n";
echo "RÉSUMÉ: Tous les tests du flux complet sont terminés\n";
echo "═══════════════════════════════════════════════════════\n";
