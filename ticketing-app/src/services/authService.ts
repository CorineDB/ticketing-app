import api from './api'
import type {
  AuthResponse,
  LoginCredentials,
  OTPRequest,
  OTPVerification,
  User
} from '@/types/api'

class AuthService {
  /**
   * Login with email and password
   */
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/login', credentials)
    return response.data
  }

  /**
   * Request OTP code
   */
  async requestOtp(data: OTPRequest): Promise<{ message: string }> {
    const response = await api.post('/auth/otp/request', data)
    return response.data
  }

  /**
   * Verify OTP and authenticate
   */
  async verifyOtp(data: OTPVerification): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/otp/verify', data)
    return response.data
  }

  /**
   * Get current authenticated user
   */
  async me(): Promise<User> {
    const response = await api.get<User>('/auth/me')
    return response.data
  }

  /**
   * Logout current user
   */
  async logout(): Promise<void> {
    await api.post('/auth/logout')
  }

  /**
   * Change password
   */
  async changePassword(currentPassword: string, newPassword: string): Promise<void> {
    await api.post('/auth/change-password', {
      current_password: currentPassword,
      password: newPassword,
      password_confirmation: newPassword
    })
  }

  /**
   * Request password reset
   */
  async forgotPassword(email: string): Promise<{ message: string }> {
    const response = await api.post('/auth/forgot-password', { email })
    return response.data
  }

  /**
   * Reset password with token
   */
  async resetPassword(token: string, email: string, password: string): Promise<void> {
    await api.post('/auth/reset-password', {
      token,
      email,
      password,
      password_confirmation: password
    })
  }

  /**
   * Update user profile
   */
  async updateProfile(data: Partial<User>): Promise<User> {
    const response = await api.put<{ data: User }>('/auth/me', data)
    return response.data.data
  }
}

export default new AuthService()
