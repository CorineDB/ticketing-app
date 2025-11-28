<?php

/**
 * Configure un event de test avec des ticket types
 * Nécessite une authentification admin
 */

$baseUrl = $argv[1] ?? 'http://localhost:8000';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        CONFIGURATION D'UN EVENT DE TEST                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

function makeRequest($method, $url, $data = null, $token = null) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [
        'data' => json_decode($response, true),
        'http_code' => $httpCode,
        'raw' => $response
    ];
}

// Demander les credentials
echo "Veuillez fournir vos identifiants admin:\n\n";

echo "Email: ";
$email = trim(fgets(STDIN));

echo "Password: ";
// Désactiver l'écho pour le mot de passe
system('stty -echo');
$password = trim(fgets(STDIN));
system('stty echo');
echo "\n\n";

// Login
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 1: Authentification\n";
echo "═══════════════════════════════════════════════════════════════\n";

$response = makeRequest('POST', "$baseUrl/api/auths/login", [
    'email' => $email,
    'password' => $password,
]);

if ($response['http_code'] !== 200) {
    echo "❌ Échec de l'authentification\n";
    echo "HTTP Code: {$response['http_code']}\n";
    echo "Response: " . ($response['raw'] ?? 'Aucune réponse') . "\n";
    exit(1);
}

$token = $response['data']['token'] ?? null;

if (!$token) {
    echo "❌ Token non reçu\n";
    exit(1);
}

echo "✅ Authentification réussie\n";
echo "   User: {$response['data']['user']['firstname']} {$response['data']['user']['lastname']}\n";
echo "   Email: {$response['data']['user']['email']}\n\n";

// Récupérer les events
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 2: Sélection de l'event\n";
echo "═══════════════════════════════════════════════════════════════\n";

$response = makeRequest('GET', "$baseUrl/api/public/events", null, $token);

if ($response['http_code'] !== 200) {
    echo "❌ Erreur lors de la récupération des events\n";
    exit(1);
}

$events = $response['data']['data'] ?? [];

if (empty($events)) {
    echo "Aucun event existant. Création d'un nouvel event...\n\n";

    $newEvent = [
        'title' => 'Event Test FedaPay - ' . date('Y-m-d H:i'),
        'description' => 'Event créé automatiquement pour tester l\'intégration FedaPay',
        'date' => date('Y-m-d', strtotime('+7 days')),
        'start_time' => '19:00:00',
        'end_time' => '23:00:00',
        'location' => 'Cotonou, Bénin',
        'category' => 'Concert',
        'organizer_id' => $response['data']['user']['id'],
    ];

    $response = makeRequest('POST', "$baseUrl/api/events", $newEvent, $token);

    if ($response['http_code'] !== 201) {
        echo "❌ Erreur lors de la création de l'event\n";
        echo json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
        exit(1);
    }

    $event = $response['data'];
    echo "✅ Event créé: {$event['title']}\n\n";
} else {
    $event = $events[0];
    echo "✅ Event sélectionné: {$event['title']}\n";
    echo "   ID: {$event['id']}\n\n";
}

// Vérifier si des ticket types existent déjà
echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 3: Configuration des ticket types\n";
echo "═══════════════════════════════════════════════════════════════\n";

$response = makeRequest('GET', "$baseUrl/api/public/events/{$event['id']}/ticket-types", null, $token);
$existingTicketTypes = $response['data']['data'] ?? [];

if (!empty($existingTicketTypes)) {
    echo "Ticket types existants trouvés:\n";
    foreach ($existingTicketTypes as $tt) {
        echo "  - {$tt['name']}: {$tt['price']} XOF (quota: {$tt['quota']})\n";
    }
    echo "\n";
} else {
    echo "Création de ticket types par défaut...\n\n";

    $ticketTypes = [
        [
            'name' => 'Standard',
            'description' => 'Ticket standard',
            'price' => 5000,
            'quota' => 100,
        ],
        [
            'name' => 'VIP',
            'description' => 'Ticket VIP avec accès privilégié',
            'price' => 15000,
            'quota' => 50,
        ],
    ];

    foreach ($ticketTypes as $ticketType) {
        $response = makeRequest('POST', "$baseUrl/api/events/{$event['id']}/ticket-types", $ticketType, $token);

        if ($response['http_code'] === 201) {
            echo "✅ Ticket type créé: {$ticketType['name']} ({$ticketType['price']} XOF)\n";
        } else {
            echo "⚠️  Échec création de {$ticketType['name']}\n";
        }
    }
    echo "\n";
}

// Récupérer les ticket types finaux
$response = makeRequest('GET', "$baseUrl/api/public/events/{$event['id']}/ticket-types", null, $token);
$ticketTypes = $response['data']['data'] ?? [];

echo "═══════════════════════════════════════════════════════════════\n";
echo "RÉSUMÉ DE LA CONFIGURATION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Event: {$event['title']}\n";
echo "  ID: {$event['id']}\n";
echo "  Location: " . ($event['location'] ?? 'N/A') . "\n\n";

echo "Ticket Types:\n";
foreach ($ticketTypes as $tt) {
    echo "  - {$tt['name']}: {$tt['price']} XOF\n";
    echo "    ID: {$tt['id']}\n";
    echo "    Quota: {$tt['quota']}\n";
    echo "    Disponible: " . ($tt['remaining_quota'] ?? $tt['quota']) . "\n\n";
}

// Sauvegarder pour les tests
$setupData = [
    'event' => $event,
    'ticket_types' => $ticketTypes,
    'base_url' => $baseUrl,
    'configured_at' => date('Y-m-d H:i:s'),
];

file_put_contents(__DIR__ . '/test-setup.json', json_encode($setupData, JSON_PRETTY_PRINT));

echo "💾 Configuration sauvegardée dans: test-setup.json\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ CONFIGURATION TERMINÉE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Vous pouvez maintenant exécuter:\n";
echo "  php test-purchase-e2e.php\n\n";
