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
    const response = await api.post<AuthResponse>('/auths/login', credentials)
    return response.data
  }

  /**
   * Request OTP code
   */
  async requestOtp(data: OTPRequest): Promise<{ message: string }> {
    const response = await api.post('/auths/otp/request', data)
    return response.data
  }

  /**
   * Verify OTP and authenticate
   */
  async verifyOtp(data: OTPVerification): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auths/otp/verify', data)
    return response.data
  }

  /**
   * Get current authenticated user
   */
  async me(): Promise<User> {
    const response = await api.get<{ data: User }>('/auths/me')
    return response.data
  }

  /**
   * Logout current user
   */
  async logout(): Promise<void> {
    await api.post('/auths/logout')
  }

  /**
   * Change password
   */
  async changePassword(oldPassword: string, newPassword: string): Promise<void> {
    await api.post('/auths/change-password', {
      old_password: oldPassword,
      new_password: newPassword,
      new_password_confirmation: newPassword
    })
  }

  /**
   * Request password reset
   */
  async forgotPassword(email: string): Promise<{ message: string }> {
    const response = await api.post('/auths/forgot-password', { email })
    return response.data
  }

  /**
   * Reset password with token
   */
  async resetPassword(token: string, email: string, password: string): Promise<void> {
    await api.post('/auths/reset-password', {
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
    const response = await api.put<{ data: User }>('/auths/me', data)
    return response.data.data
  }
}

export default new AuthService()
