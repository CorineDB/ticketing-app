<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Reverted to Model
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Add this
use App\Models\Scopes\RoleOwnershipScope;

class Role extends Model // Reverted to extend Model
{
    use HasFactory, HasUuids;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new RoleOwnershipScope);
    }

    protected $fillable = [
        'name',
        'slug',
        'created_by',
    ];

    protected $keyType = 'string'; // Added for UUIDs
    public $incrementing = false;  // Added for UUIDs

    public function users(): HasMany // Reverted to HasMany, as it's my local relationship
    {
        return $this->hasMany(User::class);
    }

    // Add permissions relationship
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }
}
