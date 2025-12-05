<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'fedapay_transaction_id',
        'fedapay_reference',
        'amount',
        'currency',
        'status',
        'event_id',
        'event_title',
        'event_start_date',
        'event_end_date',
        'event_location',
        'customer_firstname',
        'customer_lastname',
        'customer_email',
        'customer_phone',
        'ticket_count',
        'ticket_types_summary',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'ticket_count' => 'integer',
        'metadata' => 'array',
        'ticket_types_summary' => 'array',
        'event_start_date' => 'datetime',
        'event_end_date' => 'datetime',
        'paid_at' => 'datetime',
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
     * Scope pour les paiements approuvés
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope pour les paiements en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Vérifier si le paiement est approuvé
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Marquer comme approuvé
     */
    public function markAsApproved(): void
    {
        $this->update([
            'status' => 'approved',
            'paid_at' => now(),
        ]);
    }

    /**
     * Marquer comme annulé
     */
    public function markAsCanceled(): void
    {
        $this->update(['status' => 'canceled']);
    }
}
