<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organisateur_id',
        'title',
        'description',
        'image_url',
        'start_datetime',
        'end_datetime',
        'location',
        'capacity',
        'timezone',
        'dress_code',
        'allow_reentry',
        'status',
        'created_by',
        'slug',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'allow_reentry' => 'boolean',
        'capacity' => 'integer',
    ];

    public function organisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organisateur_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function counter(): HasOne
    {
        return $this->hasOne(EventCounter::class);
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if (!$user) {
            // Unauthenticated users see only published events
            return $query->where('is_published', true)->where('status', 'published');
        }

        if ($user->isSuperAdmin()) {
            // Super Admin sees all events
            return $query;
        }

        if ($user->isOrganizer()) {
            // Organizer sees events they created or are the organisateur of
            return $query->where('organisateur_id', $user->id)
                         ->orWhere('created_by', $user->id);
        }

        // Other authenticated users (Agent, Cashier, Participant) see only published events
        return $query->where('is_published', true)->where('status', 'published');
    }
}
