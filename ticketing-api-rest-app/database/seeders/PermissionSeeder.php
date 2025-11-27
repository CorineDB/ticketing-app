<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate existing permissions and role_permissions tables
        DB::table('role_permissions')->truncate('RESTART IDENTITY CASCADE');
        DB::table('permissions')->truncate('RESTART IDENTITY CASCADE');

        $permissionsData = [
            // Utilisateurs
            ['name' => 'view_users', 'slug' => 'view-users', 'description' => 'Voir les utilisateurs'],
            ['name' => 'manage_users', 'slug' => 'manage-users', 'description' => 'Gérer les utilisateurs (créer, modifier, supprimer)'],
            ['name' => 'assign_roles', 'slug' => 'assign-roles', 'description' => 'Assigner des rôles aux utilisateurs'],

            // Rôles
            ['name' => 'view_roles', 'slug' => 'view-roles', 'description' => 'Voir les rôles'],
            ['name' => 'manage_roles', 'slug' => 'manage-roles', 'description' => 'Gérer les rôles (créer, modifier, supprimer)'],

            // Permissions (gestion des permissions elles-mêmes) - typically only for super-admin
            ['name' => 'view_permissions', 'slug' => 'view-permissions', 'description' => 'Voir les permissions'],
            ['name' => 'manage_permissions', 'slug' => 'manage-permissions', 'description' => 'Gérer les permissions'],

            // Événements
            ['name' => 'view_events', 'slug' => 'view-events', 'description' => 'Voir les événements'],
            ['name' => 'create_events', 'slug' => 'create-events', 'description' => 'Créer des événements'],
            ['name' => 'update_events', 'slug' => 'update-events', 'description' => 'Modifier des événements'],
            ['name' => 'delete_events', 'slug' => 'delete-events', 'description' => 'Supprimer des événements'],
            ['name' => 'manage_event_ticket_types', 'slug' => 'manage-event-ticket-types', 'description' => 'Gérer les types de tickets d\'un événement'],
            ['name' => 'view_event_stats', 'slug' => 'view-event-stats', 'description' => 'Voir les statistiques d\'un événement'],

            // Tickets
            ['name' => 'view_tickets', 'slug' => 'view-tickets', 'description' => 'Voir les tickets'],
            ['name' => 'create_tickets', 'slug' => 'create-tickets', 'description' => 'Créer des tickets'],
            ['name' => 'update_tickets', 'slug' => 'update-tickets', 'description' => 'Modifier des tickets'],
            ['name' => 'delete_tickets', 'slug' => 'delete-tickets', 'description' => 'Supprimer des tickets'],
            ['name' => 'mark_tickets_paid', 'slug' => 'mark-tickets-paid', 'description' => 'Marquer des tickets comme payés'],
            ['name' => 'generate_qr_codes', 'slug' => 'generate-qr-codes', 'description' => 'Générer des codes QR pour les tickets'],

            // Portes d\'accès (Gates)
            ['name' => 'view_gates', 'slug' => 'view-gates', 'description' => 'Voir les portes d\'accès'],
            ['name' => 'manage_gates', 'slug' => 'manage-gates', 'description' => 'Gérer les portes d\'accès (créer, modifier, supprimer)'],

            // Scans (Contrôle d\'accès)
            ['name' => 'perform_scan', 'slug' => 'perform-scan', 'description' => 'Effectuer un scan de ticket (entrée/sortie)'],
            ['name' => 'view_scan_logs', 'slug' => 'view-scan-logs', 'description' => 'Voir les logs de scan'],

            // Webhooks
            ['name' => 'manage_webhooks', 'slug' => 'manage-webhooks', 'description' => 'Gérer les webhooks (paiement, etc.)'],
        ];

        $createdPermissions = [];
        foreach ($permissionsData as $data) {
            $permission = Permission::create(array_merge($data, ['id' => Str::uuid()]));
            $createdPermissions[$permission->name] = $permission;
        }

        // Assign permissions to roles
        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $organizerRole = Role::where('slug', 'organizer')->firstOrFail();
        $agentRole = Role::where('slug', 'agent-de-controle')->firstOrFail();
        $comptableRole = Role::where('slug', 'comptable')->firstOrFail();
        // Participant role typically has no extra permissions through roles, just their own tickets.

        // Super Admin gets all permissions
        $superAdminRole->permissions()->attach(Permission::all()->pluck('id')->toArray());

        // Organizer permissions
        $organizerPermissionsNames = [
            'view_users', 'manage_users',
            'view_roles',
            'view_events', 'create_events', 'update_events', 'delete_events',
            'manage_event_ticket_types', 'view_event_stats',
            'view_tickets', 'create_tickets', 'update_tickets', 'delete_tickets',
            'mark_tickets_paid', 'generate_qr_codes',
            'view_gates', 'manage_gates',
            'view_scan_logs',
            'manage_webhooks',
        ];
        $organizerRole->permissions()->attach(array_map(function($name) use ($createdPermissions) {
            return $createdPermissions[$name]->id;
        }, $organizerPermissionsNames));

        // Agent de Controle permissions
        $agentPermissionsNames = [
            'perform_scan',
            'view_scan_logs',
            'view_tickets',
            'view_events',
            'view_gates',
        ];
        $agentRole->permissions()->attach(array_map(function($name) use ($createdPermissions) {
            return $createdPermissions[$name]->id;
        }, $agentPermissionsNames));

        // Comptable permissions
        $comptablePermissionsNames = [
            'view_tickets',
            'view_event_stats',
            'mark_tickets_paid',
            'view_events',
        ];
        $comptableRole->permissions()->attach(array_map(function($name) use ($createdPermissions) {
            return $createdPermissions[$name]->id;
        }, $comptablePermissionsNames));
    }
}
