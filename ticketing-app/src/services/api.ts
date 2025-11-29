import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig, AxiosResponse } from 'axios'
import router from '@/router'

const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://192.168.8.107:8000/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor - Add authentication token
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('auth_token')

    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
      console.log('Axios Interceptor: Attaching token:', token);
    } else {
      console.log('Axios Interceptor: No token found or headers missing for request:', config.url);
    }

    return config
  },
  (error: AxiosError) => {
    return Promise.reject(error)
  }
)

// Response interceptor - Handle errors globally
api.interceptors.response.use(
  (response: AxiosResponse) => {
    return response
  },
  (error: AxiosError) => {
    const status = error.response?.status

    // Skip redirect if this is an auth endpoint
    const isAuthEndpoint = error.config?.url?.includes('/auth/')

    if (status === 401 && !isAuthEndpoint) {
      // Unauthorized - clear token and redirect to login
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')

      if (router.currentRoute.value.path !== '/login') {
        router.push('/login')
      }
    } else if (status === 403) {
      // Forbidden - insufficient permissions
      console.error('Forbidden: Insufficient permissions')
    } else if (status === 500) {
      // Server error
      console.error('Server error occurred')
    }

    return Promise.reject(error)
  }
)

export default api
