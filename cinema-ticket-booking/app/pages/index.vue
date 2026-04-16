<template>
  <!-- hero section -->
  <section class="relative h-[70vh] md:h-[80vh] overflow-hidden z-0">
    <Banner />
  </section>

  <!-- show time -->
  <section class="px-8 py-10">

    <!-- Tabs -->
    <div class="flex items-center border-b border-white/10 mb-6">
      <button v-for="tab in tabs" :key="tab" @click="cinemaStore.setActiveTab(tab)"
        class="relative px-6 py-3 text-sm font-semibold transition-colors"
        :class="activeTab === tab ? 'text-red-500' : 'text-white/40 hover:text-white/70'">
        {{ tab }}
        <span v-if="activeTab === tab" class="absolute bottom-0 left-0 w-full h-0.5 bg-red-600 rounded-full" />
      </button>
    </div>

    <!-- Date Filter -->
    <div v-if="activeTab === 'Now Showing'" class="flex items-center justify-between gap-4 mb-8">
      <div class="flex items-center gap-3 overflow-x-auto pb-1 scrollbar-hide flex-1">
        <button v-for="(date, i) in dates" :key="i" @click="cinemaStore.setActiveDateIndex(i)"
          class="flex flex-col items-center px-5 py-3 rounded-xl border transition-colors shrink-0 min-w-[80px]"
          :class="activeDateIndex === i
            ? 'border-red-600 bg-black text-white'
            : 'border-white/10 bg-black/40 text-white/60 hover:border-white/30 hover:text-white'">
          <span class="text-xs font-medium mb-1">{{ date.label }}</span>
          <span class="text-2xl font-black leading-none">{{ date.day }}</span>
          <span class="text-xs mt-1 opacity-70">{{ date.month }}</span>
        </button>
      </div>
      <CinemaDropdown v-model="selectedCinema" class="shrink-0" />
    </div>

    <!-- Active Filters -->
    <div v-if="selectedCinema !== 'All Cinemas'" class="flex items-center gap-3 mb-6 flex-wrap">
      <span class="text-white/40 text-xs">Filters:</span>
      <span class="flex items-center gap-1 px-2 py-1 bg-red-500/20 text-red-400 text-xs rounded-full">
        <Icon name="lucide:map-pin" class="w-3 h-3" />
        {{ selectedCinema }}
        <button @click="cinemaStore.setCinema('All Cinemas')" class="ml-1 hover:text-white">
          <Icon name="lucide:x" class="w-3 h-3" />
        </button>
      </span>
    </div>

    <!-- Loading -->
    <div v-if="movieStore.loading" class="flex items-center justify-center py-20">
      <Icon name="lucide:loader-circle" class="w-8 h-8 text-red-500 animate-spin" />
    </div>

    <!-- Error -->
    <div v-else-if="movieStore.error" class="text-center py-12">
      <Icon name="lucide:wifi-off" class="w-12 h-12 text-white/20 mx-auto mb-3" />
      <p class="text-white/40 text-sm">Failed to load movies</p>
      <button @click="loadMovies" class="mt-3 text-red-500 hover:text-red-400 text-sm">
        Try again
      </button>
    </div>

    <!-- Movie Grid -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
      <NuxtLink v-for="movie in filteredMovies" :key="movie.id" :to="`/movie/${movie.slug}`"
        class="group cursor-pointer">
        <div class="relative rounded-xl overflow-hidden aspect-[2/3] bg-white/5 mb-3">
          <img :src="movie.poster" :alt="movie.title"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
          <div class="absolute top-2 right-2 bg-black/70 text-yellow-400 text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
            <Icon name="lucide:star" class="w-3 h-3 fill-yellow-400" />
            {{ movie.rating }}
          </div>
        </div>
        <p class="text-white text-sm font-semibold truncate">{{ movie.title }}</p>
        <p class="text-white/40 text-xs mt-0.5">
          {{ Array.isArray(movie.genres) ? movie.genres.join(' · ') : movie.genres }}
        </p>
      </NuxtLink>
    </div>

    <!-- No Results -->
    <div v-if="!movieStore.loading && filteredMovies.length === 0" class="text-center py-12">
      <Icon name="lucide:film" class="w-16 h-16 text-white/20 mx-auto mb-4" />
      <p class="text-white/40">No movies found.</p>
      <button @click="cinemaStore.clearAllFilters()" class="mt-4 text-red-500 hover:text-red-400 text-sm">
        Clear filters
      </button>
    </div>

  </section>
</template>

<script setup>
import Banner from '~/components/ui/Banner.vue'
import CinemaDropdown from '~/components/ui/CinemaDropdown.vue'
import { useCinemaStore } from '~/stores/cinemaStore'
import { useMovieStore } from '~/stores/movieStore'
import { storeToRefs } from 'pinia'


const cinemaStore = useCinemaStore()
const movieStore = useMovieStore()
const { selectedCinema, activeTab, activeDateIndex } = storeToRefs(cinemaStore)

const tabs = ['Now Showing', 'Coming Soon']

const dates = computed(() => {
  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date()
    d.setDate(d.getDate() + i)
    return {
      label: i === 0 ? 'Today' : d.toLocaleDateString('en-US', { weekday: 'short' }),
      day: d.getDate(),
      month: d.toLocaleDateString('en-US', { month: 'short' }),
      fullDate: `${d.getDate()}-${d.getMonth() + 1}`,
    }
  })
})

// Map tab label to API status value
const statusMap = {
  'Now Showing': 'now_showing',
  'Coming Soon': 'coming_soon',
}

const loadMovies = async () => {
  await movieStore.fetchMovies({
    status: statusMap[activeTab.value],
  })
}

// Reload when tab changes
watch(activeTab, () => {
  cinemaStore.clearAllFilters()
  loadMovies()
})

const filteredMovies = computed(() => {
  let movies = movieStore.movies ?? []

  if (activeTab.value === 'Now Showing') {
    const selectedDate = dates.value[activeDateIndex.value]
    if (selectedDate) {
      // Build ISO date from selected date object
      const d = new Date()
      d.setDate(d.getDate() + activeDateIndex.value)
      const isoDate = d.toISOString().split('T')[0] // "2026-04-13"

      movies = movies.filter((m) =>
        Array.isArray(m.show_dates) && m.show_dates.includes(isoDate)
      )
    }
  }

  // Cinema filter
  if (selectedCinema.value && selectedCinema.value !== 'All Cinemas') {
    movies = movies.filter((m) =>
      m.cinemas?.includes(selectedCinema.value)
    )
  }

  return movies
})
onMounted(async () => {
  cinemaStore.loadSavedState()
  await loadMovies()
})
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>