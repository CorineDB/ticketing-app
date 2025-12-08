<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrganizerScope implements Scope
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

        $isSuperAdmin = \DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', $user->id)
            ->where('roles.slug', 'super-admin')
            ->exists();

        //throw new \Exception("Is organisateur : " . $isOrganizer . " Is Super admin : " . $isSuperAdmin, 500);


        if ($isOrganizer) {
            // Filter users to show only those belonging to this organizer
            $builder->where('organisateur_id', $user->id);
        } elseif ($isSuperAdmin) {
            // Super admin: exclude organizers and super-admins
            $builder->where(function ($query) {
                $query->whereHas('role', function ($q) {
                    $q->whereNotIn('slug', ['organizer', 'super-admin']);
                })
                    ->orWhereDoesntHave('role');
            });
        }
    }
}
