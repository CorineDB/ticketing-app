<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateEventStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update event statuses based on their start and end dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $updated = 0;

        // Get all events
        $events = Event::withoutGlobalScopes()->get();

        foreach ($events as $event) {
            $oldStatus = $event->status;
            $newStatus = $this->determineStatus($event, $now);

            if ($oldStatus !== $newStatus) {
                $event->status = $newStatus;
                $event->save();
                $updated++;

                $this->info("Event '{$event->title}': {$oldStatus} → {$newStatus}");
            }
        }

        $this->info("✅ Updated {$updated} event(s) status");
        return Command::SUCCESS;
    }

    /**
     * Determine the status of an event based on current time
     */
    private function determineStatus(Event $event, Carbon $now): string
    {
        // Don't change draft or cancelled events
        if (in_array($event->status, ['draft', 'cancelled'])) {
            return $event->status;
        }

        $startDate = Carbon::parse($event->start_datetime);
        $endDate = Carbon::parse($event->end_datetime);

        if ($now->between($startDate, $endDate)) {
            return 'ongoing';
        } elseif ($now->gt($endDate)) {
            return 'completed';
        } else {
            // Event hasn't started yet, keep as published if it was published
            return $event->status === 'published' ? 'published' : $event->status;
        }
    }
}
