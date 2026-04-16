
<!-- pages/admin/banners.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Banners</h1>
        <p class="text-white/50 text-sm mt-1">Manage homepage hero banners</p>
      </div>
      <button
        @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors shadow-lg shadow-red-500/20"
      >
        <Icon name="lucide:plus" class="w-4 h-4" />
        Add Banner
      </button>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
          <Icon name="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by title..."
            class="w-full bg-black/40 border border-white/10 rounded-lg pl-9 pr-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
          />
        </div>
        <select
          v-model="statusFilter"
          class="bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500"
        >
          <option value="all">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </div>

    <!-- Banners Table -->
    <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-white/5 border-b border-white/10">
            <tr>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Image</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Title</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Description</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Order</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Status</th>
              <th class="text-right px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="banner in paginatedBanners" :key="banner.id" class="hover:bg-white/5 transition-colors">
              <td class="px-4 py-3">
                <img :src="banner.image" :alt="banner.title" class="w-16 h-10 object-cover rounded-lg" />
              </td>
              <td class="px-4 py-3">
                <span class="text-white font-medium">{{ banner.title }}</span>
              </td>
              <td class="px-4 py-3 text-white/70 text-sm max-w-xs truncate">{{ banner.description }}</td>
              <td class="px-4 py-3 text-white/70">{{ banner.order }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="banner.isActive ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                >
                  {{ banner.isActive ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(banner)" class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors">
                    <Icon name="lucide:edit" class="w-4 h-4" />
                  </button>
                  <button @click="confirmDelete(banner)" class="p-1.5 rounded-lg text-white/50 hover:text-red-500 hover:bg-red-500/10 transition-colors">
                    <Icon name="lucide:trash-2" class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredBanners.length === 0">
              <td colspan="6" class="px-4 py-12 text-center text-white/40">No banners found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t border-white/10 bg-white/5">
        <div class="text-white/40 text-sm">
          Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredBanners.length) }} of {{ filteredBanners.length }} banners
        </div>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed">Previous</button>
          <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed">Next</button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Banner Modal -->
    <DialogRoot v-model:open="modalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50 shadow-2xl">
          <DialogTitle class="text-xl font-bold text-white mb-2">{{ editingBanner ? 'Edit Banner' : 'Add New Banner' }}</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">{{ editingBanner ? 'Update banner details.' : 'Add a new homepage banner.' }}</DialogDescription>

          <form @submit.prevent="saveBanner" class="space-y-4">
            <div>
              <label class="block text-white/60 text-sm mb-1">Title</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
                placeholder="Banner title"
              />
            </div>
            <div>
              <label class="block text-white/60 text-sm mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="2"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
                placeholder="Short description"
              ></textarea>
            </div>
            <div>
              <label class="block text-white/60 text-sm mb-1">Banner Image</label>
              <div class="flex gap-3 items-start">
                <div class="flex-1">
                  <input
                    type="file"
                    accept="image/*"
                    @change="handleImageUpload"
                    class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white/70 text-sm file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-red-600 file:text-white hover:file:bg-red-500"
                  />
                </div>
                <div v-if="form.imagePreview" class="w-16 h-10 rounded-lg overflow-hidden border border-white/10 shrink-0">
                  <img :src="form.imagePreview" alt="Preview" class="w-full h-full object-cover" />
                </div>
              </div>
            </div>
            <div>
              <label class="block text-white/60 text-sm mb-1">Link URL (optional)</label>
              <input
                v-model="form.link"
                type="url"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
                placeholder="https://..."
              />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-white/60 text-sm mb-1">Display Order</label>
                <input
                  v-model.number="form.order"
                  type="number"
                  required
                  min="0"
                  class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500"
                />
              </div>
              <div>
                <label class="block text-white/60 text-sm mb-1">Status</label>
                <select v-model="form.isActive" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                  <option :value="true">Active</option>
                  <option :value="false">Inactive</option>
                </select>
              </div>
            </div>

            <div class="flex gap-3 pt-4">
              <button type="button" @click="modalOpen = false" class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5">Cancel</button>
              <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg">{{ editingBanner ? 'Update' : 'Create' }}</button>
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
          <DialogTitle class="text-xl font-bold text-white mb-2">Delete Banner</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">Are you sure you want to delete <span class="text-red-400">{{ bannerToDelete?.title }}</span>? This action cannot be undone.</DialogDescription>
          <div class="flex gap-3">
            <button @click="deleteDialogOpen = false" class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5">Cancel</button>
            <button @click="deleteBanner" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg">Delete</button>
          </div>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import {
  DialogRoot,
  DialogPortal,
  DialogOverlay,
  DialogContent,
  DialogTitle,
  DialogDescription,
} from 'radix-vue'

definePageMeta({ layout: 'admin-layout' })

// ---------- Mock Data ----------
const banners = ref([
  {
    id: 1,
    title: 'Dune: Part Two',
    description: 'Experience the epic journey on the big screen.',
    image: 'https://image.tmdb.org/t/p/original/xOMo8BRK7PfcJv9JCnx7s5hj0PX.jpg',
    link: '/movie/dune-part-two',
    order: 1,
    isActive: true,
  },
  {
    id: 2,
    title: 'Godzilla x Kong',
    description: 'Two icons collide in an epic battle.',
    image: 'https://image.tmdb.org/t/p/original/tMefBSflR6PGQLv7WvFPpKLZkyk.jpg',
    link: '/movie/godzilla-x-kong',
    order: 2,
    isActive: true,
  },
  {
    id: 3,
    title: 'Civil War',
    description: 'A journey across a dystopian future America.',
    image: 'https://image.tmdb.org/t/p/original/sh7Rg8Er3tFcN9BpKIPOMvALgZd.jpg',
    link: '/movie/civil-war',
    order: 3,
    isActive: false,
  },
])

// ---------- Search & Filter ----------
const searchQuery = ref('')
const statusFilter = ref('all')

const filteredBanners = computed(() => {
  let result = banners.value
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(b => b.title.toLowerCase().includes(q))
  }
  if (statusFilter.value !== 'all') {
    const isActive = statusFilter.value === 'active'
    result = result.filter(b => b.isActive === isActive)
  }
  return result
})

// ---------- Pagination ----------
const pageSize = 5
const currentPage = ref(1)
const totalPages = computed(() => Math.ceil(filteredBanners.value.length / pageSize))
const paginatedBanners = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredBanners.value.slice(start, start + pageSize)
})
watch([searchQuery, statusFilter], () => { currentPage.value = 1 })

// ---------- Modal State ----------
const modalOpen = ref(false)
const editingBanner = ref(null)

const form = reactive({
  title: '',
  description: '',
  image: '',
  imagePreview: '',
  link: '',
  order: 0,
  isActive: true,
})

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      form.imagePreview = e.target.result
      form.image = e.target.result // store base64 (for demo)
    }
    reader.readAsDataURL(file)
  }
}

const openCreateModal = () => {
  editingBanner.value = null
  form.title = ''
  form.description = ''
  form.image = ''
  form.imagePreview = ''
  form.link = ''
  form.order = banners.value.length + 1
  form.isActive = true
  modalOpen.value = true
}

const openEditModal = (banner) => {
  editingBanner.value = banner
  form.title = banner.title
  form.description = banner.description
  form.image = banner.image
  form.imagePreview = banner.image
  form.link = banner.link || ''
  form.order = banner.order
  form.isActive = banner.isActive
  modalOpen.value = true
}

const saveBanner = () => {
  const bannerData = {
    title: form.title,
    description: form.description,
    image: form.image,
    link: form.link,
    order: form.order,
    isActive: form.isActive,
  }

  if (editingBanner.value) {
    const index = banners.value.findIndex(b => b.id === editingBanner.value.id)
    if (index !== -1) {
      banners.value[index] = { ...banners.value[index], ...bannerData }
    }
  } else {
    const newId = Math.max(...banners.value.map(b => b.id), 0) + 1
    banners.value.push({ id: newId, ...bannerData })
  }
  modalOpen.value = false
}

// ---------- Delete ----------
const deleteDialogOpen = ref(false)
const bannerToDelete = ref(null)
const confirmDelete = (banner) => {
  bannerToDelete.value = banner
  deleteDialogOpen.value = true
}
const deleteBanner = () => {
  if (bannerToDelete.value) {
    banners.value = banners.value.filter(b => b.id !== bannerToDelete.value.id)
    deleteDialogOpen.value = false
    bannerToDelete.value = null
  }
}
</script>