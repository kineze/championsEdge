import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';

import Toast, { POSITION } from "vue-toastification";

import { createApp } from 'vue'

import DarkModeToggle from './components/DarkModeToggle.vue';
import RolePermissionManager from './components/RolePermissionManager.vue';
import UserManager from './components/UserManager.vue';
import FacilityManager from './components/FacilityManager.vue';
import AdminFacilityDetail from './components/AdminFacilityDetail.vue';
import AgeGroupManager from './components/AgeGroupManager.vue';
import FacilitySubscriptionPricingManager from './components/FacilitySubscriptionPricingManager.vue';
import FacilityReservationPricingManager from './components/FacilityReservationPricingManager.vue';
import SubscriptionManager from './components/SubscriptionManager.vue';
import HomePage from './site/Home.vue';
import AboutPage from './site/About.vue';
import FacilitiesPage from './site/Facilities.vue';
import FacilityOutdoorStadium from './site/facilities/OutdoorStadium.vue';
import FacilityIndoorStadium from './site/facilities/IndoorStadium.vue';
import FacilitySwimmingPool from './site/facilities/SwimmingPool.vue';
import FacilityGym from './site/facilities/Gym.vue';
import FacilityDetail from './site/FacilityDetail.vue';

const app = createApp({})

.component('dark-mode-toggle', DarkModeToggle)
.component('roles-and-permission-manager', RolePermissionManager)
.component('user-manager', UserManager)
.component('facility-manager', FacilityManager)
.component('admin-facility-detail', AdminFacilityDetail)
.component('age-group-manager', AgeGroupManager)
.component('facility-subscription-pricing-manager', FacilitySubscriptionPricingManager)
.component('facility-reservation-pricing-manager', FacilityReservationPricingManager)
.component('subscription-manager', SubscriptionManager)
.component('home-page', HomePage)
.component('about-page', AboutPage)
.component('facilities-page', FacilitiesPage)
.component('facility-outdoor-stadium', FacilityOutdoorStadium)
.component('facility-indoor-stadium', FacilityIndoorStadium)
.component('facility-swimming-pool', FacilitySwimmingPool)
.component('facility-gym', FacilityGym)
.component('facility-detail', FacilityDetail)

.mount('#app')
