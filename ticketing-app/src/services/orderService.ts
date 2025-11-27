import api from './api'
import type {
  Order,
  CreateOrderData,
  OrderFilters,
  PaginatedResponse,
  PaymentInitResponse
} from '@/types/api'

class OrderService {
  /**
   * Get all orders with optional filters
   */
  async getAll(filters?: OrderFilters): Promise<PaginatedResponse<Order>> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<PaginatedResponse<Order>>('/orders', { params })
    return response.data
  }

  /**
   * Get single order by ID
   */
  async getById(id: string): Promise<Order> {
    const response = await api.get<{ data: Order }>(`/orders/${id}`)
    return response.data.data
  }

  /**
   * Get order by order number
   */
  async getByOrderNumber(orderNumber: string): Promise<Order> {
    const response = await api.get<{ data: Order }>(`/orders/number/${orderNumber}`)
    return response.data.data
  }

  /**
   * Create a new order
   */
  async create(data: CreateOrderData): Promise<Order> {
    const response = await api.post<{ data: Order }>('/orders', data)
    return response.data.data
  }

  /**
   * Initialize payment for an order
   */
  async initializePayment(orderId: number): Promise<PaymentInitResponse> {
    const response = await api.post<{ data: PaymentInitResponse }>(
      `/orders/${orderId}/payment/initialize`
    )
    return response.data.data
  }

  /**
   * Handle payment callback
   */
  async handlePaymentCallback(transactionId: string, orderId: number): Promise<Order> {
    const response = await api.post<{ data: Order }>('/orders/payment/callback', {
      transaction_id: transactionId,
      order_id: orderId
    })
    return response.data.data
  }

  /**
   * Cancel an order
   */
  async cancel(id: string, reason?: string): Promise<Order> {
    const response = await api.post<{ data: Order }>(`/orders/${id}/cancel`, { reason })
    return response.data.data
  }

  /**
   * Refund an order
   */
  async refund(id: string, reason?: string): Promise<Order> {
    const response = await api.post<{ data: Order }>(`/orders/${id}/refund`, { reason })
    return response.data.data
  }

  /**
   * Download order receipt
   */
  async downloadReceipt(id: string): Promise<Blob> {
    const response = await api.get(`/orders/${id}/receipt`, {
      responseType: 'blob'
    })
    return response.data
  }

  /**
   * Resend order confirmation email
   */
  async resendConfirmation(id: string): Promise<void> {
    await api.post(`/orders/${id}/resend-confirmation`)
  }

  /**
   * Build query parameters from filters
   */
  private buildQueryParams(filters?: OrderFilters): Record<string, any> {
    if (!filters) return {}

    const params: Record<string, any> = {}

    if (filters.event_id) params.event_id = filters.event_id
    if (filters.status) params.status = filters.status
    if (filters.customer_email) params.customer_email = filters.customer_email
    if (filters.search) params.search = filters.search

    return params
  }
}

export default new OrderService()
