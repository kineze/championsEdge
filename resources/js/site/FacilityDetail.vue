<script setup>
import { onMounted, ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  facilityId: {
    type: [String, Number],
    required: true
  }
})

const facility = ref(null)
const loading = ref(false)

const primaryImageUrl = computed(() => {
  if (!facility.value?.primary_image) return ''
  return facility.value.primary_image.image_url || `/storage/${facility.value.primary_image.image_path}`
})

const galleryImages = computed(() => facility.value?.images || [])
const selectedGalleryUrl = ref('')

const bigImageUrl = computed(() => {
  return selectedGalleryUrl.value || primaryImageUrl.value
})

const fetchFacility = async () => {
  try {
    loading.value = true
    const res = await axios.get(`/api/public/facilities/${props.facilityId}`)
    facility.value = res.data.facility
    selectedGalleryUrl.value = ''
  } catch {
    facility.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchFacility()
})
</script>

<template>
  <section
    class="relative bg-gray-900 py-44"
    :style="bigImageUrl ? { backgroundImage: `url(${bigImageUrl})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}"
  >
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative mx-auto max-w-7xl px-6">
      <a href="/facilities" class="text-sm font-semibold text-cyan-300 hover:underline">
        Back to facilities
      </a>
      <h1 class="mt-4 text-4xl font-bold text-white">
        {{ facility?.title || 'Facility' }}
      </h1>
      <p class="mt-2 text-slate-300" v-if="facility?.description">{{ facility.description }}</p>
    </div>
  </section>

  <section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-6">
      <div v-if="loading" class="text-sm text-slate-500">Loading facility...</div>

      <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_1.2fr]">
        <div>
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Gallery</h2>
            <span class="text-sm text-slate-500">{{ galleryImages.length }} images</span>
          </div>

          <div v-if="galleryImages.length === 0" class="mt-4 text-sm text-slate-500">
            No gallery images available.
          </div>

          <div v-else class="mt-4 grid grid-cols-2 gap-4">
            <div v-for="img in galleryImages" :key="img.id" class="relative">
              <img
                :src="img.image_url || `/storage/${img.image_path}`"
                alt="Gallery"
                class="h-44 w-full cursor-pointer rounded-lg object-cover ring-1 ring-slate-200 transition hover:opacity-80"
                @click="selectedGalleryUrl = img.image_url || `/storage/${img.image_path}`"
              />
              <span
                v-if="facility?.primary_image_id === img.id"
                class="absolute left-2 top-2 rounded bg-emerald-600/90 px-2 py-0.5 text-[10px] font-semibold text-white"
              >
                Primary
              </span>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
          <h2 class="text-2xl font-semibold text-slate-900">{{ facility?.title || 'Facility' }}</h2>
          <p class="mt-3 text-sm leading-relaxed text-slate-600">
            {{ facility?.description || 'No description available.' }}
          </p>

          <div class="mt-6 rounded-xl bg-slate-50 text-sm text-slate-600">
            <div class="flex items-center justify-between">
              <span class="font-semibold text-slate-500">Status</span>
              <span>{{ facility?.status || '-' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
