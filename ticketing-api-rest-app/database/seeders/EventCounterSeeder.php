<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\EventCounter;
use App\Models\TicketScanLog;
use Illuminate\Support\Facades\DB;

class EventCounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('event_counters')->truncate('RESTART IDENTITY CASCADE');

        $events = Event::all();

        if ($events->isEmpty()) {
            $this->command->error('No events found. Please run EventSeeder first.');
            return;
        }

        foreach ($events as $event) {
            $entryScans = TicketScanLog::whereHas('ticket', function ($query) use ($event) {
                $query->where('event_id', $event->id);
            })->where('scan_type', 'entry')->count();

            $exitScans = TicketScanLog::whereHas('ticket', function ($query) use ($event) {
                $query->where('event_id', $event->id);
            })->where('scan_type', 'exit')->count();

            $currentIn = $entryScans - $exitScans;

            EventCounter::updateOrCreate(
                ['event_id' => $event->id],
                ['current_in' => max(0, $currentIn)] // Ensure current_in doesn't go below 0
            );
        }
    }
}