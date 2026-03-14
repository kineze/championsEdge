<template>
  <section
    class="relative flex h-[55vh] items-center justify-center bg-cover bg-center"
    style="background-image: url('/images/gym.jpg');"
  >
    <div class="absolute inset-0 bg-black/60"></div>
    <h1 class="relative text-5xl font-bold text-white md:text-6xl">Gym &amp; Fitness</h1>
  </section>

  <section class="bg-white py-20">
    <div class="mx-auto grid max-w-6xl gap-10 px-6 md:grid-cols-2">
      <div>
        <h2 class="mb-4 text-2xl font-bold">Strength &amp; Conditioning</h2>
        <p class="text-gray-600">
          A fully equipped fitness center with modern machines, free weights,
          and personal training programs designed to push every athlete further.
        </p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <img src="/images/gym.jpg" class="h-48 w-full rounded-xl object-cover" alt="Gym" />
        <img src="/images/Fitness Center1.jpg" class="h-48 w-full rounded-xl object-cover" alt="Fitness" />
        <img src="/images/Fitness Center2.jpg" class="h-48 w-full rounded-xl object-cover" alt="Fitness" />
        <img src="/images/Fitness Center3.jpg" class="h-48 w-full rounded-xl object-cover" alt="Fitness" />
      </div>
    </div>
  </section>
</template>
