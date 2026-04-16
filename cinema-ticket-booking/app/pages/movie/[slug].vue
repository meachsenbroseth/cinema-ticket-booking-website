<template>
  <div class="min-h-screen bg-[#0d0000]">
    <!-- Hero Banner -->
    <section class="relative h-[60vh] md:h-[70vh] overflow-hidden">
      <div class="absolute inset-0">
        <img :src="movie?.banner" :alt="movie?.title" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-gradient-to-t from-[#0d0000] via-[#0d0000]/80 to-transparent" />
        <div class="absolute inset-0 bg-gradient-to-r from-[#0d0000] via-transparent to-transparent" />
      </div>

      <div class="relative container mx-auto px-4 h-full flex items-end pb-12">
        <div class="flex flex-col md:flex-row gap-8 items-end md:items-start w-full">
          <!-- Poster with glass frame -->
          <div class="w-64 flex-shrink-0 animate-fade-in-up">
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-red-500/30 backdrop-blur-sm">
              <img :src="movie?.poster" :alt="movie?.title" class="w-full" />
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1 animate-fade-in-up animation-delay-200">
            <div class="flex flex-wrap gap-2 mb-4">
              <span v-for="genre in movie?.genres" :key="genre"
                class="px-3 py-1 rounded-full bg-red-500/20 text-red-500 text-sm border border-red-500/40 backdrop-blur-sm">
                {{ genre }}
              </span>
            </div>

            <h1 class="text-4xl md:text-6xl font-black text-white mb-4 drop-shadow-lg">{{ movie?.title }}</h1>

            <div class="flex flex-wrap gap-6 mb-6 text-sm">
              <div class="flex items-center gap-2 bg-black/30 backdrop-blur-sm px-3 py-1 rounded-full">
                <Icon name="lucide:star" class="w-4 h-4 text-yellow-500 fill-yellow-500" />
                <span class="font-semibold text-white">{{ movie?.rating }}/10</span>
              </div>
              <div class="flex items-center gap-2 bg-black/30 backdrop-blur-sm px-3 py-1 rounded-full text-white/80">
                <Icon name="lucide:clock" class="w-4 h-4" />
                <span>{{ movie?.duration }}</span>
              </div>
              <div class="flex items-center gap-2 bg-black/30 backdrop-blur-sm px-3 py-1 rounded-full text-white/80">
                <Icon name="lucide:calendar" class="w-4 h-4" />
                <span>{{ movie?.release_date_formatted }}</span>
              </div>
            </div>

            <div class="flex gap-4">
              <a v-if="movie?.trailer_url" :href="movie.trailer_url" target="_blank"
                class="group px-8 py-3 rounded-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-semibold shadow-lg shadow-red-500/30 transition-all flex items-center gap-2 transform hover:scale-105">
                <Icon name="lucide:play" class="w-5 h-5 fill-white group-hover:animate-pulse" />
                Watch Trailer
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Details Section -->
    <section class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">

          <!-- Synopsis -->
          <div
            class="group bg-white/5 border border-white/10 rounded-2xl p-6 hover:border-white/20 transition-all duration-300 backdrop-blur-sm">
            <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
              <Icon name="lucide:clapperboard" class="w-6 h-6 text-red-500" />
              Synopsis
            </h2>
            <p class="text-white/70 leading-relaxed">{{ movie?.synopsis }}</p>
          </div>

          <!-- Cast -->
          <div v-if="movie?.cast?.length" class="bg-white/5 border border-white/10 rounded-2xl p-6">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
              <Icon name="lucide:users" class="w-6 h-6 text-red-500" />
              Cast
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
              <div v-for="actor in movie.cast" :key="actor.id" class="text-center group">
                <div class="relative w-24 h-24 mx-auto mb-3">
                  <div
                    class="absolute inset-0 rounded-full bg-gradient-to-br from-red-500 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 blur-md">
                  </div>
                  <div
                    class="relative w-24 h-24 rounded-full overflow-hidden border-2 border-white/20 group-hover:border-red-500 transition-all duration-300">
                    <img :src="actor.avatar" :alt="actor.name" class="w-full h-full object-cover" />
                  </div>
                </div>
                <h4 class="font-semibold text-white text-sm">{{ actor.name }}</h4>
                <p class="text-xs text-white/40">{{ actor.character }}</p>
              </div>
            </div>
          </div>

          <!-- Showtimes Section -->
          <div id="showtimes" class="bg-white/5 border border-white/10 rounded-2xl p-6 scroll-mt-24">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
              <Icon name="lucide:calendar-clock" class="w-6 h-6 text-red-500" />
              Showtimes
            </h2>

            <!-- Date Picker -->
            <div class="flex gap-3 mb-8 overflow-x-auto pb-3 scrollbar-hide">
              <button v-for="(date, index) in availableDates" :key="index" @click="selectedDate = date"
                class="flex flex-col items-center px-5 py-3 rounded-xl border transition-all duration-200 shrink-0 min-w-[80px] backdrop-blur-sm"
                :class="selectedDate?.dateString === date.dateString
                  ? 'border-red-600 bg-red-600/20 text-white shadow-lg shadow-red-500/20'
                  : 'border-white/10 bg-black/40 text-white/60 hover:border-white/30 hover:text-white hover:bg-white/5'">
                <span class="text-xs font-medium uppercase tracking-wider">{{ date.dayName }}</span>
                <span class="text-2xl font-black leading-none mt-1">{{ date.day }}</span>
                <span class="text-xs opacity-70 mt-0.5">{{ date.month }}</span>
              </button>
            </div>

            <!-- Showtimes Loading State -->
            <div v-if="showtimeStore.loading" class="space-y-4">
              <div v-for="i in 2" :key="i" class="animate-pulse">
                <div class="h-32 bg-white/10 rounded-xl"></div>
              </div>
            </div>

            <!-- No Showtimes -->
            <div v-else-if="filteredShowtimes.length === 0" class="text-center py-12">
              <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-white/5 flex items-center justify-center">
                <Icon name="lucide:calendar-x" class="w-12 h-12 text-white/30" />
              </div>
              <p class="text-white/40 text-sm">No showtimes available for this date</p>
              <p class="text-white/20 text-xs mt-1">Try selecting another day</p>
            </div>

            <!-- Cinema Showtimes List -->
            <div v-else class="space-y-5">
              <div v-for="cinema in filteredShowtimes" :key="cinema.id"
                class="group border-l-2 border-red-600 pl-5 hover:pl-6 transition-all duration-300">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-3">
                  <div class="flex-1">
                    <h3 class="font-bold text-white text-lg flex items-center gap-2">
                      <Icon name="lucide:building-2" class="w-4 h-4 text-red-500" />
                      {{ cinema.cinema }}
                    </h3>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-white/50">
                      <span class="flex items-center gap-1">
                        <Icon name="lucide:map-pin" class="w-3.5 h-3.5" />
                        {{ cinema.address }}
                      </span>
                      <span class="flex items-center gap-1">
                        <Icon name="lucide:monitor" class="w-3.5 h-3.5" />
                        {{ cinema.format }}
                      </span>
                    </div>
                  </div>
                  <div class="text-right shrink-0">
                    <div class="text-white/40 text-xs uppercase tracking-wider">Starting from</div>
                    <div class="text-red-500 font-black text-2xl">${{ cinema.price }}</div>
                  </div>
                </div>

                <!-- Time Slots Grid -->
                <div class="flex flex-wrap gap-3 mt-3">
                  <!-- <button
                    v-for="slot in cinema.times"
                    :key="slot.id"
                    @click="selectShowtime(cinema, slot)"
                    class="relative px-5 py-2.5 rounded-xl border transition-all duration-200 font-semibold text-sm overflow-hidden group/btn"
                    :class="selectedShowtime?.showtimeId === slot.id
                      ? 'bg-red-600 text-white border-red-600 shadow-lg shadow-red-500/30 scale-105'
                      : 'border-white/15 bg-white/5 text-white/80 hover:border-red-500 hover:bg-red-500/10 hover:text-white hover:scale-105'"
                  >
                    {{ slot.time }}
                    <span
                      v-if="selectedShowtime?.showtimeId === slot.id"
                      class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse shadow-lg shadow-green-500/50"
                    />
                  </button> -->
                  <!-- Time Slots — slot is { id, time } -->
                  <button v-for="slot in cinema.times" :key="slot.id" @click="selectShowtime(cinema, slot)"
                    class="relative px-5 py-2.5 rounded-xl border transition-all duration-200 font-semibold text-sm"
                    :class="selectedShowtime?.showtimeId === slot.id
                      ? 'bg-red-600 text-white border-red-600 shadow-lg shadow-red-500/30 scale-105'
                      : 'border-white/15 bg-white/5 text-white/80 hover:border-red-500 hover:bg-red-500/10 hover:text-white'">
                    {{ slot.time }} <!-- ✅ shows "7:00 PM" -->
                    <span v-if="selectedShowtime?.showtimeId === slot.id"
                      class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Selected Showtime Summary Card -->
            <transition name="slide-up">
              <div v-if="selectedShowtime"
                class="mt-8 p-5 bg-gradient-to-r from-red-600/20 to-red-600/5 border border-red-500/30 rounded-xl backdrop-blur-sm">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center">
                      <Icon name="lucide:ticket" class="w-6 h-6 text-red-500" />
                    </div>
                    <div>
                      <p class="text-white font-semibold">Ready to book?</p>
                      <p class="text-white/50 text-sm">
                        {{ selectedShowtime.cinemaName }} · {{ selectedShowtime.time }} · {{ formatDate(selectedDate) }}
                      </p>
                    </div>
                  </div>
                  <button @click="proceedToBooking"
                    class="px-6 py-2.5 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl transition-all transform hover:scale-105 shadow-lg shadow-red-500/30 flex items-center gap-2 group/btn">
                    Continue
                    <Icon name="lucide:arrow-right"
                      class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" />
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>

        <!-- Sidebar Info Card -->
        <div class="space-y-6">
          <div
            class="sticky top-24 bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-sm transition-all duration-300 hover:border-white/20">
            <h3 class="text-xl font-bold text-white mb-5 flex items-center gap-2">
              <Icon name="lucide:info" class="w-5 h-5 text-red-500" />
              Movie Info
            </h3>
            <div class="space-y-4">
              <div class="border-b border-white/10 pb-3">
                <span class="text-white/40 text-xs uppercase tracking-wider block mb-1">Director</span>
                <p class="font-semibold text-white">{{ movie?.director || 'TBA' }}</p>
              </div>
              <div class="border-b border-white/10 pb-3">
                <span class="text-white/40 text-xs uppercase tracking-wider block mb-1">Genres</span>
                <div class="flex flex-wrap gap-1 mt-1">
                  <span v-for="g in movie?.genres" :key="g" class="text-sm text-white/80">• {{ g }}</span>
                </div>
              </div>
              <div class="border-b border-white/10 pb-3">
                <span class="text-white/40 text-xs uppercase tracking-wider block mb-1">Duration</span>
                <p class="font-semibold text-white">{{ movie?.duration || 'N/A' }}</p>
              </div>
              <div class="border-b border-white/10 pb-3">
                <span class="text-white/40 text-xs uppercase tracking-wider block mb-1">Release Date</span>
                <p class="font-semibold text-white">{{ movie?.release_date_formatted || 'TBA' }}</p>
              </div>
              <div>
                <span class="text-white/40 text-xs uppercase tracking-wider block mb-1">Rating</span>
                <div class="flex items-center gap-2">
                  <div class="flex-1 h-2 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-yellow-500 to-yellow-400 rounded-full"
                      :style="{ width: `${(movie?.rating || 0) * 10}%` }"></div>
                  </div>
                  <span class="font-bold text-yellow-400">{{ movie?.rating || 'NR' }}/10</span>
                </div>
              </div>
            </div>

            <button v-if="movie?.status === 'now_showing'" @click="scrollToShowtimes"
              class="mt-6 w-full py-3 px-4 rounded-xl bg-red-600 hover:bg-red-500 text-white font-semibold transition-all flex items-center justify-center gap-2 group/btn shadow-lg shadow-red-500/20">
              <Icon name="lucide:clock" class="w-4 h-4" />
              View Showtimes
              <Icon name="lucide:chevron-down" class="w-4 h-4 group-hover/btn:translate-y-1 transition-transform" />
            </button>
          </div>
        </div>

      </div>
    </section>

    <!-- Not Found & Loading States -->
    <div v-if="!movie && !loading" class="container mx-auto px-4 py-20 text-center">
      <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-white/5 flex items-center justify-center">
        <Icon name="lucide:film" class="w-16 h-16 text-white/20" />
      </div>
      <h1 class="text-3xl font-bold text-white mb-3">Movie Not Found</h1>
      <p class="text-white/40 mb-6">The movie you're looking for doesn't exist or has been removed.</p>
      <NuxtLink to="/"
        class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-500 text-white rounded-xl transition-all">
        <Icon name="lucide:arrow-left" class="w-4 h-4" />
        Back to Home
      </NuxtLink>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
          <div class="animate-pulse">
            <div class="h-64 bg-white/10 rounded-2xl mb-6"></div>
            <div class="h-32 bg-white/10 rounded-2xl"></div>
            <div class="grid grid-cols-4 gap-4 mt-6">
              <div v-for="i in 4" :key="i" class="h-32 bg-white/10 rounded-2xl"></div>
            </div>
          </div>
        </div>
        <div class="space-y-6">
          <div class="animate-pulse h-96 bg-white/10 rounded-2xl"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useShowtimeStore } from '~/stores/showtimeStore'

const route = useRoute()
const slug = route.params.slug
const { $api } = useNuxtApp()

const showtimeStore = useShowtimeStore()

const loading = ref(true)
const movie = ref(null)
const selectedDate = ref(null)
const selectedShowtime = ref(null)

// Available dates with local timezone handling
const availableDates = computed(() => {
  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date()
    d.setDate(d.getDate() + i)
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    const dateString = `${year}-${month}-${day}`

    return {
      day: d.getDate(),
      month: d.toLocaleDateString('en-US', { month: 'short' }),
      dayName: i === 0 ? 'Today' : d.toLocaleDateString('en-US', { weekday: 'short' }),
      fullDate: d,
      dateString,
    }
  })
})

const filteredShowtimes = computed(() => {
  if (!selectedDate.value) return []
  const key = selectedDate.value.dateString
  return showtimeStore.movieShowtimes[key] ?? []
})

const fetchMovie = async () => {
  try {
    const response = await $api(`/movies/${slug}`)
    movie.value = {
      ...response,
      release_date_formatted: response.release_date
        ? new Date(response.release_date).toLocaleDateString('en-US', {
          year: 'numeric', month: 'long', day: 'numeric',
        })
        : null,
      genres: Array.isArray(response.genres) ? response.genres : [],
    }
  } catch (err) {
    console.error('Failed to fetch movie:', err)
    movie.value = null
  }
}

const selectShowtime = (cinema, slot) => {
  if (selectedShowtime.value?.showtimeId === slot.id) {
    selectedShowtime.value = null
    return
  }
  selectedShowtime.value = {
    cinemaId: cinema.id,
    cinemaName: cinema.cinema,
    showtimeId: slot.id,
    time: slot.time,
    price: cinema.price,
    format: cinema.format,
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return `${date.dayName}, ${date.month} ${date.day}`
}

const proceedToBooking = () => {
  if (!selectedShowtime.value) return
  navigateTo({
    path: `/movie/seat/${slug}`,
    query: {
      showtime_id: selectedShowtime.value.showtimeId,
      cinema: selectedShowtime.value.cinemaName,
      time: selectedShowtime.value.time,
      date: selectedDate.value.dateString,
      price: selectedShowtime.value.price,
    },
  })
}

const scrollToShowtimes = () => {
  document.getElementById('showtimes')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

onMounted(async () => {
  loading.value = true
  showtimeStore.reset()
  await Promise.all([fetchMovie(), showtimeStore.fetchMovieShowtimes(slug)])
  selectedDate.value = availableDates.value[0]
  loading.value = false
})

onUnmounted(() => showtimeStore.reset())
</script>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInRight {
  from {
    opacity: 0;
    transform: translateX(30px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
}

.animate-fade-in-right {
  animation: fadeInRight 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
}

.animation-delay-200 {
  animation-delay: 0.15s;
}

.animation-delay-300 {
  animation-delay: 0.25s;
}

.animation-delay-400 {
  animation-delay: 0.35s;
}

.animation-delay-500 {
  animation-delay: 0.45s;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease-out;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>