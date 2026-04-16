<!-- pages/booking/success.vue -->
<template>
  <div class="min-h-screen bg-[#0d0000] py-10 px-4">
    <div class="max-w-2xl mx-auto">

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

      <!-- Success Content -->
      <template v-else-if="booking">

        <!-- Success Animation -->
        <div class="text-center mb-8">
          <div class="relative inline-flex items-center justify-center">
            <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center animate-pulse">
              <div class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center">
                <Icon name="lucide:check" class="w-8 h-8 text-white" />
              </div>
            </div>
          </div>
          <h1 class="text-2xl font-black text-white mt-4">Payment Successful!</h1>
          <p class="text-white/50 text-sm mt-1">Your booking has been confirmed</p>
        </div>

        <!-- Booking Card -->
        <div
          class="bg-gradient-to-br from-red-900/20 to-black border border-red-500/20 rounded-2xl overflow-hidden mb-6">

          <!-- Ticket Header -->
          <div class="relative bg-red-600/10 px-6 py-4 border-b border-red-500/20">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-white/40 text-xs uppercase tracking-widest">Booking Code</p>
                <p class="text-white font-mono text-xl font-bold">{{ booking.booking_code }}</p>
              </div>
              <div class="text-right">
                <p class="text-white/40 text-xs uppercase tracking-widest">Status</p>
                <span
                  class="inline-flex items-center gap-1 px-2 py-1 bg-green-500/20 rounded-lg text-green-400 text-sm">
                  <Icon name="lucide:check-circle" class="w-3 h-3 fill-green-400" />
                  {{ booking.status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Movie Info -->
          <div class="p-6 flex gap-4 border-b border-white/10">
            <img :src="booking.movie?.poster" :alt="booking.movie?.title" class="w-16 h-24 object-cover rounded-xl" />
            <div class="flex-1">
              <h2 class="text-white font-bold text-lg">{{ booking.movie?.title }}</h2>
              <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm">
                <div class="flex items-center gap-1 text-white/50">
                  <Icon name="lucide:map-pin" class="w-3.5 h-3.5 text-red-500" />
                  {{ booking.cinema?.name }}
                </div>
                <div class="flex items-center gap-1 text-white/50">
                  <Icon name="lucide:calendar" class="w-3.5 h-3.5" />
                  {{ formatDate(booking.showtime?.date) }}
                </div>
                <div class="flex items-center gap-1 text-white/50">
                  <Icon name="lucide:clock" class="w-3.5 h-3.5" />
                  {{ formatTime(booking.showtime?.time) }}
                </div>
                <div class="flex items-center gap-1 text-white/50">
                  <Icon name="lucide:monitor" class="w-3.5 h-3.5" />
                  {{ booking.hall?.name }} · {{ booking.showtime?.format?.toUpperCase() }}
                </div>
              </div>
            </div>
          </div>

          <!-- Seats -->
          <div class="p-6 border-b border-white/10">
            <p class="text-white/40 text-xs uppercase tracking-widest mb-3">Seats</p>
            <div class="flex flex-wrap gap-3">
              <div v-for="seat in booking.seats" :key="seat.seat_code"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold" :class="seat.type === 'vip'
                  ? 'bg-yellow-500/20 border border-yellow-500/30 text-yellow-400'
                  : 'bg-red-600/20 border border-red-500/20 text-red-400'">
                <Icon :name="seat.type === 'vip' ? 'lucide:crown' : 'lucide:armchair'" class="w-3.5 h-3.5" />
                {{ seat.seat_code }}
                <span class="text-xs opacity-60">{{ seat.type === 'vip' ? 'VIP' : 'Standard' }}</span>
              </div>
            </div>
          </div>

          <!-- Payment Info -->
          <div class="p-6">
            <div class="space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span class="text-white/40">Subtotal</span>
                <span class="text-white">${{ (booking.total_amount).toFixed(2) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-white/40">Tax (10%)</span>
                <span class="text-white">${{ (booking.total_amount * 0.1).toFixed(2) }}</span>
              </div>
              <div class="h-px bg-white/10 my-2" />
              <div class="flex items-center justify-between">
                <span class="text-white font-semibold">Total Paid</span>
                <span class="text-red-500 font-black text-2xl">${{ (booking.total_amount).toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
          <button @click="downloadTicket"
            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/15 text-white font-semibold rounded-xl transition-all">
            <Icon name="lucide:download" class="w-4 h-4" />
            Download Ticket
          </button>
          <button @click="printTicket"
            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/15 text-white font-semibold rounded-xl transition-all">
            <Icon name="lucide:printer" class="w-4 h-4" />
            Print Ticket
          </button>
          <NuxtLink to="/account/tickets"
            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl transition-all">
            <Icon name="lucide:ticket" class="w-4 h-4" />
            My Tickets
          </NuxtLink>
        </div>

        <!-- QR Code Section (optional) -->
        <div class="mt-6 text-center">
          <p class="text-white/30 text-xs">Show this QR code at the cinema entrance</p>
          <div class="inline-flex items-center justify-center p-4 bg-white rounded-xl mt-2">
            <div class="w-32 h-32 bg-gray-200 rounded-lg flex items-center justify-center">
              <Icon name="lucide:qr-code" class="w-24 h-24 text-gray-600" />
            </div>
          </div>
          <p class="text-white/20 text-xs mt-2">Scan at the entrance for verification</p>
        </div>

      </template>
    </div>
  </div>
</template>

<script setup>
definePageMeta({ middleware: 'auth' })

const route = useRoute()
const { $api } = useNuxtApp()

const bookingCode = route.query.code
const loading = ref(true)
const error = ref(null)
const booking = ref(null)

// ── Fetch booking details ──────────────────────────────
const fetchBooking = async () => {
  if (!bookingCode) {
    error.value = 'No booking code provided'
    loading.value = false
    return
  }

  loading.value = true
  error.value = null

  try {
    // Get user's bookings and find by code
    const response = await $api('/user/bookings', { params: { per_page: 50 } })
    const bookings = response.data || response

    // Find the booking with matching code
    const foundBooking = bookings.find(b => b.booking_code === bookingCode)

    if (!foundBooking) {
      error.value = 'Booking not found'
      return
    }

    // Fetch full booking details
    const details = await $api(`/bookings/${foundBooking.id}`)
    booking.value = {
      id: details.id,
      booking_code: details.booking_code,
      status: details.status,
      total_amount: Number(details.total_amount),

      // ✅ Movie
      movie: {
        title: details.showtime?.movie?.title,
        poster: details.showtime?.movie?.poster,
        slug: details.showtime?.movie?.slug,
      },

      // ✅ Cinema
      cinema: {
        name: details.showtime?.hall?.cinema?.name,
      },

      // ✅ Hall
      hall: {
        name: details.showtime?.hall?.name,
      },

      // ✅ Showtime
      showtime: {
        date: details.showtime?.show_date,
        time: details.showtime?.show_time,
        format: details.showtime?.format,
      },

      // ✅ Seats
      seats: details.booking_seats?.map(s => ({
        seat_code: s.seat?.seat_code,
        type: s.seat?.type,
        price: Number(s.price),
      })) || [],
    }

    // If not confirmed, redirect to checkout
    if (booking.value.status !== 'confirmed') {
      navigateTo(`/checkout/${booking.value.movie?.slug}?booking_id=${foundBooking.id}`)
    }
  } catch (err) {
    console.error('Failed to fetch booking:', err)
    error.value = err?.data?.message || 'Failed to load booking details'
  } finally {
    loading.value = false
  }
}

// ── Helpers ─────────────────────────────────────────────
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatTime = (timeStr) => {
  if (!timeStr) return ''
  return timeStr.slice(0, 5)
}

const downloadTicket = () => {
  // In a real app, generate PDF ticket
  alert('PDF download would start here')
}

const printTicket = () => {
  window.print()
}

// ── Lifecycle ──────────────────────────────────────────
onMounted(() => {
  fetchBooking()
})
</script>

<style scoped>
@media print {
  .min-h-screen {
    background: white !important;
  }

  .bg-gradient-to-br,
  .bg-red-600\/10,
  .bg-white\/10,
  .bg-red-600\/20 {
    background: #f3f4f6 !important;
    border-color: #e5e7eb !important;
  }

  .text-white,
  .text-white\/40,
  .text-white\/50,
  .text-white\/60,
  .text-white\/70 {
    color: #1f2937 !important;
  }

  .text-red-500,
  .text-yellow-400,
  .text-green-400 {
    color: #000 !important;
  }

  .bg-red-500\/20,
  .bg-yellow-500\/20,
  .bg-green-500\/20 {
    background: #e5e7eb !important;
  }

  .p-6,
  .px-6,
  .py-4,
  .p-4 {
    padding: 1rem !important;
  }

  .gap-3,
  .gap-4 {
    gap: 1rem !important;
  }

  button,
  .nuxt-link {
    display: none !important;
  }
}
</style>