<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ticket = \App\Models\Ticket::with(['event', 'ticketType'])
    ->where('status', 'paid')
    ->first();

if (!$ticket) {
    $ticket = \App\Models\Ticket::with(['event', 'ticketType'])->first();
}

if ($ticket) {
    if (!$ticket->magic_link_token) {
        $ticket->magic_link_token = \Illuminate\Support\Str::random(64);
        $ticket->save();
    }
    echo 'http://localhost:5173/public/tickets/' . $ticket->id . '?token=' . $ticket->magic_link_token . "\n";
} else {
    echo "No tickets found in database\n";
}
