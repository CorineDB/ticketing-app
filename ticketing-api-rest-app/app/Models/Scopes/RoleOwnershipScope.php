<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class RoleOwnershipScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Skip scope during authentication to avoid circular dependency
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Check if user has organizer role using database query instead of model method
        $isOrganizer = \DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', $user->id)
            ->where('roles.slug', 'organizer')
            ->exists();

        if ($isOrganizer) {
            // Filter roles to show only those created by this organizer + system roles
            $builder->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereNull('created_by');
            });
        }
        // Super admin sees all roles (no filter)
    }
}
