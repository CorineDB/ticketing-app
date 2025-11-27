/**
 * Application Permissions
 * These permissions must match the backend permissions exactly
 */

export const PERMISSIONS = {
  // Utilisateurs
  VIEW_USERS: 'view_users',
  MANAGE_USERS: 'manage_users',
  ASSIGN_ROLES: 'assign_roles',

  // Rôles
  VIEW_ROLES: 'view_roles',
  MANAGE_ROLES: 'manage_roles',

  // Permissions
  VIEW_PERMISSIONS: 'view_permissions',
  MANAGE_PERMISSIONS: 'manage_permissions',

  // Événements
  VIEW_EVENTS: 'view_events',
  CREATE_EVENTS: 'create_events',
  UPDATE_EVENTS: 'update_events',
  DELETE_EVENTS: 'delete_events',
  MANAGE_EVENT_TICKET_TYPES: 'manage_event_ticket_types',
  VIEW_EVENT_STATS: 'view_event_stats',

  // Tickets
  VIEW_TICKETS: 'view_tickets',
  CREATE_TICKETS: 'create_tickets',
  UPDATE_TICKETS: 'update_tickets',
  DELETE_TICKETS: 'delete_tickets',
  MARK_TICKETS_PAID: 'mark_tickets_paid',
  GENERATE_QR_CODES: 'generate_qr_codes',

  // Portes d'accès (Gates)
  VIEW_GATES: 'view_gates',
  MANAGE_GATES: 'manage_gates',

  // Scans (Contrôle d'accès)
  PERFORM_SCAN: 'perform_scan',
  VIEW_SCAN_LOGS: 'view_scan_logs',

  // Webhooks
  MANAGE_WEBHOOKS: 'manage_webhooks'
} as const

export type Permission = typeof PERMISSIONS[keyof typeof PERMISSIONS]

/**
 * Permission descriptions (for UI display)
 */
export const PERMISSION_DESCRIPTIONS: Record<Permission, string> = {
  // Utilisateurs
  [PERMISSIONS.VIEW_USERS]: 'Voir les utilisateurs',
  [PERMISSIONS.MANAGE_USERS]: 'Gérer les utilisateurs (créer, modifier, supprimer)',
  [PERMISSIONS.ASSIGN_ROLES]: 'Assigner des rôles aux utilisateurs',

  // Rôles
  [PERMISSIONS.VIEW_ROLES]: 'Voir les rôles',
  [PERMISSIONS.MANAGE_ROLES]: 'Gérer les rôles (créer, modifier, supprimer)',

  // Permissions
  [PERMISSIONS.VIEW_PERMISSIONS]: 'Voir les permissions',
  [PERMISSIONS.MANAGE_PERMISSIONS]: 'Gérer les permissions',

  // Événements
  [PERMISSIONS.VIEW_EVENTS]: 'Voir les événements',
  [PERMISSIONS.CREATE_EVENTS]: 'Créer des événements',
  [PERMISSIONS.UPDATE_EVENTS]: 'Modifier des événements',
  [PERMISSIONS.DELETE_EVENTS]: 'Supprimer des événements',
  [PERMISSIONS.MANAGE_EVENT_TICKET_TYPES]: "Gérer les types de tickets d'un événement",
  [PERMISSIONS.VIEW_EVENT_STATS]: "Voir les statistiques d'un événement",

  // Tickets
  [PERMISSIONS.VIEW_TICKETS]: 'Voir les tickets',
  [PERMISSIONS.CREATE_TICKETS]: 'Créer des tickets',
  [PERMISSIONS.UPDATE_TICKETS]: 'Modifier des tickets',
  [PERMISSIONS.DELETE_TICKETS]: 'Supprimer des tickets',
  [PERMISSIONS.MARK_TICKETS_PAID]: 'Marquer des tickets comme payés',
  [PERMISSIONS.GENERATE_QR_CODES]: 'Générer des codes QR pour les tickets',

  // Portes d'accès
  [PERMISSIONS.VIEW_GATES]: "Voir les portes d'accès",
  [PERMISSIONS.MANAGE_GATES]: "Gérer les portes d'accès (créer, modifier, supprimer)",

  // Scans
  [PERMISSIONS.PERFORM_SCAN]: 'Effectuer un scan de ticket (entrée/sortie)',
  [PERMISSIONS.VIEW_SCAN_LOGS]: 'Voir les logs de scan',

  // Webhooks
  [PERMISSIONS.MANAGE_WEBHOOKS]: 'Gérer les webhooks (paiement, etc.)'
}

/**
 * Permission groups for UI organization
 */
export const PERMISSION_GROUPS = {
  users: {
    label: 'Utilisateurs',
    permissions: [
      PERMISSIONS.VIEW_USERS,
      PERMISSIONS.MANAGE_USERS,
      PERMISSIONS.ASSIGN_ROLES
    ]
  },
  roles: {
    label: 'Rôles',
    permissions: [
      PERMISSIONS.VIEW_ROLES,
      PERMISSIONS.MANAGE_ROLES
    ]
  },
  permissions: {
    label: 'Permissions',
    permissions: [
      PERMISSIONS.VIEW_PERMISSIONS,
      PERMISSIONS.MANAGE_PERMISSIONS
    ]
  },
  events: {
    label: 'Événements',
    permissions: [
      PERMISSIONS.VIEW_EVENTS,
      PERMISSIONS.CREATE_EVENTS,
      PERMISSIONS.UPDATE_EVENTS,
      PERMISSIONS.DELETE_EVENTS,
      PERMISSIONS.MANAGE_EVENT_TICKET_TYPES,
      PERMISSIONS.VIEW_EVENT_STATS
    ]
  },
  tickets: {
    label: 'Tickets',
    permissions: [
      PERMISSIONS.VIEW_TICKETS,
      PERMISSIONS.CREATE_TICKETS,
      PERMISSIONS.UPDATE_TICKETS,
      PERMISSIONS.DELETE_TICKETS,
      PERMISSIONS.MARK_TICKETS_PAID,
      PERMISSIONS.GENERATE_QR_CODES
    ]
  },
  gates: {
    label: "Portes d'accès",
    permissions: [
      PERMISSIONS.VIEW_GATES,
      PERMISSIONS.MANAGE_GATES
    ]
  },
  scans: {
    label: 'Contrôle d\'accès',
    permissions: [
      PERMISSIONS.PERFORM_SCAN,
      PERMISSIONS.VIEW_SCAN_LOGS
    ]
  },
  webhooks: {
    label: 'Webhooks',
    permissions: [
      PERMISSIONS.MANAGE_WEBHOOKS
    ]
  }
}

/**
 * Default permissions by user type (for reference)
 * The actual permissions are managed on the backend
 */
export const DEFAULT_PERMISSIONS_BY_TYPE = {
  super-admin: Object.values(PERMISSIONS), // All permissions

  organizer: [
    PERMISSIONS.VIEW_EVENTS,
    PERMISSIONS.CREATE_EVENTS,
    PERMISSIONS.UPDATE_EVENTS,
    PERMISSIONS.DELETE_EVENTS,
    PERMISSIONS.MANAGE_EVENT_TICKET_TYPES,
    PERMISSIONS.VIEW_EVENT_STATS,
    PERMISSIONS.VIEW_TICKETS,
    PERMISSIONS.CREATE_TICKETS,
    PERMISSIONS.UPDATE_TICKETS,
    PERMISSIONS.DELETE_TICKETS,
    PERMISSIONS.MARK_TICKETS_PAID,
    PERMISSIONS.GENERATE_QR_CODES,
    PERMISSIONS.VIEW_GATES,
    PERMISSIONS.MANAGE_GATES,
    PERMISSIONS.VIEW_SCAN_LOGS,
    PERMISSIONS.VIEW_USERS,
    PERMISSIONS.MANAGE_USERS
  ],

  'agent-de-controle': [
    PERMISSIONS.PERFORM_SCAN,
    PERMISSIONS.VIEW_SCAN_LOGS,
    PERMISSIONS.VIEW_GATES
  ],

  comptable: [
    PERMISSIONS.VIEW_TICKETS,
    PERMISSIONS.CREATE_TICKETS,
    PERMISSIONS.MARK_TICKETS_PAID,
    PERMISSIONS.VIEW_EVENTS
  ],

  participant: [
    PERMISSIONS.VIEW_EVENTS
  ]
}
