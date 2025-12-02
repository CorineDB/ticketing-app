<template>
  <PublicLayout>
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center px-4 py-12">
      <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
          <div class="flex justify-center mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center">
              <PhoneIcon class="w-10 h-10 text-white" />
            </div>
          </div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">
            {{ step === 'phone' ? 'Login with OTP' : 'Verify OTP' }}
          </h1>
          <p class="text-gray-600">
            {{ step === 'phone'
              ? 'Enter your phone number to receive a verification code'
              : 'Enter the 6-digit code sent to your phone'
            }}
          </p>
        </div>

        <!-- OTP Form -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
          <!-- Step 1: Phone Number -->
          <form v-if="step === 'phone'" @submit.prevent="handleSendOTP" class="space-y-6">
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                Phone Number
              </label>
              <input
                id="phone"
                v-model="phone"
                type="tel"
                required
                placeholder="+1234567890"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :disabled="loading"
              />
              <p class="mt-2 text-sm text-gray-500">
                Include country code (e.g., +1 for US)
              </p>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <div class="flex gap-3">
                <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
                <p class="text-sm text-red-700">{{ error }}</p>
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <LoaderIcon v-if="loading" class="w-5 h-5 animate-spin" />
              <span>{{ loading ? 'Sending...' : 'Send OTP' }}</span>
            </button>
          </form>

          <!-- Step 2: OTP Verification -->
          <form v-else @submit.prevent="handleVerifyOTP" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Verification Code
              </label>
              <div class="flex gap-2 justify-center">
                <input
                  v-for="(_, index) in otp"
                  :key="index"
                  :ref="(el) => otpInputs[index] = el as HTMLInputElement"
                  v-model="otp[index]"
                  type="text"
                  maxlength="1"
                  pattern="[0-9]"
                  class="w-12 h-14 text-center text-2xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :disabled="loading"
                  @input="handleOTPInput(index, $event)"
                  @keydown="handleOTPKeydown(index, $event)"
                />
              </div>
              <p class="mt-4 text-center text-sm text-gray-600">
                Code sent to <span class="font-medium">{{ phone }}</span>
                <button
                  type="button"
                  @click="step = 'phone'; otp = ['', '', '', '', '', '']"
                  class="ml-2 text-blue-600 hover:text-blue-700"
                >
                  Change
                </button>
              </p>
            </div>

            <!-- Resend OTP -->
            <div class="text-center">
              <button
                v-if="canResend"
                type="button"
                @click="handleSendOTP"
                class="text-sm text-blue-600 hover:text-blue-700"
              >
                Resend code
              </button>
              <p v-else class="text-sm text-gray-500">
                Resend code in {{ resendCountdown }}s
              </p>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <div class="flex gap-3">
                <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
                <p class="text-sm text-red-700">{{ error }}</p>
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="loading || !isOTPComplete"
              class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <LoaderIcon v-if="loading" class="w-5 h-5 animate-spin" />
              <span>{{ loading ? 'Verifying...' : 'Verify & Login' }}</span>
            </button>
          </form>

          <!-- Back to Login -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <RouterLink
              to="/login"
              class="w-full py-2 px-4 text-gray-700 text-center hover:text-gray-900 flex items-center justify-center gap-2 text-sm"
            >
              <ArrowLeftIcon class="w-4 h-4" />
              Back to password login
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import {
  PhoneIcon,
  AlertCircleIcon,
  LoaderIcon,
  ArrowLeftIcon
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const step = ref<'phone' | 'otp'>('phone')
const phone = ref('')
const otp = ref(['', '', '', '', '', ''])
const otpInputs = ref<HTMLInputElement[]>([])
const loading = ref(false)
const error = ref('')

const resendCountdown = ref(0)
const canResend = computed(() => resendCountdown.value === 0)
const isOTPComplete = computed(() => otp.value.every(digit => digit !== ''))

let resendInterval: ReturnType<typeof setInterval> | null = null

async function handleSendOTP() {
  loading.value = true
  error.value = ''

  try {
    await authStore.requestOtp({ phone: phone.value })
    step.value = 'otp'
    startResendCountdown()

    // Focus first OTP input
    setTimeout(() => {
      otpInputs.value[0]?.focus()
    }, 100)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to send OTP. Please try again.'
  } finally {
    loading.value = false
  }
}

async function handleVerifyOTP() {
  loading.value = true
  error.value = ''

  try {
    const otpCode = otp.value.join('')
    const success = await authStore.verifyOtp({
      phone: phone.value,
      otp: otpCode
    })

    if (success) {
      // Redirect based on user role
      if (authStore.isScanner) {
        router.push('/dashboard/scanner')
      } else {
        router.push('/dashboard')
      }
    } else {
      error.value = 'Invalid OTP code'
      otp.value = ['', '', '', '', '', '']
      otpInputs.value[0]?.focus()
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to verify OTP. Please try again.'
    otp.value = ['', '', '', '', '', '']
    otpInputs.value[0]?.focus()
  } finally {
    loading.value = false
  }
}

function handleOTPInput(index: number, event: Event) {
  const input = event.target as HTMLInputElement
  const value = input.value

  if (value && index < 5) {
    otpInputs.value[index + 1]?.focus()
  }
}

function handleOTPKeydown(index: number, event: KeyboardEvent) {
  if (event.key === 'Backspace' && !otp.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus()
  }
}

function startResendCountdown() {
  resendCountdown.value = 60
  resendInterval = setInterval(() => {
    resendCountdown.value--
    if (resendCountdown.value === 0 && resendInterval) {
      clearInterval(resendInterval)
    }
  }, 1000)
}

onUnmounted(() => {
  if (resendInterval) {
    clearInterval(resendInterval)
  }
})
</script>
