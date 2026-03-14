<template>
  <section
    class="relative flex h-[55vh] items-center justify-center bg-cover bg-center"
    style="background-image: url('/images/swimming1.jpg');"
  >
    <div class="absolute inset-0 bg-black/60"></div>
    <h1 class="relative text-5xl font-bold text-white md:text-6xl">Swimming Pool</h1>
  </section>

  <section class="bg-white py-20">
    <div class="mx-auto grid max-w-6xl gap-10 px-6 md:grid-cols-2">
      <div>
        <h2 class="mb-4 text-2xl font-bold">Professional Training Pool</h2>
        <p class="text-gray-600">
          A pristine, temperature-controlled pool for training, fitness, and
          leisure. Certified coaches and safe, modern amenities for all ages.
        </p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <img src="/images/swimming1.jpg" class="h-48 w-full rounded-xl object-cover" alt="Swimming" />
        <img src="/images/swimming2.jpg" class="h-48 w-full rounded-xl object-cover" alt="Swimming" />
        <img src="/images/swimming3.jpg" class="h-48 w-full rounded-xl object-cover" alt="Swimming" />
        <img src="/images/swimming4.jpg" class="h-48 w-full rounded-xl object-cover" alt="Swimming" />
      </div>
    </div>
  </section>
</template>
