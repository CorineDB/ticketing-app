<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ticketTypeId = '019ac8e5-5344-7125-95e5-68dfd4a3a654';
$increaseBy = 100;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        AUGMENTATION DU QUOTA DE TICKETS                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$ticketType = \App\Models\TicketType::find($ticketTypeId);

if (!$ticketType) {
    echo "❌ Ticket Type avec ID '$ticketTypeId' non trouvé.\n";
    exit(1);
}

echo "Ticket Type: '{$ticketType->name}' (ID: $ticketTypeId)\n";
echo "  Quota actuel: {$ticketType->quota}\n";

$ticketType->quota += $increaseBy;
$ticketType->save();

echo "  Nouveau quota: {$ticketType->quota}\n\n";

echo "✅ Quota augmenté avec succès de $increaseBy.\n";

