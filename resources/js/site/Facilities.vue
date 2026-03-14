<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const facilities = ref([])
const facilitiesLoading = ref(false)

const fetchFacilities = async () => {
  try {
    facilitiesLoading.value = true
    const res = await axios.get('/api/public/facilities')
    facilities.value = res.data.facilities || []
  } catch {
    facilities.value = []
  } finally {
    facilitiesLoading.value = false
  }
}

onMounted(() => {
  fetchFacilities()
})
</script>

<template>
  <section
    class="relative flex h-[55vh] items-center justify-center bg-cover bg-center"
    style="background-image: url('/images/indoor.jpg');"
  >
    <div class="absolute inset-0 bg-black/60"></div>
    <h1 class="relative text-5xl font-bold text-white md:text-6xl">
      Our Facilities
    </h1>
  </section>

  <section class="bg-gray-100 py-24">
    <div class="mx-auto max-w-7xl px-6">
      <div v-if="facilitiesLoading" class="text-sm text-gray-500">Loading facilities...</div>

      <div v-else class="grid gap-8 md:grid-cols-4">
        <a
          v-for="facility in facilities"
          :key="facility.id"
          :href="`/facilities/${facility.id}`"
          class="group relative block cursor-pointer overflow-hidden rounded-2xl shadow-lg"
        >
          <img
            :src="facility.primary_image?.image_url || '/images/slide1.jpg'"
            class="h-[320px] w-full object-cover transition-transform duration-500 group-hover:scale-110"
            :alt="facility.title"
          />
          <div class="absolute inset-0 bg-black/40 opacity-0 transition duration-500 group-hover:opacity-100"></div>
          <div class="absolute inset-0 flex items-center justify-center opacity-0 transition duration-500 group-hover:opacity-100">
            <div class="text-center">
              <h3 class="mb-2 text-xl font-semibold text-white">{{ facility.title }}</h3>
              <span class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-800">Learn More</span>
            </div>
          </div>
        </a>

        <div v-if="facilities.length === 0" class="col-span-full text-sm text-gray-500">
          No facilities available yet.
        </div>
      </div>
    </div>
  </section>
</template>
