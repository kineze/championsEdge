<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'

const images = [
  '/images/slide1.jpg',
  '/images/slide3.jpg',
  '/images/indoor2.jpg',
  '/images/indoor.jpg',
  '/images/volleyball.jpg',
  '/images/gym.jpg'
]

const current = ref(0)
let interval = null

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

const featuredFacilities = computed(() => facilities.value.slice(0, 4))

const goToSlide = (index) => {
  current.value = index
}

onMounted(() => {
  interval = setInterval(() => {
    current.value = (current.value + 1) % images.length
  }, 4000)
  fetchFacilities()
})

onUnmounted(() => {
  clearInterval(interval)
})
</script>

<template>
  <!-- HERO SLIDESHOW -->
  <section class="relative h-screen overflow-hidden pt-0">
    <!-- Slides -->
    <img
      v-for="(image, index) in images"
      :key="index"
      :src="image"
      class="absolute inset-0 h-full w-full object-cover transition-all duration-[2000ms] ease-in-out"
      :class="current === index ? 'opacity-100 scale-105' : 'opacity-0 scale-100'"
      alt=""
    />

    <!-- Dark Overlay -->
    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-60">
      <!-- Center Content -->
      <div class="px-6 text-center text-white">
        <h1 class="mb-6 text-5xl font-bold tracking-wide md:text-6xl">
          Smart Sports Complex
        </h1>

        <p class="mx-auto mb-8 max-w-2xl text-lg md:text-2xl">
          Experience world-class sports facilities with seamless online booking
          and modern membership management.
        </p>

        <div class="space-x-6">
          <a
            href="/booking"
            class="rounded-lg bg-white px-6 py-3 text-lg text-gray-700 transition duration-300 hover:bg-gray-200"
          >
            Book Now
          </a>
        </div>
      </div>
    </div>

    <!-- Slide Navigation Dots -->
    <div class="absolute bottom-8 flex w-full justify-center space-x-3">
      <button
        v-for="(image, index) in images"
        :key="index"
        @click="goToSlide(index)"
        class="h-4 w-4 rounded-full transition-all duration-300"
        :class="current === index ? 'bg-white scale-110' : 'bg-white/50 hover:bg-white'"
        aria-label="Go to slide"
      ></button>
    </div>
  </section>

  <!-- ABOUT SECTION -->
  <section class="bg-gray-50 py-28">
    <div class="mx-auto grid max-w-7xl items-center gap-16 px-6 md:grid-cols-2">
      <div>
        <h2 class="mb-8 text-3xl font-extrabold leading-tight text-black md:text-5xl">
          Champion’s Edge
          <br />
          Sport Arena
        </h2>

        <p class="text-justify text-lg leading-relaxed text-gray-600">
          Smart Sports Complex is a state-of-the-art facility dedicated to nurturing
          athletic talent and fostering a strong sense of community.
          Situated in a dynamic and accessible location, our complex
          offers world-class courts, modern amenities, and
          professional training environments designed for athletes of all levels.
        </p>

        <p class="text-justify text-lg leading-relaxed text-gray-600">
          Whether you're here for fitness, competition, or leisure,
          we provide an inspiring environment where passion meets performance.
          Join us and be part of a legacy that champions excellence,
          health, and the spirit of sportsmanship.
        </p>
      </div>

      <div class="flex justify-center">
        <img
          src="/images/sports.png"
          alt="Athlete"
          class="max-h-[300px] w-auto object-contain opacity-90"
        />
      </div>
    </div>
  </section>

  <!-- FACILITIES SECTION -->
  <section class="bg-gray-100 py-24">
    <div class="mx-auto max-w-7xl px-6">
      <h2 class="mb-16 text-center text-4xl font-bold">
        Our Facilities
      </h2>

      <div v-if="facilitiesLoading" class="text-sm text-gray-500">Loading facilities...</div>

      <div v-else class="grid gap-8 md:grid-cols-4">
        <a
          v-for="facility in featuredFacilities"
          :key="facility.id"
          :href="`/facilities/${facility.id}`"
          class="group relative block cursor-pointer overflow-hidden rounded-2xl shadow-lg"
        >
          <img
            :src="facility.primary_image?.image_url || '/images/slide1.jpg'"
            class="h-[350px] w-full object-cover transition-transform duration-500 group-hover:scale-110"
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

        <div v-if="featuredFacilities.length === 0" class="col-span-full text-sm text-gray-500">
          No facilities available yet.
        </div>
      </div>
    </div>
  </section>

  <!-- MISSION & VISION SECTION -->
  <section class="bg-white py-28">
    <div class="mx-auto grid max-w-7xl items-center gap-16 px-6 md:grid-cols-3">
      <div>
        <h3 class="mb-4 text-2xl font-bold">Our Mission</h3>
        <p class="text-justify leading-relaxed text-gray-600">
          Providing modern sports facilities to improve the mental and physical
          well-being of every age group. We aim to motivate and develop
          athletes by creating opportunities to compete at national and
          international levels while ensuring access to advanced technology
          and world-class infrastructure.
        </p>
      </div>

      <div class="flex justify-center">
        <img
          src="/images/sports2.png"
          alt="Athlete Illustration"
          class="max-h-[200px] opacity-70"
        />
      </div>

      <div>
        <h3 class="mb-4 text-2xl font-bold">Our Vision</h3>
        <p class="text-justify leading-relaxed text-gray-600">
          To create a healthy, active, and disciplined community by
          nurturing talent, promoting excellence, and building a
          sustainable sports culture that inspires future generations.
        </p>
      </div>
    </div>
  </section>
</template>
