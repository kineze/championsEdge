<template>
  <section class="relative overflow-hidden bg-slate-100 pb-20 pt-36 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-16 h-64 w-64 rounded-full bg-cyan-300/25 blur-3xl dark:bg-cyan-500/20"></div>
      <div class="absolute -right-20 bottom-4 h-72 w-72 rounded-full bg-amber-300/25 blur-3xl dark:bg-amber-500/20"></div>
    </div>

    <div class="relative mx-auto max-w-6xl px-6">
      <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-300">Members Registration</p>
        <h1 class="mt-2 text-4xl font-black tracking-tight md:text-5xl">Join Champions Edge</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600 dark:text-slate-300 md:text-base">
          Complete your details, select your facility and matching plan, then finish secure payment through Seylan.
        </p>
      </div>

      <div class="mb-6 grid gap-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 md:grid-cols-3">
        <div class="rounded-xl px-3 py-2 text-sm font-semibold" :class="stepBadgeClass(1)">1. Member Details</div>
        <div class="rounded-xl px-3 py-2 text-sm font-semibold" :class="stepBadgeClass(2)">2. Select Plan</div>
        <div class="rounded-xl px-3 py-2 text-sm font-semibold" :class="stepBadgeClass(3)">3. Summary & Payment</div>
      </div>

      <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900 sm:p-8">
        <div v-if="step === 1" class="space-y-5">
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="label">Full Name</label>
              <input v-model.trim="form.name" type="text" class="field" :class="errors.name ? 'field-error' : ''" placeholder="Member name" />
              <p v-if="errors.name" class="form-error">{{ errors.name }}</p>
            </div>
            <div>
              <label class="label">Email</label>
              <input v-model.trim="form.email" type="email" class="field" :class="errors.email ? 'field-error' : ''" placeholder="member@example.com" />
              <p v-if="errors.email" class="form-error">{{ errors.email }}</p>
            </div>
            <div>
              <label class="label">Phone</label>
              <input v-model.trim="form.phone" type="text" class="field" :class="errors.phone ? 'field-error' : ''" placeholder="0771234567" />
              <p v-if="errors.phone" class="form-error">{{ errors.phone }}</p>
            </div>
            <div>
              <label class="label">NIC (Optional)</label>
              <input v-model.trim="form.nic" type="text" class="field" placeholder="NIC number" />
            </div>
            <div>
              <label class="label">Date of Birth</label>
              <input v-model="form.date_of_birth" type="date" class="field" :class="errors.date_of_birth ? 'field-error' : ''" />
              <p v-if="errors.date_of_birth" class="form-error">{{ errors.date_of_birth }}</p>
            </div>
            <div>
              <label class="label">Facility</label>
              <select v-model.number="form.facility_id" class="field" :class="errors.facility_id ? 'field-error' : ''">
                <option :value="null">Select facility</option>
                <option v-for="facility in facilities" :key="facility.id" :value="facility.id">{{ facility.title }}</option>
              </select>
              <p v-if="errors.facility_id" class="form-error">{{ errors.facility_id }}</p>
            </div>
            <div>
              <label class="label">Password</label>
              <input v-model="form.password" type="password" class="field" :class="errors.password ? 'field-error' : ''" placeholder="Minimum 8 characters" />
              <p v-if="errors.password" class="form-error">{{ errors.password }}</p>
            </div>
            <div>
              <label class="label">Confirm Password</label>
              <input v-model="form.password_confirmation" type="password" class="field" :class="errors.password_confirmation ? 'field-error' : ''" placeholder="Re-enter password" />
              <p v-if="errors.password_confirmation" class="form-error">{{ errors.password_confirmation }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="label">Address (Optional)</label>
              <textarea v-model.trim="form.address" rows="3" class="field" placeholder="Address"></textarea>
            </div>
          </div>

          <div class="flex justify-end">
            <button type="button" class="btn-primary" @click="goToStep2">Continue to Plans</button>
          </div>
        </div>

        <div v-else-if="step === 2" class="space-y-5">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-950/60">
            <p><span class="font-semibold">Facility:</span> {{ selectedFacilityTitle || '-' }}</p>
            <p><span class="font-semibold">Age:</span> {{ age !== null ? `${age} years` : '-' }}</p>
          </div>

          <div v-if="matchingPlans.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-300">
            No matching pricing plans found for the selected facility and age.
          </div>
          <p v-if="errors.plan_id" class="form-error">{{ errors.plan_id }}</p>

          <div v-else class="grid gap-4 md:grid-cols-2">
            <label
              v-for="plan in matchingPlans"
              :key="plan.id"
              class="cursor-pointer rounded-2xl border p-4 transition"
              :class="selectedPlanId === plan.id
                ? 'border-cyan-500 bg-cyan-50 ring-2 ring-cyan-200 dark:border-cyan-400 dark:bg-cyan-500/10 dark:ring-cyan-400/30'
                : 'border-slate-200 bg-white hover:border-cyan-300 dark:border-slate-700 dark:bg-slate-950/60 dark:hover:border-cyan-400'"
            >
              <input v-model.number="selectedPlanId" :value="plan.id" type="radio" class="sr-only">
              <p class="text-xs font-semibold uppercase tracking-[0.13em] text-slate-500 dark:text-slate-400">{{ plan.frequency }}</p>
              <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">{{ formatCurrency(plan.price) }}</p>
              <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">
                Age Group: {{ (plan.age_group || plan.ageGroup)?.group_name }} ({{ (plan.age_group || plan.ageGroup)?.age_start }}-{{ (plan.age_group || plan.ageGroup)?.age_end }})
              </p>
            </label>
          </div>

          <div class="flex items-center justify-between">
            <button type="button" class="btn-muted" @click="step = 1">Back</button>
            <button type="button" class="btn-primary" @click="goToStep3">Review Summary</button>
          </div>
        </div>

        <div v-else class="space-y-5">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-950/60">
            <p><span class="font-semibold">Name:</span> {{ form.name }}</p>
            <p><span class="font-semibold">Email:</span> {{ form.email }}</p>
            <p><span class="font-semibold">Phone:</span> {{ form.phone }}</p>
            <p><span class="font-semibold">Facility:</span> {{ selectedFacilityTitle }}</p>
            <p><span class="font-semibold">Plan:</span> {{ selectedPlan ? `${selectedPlan.frequency} - ${formatCurrency(selectedPlan.price)}` : '-' }}</p>
          </div>

          <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-500/30 dark:bg-cyan-500/10">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-700 dark:text-cyan-300">Secure Payment</p>
            <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
              Clicking purchase will redirect you to Seylan secure card checkout.
            </p>
          </div>

          <div class="flex items-center justify-between">
            <button type="button" class="btn-muted" @click="step = 2">Back</button>
            <button type="button" class="btn-primary" :disabled="submitting" @click="purchase">
              {{ submitting ? 'Redirecting...' : 'Purchase & Register' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const step = ref(1)
const submitting = ref(false)
const facilities = ref([])
const plans = ref([])
const selectedPlanId = ref(null)
const errors = ref({})

const form = ref({
  name: '',
  email: '',
  phone: '',
  nic: '',
  address: '',
  date_of_birth: '',
  facility_id: null,
  password: '',
  password_confirmation: '',
})

const age = computed(() => {
  if (!form.value.date_of_birth) return null
  const dob = new Date(form.value.date_of_birth)
  if (Number.isNaN(dob.getTime())) return null
  const now = new Date()
  let years = now.getFullYear() - dob.getFullYear()
  const monthDiff = now.getMonth() - dob.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < dob.getDate())) years -= 1
  return years
})

const selectedFacilityTitle = computed(() => {
  return facilities.value.find((f) => Number(f.id) === Number(form.value.facility_id))?.title || ''
})

const matchingPlans = computed(() => {
  if (!form.value.facility_id || age.value === null) return []
  return plans.value.filter((plan) => {
    const facilityMatch = Number(plan.facility_id) === Number(form.value.facility_id)
    const ageGroup = plan.age_group || plan.ageGroup || null
    const min = Number(ageGroup?.age_start ?? 0)
    const max = Number(ageGroup?.age_end ?? 0)
    return facilityMatch && age.value >= min && age.value <= max
  })
})

const selectedPlan = computed(() => {
  return matchingPlans.value.find((plan) => Number(plan.id) === Number(selectedPlanId.value)) || null
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-LK', {
    style: 'currency',
    currency: 'LKR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value || 0))
}

const stepBadgeClass = (s) => {
  if (step.value === s) return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-500/20 dark:text-cyan-200'
  if (step.value > s) return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200'
  return 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300'
}

const fetchMeta = async () => {
  try {
    const res = await axios.get('/api/public/member-registration/meta')
    facilities.value = Array.isArray(res.data.facilities) ? res.data.facilities : []
    plans.value = Array.isArray(res.data.plans) ? res.data.plans : []
  } catch {
    facilities.value = []
    plans.value = []
    toast.error('Failed to load registration details')
  }
}

const goToStep2 = () => {
  errors.value = {}
  if (!form.value.name) errors.value.name = 'Name is required.'
  if (!form.value.email) errors.value.email = 'Email is required.'
  if (!form.value.phone) errors.value.phone = 'Phone is required.'
  if (!form.value.date_of_birth) errors.value.date_of_birth = 'Date of birth is required.'
  if (!form.value.facility_id) errors.value.facility_id = 'Please select a facility.'
  if (!form.value.password || form.value.password.length < 8) errors.value.password = 'Password must be at least 8 characters.'
  if (form.value.password !== form.value.password_confirmation) errors.value.password_confirmation = 'Password confirmation does not match.'

  if (Object.keys(errors.value).length > 0) {
    toast.error('Please complete required member details.')
    return
  }
  step.value = 2
}

const goToStep3 = () => {
  errors.value = {}
  if (!selectedPlan.value) {
    errors.value.plan_id = 'Please select a monthly or yearly plan.'
    toast.error('Please select a monthly or yearly plan.')
    return
  }
  step.value = 3
}

const purchase = async () => {
  errors.value = {}
  if (!selectedPlan.value) {
    errors.value.plan_id = 'Please select a plan.'
    toast.error('Please select a plan.')
    return
  }

  try {
    submitting.value = true
    const payload = {
      ...form.value,
      plan_id: selectedPlan.value.id,
    }

    const res = await axios.post('/api/public/member-registration/initiate-payment', payload)
    const checkoutUrl = res.data?.payment?.checkout_url
    if (!checkoutUrl) {
      toast.error('Checkout link was not returned.')
      return
    }
    window.location.href = checkoutUrl
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error(err.response?.data?.message || 'Failed to start payment.')
    }
  } finally {
    submitting.value = false
  }
}

onMounted(fetchMeta)
</script>

<style scoped>
.label {
  @apply mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400;
}

.field {
  @apply w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200;
}

.field-error {
  @apply border-red-400 ring-red-200 focus:ring-red-300 dark:border-red-500;
}

.form-error {
  @apply mt-1 text-xs font-medium text-red-600 dark:text-red-400;
}

.btn-primary {
  @apply rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60;
}

.btn-muted {
  @apply rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800;
}
</style>
