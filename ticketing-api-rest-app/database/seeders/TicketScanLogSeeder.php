<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\TicketScanLog;
use App\Models\Gate;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class TicketScanLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ticket_scan_logs')->truncate('RESTART IDENTITY CASCADE');

        $faker = Faker::create();
        $paidTickets = Ticket::where('status', 'paid')->get();
        $gates = Gate::all();
        $agentRole = Role::where('slug', 'agent-de-controle')->first();
        $agents = $agentRole ? User::where('role_id', $agentRole->id)->get() : User::where('email', 'admin@example.com')->get(); // Fallback to admin if no agents

        if ($paidTickets->isEmpty() || $gates->isEmpty() || $agents->isEmpty()) {
            $this->command->warn('Skipping TicketScanLogSeeder: Not enough paid tickets, gates, or agents available.');
            return;
        }

        // Simulate scans for a percentage of paid tickets
        $ticketsToScan = $paidTickets->random(min(count($paidTickets), (int) (count($paidTickets) * 0.7))); // Scan up to 70% of paid tickets

        foreach ($ticketsToScan as $ticket) {
            $agent = $agents->random();
            $gateEntry = $gates->where('gate_type', 'entrance')->random();
            $gateExit = $gates->where('gate_type', 'exit')->random();

            // Simulate Entry Scan
            $scanTimeEntry = $faker->dateTimeBetween($ticket->paid_at ?? '-1 week', 'now');
            TicketScanLog::create([
                'id' => Str::uuid(),
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
                'gate_id' => $gateEntry->id,
                'scan_type' => 'entry',
                'scan_time' => $scanTimeEntry,
                'result' => 'ok',
            ]);

            // Update ticket status for entry
            $ticket->status = 'in';
            $ticket->used_count += 1;
            $ticket->gate_in = $gateEntry->id;
            $ticket->last_used_at = $scanTimeEntry;
            $ticket->save();

            // Randomly simulate an Exit Scan for some tickets (e.g., 50% of scanned tickets)
            if ($faker->boolean(50)) { // 50% chance to have an exit scan
                $scanTimeExit = $faker->dateTimeBetween($scanTimeEntry, 'now');
                TicketScanLog::create([
                    'id' => Str::uuid(),
                    'ticket_id' => $ticket->id,
                    'agent_id' => $agent->id,
                    'gate_id' => $gateExit->id,
                    'scan_type' => 'exit',
                    'scan_time' => $scanTimeExit,
                    'result' => 'ok',
                ]);

                // Update ticket status for exit
                $ticket->status = 'out';
                $ticket->last_gate_out = $gateExit->id;
                $ticket->last_used_at = $scanTimeExit;
                $ticket->save();
            }
        }
    }
}
