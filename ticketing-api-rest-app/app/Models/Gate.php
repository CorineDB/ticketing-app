<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'location',
        'type',
        'status',
    ];

    // NEW: Events relationship (Many-to-Many via pivot)
    public function events(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_gate')
            ->withPivot([
                'agent_id',
                'operational_status',
                'schedule',
                'ticket_type_ids',
                'max_capacity'
            ])
            ->withTimestamps();
    }

    public function ticketsIn(): HasMany
    {
        return $this->hasMany(Ticket::class, 'gate_in');
    }

    public function ticketsOut(): HasMany
    {
        return $this->hasMany(Ticket::class, 'last_gate_out');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(TicketScanLog::class);
    }

    /**
     * Scope a query to only include gates accessible by the authenticated user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\User|null  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAuthUser($query, $user = null)
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // No user, no gates
        }

        if ($user->hasRole('super-admin')) {
            return $query; // Super admin sees all gates
        }

        if ($user->hasRole('organizer')) {
            // Organizers see gates for their events
            return $query->whereHas('events', function ($q) use ($user) {
                $q->where('organisateur_id', $user->id);
            });
        }

        if ($user->hasRole('agent-de-controle')) {
            // Agents see gates they are assigned to
            return $query->whereHas('events', function ($q) use ($user) {
                $q->whereHas('gates', function ($q2) use ($user) {
                    $q2->where('event_gate.agent_id', $user->id);
                });
            });
        }

        return $query->whereRaw('1 = 0'); // Default to no access
    }
}
