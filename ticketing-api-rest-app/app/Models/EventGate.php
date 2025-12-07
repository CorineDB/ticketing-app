<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGate extends Model
{
    use HasUuids;

    protected $table = 'event_gate';

    protected $fillable = [
        'event_id',
        'gate_id',
        'agent_id',
        'operational_status',
        'schedule',
        'ticket_type_ids',
        'max_capacity',
    ];

    protected $casts = [
        'schedule' => 'array',
        'ticket_type_ids' => 'array',
        'max_capacity' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function gate(): BelongsTo
    {
        return $this->belongsTo(Gate::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get ticket types for this gate assignment
     */
    public function ticketTypes()
    {
        if (empty($this->ticket_type_ids)) {
            return collect([]);
        }

        return TicketType::whereIn('id', $this->ticket_type_ids)->get();
    }
}
