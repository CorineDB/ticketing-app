export interface ScanRequestPayload {
  ticket_id: string
  sig: string
}

export interface TicketInfo {
  id: string
  code: string
  status: 'issued' | 'paid' | 'in' | 'out' | 'reserved' | 'invalid' | 'refunded'
  buyer_name: string
  buyer_email: string
  buyer_phone?: string
  event: {
    id: string
    title: string
    start_datetime: string
    end_datetime: string
  }
  ticket_type: {
    id: string
    name: string
    price: string
  }
}

export interface ScanRequestResponse {
  scan_session_token: string
  scan_nonce: string
  expires_in: number
  ticket: TicketInfo
}

export interface ScanConfirmPayload {
  scan_session_token: string
  scan_nonce: string
  gate_id: string
  agent_id: string
  action: 'in' | 'out' | 'entry' | 'exit'
}

export interface ScanConfirmResponse {
  valid: boolean
  code: string
  message: string
  ticket: TicketInfo
  scan_log_id?: string
}

export type ScanStatus = 'idle' | 'scanning' | 'validating' | 'success' | 'error'
