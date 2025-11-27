<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organizer = User::where('email', 'organizer@example.com')->first();

        if (!$organizer) {
            $this->command->error('Organizer user not found. Please run UserSeeder first.');
            return;
        }

        DB::table('events')->truncate('RESTART IDENTITY CASCADE');

        Event::create([
            'id' => Str::uuid(),
            'title' => 'Laravel Conference 2025',
            'description' => 'Annual conference for Laravel developers.',
            'start_datetime' => Carbon::now()->addMonths(2)->setHour(9)->setMinute(0),
            'end_datetime' => Carbon::now()->addMonths(2)->addDays(1)->setHour(17)->setMinute(0),
            'location' => 'New York, NY',
            'capacity' => 500,
            'organisateur_id' => $organizer->id,
            'created_by' => $organizer->id,
            'allow_reentry' => true,
        ]);

        Event::create([
            'id' => Str::uuid(),
            'title' => 'Electronic Music Festival',
            'description' => 'A weekend of the best electronic music.',
            'start_datetime' => Carbon::now()->addMonths(3)->setHour(18)->setMinute(0),
            'end_datetime' => Carbon::now()->addMonths(3)->addDays(2)->setHour(23)->setMinute(59),
            'location' => 'Miami, FL',
            'capacity' => 10000,
            'organisateur_id' => $organizer->id,
            'created_by' => $organizer->id,
            'allow_reentry' => false,
        ]);
    }
}
