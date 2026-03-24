<template>
  <section class="relative overflow-hidden bg-slate-100 pb-20 pt-36 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-16 h-64 w-64 rounded-full bg-cyan-300/25 blur-3xl dark:bg-cyan-500/20"></div>
      <div class="absolute -right-20 bottom-4 h-72 w-72 rounded-full bg-amber-300/25 blur-3xl dark:bg-amber-500/20"></div>
    </div>

    <div class="relative mx-auto max-w-6xl px-6">
      <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-300">Member Subscription</p>
        <h1 class="mt-2 text-4xl font-black tracking-tight md:text-5xl">Buy Subscription</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600 dark:text-slate-300 md:text-base">
          Choose your facility and subscription plan, then complete secure payment through Seylan.
        </p>
      </div>

      <div class="mb-6 grid gap-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 md:grid-cols-2">
        <div class="rounded-xl px-3 py-2 text-sm font-semibold" :class="stepBadgeClass(1)">1. Select Plan</div>
        <div class="rounded-xl px-3 py-2 text-sm font-semibold" :class="stepBadgeClass(2)">2. Summary & Payment</div>
      </div>

      <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900 sm:p-8">
        <div v-if="loadingMeta" class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
          Loading subscription options...
        </div>

        <template v-else>
          <div v-if="missingDob" class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            Your date of birth is missing in your profile. Please update it to continue with subscription purchase.
          </div>

          <div v-if="step === 1" class="space-y-5">
            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="label">Member</label>
                <input :value="member.name || '-'" type="text" class="field" disabled />
              </div>
              <div>
                <label class="label">Email</label>
                <input :value="member.email || '-'" type="text" class="field" disabled />
              </div>
              <div>
                <label class="label">Date Of Birth</label>
                <input :value="member.date_of_birth || '-'" type="text" class="field" disabled />
              </div>
              <div>
                <label class="label">Age</label>
                <input :value="age !== null ? `${age} years` : '-'" type="text" class="field" disabled />
              </div>
              <div class="md:col-span-2">
                <label class="label">Facility</label>
                <select v-model.number="form.facility_id" class="field" :class="errors.facility_id ? 'field-error' : ''">
                  <option :value="null">Select facility</option>
                  <option v-for="facility in facilities" :key="facility.id" :value="facility.id">{{ facility.title }}</option>
                </select>
                <p v-if="errors.facility_id" class="form-error">{{ errors.facility_id }}</p>
              </div>
            </div>

            <div v-if="form.facility_id">
              <p class="label">Plans</p>
              <div v-if="matchingPlans.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-300">
                No matching plans found for your age in this facility.
              </div>
              <p v-if="errors.plan_id" class="form-error">{{ errors.plan_id }}</p>

              <div v-if="matchingPlans.length" class="grid gap-4 md:grid-cols-2">
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
            </div>

            <div class="flex justify-end">
              <button type="button" class="btn-primary" :disabled="missingDob" @click="goToSummary">Review & Pay</button>
            </div>
          </div>

          <div v-else class="space-y-5">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-950/60">
              <p><span class="font-semibold">Member:</span> {{ member.name || '-' }}</p>
              <p><span class="font-semibold">Email:</span> {{ member.email || '-' }}</p>
              <p><span class="font-semibold">Facility:</span> {{ selectedFacilityTitle || '-' }}</p>
              <p><span class="font-semibold">Plan:</span> {{ selectedPlan ? `${selectedPlan.frequency} - ${formatCurrency(selectedPlan.price)}` : '-' }}</p>
            </div>

            <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-500/30 dark:bg-cyan-500/10">
              <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-700 dark:text-cyan-300">Secure Payment</p>
              <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                Clicking buy subscription will redirect you to Seylan secure card checkout.
              </p>
            </div>

            <div class="flex items-center justify-between">
              <button type="button" class="btn-muted" @click="step = 1">Back</button>
              <button type="button" class="btn-primary" :disabled="submitting || missingDob" @click="purchase">
                {{ submitting ? 'Redirecting...' : 'Buy Subscription' }}
              </button>
            </div>
          </div>
        </template>
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
const loadingMeta = ref(false)
const submitting = ref(false)
const facilities = ref([])
const plans = ref([])
const selectedPlanId = ref(null)
const errors = ref({})

const member = ref({
  name: '',
  email: '',
  date_of_birth: '',
})

const form = ref({
  facility_id: null,
})

const age = computed(() => {
  if (!member.value.date_of_birth) return null
  const dob = new Date(member.value.date_of_birth)
  if (Number.isNaN(dob.getTime())) return null
  const now = new Date()
  let years = now.getFullYear() - dob.getFullYear()
  const monthDiff = now.getMonth() - dob.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < dob.getDate())) years -= 1
  return years
})

const missingDob = computed(() => !member.value.date_of_birth)

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
  loadingMeta.value = true
  try {
    const res = await axios.get('/api/member/subscription/purchase-meta')
    facilities.value = Array.isArray(res.data.facilities) ? res.data.facilities : []
    plans.value = Array.isArray(res.data.plans) ? res.data.plans : []
    member.value = res.data.member || member.value
  } catch {
    facilities.value = []
    plans.value = []
    toast.error('Failed to load subscription options.')
  } finally {
    loadingMeta.value = false
  }
}

const goToSummary = () => {
  errors.value = {}
  if (missingDob.value) {
    toast.error('Date of birth is required in profile to continue.')
    return
  }
  if (!form.value.facility_id) errors.value.facility_id = 'Please select a facility.'
  if (!selectedPlan.value) errors.value.plan_id = 'Please select a plan.'

  if (Object.keys(errors.value).length > 0) {
    toast.error('Please select facility and plan.')
    return
  }

  step.value = 2
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
      facility_id: form.value.facility_id,
      plan_id: selectedPlan.value.id,
    }

    const res = await axios.post('/api/member/subscription/initiate-purchase', payload)
    const checkoutUrl = res.data?.payment?.checkout_url
    if (!checkoutUrl) {
      toast.error('Checkout link was not returned.')
      return
    }
    window.location.href = checkoutUrl
  } catch (err) {
    if (err.response?.status === 422) {
      const responseErrors = err.response.data?.errors || {}
      errors.value = responseErrors
      const validationMessages = Object.values(responseErrors).flat()
      if (validationMessages.length) {
        validationMessages.forEach((msg) => toast.error(msg))
      } else if (err.response.data?.message) {
        toast.error(err.response.data.message)
      }
    } else {
      toast.error(err.response?.data?.message || 'Failed to start subscription payment.')
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
