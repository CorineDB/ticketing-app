<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$gateId = 'acac322c-97a5-4887-b33a-6296cbd57060';

$gate = \App\Models\Gate::find($gateId);

if ($gate) {
    echo "Gate found: " . $gate->name . "\n";
} else {
    echo "Gate NOT found.\n";
    echo "Existing gates:\n";
    $gates = \App\Models\Gate::all();
    foreach ($gates as $g) {
        echo $g->id . " - " . $g->name . "\n";
    }
}
