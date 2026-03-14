<template>
  <section
    class="relative flex h-[60vh] items-center justify-center bg-cover bg-center"
    style="background-image: url('/images/slide1.jpg');"
  >
    <div class="absolute inset-0 bg-black/60"></div>
    <h1 class="relative text-5xl font-bold text-white md:text-6xl">
      About Us
    </h1>
  </section>

  <section class="bg-white py-20">
    <div class="mx-auto max-w-5xl px-6 text-center">
      <h2 class="mb-6 text-2xl font-bold">Who We Are</h2>
      <p class="text-center text-lg leading-relaxed text-gray-600">
        Smart Sports Complex is a state-of-the-art sports facility
        designed to empower athletes and inspire communities.
        We provide modern infrastructure, professional training
        environments, and seamless booking systems to ensure
        excellence in every sporting experience.
      </p>
    </div>
  </section>

  <div class="flex justify-center bg-white pb-6">
    <img src="/images/logo2.png" alt="Smart Sports" class="h-28 w-auto opacity-90" />
  </div>

  <section class="bg-gray-50 py-20">
    <div class="mx-auto grid max-w-6xl gap-10 px-6 md:grid-cols-2">
      <div class="rounded-2xl bg-white p-8 shadow-sm">
        <h3 class="mb-4 text-xl font-bold">Our Mission</h3>
        <p class="text-gray-600">
          To provide accessible, high-quality sports facilities that
          nurture talent, promote wellness, and build a thriving
          community of athletes and enthusiasts.
        </p>
      </div>
      <div class="rounded-2xl bg-white p-8 shadow-sm">
        <h3 class="mb-4 text-xl font-bold">Our Vision</h3>
        <p class="text-gray-600">
          To be the region’s most trusted sports hub, fostering
          excellence, inclusion, and lifelong fitness.
        </p>
      </div>
    </div>
  </section>
</template>
