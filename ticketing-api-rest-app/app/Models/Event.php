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
        'social_links',        // NEW
        'gallery_images',      // NEW
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
        'social_links' => 'array',      // NEW
        'gallery_images' => 'array',    // NEW
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

    // NEW: Gates relationship (Many-to-Many via pivot)
    public function gates(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Gate::class, 'event_gate')
            ->withPivot([
                'agent_id',
                'operational_status',
                'schedule',
                'ticket_type_ids',
                'max_capacity'
            ])
            ->withCasts([
                'schedule' => 'array',
                'ticket_type_ids' => 'array'
            ])
            ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($event) {
            // Generate slug if it's null or empty
            if (empty($event->slug) && !empty($event->title)) {
                $event->slug = \Illuminate\Support\Str::slug($event->title);

                // Ensure slug is unique
                $originalSlug = $event->slug;
                $count = 1;

                while (
                    static::where('slug', $event->slug)
                        ->where('id', '!=', $event->id)
                        ->exists()
                ) {
                    $event->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if (!$user) {
            // Unauthenticated users see all events
            return $query;
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

        // Other authenticated users (Agent, Cashier, Participant) see all events
        return $query;
    }
}
