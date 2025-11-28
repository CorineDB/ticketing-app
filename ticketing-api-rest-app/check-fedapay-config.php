<?php

/**
 * Script de vérification de la configuration FedaPay
 * Vérifie que toutes les variables d'environnement sont correctement configurées
 */

echo "=== Vérification de la configuration FedaPay ===\n\n";

// Charger les variables d'environnement
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    preg_match_all('/^([A-Z_]+)=(.*)$/m', $envContent, $matches);
    $env = array_combine($matches[1], $matches[2]);
} else {
    echo "⚠️  Fichier .env non trouvé\n\n";
    $env = [];
}

// Configuration attendue
$requiredVars = [
    'FEDAPAY_PUBLIC_KEY' => [
        'sandbox' => 'pk_sandbox_',
        'live' => 'pk_live_',
    ],
    'FEDAPAY_SECRET_KEY' => [
        'sandbox' => 'sk_sandbox_',
        'live' => 'sk_live_',
    ],
    'FEDAPAY_WEBHOOK_SECRET' => true,
    'FEDAPAY_ENVIRONMENT' => ['sandbox', 'live'],
    'FEDAPAY_CURRENCY' => ['XOF', 'EUR', 'USD'],
];

$errors = [];
$warnings = [];
$success = [];

// Vérifier chaque variable
foreach ($requiredVars as $varName => $validation) {
    $value = $env[$varName] ?? null;

    if ($value === null || $value === '') {
        $errors[] = "❌ $varName n'est pas défini";
        continue;
    }

    // Validation spécifique
    switch ($varName) {
        case 'FEDAPAY_PUBLIC_KEY':
            if (str_starts_with($value, 'pk_sandbox_')) {
                $success[] = "✅ $varName configuré (SANDBOX)";
            } elseif (str_starts_with($value, 'pk_live_')) {
                $warnings[] = "⚠️  $varName configuré (LIVE - Production)";
            } else {
                $errors[] = "❌ $varName format invalide (doit commencer par pk_sandbox_ ou pk_live_)";
            }
            break;

        case 'FEDAPAY_SECRET_KEY':
            if (str_starts_with($value, 'sk_sandbox_')) {
                $success[] = "✅ $varName configuré (SANDBOX)";
            } elseif (str_starts_with($value, 'sk_live_')) {
                $warnings[] = "⚠️  $varName configuré (LIVE - Production)";
            } else {
                $errors[] = "❌ $varName format invalide (doit commencer par sk_sandbox_ ou sk_live_)";
            }
            break;

        case 'FEDAPAY_WEBHOOK_SECRET':
            if (strlen($value) < 8) {
                $warnings[] = "⚠️  $varName est trop court (recommandé: 16+ caractères)";
            } else {
                $success[] = "✅ $varName configuré";
            }
            break;

        case 'FEDAPAY_ENVIRONMENT':
            if (in_array($value, ['sandbox', 'live'])) {
                if ($value === 'sandbox') {
                    $success[] = "✅ $varName = $value (mode test)";
                } else {
                    $warnings[] = "⚠️  $varName = $value (PRODUCTION)";
                }
            } else {
                $errors[] = "❌ $varName doit être 'sandbox' ou 'live' (actuel: $value)";
            }
            break;

        case 'FEDAPAY_CURRENCY':
            if (in_array($value, ['XOF', 'EUR', 'USD'])) {
                $success[] = "✅ $varName = $value";
            } else {
                $warnings[] = "⚠️  $varName = $value (devise non standard)";
            }
            break;
    }
}

// Afficher les résultats
if (!empty($success)) {
    echo "Succès:\n";
    foreach ($success as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "Avertissements:\n";
    foreach ($warnings as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "Erreurs:\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

// Résumé
echo "═══════════════════════════════════════════\n";
if (empty($errors)) {
    echo "✅ Configuration FedaPay valide!\n";

    // Vérifier la cohérence sandbox/live
    $isSandbox = isset($env['FEDAPAY_ENVIRONMENT']) && $env['FEDAPAY_ENVIRONMENT'] === 'sandbox';
    $publicKeySandbox = isset($env['FEDAPAY_PUBLIC_KEY']) && str_starts_with($env['FEDAPAY_PUBLIC_KEY'], 'pk_sandbox_');
    $secretKeySandbox = isset($env['FEDAPAY_SECRET_KEY']) && str_starts_with($env['FEDAPAY_SECRET_KEY'], 'sk_sandbox_');

    if ($isSandbox && $publicKeySandbox && $secretKeySandbox) {
        echo "Mode: SANDBOX (test)\n";
        echo "✅ Toutes les clés correspondent au mode sandbox\n";
    } elseif (!$isSandbox && !$publicKeySandbox && !$secretKeySandbox) {
        echo "Mode: LIVE (production)\n";
        echo "⚠️  ATTENTION: Configuration en mode PRODUCTION\n";
    } else {
        echo "⚠️  INCOHÉRENCE: L'environnement et les clés ne correspondent pas!\n";
        echo "   Environment: " . ($env['FEDAPAY_ENVIRONMENT'] ?? 'non défini') . "\n";
        echo "   Public Key: " . ($publicKeySandbox ? 'sandbox' : 'live/invalide') . "\n";
        echo "   Secret Key: " . ($secretKeySandbox ? 'sandbox' : 'live/invalide') . "\n";
    }
} else {
    echo "❌ Erreurs de configuration détectées\n";
    echo "Veuillez corriger les erreurs ci-dessus\n";
}
echo "═══════════════════════════════════════════\n\n";

// Test de connexion (si autoload disponible)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "=== Test de connexion FedaPay ===\n";

    require __DIR__ . '/vendor/autoload.php';

    try {
        $secretKey = $env['FEDAPAY_SECRET_KEY'] ?? null;
        $environment = $env['FEDAPAY_ENVIRONMENT'] ?? 'sandbox';

        if (!$secretKey) {
            echo "❌ Impossible de tester: FEDAPAY_SECRET_KEY non défini\n\n";
            exit(1);
        }

        \FedaPay\FedaPay::setApiKey($secretKey);
        \FedaPay\FedaPay::setEnvironment($environment);

        echo "Configuration appliquée:\n";
        echo "  Environment: $environment\n";
        echo "  API Key: " . substr($secretKey, 0, 15) . "...\n\n";

        // Test simple: créer un client temporaire
        $testCustomer = \FedaPay\Customer::create([
            'firstname' => 'Test',
            'lastname' => 'Config',
            'email' => 'test-config-' . time() . '@example.com',
        ]);

        echo "✅ Connexion FedaPay réussie!\n";
        echo "   Client test créé (ID: {$testCustomer->id})\n";
        echo "   L'API FedaPay répond correctement\n\n";

    } catch (\Exception $e) {
        echo "❌ Erreur de connexion FedaPay:\n";
        echo "   " . $e->getMessage() . "\n\n";
        exit(1);
    }
} else {
    echo "ℹ️  Vendor autoload non disponible, test de connexion ignoré\n\n";
}

echo "✅ Vérification terminée\n";
