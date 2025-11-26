<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'event_id',
        'ticket_type_id',
        'code',
        'qr_path',
        'qr_hmac',
        'magic_link_token',
        'status',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'issued_at',
        'paid_at',
        'used_count',
        'last_used_at',
        'gate_in',
        'last_gate_out',
        'metadata',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'last_used_at' => 'datetime',
        'used_count' => 'integer',
        'metadata' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function gateIn(): BelongsTo
    {
        return $this->belongsTo(Gate::class, 'gate_in');
    }

    public function gateOut(): BelongsTo
    {
        return $this->belongsTo(Gate::class, 'last_gate_out');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(TicketScanLog::class);
    }
}
