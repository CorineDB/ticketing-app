<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketScanLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ticket_id',
        'agent_id',
        'gate_id',
        'scan_type',
        'scan_time',
        'result',
        'details',
        'metadata',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
        'details' => 'array',
        'metadata' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function gate(): BelongsTo
    {
        return $this->belongsTo(Gate::class);
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isOrganizer()) {
            return $query->whereHas('ticket.event', function ($q) use ($user) {
                $q->where('organisateur_id', $user->id);
            });
        }

        if ($user->isAgent()) {
            return $query->where('agent_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }
}
