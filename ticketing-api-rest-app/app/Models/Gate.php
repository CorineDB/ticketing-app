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
        'gate_type',
        'status',
    ];

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
}
