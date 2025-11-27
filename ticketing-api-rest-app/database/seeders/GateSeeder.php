<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('gates')->truncate('RESTART IDENTITY CASCADE');

        Gate::create([
            'id' => Str::uuid(),
            'name' => 'Main Entrance',
            'gate_type' => 'entrance',
            'status' => 'active',
        ]);

        Gate::create([
            'id' => Str::uuid(),
            'name' => 'VIP Entrance',
            'gate_type' => 'vip',
            'status' => 'active',
        ]);

        Gate::create([
            'id' => Str::uuid(),
            'name' => 'Main Exit',
            'gate_type' => 'exit',
            'status' => 'active',
        ]);
    }
}
