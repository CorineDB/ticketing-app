import api from './api'

interface PurchaseTicketData {
  ticket_type_id: string
  quantity: number
  customer: {
    firstname: string
    lastname: string
    email: string
    phone_number: string
  }
}

interface PurchaseResponse {
  success: boolean
  message: string
  payment_url: string
  transaction_id: string
}

class PaymentService {
  async purchaseTicket(data: PurchaseTicketData): Promise<PurchaseResponse> {
    const response = await api.post<PurchaseResponse>('/tickets/purchase', data)
    return response.data
  }
}

export default new PaymentService()
