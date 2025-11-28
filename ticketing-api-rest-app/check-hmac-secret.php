<?php

/**
 * Affiche la clé HMAC utilisée pour les signatures de tickets
 */

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ticketId = $argv[1] ?? '019aca1b-7c6c-72d8-96c4-27397e5cda31';
$eventId = $argv[2] ?? '019ac8e5-5332-7073-9c71-0c2187812cb0';

echo "═══════════════════════════════════════════════════════════════\n";
echo "VÉRIFICATION DE LA CLÉ HMAC POUR LES TICKETS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$secret = config('app.ticket_hmac_secret', config('app.key'));

echo "Clé HMAC utilisée:\n";
if (config('app.ticket_hmac_secret')) {
    echo "  Source: config('app.ticket_hmac_secret')\n";
} else {
    echo "  Source: config('app.key') (fallback)\n";
}

echo "  Valeur: " . substr($secret, 0, 20) . "...\n";
echo "  Longueur: " . strlen($secret) . " caractères\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "GÉNÉRATION DE LA SIGNATURE CORRECTE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$payload = $ticketId . '|' . $eventId;
$signature = hash_hmac('sha256', $payload, $secret);

echo "Payload: $payload\n";
echo "Signature: $signature\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST DE VALIDATION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Test de validation comme dans ScanService
$expectedSignature = hash_hmac('sha256', $ticketId . '|' . $eventId, $secret);

echo "Signature attendue: $expectedSignature\n";
echo "Signature calculée: $signature\n";
echo "Match: " . ($expectedSignature === $signature ? '✅ OUI' : '❌ NON') . "\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "POUR UTILISER DANS LE TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Utilisez cette signature dans votre requête POST /api/scan/request:\n";
echo "{\n";
echo "  \"ticket_id\": \"$ticketId\",\n";
echo "  \"sig\": \"$signature\"\n";
echo "}\n\n";
