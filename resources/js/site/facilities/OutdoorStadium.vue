<template>
  <section
    class="relative flex h-[55vh] items-center justify-center bg-cover bg-center"
    style="background-image: url('/images/outdoor.jpg');"
  >
    <div class="absolute inset-0 bg-black/60"></div>
    <h1 class="relative text-5xl font-bold text-white md:text-6xl">Outdoor Stadium</h1>
  </section>

  <section class="bg-white py-20">
    <div class="mx-auto grid max-w-6xl gap-10 px-6 md:grid-cols-2">
      <div>
        <h2 class="mb-4 text-2xl font-bold">World-Class Open Arena</h2>
        <p class="text-gray-600">
          Experience competitive football, athletics, and large-scale events
          in our outdoor stadium. Professional lighting, seating, and modern
          turf provide the perfect stage for training and tournaments.
        </p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <img src="/images/slide1.jpg" class="h-48 w-full rounded-xl object-cover" alt="Outdoor Stadium" />
        <img src="/images/outdoor.jpg" class="h-48 w-full rounded-xl object-cover" alt="Outdoor Stadium" />
        <img src="/images/football.jpg" class="h-48 w-full rounded-xl object-cover" alt="Football" />
        <img src="/images/outdoor.jpg" class="h-48 w-full rounded-xl object-cover" alt="Outdoor" />
      </div>
    </div>
  </section>
</template>
