<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketType;
use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $conferenceEvent = Event::where('title', 'Laravel Conference 2025')->first();
        $festivalEvent = Event::where('title', 'Electronic Music Festival')->first();

        if (!$conferenceEvent || !$festivalEvent) {
            $this->command->error('Events not found. Please run EventSeeder first.');
            return;
        }

        DB::table('ticket_types')->truncate('RESTART IDENTITY CASCADE');

        // Ticket types for Laravel Conference
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $conferenceEvent->id,
            'name' => 'General Admission',
            'price' => 299.00,
            'quota' => 400,
        ]);

        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $conferenceEvent->id,
            'name' => 'VIP Pass',
            'price' => 599.00,
            'quota' => 100,
        ]);

        // Ticket types for Electronic Music Festival
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $festivalEvent->id,
            'name' => 'Weekend Pass',
            'price' => 150.00,
            'quota' => 8000,
        ]);

        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $festivalEvent->id,
            'name' => 'VIP Weekend Pass',
            'price' => 350.00,
            'quota' => 2000,
        ]);
    }
}
