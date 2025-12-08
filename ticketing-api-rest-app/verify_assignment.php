<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Event;
use App\Models\Gate;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

// Create or get agent role
$agentRole = Role::firstOrCreate(
    ['slug' => 'agent-de-controle'],
    ['name' => 'Agent de Contrôle', 'permissions' => []]
);

// Create agent user
$agent = User::create([
    'name' => 'Test Agent',
    'email' => 'agent_' . uniqid() . '@test.com',
    'password' => bcrypt('password'),
    'role_id' => $agentRole->id
]);

// Create event
$event = Event::create([
    'title' => 'Test Event',
    'start_datetime' => now(),
    'end_datetime' => now()->addDays(1),
    'organisateur_id' => $agent->id // Just assigning to agent for simplicity, or create another user
]);

// Create gate attached to event
$gate = Gate::create([
    'name' => 'Test Gate',
    'status' => 'active',
    'type' => 'entry' // Assuming 'type' is required
]);
$event->gates()->attach($gate->id);

echo "Created Agent: {$agent->id}\n";
echo "Created Event: {$event->id}\n";
echo "Created Gate: {$gate->id}\n";

// Test Assignment Logic (simulating Controller)
echo "Assigning agent to gate...\n";

$event->gates()->updateExistingPivot($gate->id, [
    'agent_id' => $agent->id
]);

// Verify
$updatedGate = $event->gates()
    ->withPivot(['agent_id'])
    ->where('gates.id', $gate->id)
    ->first();

if ($updatedGate->pivot->agent_id == $agent->id) {
    echo "SUCCESS: Agent assigned correctly in DB.\n";
} else {
    echo "FAILURE: Agent ID mismatch. Expected {$agent->id}, got {$updatedGate->pivot->agent_id}\n";
}

// Test Controller Response Logic
$gateWithAgent = $event->gates()
    ->withPivot(['agent_id'])
    ->where('gates.id', $gate->id)
    ->first();

if ($gateWithAgent && $gateWithAgent->pivot && $gateWithAgent->pivot->agent_id) {
    $gateWithAgent->pivot->setRelation('agent', User::find($gateWithAgent->pivot->agent_id));
}

if ($gateWithAgent->pivot->agent) {
    echo "SUCCESS: Agent relation loaded on pivot.\n";
    echo "Agent Name: " . $gateWithAgent->pivot->agent->name . "\n";
} else {
    echo "FAILURE: Agent relation NOT loaded on pivot.\n";
}

// Cleanup
$event->gates()->detach($gate->id);
$gate->delete();
$event->delete();
$agent->delete();