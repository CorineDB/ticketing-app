-- Roles
CREATE TABLE roles (
  id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  name text NOT NULL,
  slug text NOT NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

-- Users
CREATE TABLE users (
  id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  name text NOT NULL,
  email text UNIQUE NOT NULL,
  password_hash text, 
  type text NOT NULL CHECK (type IN ('superadmin', 'organizer', 'agent', 'caissier')),
  role_id uuid NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX idx_users_email ON users(email);

-- Events
CREATE TABLE events (
  id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  organisateur_id uuid REFERENCES users(id),
  title text NOT NULL,
  description text,
  image_url text,
  start_datetime timestamptz NOT NULL,
  end_datetime timestamptz NOT NULL,
  location text,
  capacity integer NOT NULL DEFAULT 0,
  timezone text NOT NULL DEFAULT 'UTC',
  dress_code text,
  created_by uuid REFERENCES users(id) ON DELETE SET NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX idx_events_start ON events(start_datetime);
CREATE INDEX idx_events_title ON events(title);
CREATE UNIQUE INDEX uq_organisateura_event_title ON events(organisateur_id, title);

-- Ticket Types
CREATE TABLE ticket_types (
  id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  event_id uuid NOT NULL REFERENCES events(id) ON DELETE CASCADE,
  name text NOT NULL,
  price numeric(12,2) NOT NULL DEFAULT 0,
  validity_from timestamptz,
  validity_to timestamptz,
  usage_limit integer DEFAULT 1,
  quota integer DEFAULT NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT uq_ticket_event_name UNIQUE (event_id, name)
);
CREATE INDEX idx_ticket_types_event ON ticket_types(event_id);
CREATE INDEX idx_ticket_types_name ON ticket_types(name);

-- Gates
CREATE TYPE type_enum AS ENUM ('entrance','exit','vip','other');
CREATE TYPE gate_status_enum AS ENUM ('active','pause','inactive');

CREATE TABLE gates (
  id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  name text NOT NULL,
  location text,
  type type_enum NOT NULL DEFAULT 'entrance',
  status gate_status_enum NOT NULL DEFAULT 'active',
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

-- Tickets
CREATE TYPE ticket_status_enum AS ENUM ('issued','reserved','paid','in','out','invalid','refunded');

CREATE TABLE tickets (
  id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  event_id uuid NOT NULL REFERENCES events(id) ON DELETE CASCADE,
  ticket_type_id uuid REFERENCES ticket_types(id) ON DELETE SET NULL,
  code text NOT NULL,
  qr_path text,
  qr_hmac text,
  magic_link_token text,
  status ticket_status_enum NOT NULL DEFAULT 'issued',
  buyer_name text,
  buyer_email text,
  buyer_phone text,
  issued_at timestamptz NOT NULL DEFAULT now(),
  paid_at timestamptz,
  used_count integer DEFAULT 0,
  last_used_at timestamptz,
  gate_in uuid REFERENCES gates(id),
  last_gate_out uuid REFERENCES gates(id),
  metadata jsonb DEFAULT '{}',
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX idx_tickets_event ON tickets(event_id);
CREATE INDEX idx_tickets_code ON tickets(code);
CREATE UNIQUE INDEX uq_ticket_code_event ON tickets (event_id, code);

-- Ticket Scan Logs
CREATE TABLE ticket_scan_logs (
  id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  ticket_id uuid NOT NULL REFERENCES tickets(id) ON DELETE CASCADE,
  agent_id uuid REFERENCES users(id),
  gate_id uuid REFERENCES gates(id),
  scan_type text NOT NULL CHECK (scan_type IN ('entry','exit')),
  scan_time timestamptz NOT NULL DEFAULT now(),
  result text NOT NULL CHECK (result IN ('ok','already_in','already_out','invalid','expired','capacity_full')),
  details jsonb DEFAULT '{}',
  metadata jsonb DEFAULT '{}',
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX idx_ticket_scan_ticket ON ticket_scan_logs(ticket_id);
CREATE INDEX idx_ticket_scan_created ON ticket_scan_logs(created_at);

-- Event Counters (current_in)
CREATE TABLE event_counters (
  event_id uuid PRIMARY KEY REFERENCES events(id) ON DELETE CASCADE,
  current_in integer NOT NULL DEFAULT 0,
  updated_at timestamptz NOT NULL DEFAULT now()
);

