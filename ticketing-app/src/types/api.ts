/**
 * TypeScript Type Definitions for Ticketing App
 * Following Sirene Vue3 Architecture Pattern
 */

// ==================== AUTH & USER TYPES ====================

export type UserType = 'super-admin' | 'organizer' | 'agent-de-controle' | 'comptable' | 'participant'

export interface User {
  id: string
  name: string
  email: string
  phone?: string
  type: UserType
  avatar?: string
  organisateur_id?: string
  organisateur?: User
  role_id?: string
  role?: Role
  permissions?: Permission[]
  status?: 'active' | 'inactive' | 'suspended'
  created_at: string
  updated_at: string
  last_login_at?: string
}

export interface Role {
  id: string
  name: string
  slug: string
  description?: string
  permissions: Permission[]
  permission_ids?: string[]
  created_at: string
  updated_at: string
}

export interface Permission {
  id: string
  name: string
  slug: string
  description?: string
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface OTPRequest {
  phone: string
}

export interface OTPVerification {
  phone: string
  otp: string
}

export interface AuthResponse {
  access_token: string
  token_type: string
  expires_in: number
  user: User
}

export interface UserFilters extends BaseFilters {
  role_id?: string;
  status?: 'active' | 'inactive' | 'suspended';
}

// ==================== ORGANIZATION TYPES ====================

export interface Organization {
  id: string
  name: string
  slug: string
  logo?: string
  description?: string
  email: string
  phone: string
  address?: string
  website?: string
  status: 'active' | 'suspended' | 'inactive'
  owner_id: string
  owner?: User
  events_count?: number
  users_count?: number
  total_revenue?: number
  created_at: string
  updated_at: string
}

export interface CreateOrganizationData {
  name: string
  email: string
  phone: string
  description?: string
  logo?: File
  address?: string
  website?: string
}

export interface UpdateOrganizationData extends Partial<CreateOrganizationData> { }

export interface OrganizationFilters {
  search?: string
  status?: 'active' | 'suspended' | 'inactive'
}

// ==================== EVENT TYPES ====================

export type EventStatus = 'draft' | 'published' | 'ongoing' | 'completed' | 'cancelled'

export interface Event {
  id: string
  organisateur_id: string
  organisateur?: User
  title: string
  slug: string
  description?: string
  image_url?: string
  social_links?: {
    facebook?: string
    instagram?: string
    twitter?: string
    linkedin?: string
    tiktok?: string
    website?: string
  }
  gallery_images?: string[]
  location: string
  venue: string
  address?: string
  city?: string
  country?: string
  start_datetime: string
  end_datetime: string
  start_time: string
  end_time?: string
  capacity: number
  current_in: number // Current attendance count (atomic via Redis)
  dress_code?: string
  allow_reentry: boolean // Allow tickets to scan in/out multiple times
  status: EventStatus
  is_published: boolean
  ticket_types?: TicketType[]
  gates?: Gate[]
  tickets_sold?: number
  tickets_scanned?: number
  revenue?: number
  created_by: number
  creator?: User
  created_at: string
  updated_at: string
}

export interface CreateEventData {
  title: string
  description?: string
  image_url?: string | File
  location?: string
  start_datetime: string
  end_datetime: string
  capacity: number
  timezone?: string
  dress_code?: string
  allow_reentry?: boolean
  ticket_types?: Array<{
    name: string
    price?: number
    validity_from?: string
    validity_to?: string
    usage_limit?: number
    quota?: number
  }>
}

export interface UpdateEventData extends Partial<CreateEventData> {
  status?: EventStatus
  is_published?: boolean
}

export interface EventFilters {
  status?: EventStatus
  organisateur_id?: string
  start_date?: string
  end_date?: string
  city?: string
  search?: string
}

// ==================== TICKET TYPE TYPES ====================

export interface TicketType {
  id: string
  event_id: string
  event?: Event
  name: string
  description?: string
  price: number
  currency: string
  usage_limit: number
  quantity: number
  quota: number  // Total quota for this ticket type
  quantity_sold: number
  quantity_available: number
  color?: string
  benefits?: string[]
  is_active: boolean
  sale_start_date?: string
  sale_end_date?: string
  validity_from?: string // Ticket valid from this date/time
  validity_to?: string // Ticket valid until this date/time
  created_at: string
  updated_at: string
}

export interface CreateTicketTypeData {
  event_id: string
  name: string
  description?: string
  price: number
  currency?: string
  quantity: number
  color?: string
  benefits?: string[]
  sale_start_date?: string
  sale_end_date?: string
  validity_from?: string
  validity_to?: string
}

export interface UpdateTicketTypeData extends Partial<CreateTicketTypeData> {
  is_active?: boolean
}

// ==================== GATE TYPES ====================

export type GateType = 'entrance' | 'exit' | 'vip' | 'other'
export type GateStatus = 'active' | 'inactive'

export interface Gate {
  id: string
  name: string
  type: GateType  // Changed from type
  location?: string
  status: GateStatus
  created_at: string
  updated_at: string
}

export interface CreateGateData {
  event_id?: string
  name: string
  type: GateType
  location?: string
  status?: GateStatus
  scanner_id?: number
}

export interface UpdateGateData extends Partial<CreateGateData> { }

export interface GateFilters {
  event_id?: string
  type?: GateType
  status?: GateStatus
  scanner_id?: number
}

// ==================== TICKET TYPES ====================

export type TicketStatus = 'issued' | 'reserved' | 'paid' | 'in' | 'out' | 'invalid' | 'refunded'
export type PaymentMethod = 'online' | 'cash' | 'mobile_money' | 'bank_transfer'
export type PaymentProvider = 'paydunya' | 'cinetpay' | 'mtn_momo' | 'manual'

export interface Ticket {
  id: string
  ticket_type_id: string
  ticket_type?: TicketType
  event_id: string
  event?: Event
  order_id?: number
  order?: Order
  code: string // Unique QR code identifier
  qr_code: string // Base64 QR code image or URL
  qr_path: string // Path to stored QR code image
  qr_hmac: string // HMAC signature: HMAC_SHA256(ticket_id|event_id, SECRET)
  magic_link_token: string // Public access token for viewing ticket without login
  buyer_name: string
  buyer_email: string // Changed from buyer_email
  buyer_phone?: string
  price: number
  currency: string
  status: TicketStatus
  payment_method?: PaymentMethod
  payment_provider?: PaymentProvider
  payment_reference?: string
  payment_url?: string
  paid_at?: string
  used_at?: string
  used_count: number // Number of times scanned (for re-entry tracking)
  scanned_by?: number
  scanner?: User
  gate_in?: number // Last entry gate ID
  last_gate_out?: number // Last exit gate ID
  entry_time?: string
  exit_time?: string
  notes?: string
  created_at: string
  updated_at: string
}

export interface CreateTicketData {
  ticket_type_id: string
  buyer_name: string
  buyer_email: string
  buyer_phone?: string
  payment_method?: PaymentMethod
  payment_reference?: string
}

export interface UpdateTicketData {
  buyer_name?: string
  buyer_email?: string
  buyer_phone?: string
  status?: TicketStatus
  payment_reference?: string
  payment_url?: string
  notes?: string
}

export interface TicketFilters {
  event_id?: string
  ticket_type_id?: number
  status?: TicketStatus
  buyer_email?: string
  search?: string
  paid?: boolean
  used?: boolean
}

// ==================== ORDER TYPES ====================

export type OrderStatus = 'pending' | 'completed' | 'failed' | 'cancelled' | 'refunded'

export interface Order {
  id: string
  order_number: string
  event_id: string
  event?: Event
  customer_name: string
  customer_email: string
  customer_phone?: string
  total_amount: number
  currency: string
  payment_method: PaymentMethod
  payment_provider: PaymentProvider
  payment_reference?: string
  payment_url?: string
  status: OrderStatus
  tickets?: Ticket[]
  tickets_count: number
  paid_at?: string
  created_at: string
  updated_at: string
}

export interface CreateOrderData {
  event_id: string
  customer_name: string
  customer_email: string
  customer_phone?: string
  ticket_items: {
    ticket_type_id: string
    quantity: number
  }[]
  payment_method: PaymentMethod
  payment_provider?: PaymentProvider
}

export interface OrderFilters {
  event_id?: string
  status?: OrderStatus
  customer_email?: string
  search?: string
}

// ==================== SCAN TYPES ====================

export type ScanType = 'entry' | 'exit'
export type ScanResult = 'valid' | 'invalid' | 'already_used' | 'expired' | 'wrong_event' | 'capacity_full'

export interface Scan {
  id: string
  ticket_id: string
  ticket?: Ticket
  event_id: string
  event?: Event
  gate_id?: number // Gate where scan occurred
  gate?: Gate
  scanner_id: string
  scanner?: User
  scan_type: ScanType
  result: ScanResult
  nonce: string // Unique identifier for this scan attempt (anti-replay)
  scanned_at: string
  location?: string
  device_info?: string
  notes?: string
  error_message?: string // Added this line
}

// 2-STEP SCAN WORKFLOW TYPES

/**
 * Step 1: Public scan request (no authentication)
 * Validates QR code HMAC and returns session token
 */
export interface ScanRequestData {
  ticket_id: string
  event_id: string
  qr_hmac: string // HMAC signature from QR code
  gate_id?: number
  scan_type: ScanType
  device_info?: string
}

/**
 * Step 1 Response: Session token with 30-second TTL
 */
export interface ScanSessionResponse {
  success: boolean
  session_token: string
  ticket_preview?: {
    buyer_name: string
    ticket_type: string
    status: TicketStatus
  }
  expires_at: string // ISO timestamp
  message?: string
}

/**
 * Step 2: Authenticated scan confirmation
 * Executes the scan using session token
 */
export interface ScanConfirmData {
  session_token: string
  gate_id?: number
  location?: string
  notes?: string
}

/**
 * Step 2 Response: Final scan result
 */
export interface ScanResponse {
  success: boolean
  result: ScanResult
  message: string
  ticket?: Ticket
  scan?: Scan
  event_status?: {
    current_in: number
    capacity: number
    remaining: number
  }
}

// Legacy single-step scan (kept for backward compatibility)
export interface ScanTicketData {
  ticket_code: string
  scan_type: ScanType
  gate_id?: number
  location?: string
  device_info?: string
}

export interface ScanFilters {
  event_id?: string
  gate_id?: number
  scanner_id?: number
  scan_type?: ScanType
  result?: ScanResult
  date?: string
  search?: string
  start_date?: string
  end_date?: string
}

// ==================== DASHBOARD & ANALYTICS TYPES ====================

export interface EventStatistics {
  event_id: string
  total_tickets: number
  tickets_sold: number
  tickets_pending: number
  tickets_used: number
  revenue: number
  revenue_pending: number
  attendance_rate: number
  sales_by_type: {
    ticket_type: string
    quantity: number
    revenue: number
  }[]
  sales_by_date: {
    date: string
    quantity: number
    revenue: number
  }[]
  scans_by_hour: {
    hour: string
    entries: number
    exits: number
  }[]
}

export interface DashboardStats {
  total_events: number
  active_events: number
  total_tickets_sold: number
  total_revenue: number
  recent_events: Event[]
  top_events: Event[]
  revenue_by_month: {
    month: string
    revenue: number
  }[]
}

export interface OrganizerDashboard {
  organisateur?: User
  stats: {
    total_events: number
    active_events: number
    total_tickets_sold: number
    total_revenue: number
  }
  upcoming_events: Event[]
  recent_orders: Order[]
}

export interface ScannerDashboard {
  stats: {
    total_scans_today: number
    valid_scans: number
    invalid_scans: number
    current_attendance: number
  }
  current_event?: Event
  recent_scans: Scan[]
}

// ==================== PAYMENT TYPES ====================

export interface PaymentCallback {
  transaction_id: string
  order_id: string
  status: 'success' | 'failed' | 'cancelled'
  amount: number
  currency: string
  provider: PaymentProvider
  metadata?: Record<string, any>
}

export interface PaymentInitResponse {
  payment_url: string
  transaction_id: string
  order_id: string
}

// ==================== EVENT COUNTER TYPES ====================

/**
 * Atomic event counter for tracking current attendance
 * Uses Redis locks for atomicity
 */
export interface EventCounter {
  event_id: string
  current_in: number // Current people inside venue
  total_entries: number // Total entry scans
  total_exits: number // Total exit scans
  last_updated: string
}

// ==================== ERROR CODE TYPES ====================

/**
 * Standardized error codes from API specification
 */
export enum ErrorCode {
  // Authentication errors (1000-1099)
  UNAUTHORIZED = 1001,
  INVALID_CREDENTIALS = 1002,
  TOKEN_EXPIRED = 1003,
  TOKEN_INVALID = 1004,

  // Validation errors (2000-2099)
  VALIDATION_ERROR = 2001,
  MISSING_REQUIRED_FIELD = 2002,
  INVALID_FORMAT = 2003,
  INVALID_DATE = 2004,

  // Resource errors (3000-3099)
  NOT_FOUND = 3001,
  RESOURCE_NOT_FOUND = 3002,
  EVENT_NOT_FOUND = 3003,
  TICKET_NOT_FOUND = 3004,
  ORDER_NOT_FOUND = 3005,
  GATE_NOT_FOUND = 3006,

  // Business logic errors (4000-4099)
  TICKET_ALREADY_USED = 4001,
  TICKET_EXPIRED = 4002,
  TICKET_INVALID_STATUS = 4003,
  EVENT_CAPACITY_FULL = 4004,
  PAYMENT_FAILED = 4005,
  INSUFFICIENT_STOCK = 4006,
  SCAN_COOLDOWN_ACTIVE = 4007,
  WRONG_EVENT = 4008,
  GATE_INACTIVE = 4009,
  REENTRY_NOT_ALLOWED = 4010,

  // Security errors (5000-5099)
  HMAC_INVALID = 5001,
  SESSION_EXPIRED = 5002,
  SESSION_INVALID = 5003,
  RATE_LIMIT_EXCEEDED = 5004,
  REPLAY_ATTACK_DETECTED = 5005,

  // System errors (9000-9099)
  INTERNAL_SERVER_ERROR = 9001,
  DATABASE_ERROR = 9002,
  REDIS_ERROR = 9003,
  QUEUE_ERROR = 9004,
}

export interface ApiError {
  code: ErrorCode
  message: string
  field?: string
  details?: Record<string, any>
}

// ==================== NOTIFICATION TYPES ====================

export type NotificationType = 'success' | 'error' | 'warning' | 'info'

export interface Notification {
  id: string
  type: NotificationType
  title: string
  message: string
  duration?: number
  timestamp: number
}

// ==================== PAGINATION & API RESPONSE TYPES ====================

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
  links?: {
    first: string
    last: string
    prev?: string
    next?: string
  }
}

export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  errors?: Record<string, string[]>
}

// ==================== FILTER & SORT TYPES ====================

export interface SortOption {
  field: string
  direction: 'asc' | 'desc'
}

export interface BaseFilters {
  page?: number
  per_page?: number
  sort?: string
  order?: 'asc' | 'desc'
  search?: string
}

// ==================== REPORT TYPES ====================

export interface SalesReport {
  totalTicketsSold: number
  totalRevenue: number
  averageTicketPrice: number
  salesByStatus: Record<string, number>
  topEvents: Array<{
    event_id: string
    tickets_sold: number
    event: {
      id: string
      title: string
    }
  }>
}

export interface AttendanceReport {
  event_id: string
  event_name: string
  date: string
  total_capacity: number
  tickets_sold: number
  tickets_scanned: number
  attendance_rate: number
  hourly_breakdown: {
    hour: string
    entries: number
    exits: number
    current: number
  }[]
}

export interface ScanActivityReport {
  totalScans: number
  successfulScans: number
  failedScans: number
  successRate: number
  scansByType: Record<string, number>
  scansByResult: Record<string, number>
  hourlyDistribution: Array<{
    hour: number
    count: number
  }>
  topScanners: Array<{
    scanned_by: string
    scan_count: number
    scanner?: {
      id: string
      name: string
    }
  }>
}
