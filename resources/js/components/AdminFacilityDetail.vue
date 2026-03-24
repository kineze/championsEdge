<template>
  <section class="p-3 sm:p-4">
    <div class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-cyan-50 to-white p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-300">Facility Profile</p>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ facility?.title || 'Facility' }}</h2>
        </div>
        <a
          href="/admin/facilities"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-blue-500/20 transition hover:brightness-110"
        >
          <i class="fas fa-arrow-left"></i>
          <span>Back to Facilities</span>
        </a>
      </div>

      <div class="mt-5 grid grid-cols-12 gap-4">
        <div class="col-span-12 lg:col-span-4">
          <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/80">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Gallery</h3>
                <span class="text-xs text-slate-500">{{ galleryImages.length }} images</span>
              </div>

              <div class="mt-3">
                <div v-if="errorMessage" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-300">
                  {{ errorMessage }}
                </div>
                <div class="relative aspect-square overflow-hidden rounded-lg border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-900">
                  <img
                    v-if="currentImage"
                    :src="imageUrl(currentImage)"
                    :alt="currentImage.alt_text || (facility?.title || 'Facility image')"
                    class="h-full w-full object-cover"
                  />
                  <div v-else class="grid h-full place-items-center text-xs text-slate-500">No gallery images yet.</div>
                </div>

                <div v-if="galleryImages.length > 1" class="mt-3 flex items-center justify-center gap-2">
                  <button
                    v-for="(img, index) in galleryImages"
                    :key="img.id ?? index"
                    type="button"
                    class="h-2.5 w-2.5 rounded-full transition"
                    :class="index === currentIndex ? 'bg-cyan-600' : 'bg-slate-300 dark:bg-slate-600'"
                    @click="goToSlide(index)"
                  />
                </div>
              </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/80">
              <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Description</h3>
              <p class="mt-2 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">
                {{ facility?.description || 'No description added yet.' }}
              </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/80">
              <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Facility Data</h3>
              <dl class="mt-3 space-y-3 text-sm">
                <div>
                  <dt class="font-semibold text-slate-500">Status</dt>
                  <dd class="mt-0.5">
                    <span
                      class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                      :class="statusClass(facility?.status)"
                    >
                      {{ facility?.status || '-' }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="font-semibold text-slate-500">Color</dt>
                  <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ facility?.color || '-' }}</dd>
                </div>
                <div>
                  <dt class="font-semibold text-slate-500">Created</dt>
                  <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatDate(facility?.created_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <div class="col-span-12 lg:col-span-8">
          <div class="min-h-[640px] rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/80">
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3 dark:border-slate-700">
              <button
                type="button"
                class="rounded-lg px-3 py-1.5 text-sm font-semibold transition"
                :class="activeTab === 'subscription'
                  ? 'bg-cyan-600 text-white'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'"
                @click="activeTab = 'subscription'"
              >
                Subscription
              </button>
              <button
                type="button"
                class="rounded-lg px-3 py-1.5 text-sm font-semibold transition"
                :class="activeTab === 'reservation'
                  ? 'bg-cyan-600 text-white'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'"
                @click="activeTab = 'reservation'"
              >
                Reservation
              </button>
              <button
                type="button"
                class="rounded-lg px-3 py-1.5 text-sm font-semibold transition"
                :class="activeTab === 'training'
                  ? 'bg-cyan-600 text-white'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'"
                @click="activeTab = 'training'"
              >
                Training Sessions
              </button>
              <button
                type="button"
                class="rounded-lg px-3 py-1.5 text-sm font-semibold transition"
                :class="activeTab === 'extraItems'
                  ? 'bg-cyan-600 text-white'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'"
                @click="activeTab = 'extraItems'"
              >
                Extra Items
              </button>
            </div>

            <div class="pt-4">
              <facility-subscription-pricing-manager
                v-if="activeTab === 'subscription'"
                :facility-id="props.facilityId"
              />
              <facility-reservation-pricing-manager
                v-else-if="activeTab === 'reservation'"
                :facility-id="props.facilityId"
              />
              <facility-training-session-manager
                v-else-if="activeTab === 'training'"
                :facility-id="props.facilityId"
              />
              <facility-extra-item-manager
                v-else-if="activeTab === 'extraItems'"
                :facility-id="props.facilityId"
              />
              <div
                v-else
                class="min-h-[560px] rounded-lg border border-dashed border-slate-300/80 bg-white/40 p-4 dark:border-slate-600 dark:bg-slate-900/40"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  facilityId: {
    type: [String, Number],
    required: true
  }
})

const facility = ref(null)
const currentIndex = ref(0)
const carouselTimer = ref(null)
const errorMessage = ref('')
const activeTab = ref('subscription')

const galleryImages = computed(() => {
  if (!facility.value) return []
  const images = Array.isArray(facility.value.images) ? [...facility.value.images] : []
  if (!images.length && facility.value.primary_image) {
    return [facility.value.primary_image]
  }
  return images
})

const currentImage = computed(() => galleryImages.value[currentIndex.value] || null)

const clearCarousel = () => {
  if (carouselTimer.value) {
    clearInterval(carouselTimer.value)
    carouselTimer.value = null
  }
}

const startCarousel = () => {
  clearCarousel()
  if (galleryImages.value.length <= 1) return
  carouselTimer.value = setInterval(() => {
    currentIndex.value = (currentIndex.value + 1) % galleryImages.value.length
  }, 3000)
}

const goToSlide = (index) => {
  currentIndex.value = index
}

const imageUrl = (img) => {
  if (!img) return ''
  if (img.image_url) return img.image_url
  if (!img.image_path) return ''
  return `/storage/${img.image_path}`
}

const statusClass = (status) => {
  return String(status || '').toLowerCase() === 'active'
    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
    : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
}

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

const fetchFacility = async () => {
  try {
    errorMessage.value = ''
    const res = await axios.get(`/api/facilities/${props.facilityId}`)
    facility.value = res.data.facility || null
    currentIndex.value = 0
    startCarousel()
  } catch {
    facility.value = null
    errorMessage.value = 'Failed to load facility data.'
    clearCarousel()
  }
}

watch(galleryImages, () => {
  if (currentIndex.value >= galleryImages.value.length) {
    currentIndex.value = 0
  }
  startCarousel()
})

onMounted(fetchFacility)
onBeforeUnmount(clearCarousel)
</script>
