<?php

/**
 * Test du scan de tickets
 * Simule le scan d'un QR code à l'entrée d'un événement
 */

$baseUrl = $argv[1] ?? 'http://localhost:8000';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║              TEST DE SCAN DE TICKETS                         ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Fonction pour faire une requête HTTP
function makeRequest($method, $url, $headers = [], $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if (!empty($headers)) {
        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = "$key: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
    }

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        curl_close($ch);
        return ['error' => curl_error($ch), 'http_code' => 0];
    }

    curl_close($ch);

    return [
        'data' => json_decode($response, true),
        'raw' => $response,
        'http_code' => $httpCode,
    ];
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 1: RECHERCHE DE TICKETS DE TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Charger les données de test si disponibles
$testTickets = [];

if (file_exists(__DIR__ . '/test-purchase-result.json')) {
    $testData = json_decode(file_get_contents(__DIR__ . '/test-purchase-result.json'), true);
    $testTickets = $testData['purchase']['tickets'] ?? [];
    echo "✅ Tickets de test trouvés: " . count($testTickets) . "\n\n";
} else {
    echo "⚠️  Aucun ticket de test trouvé\n";
    echo "Exécutez d'abord: php test-purchase-e2e.php\n\n";
    exit(1);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "ÉTAPE 2: TEST DE SCAN DES TICKETS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

foreach ($testTickets as $index => $ticket) {
    $num = $index + 1;

    echo "───────────────────────────────────────────────────────────────\n";
    echo "TICKET #$num\n";
    echo "───────────────────────────────────────────────────────────────\n\n";

    $ticketId = $ticket['id'];
    $ticketCode = $ticket['code'];
    $magicToken = $ticket['magic_link_token'] ?? null;

    echo "ID: $ticketId\n";
    echo "Code: $ticketCode\n";
    echo "Magic Token: " . ($magicToken ? substr($magicToken, 0, 20) . '...' : 'N/A') . "\n\n";

    // Scénario 1: Scan avec magic token (utilisateur)
    echo "🔍 Scénario 1: Scan avec Magic Token (accès public)\n";
    echo "   (Comme quand l'utilisateur scanne son propre QR code)\n\n";

    if ($magicToken) {
        $url = "$baseUrl/api/public/tickets/$ticketId?token=$magicToken";
        echo "   GET $url\n";

        $response = makeRequest('GET', $url);

        if ($response['http_code'] === 200) {
            $ticketData = $response['data'];
            echo "   ✅ Ticket récupéré avec succès\n";
            echo "   Status: {$ticketData['status']}\n";
            echo "   Code: {$ticketData['code']}\n";
            echo "   Buyer: {$ticketData['buyer_name']}\n";
            echo "   Email: {$ticketData['buyer_email']}\n";

            if (isset($ticketData['event'])) {
                echo "   Event: {$ticketData['event']['title']}\n";
            }
        } else {
            echo "   ❌ Erreur HTTP {$response['http_code']}\n";
            if (isset($response['data']['error'])) {
                echo "   Erreur: {$response['data']['error']}\n";
            }
        }
    } else {
        echo "   ❌ Pas de magic token disponible\n";
    }

    echo "\n";

    // Scénario 2: Scan à l'entrée (staff de l'événement)
    echo "🎫 Scénario 2: Validation à l'entrée de l'événement\n";
    echo "   (Comme quand le staff scanne le QR à l'entrée)\n\n";

    // Le QR code contient typiquement le code du ticket
    echo "   Recherche par code: $ticketCode\n";

    // Simuler une recherche par code
    $searchUrl = "$baseUrl/api/tickets?code=$ticketCode";
    echo "   GET $searchUrl\n";

    $searchResponse = makeRequest('GET', $searchUrl);

    if ($searchResponse['http_code'] === 200) {
        echo "   ✅ Ticket trouvé\n";

        // Vérifier le statut
        $foundTicket = $searchResponse['data']['data'][0] ?? null;
        if ($foundTicket) {
            $status = $foundTicket['status'] ?? 'unknown';
            echo "   Status: $status\n";

            if ($status === 'paid') {
                echo "   ✅ TICKET VALIDE - Autoriser l'entrée\n";
            } elseif ($status === 'scanned') {
                echo "   ⚠️  TICKET DÉJÀ SCANNÉ - Vérifier doublon\n";
            } elseif ($status === 'issued') {
                echo "   ❌ TICKET NON PAYÉ - Refuser l'entrée\n";
            } else {
                echo "   ❌ STATUS INCONNU: $status\n";
            }
        }
    } else {
        echo "   ❌ Ticket non trouvé (code: {$searchResponse['http_code']})\n";
    }

    echo "\n";

    // Scénario 3: Récupération du QR code
    echo "📱 Scénario 3: Téléchargement du QR Code\n";
    echo "   (Pour afficher le QR à l'utilisateur)\n\n";

    if ($magicToken) {
        $qrUrl = "$baseUrl/api/public/tickets/$ticketId/qr?token=$magicToken";
        echo "   GET $qrUrl\n";

        $qrResponse = makeRequest('GET', $qrUrl);

        if ($qrResponse['http_code'] === 200) {
            $qrData = $qrResponse['data'];
            echo "   ✅ QR Code disponible\n";

            if (isset($qrData['qr_code'])) {
                echo "   Format: " . (strpos($qrData['qr_code'], 'data:image') === 0 ? 'Base64 Image' : 'Unknown') . "\n";
                echo "   Taille: " . strlen($qrData['qr_code']) . " caractères\n";
            }

            if (isset($qrData['url'])) {
                echo "   URL encodée: {$qrData['url']}\n";
            }
        } else {
            echo "   ❌ Erreur HTTP {$qrResponse['http_code']}\n";
        }
    }

    echo "\n";

    // Scénario 4: Téléchargement du fichier PNG
    echo "🖼️  Scénario 4: Téléchargement QR en PNG\n";
    echo "   (Pour sauvegarder ou imprimer)\n\n";

    if ($magicToken) {
        $downloadUrl = "$baseUrl/api/public/tickets/$ticketId/qr/download?token=$magicToken";
        echo "   GET $downloadUrl\n";

        $downloadResponse = makeRequest('GET', $downloadUrl);

        if ($downloadResponse['http_code'] === 200) {
            echo "   ✅ Fichier PNG disponible\n";
            echo "   Taille: " . strlen($downloadResponse['raw']) . " octets\n";

            // Vérifier si c'est bien une image PNG
            if (substr($downloadResponse['raw'], 0, 4) === "\x89PNG") {
                echo "   Format: PNG valide ✅\n";
            }
        } else {
            echo "   ❌ Erreur HTTP {$downloadResponse['http_code']}\n";
        }
    }

    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "RÉSUMÉ DU SYSTÈME DE SCAN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ FLUX D'UTILISATION DU QR CODE                               │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";
echo "│                                                             │\n";
echo "│ 1️⃣  Utilisateur achète le ticket                            │\n";
echo "│    → Ticket créé avec magic_link_token                     │\n";
echo "│    → QR code généré avec le code du ticket                 │\n";
echo "│                                                             │\n";
echo "│ 2️⃣  Utilisateur reçoit le ticket par email                  │\n";
echo "│    → Lien avec magic token                                 │\n";
echo "│    → Peut télécharger le QR code                           │\n";
echo "│                                                             │\n";
echo "│ 3️⃣  À l'entrée de l'événement                               │\n";
echo "│    → Staff scanne le QR code                               │\n";
echo "│    → Lecture du code du ticket                             │\n";
echo "│    → Vérification du statut (paid/scanned)                 │\n";
echo "│    → Autorisation ou refus d'entrée                        │\n";
echo "│                                                             │\n";
echo "│ 4️⃣  Marquage du ticket comme scanné                         │\n";
echo "│    → Status: paid → scanned                                │\n";
echo "│    → Empêche la double utilisation                         │\n";
echo "│                                                             │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "📊 ENDPOINTS TESTÉS:\n\n";

echo "✅ GET /api/public/tickets/{id}?token={magic_token}\n";
echo "   → Récupérer les infos du ticket (accès public)\n\n";

echo "✅ GET /api/tickets?code={code}\n";
echo "   → Rechercher un ticket par code (pour scan)\n\n";

echo "✅ GET /api/public/tickets/{id}/qr?token={magic_token}\n";
echo "   → Récupérer le QR code (base64)\n\n";

echo "✅ GET /api/public/tickets/{id}/qr/download?token={magic_token}\n";
echo "   → Télécharger le QR en PNG\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ TEST DE SCAN TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
