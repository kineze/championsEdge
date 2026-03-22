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

const statusLabel = computed(() => String(facility.value?.status || 'unknown').toLowerCase())

const statusClass = computed(() => {
  if (statusLabel.value === 'active') {
    return 'bg-emerald-100 text-emerald-700 ring-emerald-200'
  }

  return 'bg-amber-100 text-amber-700 ring-amber-200'
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
    class="relative  bg-slate-900 py-24 md:pt-32"
    :style="bigImageUrl ? { backgroundImage: `url(${bigImageUrl})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}"
  >
    <div class="absolute inset-0 bg-[linear-gradient(115deg,rgba(2,6,23,0.9),rgba(2,6,23,0.55),rgba(8,47,73,0.7))]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_20%,rgba(34,197,94,0.28),transparent_40%)]"></div>

    <div class="relative mx-auto max-w-7xl px-6">
      <a href="/facilities" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/30 bg-cyan-400/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-400/20">
        <span>Back</span>
        <span>Facilities</span>
      </a>

      <div class="mt-6 max-w-3xl">
        <h1 class="text-4xl font-black tracking-tight text-white md:text-6xl">
          {{ facility?.title || 'Facility' }}
        </h1>
        <p class="mt-4 text-base leading-relaxed text-slate-200 md:text-lg" v-if="facility?.description">
          {{ facility.description }}
        </p>
      </div>

      <div class="mt-8 grid max-w-2xl grid-cols-2 gap-3 md:grid-cols-3">
        <div class="rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur">
          <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-300">Gallery</p>
          <p class="mt-1 text-lg font-bold text-white">{{ galleryImages.length }}</p>
        </div>
        <div class="rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur">
          <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-300">Status</p>
          <p class="mt-1 text-lg font-bold capitalize text-white">{{ statusLabel }}</p>
        </div>
        <div class="rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur col-span-2 md:col-span-1">
          <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-300">Bookings</p>
          <p class="mt-1 text-lg font-bold text-white">Open</p>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-gradient-to-b from-white to-slate-50 ">
    <div class="mx-auto max-w-7xl px-6">
      <div v-if="loading" class="text-sm text-slate-500">Loading facility...</div>

      <div class="mt-8 grid gap-8 xl:grid-cols-[1.35fr_0.9fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:p-6">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-bold text-slate-900 md:text-2xl">Facility Gallery</h2>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ galleryImages.length }} images</span>
          </div>

          <div v-if="galleryImages.length === 0" class="mt-4 text-sm text-slate-500">
            No gallery images available.
          </div>

          <div v-else class="mt-5">
            <div class="overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
              <img
                :src="bigImageUrl"
                alt="Facility preview"
                class="aspect-[16/9] w-full object-cover transition duration-500"
              />
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
              <button
                v-for="img in galleryImages"
                :key="img.id"
                type="button"
                class="group relative overflow-hidden rounded-xl ring-2 transition"
                :class="(bigImageUrl === (img.image_url || `/storage/${img.image_path}`)) ? 'ring-cyan-400' : 'ring-transparent hover:ring-cyan-200'"
                @click="selectedGalleryUrl = img.image_url || `/storage/${img.image_path}`"
              >
                <img
                  :src="img.image_url || `/storage/${img.image_path}`"
                  alt="Gallery"
                  class="h-24 w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <span
                  v-if="facility?.primary_image_id === img.id"
                  class="absolute left-1.5 top-1.5 rounded bg-emerald-600 px-1.5 py-0.5 text-[10px] font-semibold text-white"
                >
                  Primary
                </span>
              </button>
            </div>
          </div>
        </div>

        <div class="space-y-5">
          <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-900">{{ facility?.title || 'Facility' }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">
              {{ facility?.description || 'No description available.' }}
            </p>

            <div class="mt-5 flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-3.5">
              <span class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Status</span>
              <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 capitalize" :class="statusClass">
                {{ statusLabel }}
              </span>
            </div>

            <div class="mt-3 rounded-2xl border border-cyan-100 bg-cyan-50 p-3.5">
              <p class="text-xs font-semibold uppercase tracking-[0.12em] text-cyan-700">Reservation Notice</p>
              <p class="mt-1 text-sm text-cyan-900">Submit a request below and our admin will get back after review.</p>
            </div>
          </div>

          <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">Why This Facility</h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
              <li class="flex items-start gap-2">
                <span class="mt-1 h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                <span>Modern infrastructure with maintained playing conditions.</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="mt-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                <span>Flexible booking ranges with clear plan-based pricing.</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="mt-1 h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                <span>Fast review flow through the reservation request system.</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-white pb-20">
    <div class="mx-auto grid max-w-7xl gap-6 px-6 lg:grid-cols-[0.7fr_1.3fr]">
      <aside class="h-fit overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-cyan-50 via-white to-sky-100 shadow-md">
        <div class="border-b border-cyan-100 px-6 py-5">
          <p class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">How To Book</p>
          <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Booking Journey</h3>
          <p class="mt-2 text-base leading-relaxed text-slate-700">Complete these steps to send your reservation request.</p>
        </div>

        <div class="space-y-3 p-5">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Step 1</p>
            <p class="mt-1 text-base font-bold text-slate-900">Pick date and time range</p>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">Choose start/end. The system checks availability and working-hour access.</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Step 2</p>
            <p class="mt-1 text-base font-bold text-slate-900">If unavailable, pick a suggested slot</p>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">Use nearest available suggestions and continue with one click.</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Step 3</p>
            <p class="mt-1 text-base font-bold text-slate-900">Enter your contact details</p>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">Provide name, phone and optional email for confirmation.</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Step 4</p>
            <p class="mt-1 text-base font-bold text-slate-900">Select your pricing plan</p>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">Choose the plan that matches your booking and deposit rules.</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Step 5</p>
            <p class="mt-1 text-base font-bold text-slate-900">Review total and submit request</p>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">See billable hours, total, deposit and remaining balance before submitting.</p>
          </div>

          <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.12em] text-emerald-700">Step 6</p>
            <p class="mt-1 text-base font-bold text-emerald-900">Get confirmation summary</p>
            <p class="mt-2 text-sm leading-relaxed text-emerald-800">You will see the full booking summary and receive an email acknowledgement.</p>
          </div>
        </div>
      </aside>

      <div>
        <reservation-request-page :facility-id="facility?.id" :embedded="true"></reservation-request-page>
      </div>
    </div>
  </section>

  <section class="bg-white pb-24">
    <div class="mx-auto max-w-7xl px-6">
      <booking-calendar :facility-id="facility?.id"></booking-calendar>
    </div>
  </section>
</template>
