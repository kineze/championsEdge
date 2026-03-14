<template>
  <section
    class="relative flex h-[55vh] items-center justify-center bg-cover bg-center"
    style="background-image: url('/images/indoor.jpg');"
  >
    <div class="absolute inset-0 bg-black/60"></div>
    <h1 class="relative text-5xl font-bold text-white md:text-6xl">Indoor Stadium</h1>
  </section>

  <section class="bg-white py-20">
    <div class="mx-auto grid max-w-6xl gap-10 px-6 md:grid-cols-2">
      <div>
        <h2 class="mb-4 text-2xl font-bold">All-Weather Performance</h2>
        <p class="text-gray-600">
          A premium indoor arena designed for badminton, volleyball, and
          multi-sport training. Climate control, pro-grade flooring, and
          high-end lighting ensure peak performance year-round.
        </p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <img src="/images/indoor.jpg" class="h-48 w-full rounded-xl object-cover" alt="Indoor Stadium" />
        <img src="/images/indoor2.jpg" class="h-48 w-full rounded-xl object-cover" alt="Indoor Stadium" />
        <img src="/images/batminton.jpg" class="h-48 w-full rounded-xl object-cover" alt="Badminton" />
        <img src="/images/volleyball.jpg" class="h-48 w-full rounded-xl object-cover" alt="Volleyball" />
      </div>
    </div>
  </section>
</template>
