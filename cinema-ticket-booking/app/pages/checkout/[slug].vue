<!-- pages/checkout/[slug].vue -->
<template>
  <div class="min-h-screen bg-[#0d0000] py-10 px-4">
    <div class="max-w-2xl mx-auto">

      <!-- Back -->
      <NuxtLink :to="`/movie/seat/${slug}`"
        class="inline-flex items-center gap-2 text-white/40 hover:text-white text-sm transition-colors mb-8">
        <Icon name="lucide:arrow-left" class="w-4 h-4" />
        Back to Seat Selection
      </NuxtLink>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-20">
        <Icon name="lucide:loader-circle" class="w-10 h-10 text-red-500 animate-spin" />
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center py-20">
        <Icon name="lucide:alert-circle" class="w-12 h-12 text-red-500/50 mx-auto mb-3" />
        <p class="text-white/40">{{ error }}</p>
        <NuxtLink to="/" class="mt-4 inline-block text-red-500 hover:text-red-400 text-sm">
          Go Home
        </NuxtLink>
      </div>

      <template v-else-if="booking">

        <!-- Header -->
        <div class="mb-8">
          <p class="text-red-500 text-xs uppercase tracking-widest font-semibold mb-1">Checkout</p>
          <h1 class="text-3xl font-black text-white">Complete Booking</h1>
          <p class="text-white/40 text-sm mt-1">
            Booking expires in
            <span class="text-red-400 font-semibold">{{ countdown }}</span>
          </p>
        </div>

        <div class="space-y-4">

          <!-- Booking Summary -->
          <div class="bg-white/[0.03] border border-white/10 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10">
              <h2 class="text-white font-bold">Booking Summary</h2>
            </div>

            <!-- Movie info -->
            <div class="p-6 flex items-center gap-4 border-b border-white/10">
              <img v-if="booking.movie?.poster" :src="booking.movie.poster"
                class="w-14 h-20 object-cover rounded-xl shrink-0" />
              <div class="flex-1 min-w-0">
                <h3 class="text-white font-bold text-lg truncate">{{ booking.movie?.title }}</h3>
                <div class="space-y-1 mt-2">
                  <div class="flex items-center gap-2 text-white/50 text-sm">
                    <Icon name="lucide:map-pin" class="w-3.5 h-3.5 text-red-500 shrink-0" />
                    {{ booking.cinema?.name }}
                  </div>
                  <div class="flex items-center gap-2 text-white/50 text-sm">
                    <Icon name="lucide:calendar" class="w-3.5 h-3.5 shrink-0" />
                    {{ formatDate(booking.showtime?.date) }}
                    <span class="text-white/20">·</span>
                    <Icon name="lucide:clock" class="w-3.5 h-3.5 shrink-0" />
                    {{ booking.showtime?.time }}
                  </div>
                  <div class="flex items-center gap-2 text-white/50 text-sm">
                    <Icon name="lucide:monitor" class="w-3.5 h-3.5 shrink-0" />
                    {{ booking.hall?.name }} · {{ booking.showtime?.format?.toUpperCase() }}
                  </div>
                </div>
              </div>
              <div class="text-right shrink-0">
                <p class="text-white/30 text-xs">Booking ID</p>
                <p class="text-white font-mono text-sm font-bold">#{{ booking.booking_code }}</p>
              </div>
            </div>

            <!-- Seats -->
            <div class="px-6 py-4 border-b border-white/10">
              <p class="text-white/40 text-xs uppercase tracking-widest mb-3">Seats</p>
              <div class="flex flex-wrap gap-2">
                <span v-for="seat in booking.seats" :key="seat.seat_code"
                  class="px-3 py-1 rounded-lg text-sm font-semibold"
                  :class="seat.type === 'vip'
                    ? 'bg-yellow-500/20 border border-yellow-500/30 text-yellow-400'
                    : 'bg-red-600/20 border border-red-500/20 text-red-400'">
                  {{ seat.seat_code }}
                  <span class="text-xs opacity-60 ml-1">{{ seat.type === 'vip' ? 'VIP' : 'Std' }}</span>
                </span>
              </div>
            </div>

            <!-- Price breakdown -->
            <div class="px-6 py-4">
              <div v-for="seat in booking.seats" :key="seat.seat_code + '_price'"
                class="flex items-center justify-between text-sm mb-1">
                <span class="text-white/40">{{ seat.seat_code }} ({{ seat.type === 'vip' ? 'VIP' : 'Standard' }})</span>
                <span class="text-white">${{ Number(seat.price).toFixed(2) }}</span>
              </div>
              <div class="h-px bg-white/10 my-3" />
              <div class="flex items-center justify-between">
                <span class="text-white font-semibold">Total</span>
                <span class="text-red-500 font-black text-2xl">${{ Number(booking.total_amount).toFixed(2) }}</span>
              </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="bg-white/[0.03] border border-white/10 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10">
              <h2 class="text-white font-bold">Payment Method</h2>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <button v-for="method in paymentMethods" :key="method.value"
                  @click="selectedMethod = method.value"
                  class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-all"
                  :class="selectedMethod === method.value
                    ? 'border-red-500 bg-red-500/10 text-white'
                    : 'border-white/10 bg-white/5 text-white/50 hover:border-white/30 hover:text-white'">
                  <Icon :name="method.icon" class="w-6 h-6" />
                  <span class="text-xs font-medium">{{ method.label }}</span>
                  <div v-if="selectedMethod === method.value"
                    class="w-2 h-2 rounded-full bg-red-500" />
                </button>
              </div>
            </div>
          </div>

          <!-- ABA / Wing QR placeholder -->
          <div v-if="['aba', 'wing', 'acleda'].includes(selectedMethod)"
            class="bg-white/[0.03] border border-white/10 rounded-2xl p-6 text-center">
            <div class="w-40 h-40 mx-auto bg-white rounded-xl flex items-center justify-center mb-4">
              <Icon name="lucide:qr-code" class="w-28 h-28 text-gray-700" />
            </div>
            <p class="text-white/40 text-sm">Scan QR code to pay with {{ selectedMethod.toUpperCase() }}</p>
            <p class="text-white font-bold text-xl mt-2">${{ Number(booking.total_amount).toFixed(2) }}</p>
          </div>

          <!-- Pay Error -->
          <div v-if="payError"
            class="p-3 bg-red-500/15 border border-red-500/30 rounded-xl text-red-400 text-sm flex items-center gap-2">
            <Icon name="lucide:alert-circle" class="w-4 h-4 shrink-0" />
            {{ payError }}
          </div>

          <!-- Pay Button -->
          <button @click="processPayment" :disabled="!selectedMethod || paying"
            class="w-full py-4 bg-red-600 hover:bg-red-500 active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-2xl transition-all flex items-center justify-center gap-2 group text-lg">
            <Icon v-if="paying" name="lucide:loader-circle" class="w-5 h-5 animate-spin" />
            <Icon v-else name="lucide:lock" class="w-5 h-5" />
            {{ paying ? 'Processing...' : `Pay $${Number(booking.total_amount).toFixed(2)}` }}
          </button>

          <p class="text-center text-white/20 text-xs">
            <Icon name="lucide:shield-check" class="w-3.5 h-3.5 inline mr-1" />
            Your payment is secured and encrypted
          </p>

        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
definePageMeta({ middleware: 'auth' })

const route  = useRoute()
const slug   = route.params.slug
const { $api } = useNuxtApp()

const bookingId   = route.query.booking_id
const loading     = ref(true)
const error       = ref(null)
const booking     = ref(null)
const paying      = ref(false)
const payError    = ref(null)
const selectedMethod = ref('card')

// ── Countdown timer ──────────────────────────────────────
const countdown = ref('')
let countdownTimer = null

const startCountdown = (expiresAt) => {
  const tick = () => {
    const diff = new Date(expiresAt) - new Date()
    if (diff <= 0) {
      countdown.value = 'Expired'
      clearInterval(countdownTimer)
      error.value = 'Booking has expired. Please start again.'
      booking.value = null
      return
    }
    const m = Math.floor(diff / 60000)
    const s = Math.floor((diff % 60000) / 1000)
    countdown.value = `${m}:${s.toString().padStart(2, '0')}`
  }
  tick()
  countdownTimer = setInterval(tick, 1000)
}

onUnmounted(() => clearInterval(countdownTimer))

// ── Fetch booking ────────────────────────────────────────
const fetchBooking = async () => {
  loading.value = true
  error.value   = null
  try {
    const response = await $api(`/bookings/${bookingId}`)
    booking.value  = response

    if (response.status === 'cancelled') {
      error.value = 'This booking has been cancelled.'
      return
    }
    if (response.status === 'confirmed') {
      // Already paid — go to confirmation
      navigateTo(`/booking/success?code=${response.booking_code}`)
      return
    }

    if (response.expires_at) {
      startCountdown(response.expires_at)
    }
  } catch (err) {
    error.value = 'Booking not found.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

// ── Process payment ──────────────────────────────────────
const processPayment = async () => {
  if (!selectedMethod.value || paying.value) return
  paying.value  = true
  payError.value = null

  try {
    const response = await $api('/payments', {
      method: 'POST',
      body: {
        booking_id: bookingId,
        method:     selectedMethod.value,
      },
    })

    // Success — go to confirmation
    navigateTo(`/booking/success?code=${response.data.booking_code}`)

  } catch (err) {
    payError.value = err?.data?.message || 'Payment failed. Please try again.'
  } finally {
    paying.value = false
  }
}

// ── Payment methods ──────────────────────────────────────
const paymentMethods = [
  { value: 'card',   label: 'Credit Card',  icon: 'lucide:credit-card' },
  { value: 'aba',    label: 'ABA Pay',       icon: 'lucide:qr-code' },
  { value: 'wing',   label: 'Wing',          icon: 'lucide:smartphone' },
  { value: 'acleda', label: 'ACLEDA',        icon: 'lucide:building-2' },
  { value: 'cash',   label: 'Cash',          icon: 'lucide:banknote' },
]

// ── Helpers ──────────────────────────────────────────────
const formatDate = (d) => {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-US', {
    weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
  })
}

onMounted(fetchBooking)
</script>