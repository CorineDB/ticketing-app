<?php

namespace App\Console\Commands;

use App\Models\Gate;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateGateStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gates:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update gate statuses based on their operational hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $updated = 0;

        // Get all active gates
        $gates = Gate::where('status', '!=', 'inactive')->get();

        foreach ($gates as $gate) {
            $oldStatus = $gate->status;
            $newStatus = $this->determineStatus($gate, $now);

            if ($oldStatus !== $newStatus) {
                $gate->status = $newStatus;
                $gate->save();
                $updated++;

                $this->info("Gate '{$gate->name}': {$oldStatus} → {$newStatus}");
            }
        }

        $this->info("✅ Updated {$updated} gate(s) status");
        return Command::SUCCESS;
    }

    /**
     * Determine the status of a gate based on current time
     * Status values: 'active', 'pause', 'inactive'
     */
    private function determineStatus(Gate $gate, Carbon $now): string
    {
        // If gate is inactive, don't change it
        if ($gate->status === 'inactive') {
            return 'inactive';
        }

        // Check if gate has operational hours defined
        // For now, we'll keep gates as 'active' during event hours
        // You can extend this logic based on your requirements

        return 'active';
    }
}
