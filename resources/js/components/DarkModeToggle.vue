<template>
    <button 
      @click="toggleDarkMode" 
      class="px-4"
    >
    <i v-if="!isDarkMode" class=" text-lg fas fa-sun text-yellow-500"></i>
    <i v-else class="text-lg fas fa-moon text-blue-400"></i>
    </button>
  </template>
  
  <script>
  import { ref, onMounted, watch } from "vue";
  
  export default {
    setup() {
      const isDarkMode = ref(
        localStorage.getItem("theme") === "dark" ||
        (!localStorage.getItem("theme") && window.matchMedia("(prefers-color-scheme: dark)").matches)
      );
  
      const toggleDarkMode = () => {
        isDarkMode.value = !isDarkMode.value;
        localStorage.setItem("theme", isDarkMode.value ? "dark" : "light");
        document.documentElement.classList.toggle("dark", isDarkMode.value);
      };
  
      // Apply the theme on component mount
      onMounted(() => {
        document.documentElement.classList.toggle("dark", isDarkMode.value);
      });
  
      // Watch for changes to update the DOM
      watch(isDarkMode, (newVal) => {
        document.documentElement.classList.toggle("dark", newVal);
      });
  
      return { isDarkMode, toggleDarkMode };
    },
  };
  </script>
  