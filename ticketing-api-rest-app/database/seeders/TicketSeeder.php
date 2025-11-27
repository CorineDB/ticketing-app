<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tickets')->truncate('RESTART IDENTITY CASCADE');

        $events = Event::all();
        $ticketTypes = TicketType::all();
        $faker = Faker::create();

        if ($events->isEmpty() || $ticketTypes->isEmpty()) {
            $this->command->error('No events or ticket types found. Please run EventSeeder and TicketTypeSeeder first.');
            return;
        }

        $statuses = ['issued', 'reserved', 'paid'];
        $buyers = [
            ['name' => 'Alice Smith', 'email' => 'alice.smith@example.com'],
            ['name' => 'Bob Johnson', 'email' => 'bob.j@example.com'],
            ['name' => 'Charlie Brown', 'email' => 'charlie.b@example.com'],
            ['name' => 'Diana Prince', 'email' => 'diana.p@example.com'],
        ];

        for ($i = 0; $i < 100; $i++) { // Create 100 diverse tickets
            $randomEvent = $events->random();
            $availableTicketTypes = $ticketTypes->where('event_id', $randomEvent->id);

            if ($availableTicketTypes->isEmpty()) {
                continue; // Skip if no ticket types for this event
            }

            $randomTicketType = $availableTicketTypes->random();
            $randomStatus = $faker->randomElement($statuses);
            $randomBuyer = $faker->randomElement($buyers);

            Ticket::create([
                'id' => Str::uuid(),
                'event_id' => $randomEvent->id,
                'ticket_type_id' => $randomTicketType->id,
                'code' => 'TICKET-' . strtoupper(Str::random(8)),
                'qr_hmac' => hash('sha256', Str::random(40)),
                'status' => $randomStatus,
                'buyer_name' => $randomBuyer['name'],
                'buyer_email' => $randomBuyer['email'],
                'paid_at' => ($randomStatus === 'paid') ? $faker->dateTimeBetween('-1 month', 'now') : null,
                'issued_at' => $faker->dateTimeBetween('-2 months', '-1 month'),
            ]);
        }
    }
}