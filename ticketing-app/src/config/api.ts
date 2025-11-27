export const API_CONFIG = {
  baseURL: "http://192.168.8.106:8000" || import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  timeout: 30000
}

export const PAYMENT_CONFIG = {
  paydunya: {
    apiKey: import.meta.env.VITE_PAYDUNYA_API_KEY || ''
  },
  cinetpay: {
    apiKey: import.meta.env.VITE_CINETPAY_API_KEY || '',
    siteId: import.meta.env.VITE_CINETPAY_SITE_ID || ''
  },
  mtnMomo: {
    apiKey: import.meta.env.VITE_MTN_MOMO_API_KEY || ''
  }
}
