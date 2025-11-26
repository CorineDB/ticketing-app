<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'validity_from',
        'validity_to',
        'usage_limit',
        'quota',
        'payment_url',
        'payment_transaction_id',
        'payment_token',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'validity_from' => 'datetime',
        'validity_to' => 'datetime',
        'usage_limit' => 'integer',
        'quota' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
