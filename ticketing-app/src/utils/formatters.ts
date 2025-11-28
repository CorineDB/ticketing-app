/**
 * Utility functions for formatting data
 */

/**
 * Format date to readable string
 * @param date ISO date string or Date object
 * @returns Formatted date string (e.g., "Jan 15, 2024")
 */
export function formatDate(date: string | Date | undefined): string {
  if (!date) return 'N/A'

  const d = typeof date === 'string' ? new Date(date) : date

  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).format(d)
}

/**
 * Format date and time to readable string
 * @param date ISO date string or Date object
 * @returns Formatted date and time string (e.g., "Jan 15, 2024 at 3:30 PM")
 */
export function formatDateTime(date: string | Date | undefined): string {
  if (!date) return 'N/A'

  const d = typeof date === 'string' ? new Date(date) : date

  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  }).format(d)
}

/**
 * Format time to readable string
 * @param date ISO date string or Date object
 * @returns Formatted time string (e.g., "3:30 PM")
 */
export function formatTime(date: string | Date | undefined): string {
  if (!date) return 'N/A'

  const d = typeof date === 'string' ? new Date(date) : date

  return new Intl.DateTimeFormat('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  }).format(d)
}

/**
 * Format currency amount
 * @param amount Amount in smallest currency unit
 * @param currency Currency code (default: 'USD')
 * @returns Formatted currency string (e.g., "$12.50")
 */
export function formatCurrency(amount: number, currency: string = 'USD'): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency
  }).format(amount)
}

/**
 * Format number with thousand separators
 * @param num Number to format
 * @returns Formatted number string (e.g., "1,234,567")
 */
export function formatNumber(num: number): string {
  return new Intl.NumberFormat('en-US').format(num)
}

/**
 * Format relative time (e.g., "2 hours ago")
 * @param date ISO date string or Date object
 * @returns Relative time string
 */
export function formatRelativeTime(date: string | Date): string {
  const d = typeof date === 'string' ? new Date(date) : date
  const now = new Date()
  const diffInSeconds = Math.floor((now.getTime() - d.getTime()) / 1000)

  if (diffInSeconds < 60) {
    return 'just now'
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60)
    return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600)
    return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`
  } else if (diffInSeconds < 604800) {
    const days = Math.floor(diffInSeconds / 86400)
    return `${days} ${days === 1 ? 'day' : 'days'} ago`
  } else {
    return formatDate(d)
  }
}

/**
 * Format phone number
 * @param phone Phone number string
 * @returns Formatted phone number
 */
export function formatPhone(phone: string): string {
  // Remove all non-numeric characters
  const cleaned = phone.replace(/\D/g, '')

  // Format as (XXX) XXX-XXXX for US numbers
  if (cleaned.length === 10) {
    return `(${cleaned.slice(0, 3)}) ${cleaned.slice(3, 6)}-${cleaned.slice(6)}`
  }

  // Return original if not standard format
  return phone
}

/**
 * Truncate string with ellipsis
 * @param str String to truncate
 * @param length Maximum length
 * @returns Truncated string
 */
export function truncate(str: string, length: number): string {
  if (str.length <= length) return str
  return str.slice(0, length) + '...'
}

/**
 * Capitalize first letter of string
 * @param str String to capitalize
 * @returns Capitalized string
 */
export function capitalize(str: string): string {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

/**
 * Format file size
 * @param bytes File size in bytes
 * @returns Formatted file size (e.g., "1.5 MB")
 */
export function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

/**
 * Get full image URL from relative path
 * @param imagePath Relative image path or full URL
 * @returns Full image URL
 */
export function getImageUrl(imagePath: string | undefined): string | undefined {
  if (!imagePath) return undefined

  // If already a full URL (starts with http:// or https://), return as is
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath
  }

  // Get API base URL from config
  const baseURL = import.meta.env.VITE_API_URL || 'http://192.168.8.106:8000'

  // Remove trailing slash from baseURL if present
  const cleanBaseUrl = baseURL.replace(/\/$/, '')

  // Ensure imagePath starts with /
  const cleanImagePath = imagePath.startsWith('/') ? imagePath : `/${imagePath}`

  return `${cleanBaseUrl}${cleanImagePath}`
}
