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
        'description',
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

    protected $appends = [
        'quantity_available',
        'quantity_sold',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the number of tickets sold for this ticket type
     * Counts tickets with status: issued, reserved, paid, in, out
     */
    public function getQuantitySoldAttribute(): int
    {
        return $this->tickets()
            ->whereIn('status', ['issued', 'reserved', 'paid', 'in', 'out'])
            ->count();
    }

    /**
     * Get the number of tickets still available for this ticket type
     */
    public function getQuantityAvailableAttribute(): int
    {
        return max(0, $this->quota - $this->quantity_sold);
    }

    /**
     * Check if tickets are still available
     */
    public function hasAvailableTickets(): bool
    {
        return $this->quantity_available > 0;
    }
}
