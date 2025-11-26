<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCounter extends Model
{
    use HasUuids;

    protected $primaryKey = 'event_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'current_in',
        'updated_at',
    ];

    protected $casts = [
        'current_in' => 'integer',
        'updated_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
