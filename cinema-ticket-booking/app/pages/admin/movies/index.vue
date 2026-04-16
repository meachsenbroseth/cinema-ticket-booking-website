<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Movies</h1>
        <p class="text-white/50 text-sm mt-1">Manage movie listings, showtimes, and availability</p>
      </div>
      <button
        @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors shadow-lg shadow-red-500/20"
      >
        <Icon name="lucide:film-plus" class="w-4 h-4" />
        Add Movie
      </button>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
          <Icon name="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by title..."
            class="w-full bg-black/40 border border-white/10 rounded-lg pl-9 pr-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500 transition-colors"
          />
        </div>
        <select
          v-model="statusFilter"
          class="bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500"
        >
          <option value="all">All Status</option>
          <option value="now_showing">Now Showing</option>
          <option value="coming_soon">Coming Soon</option>
        </select>
      </div>
    </div>

    <!-- Movies Table -->
    <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-white/5 border-b border-white/10">
            <tr>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Poster</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Title</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Genre</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Rating</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Status</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Show Dates</th>
              <th class="text-right px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="movie in paginatedMovies" :key="movie.id" class="hover:bg-white/5 transition-colors">
              <td class="px-4 py-3">
                <img :src="getImageUrl(movie.poster)" :alt="movie.title" class="w-10 h-14 object-cover rounded-lg" />
              </td>
              <td class="px-4 py-3">
                <span class="text-white font-medium">{{ movie.title }}</span>
              </td>
              <td class="px-4 py-3 text-white/70 text-sm">{{ movie.genres?.join(', ') || '-' }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-1">
                  <Icon name="lucide:star" class="w-3.5 h-3.5 text-yellow-500 fill-yellow-500" />
                  <span class="text-white text-sm">{{ movie.rating }}</span>
                </div>
              </td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="movie.status === 'now_showing' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'"
                >
                  {{ movie.status === 'now_showing' ? 'Now Showing' : 'Coming Soon' }}
                </span>
              </td>
              <td class="px-4 py-3 text-white/50 text-sm">
                {{ movie.show_dates?.length ? movie.show_dates.join(', ') : '-' }}
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(movie)" class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors">
                    <Icon name="lucide:edit" class="w-4 h-4" />
                  </button>
                  <button @click="confirmDelete(movie)" class="p-1.5 rounded-lg text-white/50 hover:text-red-500 hover:bg-red-500/10 transition-colors">
                    <Icon name="lucide:trash-2" class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!loading && movies.length === 0">
              <td colspan="7" class="px-4 py-12 text-center text-white/40">No movies found</td>
            </tr>
            <tr v-if="loading">
              <td colspan="7" class="px-4 py-12 text-center text-white/40">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t border-white/10 bg-white/5">
        <div class="text-white/40 text-sm">
          Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredMovies.length) }} of {{ filteredMovies.length }} movies
        </div>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">Previous</button>
          <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">Next</button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Movie Modal -->
    <DialogRoot v-model:open="modalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl max-h-[85vh] overflow-y-auto bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50 shadow-2xl">
          <DialogTitle class="text-xl font-bold text-white mb-2">
            {{ editingMovie ? 'Edit Movie' : 'Add New Movie' }}
          </DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">
            {{ editingMovie ? 'Update movie details.' : 'Fill in the details to add a new movie.' }}
          </DialogDescription>

          <form @submit.prevent="saveMovie" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-white/60 text-sm mb-1">Title</label>
                <input v-model="form.title" type="text" required @input="generateSlugFromTitle" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" placeholder="Movie Title" />
              </div>
              <div>
                <label class="block text-white/60 text-sm mb-1">Slug (auto-generated)</label>
                <input v-model="form.slug" type="text" disabled class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white/50 cursor-not-allowed" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-white/60 text-sm mb-1">Genres (comma separated)</label>
                <input v-model="form.genreInput" type="text" required class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" placeholder="Action, Drama, Sci-Fi" />
              </div>
              <div>
                <label class="block text-white/60 text-sm mb-1">Rating (0-10)</label>
                <input v-model="form.rating" type="number" step="0.1" min="0" max="10" required class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" placeholder="8.5" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-white/60 text-sm mb-1">Director</label>
                <input v-model="form.director" type="text" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" placeholder="Denis Villeneuve" />
              </div>
              <div>
                <label class="block text-white/60 text-sm mb-1">Duration</label>
                <input v-model="form.duration" type="text" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" placeholder="166 mins" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-white/60 text-sm mb-1">Release Date</label>
                <input v-model="form.release_date" type="date" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500" />
              </div>
              <div>
                <label class="block text-white/60 text-sm mb-1">Trailer URL</label>
                <input v-model="form.trailer_url" type="url" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" placeholder="https://youtube.com/..." />
              </div>
            </div>

            <!-- Image Uploads -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-white/60 text-sm mb-1">Poster Image</label>
                <div class="flex gap-3 items-start">
                  <div class="flex-1">
                    <input type="file" accept="image/*" @change="handlePosterUpload" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white/70 text-sm file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-red-600 file:text-white hover:file:bg-red-500" />
                  </div>
                  <div v-if="form.posterPreview" class="w-12 h-16 rounded-lg overflow-hidden border border-white/10 shrink-0">
                    <img :src="form.posterPreview" alt="Poster preview" class="w-full h-full object-cover" />
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-white/60 text-sm mb-1">Banner Image</label>
                <div class="flex gap-3 items-start">
                  <div class="flex-1">
                    <input type="file" accept="image/*" @change="handleBannerUpload" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white/70 text-sm file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-red-600 file:text-white hover:file:bg-red-500" />
                  </div>
                  <div v-if="form.bannerPreview" class="w-16 h-12 rounded-lg overflow-hidden border border-white/10 shrink-0">
                    <img :src="form.bannerPreview" alt="Banner preview" class="w-full h-full object-cover" />
                  </div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-white/60 text-sm mb-1">Status</label>
                <select v-model="form.status" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                  <option value="now_showing">Now Showing</option>
                  <option value="coming_soon">Coming Soon</option>
                </select>
              </div>
              <div></div>
            </div>

            <div>
              <label class="block text-white/60 text-sm mb-1">Synopsis</label>
              <textarea v-model="form.synopsis" rows="3" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" placeholder="Movie description..."></textarea>
            </div>

            <div class="flex gap-3 pt-4">
              <button type="button" @click="modalOpen = false" class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5 transition-colors">Cancel</button>
              <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition-colors">{{ editingMovie ? 'Update' : 'Create' }}</button>
            </div>
          </form>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- Delete Confirmation Dialog -->
    <DialogRoot v-model:open="deleteDialogOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50">
          <DialogTitle class="text-xl font-bold text-white mb-2">Delete Movie</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">Are you sure you want to delete <span class="text-red-400">{{ movieToDelete?.title }}</span>? This action cannot be undone.</DialogDescription>
          <div class="flex gap-3">
            <button @click="deleteDialogOpen = false" class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5 transition-colors">Cancel</button>
            <button @click="deleteMovie" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition-colors">Delete</button>
          </div>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- Toast -->
    <div v-if="toastMessage" class="fixed bottom-4 right-4 z-50 animate-slide-up">
      <div :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'" class="px-4 py-3 rounded-lg shadow-lg text-white text-sm">{{ toastMessage }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import {
  DialogRoot,
  DialogPortal,
  DialogOverlay,
  DialogContent,
  DialogTitle,
  DialogDescription,
} from 'radix-vue'
import { useAuthStore } from '~/stores/authStore'

definePageMeta({ layout: 'admin-layout' })

const authStore = useAuthStore()
const { $api } = useNuxtApp()

// State
const movies = ref([])
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')
const modalOpen = ref(false)
const editingMovie = ref(null)
const deleteDialogOpen = ref(false)
const movieToDelete = ref(null)

// Pagination (client-side)
const pageSize = 5
const currentPage = ref(1)

// Form data (matches backend fields)
const form = reactive({
  title: '',
  slug: '',
  genreInput: '',
  rating: '',
  poster: null,
  banner: null,
  posterPreview: '',
  bannerPreview: '',
  status: 'now_showing',
  duration: '',
  synopsis: '',
  director: '',
  release_date: '',
  trailer_url: '',
})

// Helper: generate slug
const generateSlug = (title) => {
  return title.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-')
}
const generateSlugFromTitle = () => {
  form.slug = form.title ? generateSlug(form.title) : ''
}

// Helper: full image URL
const getImageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return useRuntimeConfig().public.apiBase.replace('/api', '') + path
}

// Fetch movies
const fetchMovies = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize,
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (statusFilter.value !== 'all') params.status = statusFilter.value

    const response = await $api('/movies', { params })
    movies.value = response.data
    // Reset to first page if current page out of range
    if (currentPage.value > response.last_page && response.last_page > 0) {
      currentPage.value = response.last_page
      await fetchMovies() // re-fetch
    }
  } catch (err) {
    console.error('Fetch error:', err)
    showToast('Failed to load movies', 'error')
  } finally {
    loading.value = false
  }
}

// Computed filtered & paginated (server already paginates, but we keep for UI)
const filteredMovies = computed(() => movies.value)
const totalPages = computed(() => Math.ceil(filteredMovies.value.length / pageSize))
const paginatedMovies = computed(() => filteredMovies.value)

// Watch filters and re-fetch
watch([searchQuery, statusFilter], () => {
  currentPage.value = 1
  fetchMovies()
})

// Modal handlers
const openCreateModal = () => {
  editingMovie.value = null
  Object.assign(form, {
    title: '',
    slug: '',
    genreInput: '',
    rating: '',
    poster: null,
    banner: null,
    posterPreview: '',
    bannerPreview: '',
    status: 'now_showing',
    duration: '',
    synopsis: '',
    director: '',
    release_date: '',
    trailer_url: '',
  })
  modalOpen.value = true
}

const openEditModal = (movie) => {
  editingMovie.value = movie
  form.title = movie.title
  form.slug = movie.slug
  form.genreInput = movie.genres?.join(', ') || ''
  form.rating = movie.rating
  form.poster = movie.poster
  form.banner = movie.banner
  form.posterPreview = getImageUrl(movie.poster)
  form.bannerPreview = getImageUrl(movie.banner)
  form.status = movie.status
  form.duration = movie.duration || ''
  form.synopsis = movie.synopsis || ''
  form.director = movie.director || ''
  form.release_date = movie.release_date || ''
  form.trailer_url = movie.trailer_url || ''
  modalOpen.value = true
}

// Save movie
const saveMovie = async () => {
  const formData = new FormData()
  formData.append('title', form.title)
  formData.append('synopsis', form.synopsis)
  formData.append('status', form.status)
  if (form.director) formData.append('director', form.director)
  if (form.duration) formData.append('duration', form.duration)
  if (form.release_date) formData.append('release_date', form.release_date)
  if (form.rating) formData.append('rating', form.rating)
  if (form.trailer_url) formData.append('trailer_url', form.trailer_url)

  const genres = form.genreInput.split(',').map(g => g.trim()).filter(g => g)
  genres.forEach(g => formData.append('genres[]', g))

  if (form.poster && typeof form.poster !== 'string') formData.append('poster', form.poster)
  if (form.banner && typeof form.banner !== 'string') formData.append('banner', form.banner)

  try {
    if (editingMovie.value) {
      formData.append('_method', 'PUT')
      await $api(`/movies/${editingMovie.value.id}`, { method: 'POST', body: formData })
      showToast('Movie updated', 'success')
    } else {
      await $api('/movies', { method: 'POST', body: formData })
      showToast('Movie created', 'success')
    }
    modalOpen.value = false
    await fetchMovies()
  } catch (err) {
    console.error('Save error:', err)
    showToast(err?.data?.message || 'Operation failed', 'error')
  }
}

// Delete
const confirmDelete = (movie) => {
  movieToDelete.value = movie
  deleteDialogOpen.value = true
}
const deleteMovie = async () => {
  try {
    await $api(`/movies/${movieToDelete.value.id}`, { method: 'DELETE' })
    showToast('Movie deleted', 'success')
    deleteDialogOpen.value = false
    await fetchMovies()
  } catch (err) {
    console.error('Delete error:', err)
    showToast(err?.data?.message || 'Delete failed', 'error')
  }
}

// Image upload previews
const handlePosterUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.poster = file
    const reader = new FileReader()
    reader.onload = (e) => { form.posterPreview = e.target.result }
    reader.readAsDataURL(file)
  }
}
const handleBannerUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.banner = file
    const reader = new FileReader()
    reader.onload = (e) => { form.bannerPreview = e.target.result }
    reader.readAsDataURL(file)
  }
}

// Toast
const toastMessage = ref('')
const toastType = ref('success')
let toastTimeout = null
const showToast = (msg, type = 'success') => {
  if (toastTimeout) clearTimeout(toastTimeout)
  toastMessage.value = msg
  toastType.value = type
  toastTimeout = setTimeout(() => { toastMessage.value = '' }, 3000)
}

onMounted(() => {
  fetchMovies()
})
</script>

<style scoped>
@keyframes slide-up {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}
</style>