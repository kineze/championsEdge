import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';

import { createApp } from 'vue'

import DarkModeToggle from './components/DarkModeToggle.vue';

const app = createApp({})

.component('dark-mode-toggle', DarkModeToggle)

.mount('#app')