<template>
  <section class="p-3 sm:p-4">
    <div class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-cyan-50 to-white p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-300">Administration</p>
          <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Facilities Manager</h2>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add and manage facilities in the system.</p>
        </div>
        <div class="rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-3 text-white shadow-md shadow-cyan-500/20">
          <p class="text-[11px] uppercase tracking-[0.14em] text-cyan-100">Total Facilities</p>
          <p class="text-2xl font-bold">{{ facilities.length }}</p>
        </div>
      </div>

      <div class="mt-5 flex flex-wrap items-center gap-3">
        <select
          v-model="statusFilter"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
          @change="fetchFacilities"
        >
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        <div class="relative min-w-[220px] flex-1">
          <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
          <input
            v-model="search"
            type="search"
            class="w-full rounded-xl border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
            placeholder="Search by title or description"
            @input="debounceSearch"
          />
        </div>

        <button
          @click="openDrawer()"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-cyan-500/20 transition hover:brightness-110"
        >
          <i class="fas fa-plus"></i>
          <span>New Facility</span>
        </button>
      </div>

      <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="facility in facilities"
          :key="facility.id"
          class="rounded-xl border border-slate-200 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/80"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <img
                v-if="facility.primary_image"
                :src="storageUrl(facility.primary_image.image_path)"
                alt=""
                class="h-10 w-10 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-700"
              />
              <div class="grid h-10 w-10 place-items-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-300" v-else>
                N/A
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ facility.title }}</h3>
                <p class="mt-0.5 text-xs text-slate-500">ID: {{ facility.id }}</p>
              </div>
            </div>
            <span
              class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold"
              :class="statusBadgeClass(facility.status)"
            >
              {{ facility.status }}
            </span>
          </div>

          <p class="mt-3 line-clamp-3 text-sm text-slate-600 dark:text-slate-300">
            {{ facility.description || 'No description available.' }}
          </p>

          <div class="mt-4 flex items-center justify-between">
            <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
              <span class="inline-block h-3 w-3 rounded-full border border-slate-300" :style="{ backgroundColor: facility.color || '#94A3B8' }"></span>
              {{ facility.color || 'No color' }}
            </span>

            <div class="inline-flex gap-1">
              <a
                :href="`/admin/facilities/${facility.id}`"
                class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-cyan-500/10 hover:text-cyan-700 dark:text-slate-300"
                title="View facility"
              >
                <i class="fas fa-eye"></i>
              </a>
              <button
                @click="openDrawer(facility)"
                class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-cyan-500/10 hover:text-cyan-700 dark:text-slate-300"
                title="Edit facility"
              >
                <i class="fas fa-pen"></i>
              </button>
              <button
                @click="openDeleteModal(facility.id)"
                class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-red-500/15 hover:text-red-700 dark:text-slate-300"
                title="Delete facility"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </article>

        <div
          v-if="facilities.length === 0"
          class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white/70 p-8 text-center text-sm italic text-slate-500 dark:border-slate-700 dark:bg-slate-900/50"
        >
          No facilities found.
        </div>
      </div>
    </div>

    <transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showDrawer" class="fixed inset-0 z-[1300] bg-black/50">
        <div class="absolute right-0 top-0 h-full w-full max-w-md overflow-y-auto border-l border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-950">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingId ? 'Update Facility' : 'Create Facility' }}</h3>
            <button @click="closeDrawer" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="saveFacility" class="mt-4 grid gap-4">
            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Title</label>
              <input v-model="form.title" type="text" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Description</label>
              <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"></textarea>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Color</label>
                <div class="flex items-center gap-2">
                  <input v-model="form.color" type="text" placeholder="#0ea5e9" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
                  <input v-model="form.color" type="color" class="h-10 w-12 cursor-pointer rounded-lg border border-slate-300 bg-white p-1 dark:border-slate-600 dark:bg-slate-900" />
                </div>
              </div>
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Status</label>
                <select v-model="form.status" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            </form>

          <div class="mt-6 rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/80">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-300">Gallery</h4>
              <span class="text-xs text-slate-500">
                {{ isEditing ? galleryImages.length : newGalleryFiles.length }} images
              </span>
            </div>

            <div class="mt-3">
              <input
                ref="galleryInput"
                type="file"
                accept="image/*"
                multiple
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                @change="isEditing ? uploadGallery($event) : onNewGallerySelected($event)"
              />
              <p class="mt-1 text-xs text-slate-500">Upload multiple images. Drag any image onto the primary slot.</p>
            </div>

            <div class="mt-4 rounded-lg border border-dashed border-slate-300 p-3 text-center text-xs text-slate-500 dark:border-slate-700"
                 @dragover.prevent
                 @drop.prevent="isEditing ? handlePrimaryDrop() : handleNewPrimaryDrop()">
              <div v-if="isEditing ? primaryImageUrl : newPrimaryPreviewUrl" class="flex items-center justify-center gap-3">
                <img :src="isEditing ? primaryImageUrl : newPrimaryPreviewUrl" alt="Primary" class="h-14 w-14 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-700" />
                <div class="text-left">
                  <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Primary Image</p>
                  <p class="text-[11px] text-slate-400">Drag a tile here to update</p>
                </div>
              </div>
              <div v-else class="text-[11px]">
                Drop an image here to set primary
              </div>
            </div>

            <div v-if="galleryLoading" class="mt-4 text-xs text-slate-500">
              Loading images...
            </div>

            <div v-else-if="isEditing && galleryImages.length === 0" class="mt-4 text-xs text-slate-500">
              No gallery images yet.
            </div>

            <div v-else class="mt-4 grid grid-cols-3 gap-3">
              <div
                v-for="(img, index) in (isEditing ? galleryImages : newGalleryFiles)"
                :key="isEditing ? img.id : img.name + index"
                class="group relative cursor-grab rounded-lg border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                draggable="true"
                @dragstart="isEditing ? handleDragStart(img.id) : handleNewDragStart(index)"
              >
                <img :src="isEditing ? (img.image_url || storageUrl(img.image_path)) : img.preview" alt="" class="h-20 w-full rounded-md object-cover" />
                <button
                  v-if="isEditing"
                  type="button"
                  class="absolute right-1 top-1 hidden rounded bg-red-600/90 px-2 py-1 text-[10px] font-semibold text-white group-hover:block"
                  @click="deleteGalleryImage(img.id)"
                >
                  Delete
                </button>
                <span
                  v-if="isEditing ? img.id === primaryImageId : index === newPrimaryIndex"
                  class="absolute left-1 top-1 rounded bg-emerald-600/90 px-2 py-1 text-[10px] font-semibold text-white"
                >
                  Primary
                </span>
              </div>
            </div>

            <button
              type="button"
              class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-cyan-500/20 transition hover:brightness-110"
              @click="saveFacility"
            >
              {{ editingId ? 'Save Changes' : 'Create Facility' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showDeleteModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Delete Facility</h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">This action cannot be undone. Delete this facility?</p>
          <div class="mt-5 flex justify-end gap-2">
            <button @click="showDeleteModal = false" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
            <button @click="deleteFacility" class="rounded-lg bg-red-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Delete</button>
          </div>
        </div>
      </div>
    </transition>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const facilities = ref([])
const search = ref('')
const statusFilter = ref('')
const showDrawer = ref(false)
const showDeleteModal = ref(false)
const editingId = ref(null)
const deletingId = ref(null)
const galleryInput = ref(null)
const galleryImages = ref([])
const primaryImageId = ref(null)
const draggingImageId = ref(null)
const galleryLoading = ref(false)
const newGalleryFiles = ref([])
const newPrimaryIndex = ref(null)
const newDraggingIndex = ref(null)
const form = ref({
  title: '',
  description: '',
  image_path: '',
  color: '',
  status: 'active'
})

let searchTimeout = null

const statusBadgeClass = (status) => {
  return String(status).toLowerCase() === 'active'
    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
    : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
}

const fetchFacilities = async () => {
  try {
    const res = await axios.get('/api/facilities', {
      params: {
        search: search.value,
        status: statusFilter.value
      }
    })
    facilities.value = res.data.facilities
  } catch {
    toast.error('Failed to load facilities')
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchFacilities, 350)
}

const openDrawer = (facility = null) => {
  if (!facility) {
    editingId.value = null
    form.value = {
      title: '',
      description: '',
      image_path: '',
      color: '',
      status: 'active'
    }
  } else {
    editingId.value = facility.id
    form.value = {
      title: facility.title ?? '',
      description: facility.description ?? '',
      image_path: facility.image_path ?? '',
      color: facility.color ?? '',
      status: facility.status ?? 'active'
    }
  }
  galleryImages.value = []
  primaryImageId.value = null
  draggingImageId.value = null
  newGalleryFiles.value = []
  newPrimaryIndex.value = null
  newDraggingIndex.value = null
  if (galleryInput.value) galleryInput.value.value = ''
  showDrawer.value = true

  if (editingId.value) {
    loadGallery()
  }
}

const closeDrawer = () => {
  showDrawer.value = false
}

const storageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `/storage/${path}`
}

const primaryImageUrl = computed(() => {
  const match = galleryImages.value.find((img) => img.id === primaryImageId.value)
  return match ? (match.image_url || storageUrl(match.image_path)) : ''
})

const newPrimaryPreviewUrl = computed(() => {
  if (newPrimaryIndex.value === null) return ''
  return newGalleryFiles.value[newPrimaryIndex.value]?.preview || ''
})

const loadGallery = async () => {
  try {
    galleryLoading.value = true
    const res = await axios.get(`/api/facilities/${editingId.value}/images`)
    galleryImages.value = Array.isArray(res.data.images) ? res.data.images : []
    primaryImageId.value = res.data.primary_image_id || null
  } catch {
    toast.error('Failed to load gallery')
  } finally {
    galleryLoading.value = false
  }
}

const uploadGallery = async (event) => {
  const files = Array.from(event.target.files || [])
  if (!files.length || !editingId.value) return
  const payload = new FormData()
  files.forEach((file) => payload.append('images[]', file))
  try {
    const res = await axios.post(`/api/facilities/${editingId.value}/images`, payload, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    galleryImages.value = res.data.images || []
    primaryImageId.value = res.data.primary_image_id || null
    if (galleryInput.value) galleryInput.value.value = ''
    toast.success('Gallery updated')
  } catch {
    toast.error('Failed to upload images')
  }
}

const onNewGallerySelected = (event) => {
  const files = Array.from(event.target.files || [])
  if (!files.length) return
  newGalleryFiles.value = files.map((file) => ({
    file,
    name: file.name,
    preview: URL.createObjectURL(file)
  }))
  if (newPrimaryIndex.value === null && newGalleryFiles.value.length) {
    newPrimaryIndex.value = 0
  }
}

const handleNewDragStart = (index) => {
  newDraggingIndex.value = index
}

const handleNewPrimaryDrop = () => {
  if (newDraggingIndex.value === null) return
  newPrimaryIndex.value = newDraggingIndex.value
  newDraggingIndex.value = null
}

const deleteGalleryImage = async (id) => {
  if (!editingId.value) return
  try {
    const res = await axios.delete(`/api/facilities/${editingId.value}/images/${id}`)
    galleryImages.value = res.data.images || []
    primaryImageId.value = res.data.primary_image_id || null
    toast.success('Image deleted')
  } catch {
    toast.error('Failed to delete image')
  }
}

const handleDragStart = (id) => {
  draggingImageId.value = id
}

const handlePrimaryDrop = async () => {
  if (!editingId.value || !draggingImageId.value) return
  try {
    await axios.post(`/api/facilities/${editingId.value}/images/primary`, {
      image_id: draggingImageId.value
    })
    primaryImageId.value = draggingImageId.value
    toast.success('Primary image updated')
  } catch {
    toast.error('Failed to set primary image')
  } finally {
    draggingImageId.value = null
  }
}

const saveFacility = async () => {
  try {
    const payload = new FormData()
    payload.append('title', form.value.title)
    payload.append('description', form.value.description || '')
    payload.append('color', form.value.color || '')
    payload.append('status', form.value.status || 'active')
    if (!editingId.value && newGalleryFiles.value.length) {
      newGalleryFiles.value.forEach((item) => payload.append('images[]', item.file))
      payload.append('primary_index', newPrimaryIndex.value ?? 0)
    }

    if (editingId.value) {
      payload.append('_method', 'PUT')
      await axios.post(`/api/facilities/${editingId.value}`, payload, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      toast.success('Facility updated successfully')
    } else {
      await axios.post('/api/facilities', payload, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      toast.success('Facility created successfully')
    }
    closeDrawer()
    fetchFacilities()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Error saving facility')
    }
  }
}

const openDeleteModal = (id) => {
  deletingId.value = id
  showDeleteModal.value = true
}

const deleteFacility = async () => {
  try {
    await axios.delete(`/api/facilities/${deletingId.value}`)
    showDeleteModal.value = false
    toast.success('Facility deleted successfully')
    fetchFacilities()
  } catch {
    toast.error('Failed to delete facility')
  }
}

onMounted(() => {
  fetchFacilities()
})

const isEditing = computed(() => Boolean(editingId.value))

watch(editingId, (id) => {
  if (id) loadGallery()
})
</script>
