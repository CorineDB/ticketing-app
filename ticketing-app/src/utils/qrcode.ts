import QRCode from 'qrcode'

/**
 * Generate QR code as data URL
 */
export async function generateQRCode(text: string): Promise<string> {
  try {
    const dataUrl = await QRCode.toDataURL(text, {
      width: 300,
      margin: 2,
      color: {
        dark: '#000000',
        light: '#FFFFFF'
      }
    })
    return dataUrl
  } catch (error) {
    console.error('Failed to generate QR code:', error)
    throw error
  }
}

/**
 * Download QR code as image
 */
export async function downloadQRCode(text: string, filename: string = 'qrcode.png') {
  const dataUrl = await generateQRCode(text)
  const link = document.createElement('a')
  link.href = dataUrl
  link.download = filename
  link.click()
}
