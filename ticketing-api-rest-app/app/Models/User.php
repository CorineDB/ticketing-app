<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Permission; // Add this line
use App\Models\Event; // Add this line for canManageThisEvent

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organisateur_id');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(TicketScanLog::class, 'agent_id');
    }

    // Méthode pour vérifier si l'utilisateur a un rôle spécifique
    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    // Méthode pour vérifier si l'utilisateur a une permission spécifique
    public function hasPermissionTo(string $permissionSlug): bool
    {
        // Vérifie si l'utilisateur a un rôle et si ce rôle a la permission via le slug
        return $this->role && $this->role->permissions()->where('slug', $permissionSlug)->exists();
    }

    // Méthode plus flexible pour vérifier si l'utilisateur a l'un des slugs de permission fournis
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        return $this->role && $this->role->permissions()->whereIn('slug', $permissionSlugs)->exists();
    }

    // Exemple de vérification de rôle
    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->slug === 'super-admin';
    }

    public function isOrganizer(): bool
    {
        return $this->role && $this->role->slug === 'organizer';
    }

    public function isAgent(): bool
    {
        return $this->role && $this->role->slug === 'agent-de-controle';
    }

    public function isAccountant(): bool
    {
        return $this->role && $this->role->slug === 'comptable';
    }

    // Example of a more complex permission check within the model
    public function canManageThisEvent(Event $event): bool
    {
        // A Super Admin can manage any event
        if ($this->isSuperAdmin()) {
            return true;
        }

        // An Organizer can manage events they organized or created
        if ($this->isOrganizer()) {
            // Check if user has permission to update events AND if they are the organizer/creator
            return $this->hasPermissionTo('update-events') && ($this->id === $event->organisateur_id || $this->id === $event->created_by);
        }

        // Other roles might have different logic
        return false;
    }
}
