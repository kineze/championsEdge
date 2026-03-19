<template>
  <section class="p-3 sm:p-4">
    <div class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-sky-50 to-white p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">Administration</p>
          <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Subscription Manager</h2>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create and manage subscriptions for users.</p>
        </div>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-sky-500/20 transition hover:brightness-110"
          @click="openCreateModal"
        >
          <i class="fas fa-plus"></i>
          <span>Create New Subscription</span>
        </button>
      </div>

      <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950/70">
        <div class="grid grid-cols-1 gap-3 border-b border-slate-200 p-3 dark:border-slate-700 sm:grid-cols-3">
          <div>
            <label class="mb-1 inline-block text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Status</label>
            <select v-model="tableFilters.status" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="blocked">Blocked</option>
            </select>
          </div>
          <div>
            <label class="mb-1 inline-block text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Date</label>
            <input v-model="tableFilters.date" type="date" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
          </div>
          <div>
            <label class="mb-1 inline-block text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">User Search</label>
            <input v-model="tableFilters.userSearch" type="text" placeholder="Name, email, phone" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-900/80">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">User</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Facility</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Plan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Period</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Payment</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
              <tr v-if="loading">
                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">Loading subscriptions...</td>
              </tr>
              <tr v-else-if="subscriptions.length === 0">
                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">No subscriptions found.</td>
              </tr>
              <tr v-for="item in filteredSubscriptions" :key="item.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
                <td class="px-4 py-3">
                  <p class="font-semibold text-slate-800 dark:text-slate-100">{{ item.user?.name || '-' }}</p>
                  <p class="text-xs text-slate-500">{{ item.user?.email || '-' }}</p>
                </td>
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ item.facility?.title || '-' }}</td>
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                  <span class="capitalize">{{ item.plan?.frequency || '-' }}</span>
                  <span v-if="item.plan?.age_group" class="text-xs text-slate-500">({{ item.plan.age_group.group_name }})</span>
                </td>
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                  {{ formatDate(item.subscription_start_date) }} - {{ formatDate(item.subscription_end_date) }}
                </td>
                <td class="px-4 py-3 text-slate-700 capitalize dark:text-slate-200">{{ formatPaymentMethod(item.payment_method) }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="item.is_blocked ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'"
                  >
                    {{ item.is_blocked ? 'Blocked' : 'Active' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <button
                    type="button"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                    :class="item.is_blocked
                      ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-300'
                      : 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-500/20 dark:text-red-300'"
                    @click="toggleBlocked(item)"
                  >
                    {{ item.is_blocked ? 'Unblock' : 'Block' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showModal" class="fixed inset-0 z-[1700] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Create Subscription</h3>
            <button type="button" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="showModal = false">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form class="mt-4 grid gap-4" @submit.prevent="saveSubscription">
            <div>
              <p class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">User Type</p>
              <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <label
                  class="cursor-pointer rounded-xl border p-3 text-sm transition"
                  :class="form.user_mode === 'existing' ? 'border-sky-500 bg-sky-50 text-sky-700 dark:border-sky-400 dark:bg-sky-500/10 dark:text-sky-300' : 'border-slate-300 text-slate-600 dark:border-slate-600 dark:text-slate-300'"
                >
                  <input v-model="form.user_mode" type="radio" value="existing" class="sr-only" />
                  Existing User
                </label>
                <label
                  class="cursor-pointer rounded-xl border p-3 text-sm transition"
                  :class="form.user_mode === 'new' ? 'border-sky-500 bg-sky-50 text-sky-700 dark:border-sky-400 dark:bg-sky-500/10 dark:text-sky-300' : 'border-slate-300 text-slate-600 dark:border-slate-600 dark:text-slate-300'"
                >
                  <input v-model="form.user_mode" type="radio" value="new" class="sr-only" />
                  New User
                </label>
              </div>
            </div>

            <div v-if="form.user_mode === 'existing'">
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Search User</label>
              <input
                v-model="userSearch"
                type="text"
                placeholder="Search by name, email, phone or NIC"
                class="mb-3 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
              />
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Select User</label>
              <select v-model="form.user_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                <option disabled value="">Choose user</option>
                <option v-for="user in filteredUsers" :key="user.id" :value="user.id">{{ user.name }} ({{ user.email }})</option>
              </select>
              <p class="mt-2 text-xs text-slate-500">
                Selected User DOB:
                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ selectedUser?.profile?.date_of_birth || '-' }}</span>
              </p>
            </div>

            <div v-else class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
              <p class="mb-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">New User Details</p>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <input v-model="form.new_user.name" type="text" placeholder="Full name" required class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
                <input v-model="form.new_user.email" type="email" placeholder="Email" required class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
                <input v-model="form.new_user.phone" type="text" placeholder="Phone" required class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
                <input v-model="form.new_user.nic" type="text" placeholder="NIC" required class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
                <input v-model="form.new_user.date_of_birth" type="date" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
                <input v-model="form.new_user.address" type="text" placeholder="Address" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
              </div>
              <textarea v-model="form.new_user.notes" rows="2" placeholder="Other details / notes" class="mt-3 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"></textarea>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Facility</label>
                <select v-model="form.facility_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                  <option disabled value="">Select facility</option>
                  <option v-for="facility in facilities" :key="facility.id" :value="facility.id">{{ facility.title }}</option>
                </select>
              </div>

              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Pricing Plan</label>
                <select v-model="form.plan_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                  <option disabled value="">Select plan</option>
                  <option v-for="plan in matchingPlans" :key="plan.id" :value="plan.id">
                    {{ planLabel(plan) }}
                  </option>
                </select>
                <p class="mt-1 text-xs text-slate-500">
                  Matching by age:
                  <span class="font-semibold text-slate-700 dark:text-slate-200">{{ userAge !== null ? `${userAge} years` : 'No date of birth selected' }}</span>
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Start Date</label>
                <input v-model="form.subscription_start_date" type="date" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
              </div>
              <div v-if="form.user_mode === 'existing'">
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">End Date</label>
                <input v-model="form.subscription_end_date" type="date" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
              </div>
              <div v-else>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">End Date (Auto)</label>
                <input :value="computedEndDate || ''" type="date" disabled class="w-full cursor-not-allowed rounded-xl border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-700 outline-none dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200" />
              </div>
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Payment Method</label>
                <select v-model="form.payment_method" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                  <option disabled value="">Select payment method</option>
                  <option v-for="method in paymentMethods" :key="method" :value="method">{{ formatPaymentMethod(method) }}</option>
                </select>
              </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/50">
              <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Summary Before Save</p>
              <div class="mt-2 grid grid-cols-1 gap-2 text-sm text-slate-700 dark:text-slate-200 sm:grid-cols-2">
                <p><span class="font-semibold">User:</span> {{ summaryUser }}</p>
                <p><span class="font-semibold">Facility:</span> {{ summaryFacility }}</p>
                <p><span class="font-semibold">Plan:</span> {{ summaryPlan }}</p>
                <p><span class="font-semibold">Payment:</span> {{ formatPaymentMethod(form.payment_method) }}</p>
                <p><span class="font-semibold">Start Date:</span> {{ form.subscription_start_date || '-' }}</p>
                <p><span class="font-semibold">End Date:</span> {{ effectiveEndDate || '-' }}</p>
              </div>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
              <input v-model="form.is_blocked" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
              Mark as blocked
            </label>

            <div class="mt-1 flex justify-end gap-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700" @click="showModal = false">Cancel</button>
              <button type="submit" class="rounded-lg bg-sky-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">
                Create Subscription
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const subscriptions = ref([])
const users = ref([])
const facilities = ref([])
const plans = ref([])
const paymentMethods = ref([])
const showModal = ref(false)
const userSearch = ref('')
const tableFilters = ref({
  status: '',
  date: '',
  userSearch: '',
})

const form = ref({
  user_mode: 'existing',
  user_id: '',
  new_user: {
    name: '',
    email: '',
    phone: '',
    nic: '',
    address: '',
    date_of_birth: '',
    notes: '',
  },
  facility_id: '',
  plan_id: '',
  payment_method: '',
  subscription_start_date: '',
  subscription_end_date: '',
  is_blocked: false,
})

const resetForm = () => {
  form.value = {
    user_mode: 'existing',
    user_id: '',
    new_user: {
      name: '',
      email: '',
      phone: '',
      nic: '',
      address: '',
      date_of_birth: '',
      notes: '',
    },
    facility_id: '',
    plan_id: '',
    payment_method: '',
    subscription_start_date: '',
    subscription_end_date: '',
    is_blocked: false,
  }
}

const filteredUsers = computed(() => {
  const term = userSearch.value.trim().toLowerCase()
  if (!term) return users.value
  return users.value.filter((user) => {
    const phone = user.profile?.phone || ''
    const nic = user.profile?.nic || ''
    return [user.name, user.email, phone, nic].join(' ').toLowerCase().includes(term)
  })
})

const selectedUser = computed(() => {
  return users.value.find((user) => Number(user.id) === Number(form.value.user_id)) || null
})

const userAge = computed(() => {
  const dob = form.value.user_mode === 'existing'
    ? selectedUser.value?.profile?.date_of_birth
    : form.value.new_user.date_of_birth
  return calculateAge(dob)
})

const filteredPlans = computed(() => {
  if (!form.value.facility_id) return []
  return plans.value.filter((plan) => Number(plan.facility_id) === Number(form.value.facility_id))
})

const matchingPlans = computed(() => {
  if (userAge.value === null) return []
  return filteredPlans.value.filter((plan) => {
    const start = Number(plan.age_group?.age_start ?? -1)
    const end = Number(plan.age_group?.age_end ?? -1)
    return userAge.value >= start && userAge.value <= end
  })
})

const filteredSubscriptions = computed(() => {
  const search = tableFilters.value.userSearch.trim().toLowerCase()
  return subscriptions.value.filter((item) => {
    if (tableFilters.value.status === 'active' && item.is_blocked) return false
    if (tableFilters.value.status === 'blocked' && !item.is_blocked) return false

    if (tableFilters.value.date) {
      const start = item.subscription_start_date ? String(item.subscription_start_date).slice(0, 10) : ''
      const end = item.subscription_end_date ? String(item.subscription_end_date).slice(0, 10) : ''
      if (start !== tableFilters.value.date && end !== tableFilters.value.date) return false
    }

    if (search) {
      const name = item.user?.name || ''
      const email = item.user?.email || ''
      const phone = item.user?.profile?.phone || ''
      const haystack = `${name} ${email} ${phone}`.toLowerCase()
      if (!haystack.includes(search)) return false
    }

    return true
  })
})

const selectedFacility = computed(() => {
  return facilities.value.find((facility) => Number(facility.id) === Number(form.value.facility_id)) || null
})

const selectedPlan = computed(() => {
  return plans.value.find((plan) => Number(plan.id) === Number(form.value.plan_id)) || null
})

const computedEndDate = computed(() => {
  if (form.value.user_mode !== 'new') return null
  if (!form.value.subscription_start_date || !selectedPlan.value?.frequency) return null
  const date = new Date(form.value.subscription_start_date)
  if (Number.isNaN(date.getTime())) return null
  if (selectedPlan.value.frequency === 'yearly') {
    date.setFullYear(date.getFullYear() + 1)
  } else {
    date.setMonth(date.getMonth() + 1)
  }
  return date.toISOString().slice(0, 10)
})

const effectiveEndDate = computed(() => {
  return form.value.user_mode === 'new'
    ? computedEndDate.value
    : form.value.subscription_end_date
})

const summaryUser = computed(() => {
  if (form.value.user_mode === 'new') {
    return form.value.new_user.name || '-'
  }
  return selectedUser.value ? `${selectedUser.value.name} (${selectedUser.value.email})` : '-'
})

const summaryFacility = computed(() => selectedFacility.value?.title || '-')
const summaryPlan = computed(() => (selectedPlan.value ? planLabel(selectedPlan.value) : '-'))

watch([() => form.value.facility_id, () => form.value.user_id, () => form.value.new_user.date_of_birth, () => form.value.user_mode], () => {
  if (!matchingPlans.value.some((plan) => Number(plan.id) === Number(form.value.plan_id))) {
    form.value.plan_id = ''
  }
})

const fetchSubscriptions = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/subscriptions')
    subscriptions.value = Array.isArray(res.data.subscriptions) ? res.data.subscriptions : []
  } catch {
    toast.error('Failed to load subscriptions')
  } finally {
    loading.value = false
  }
}

const fetchMeta = async () => {
  try {
    const res = await axios.get('/api/subscriptions/meta')
    users.value = Array.isArray(res.data.users) ? res.data.users : []
    facilities.value = Array.isArray(res.data.facilities) ? res.data.facilities : []
    plans.value = Array.isArray(res.data.plans) ? res.data.plans : []
    paymentMethods.value = Array.isArray(res.data.payment_methods) ? res.data.payment_methods : []
  } catch {
    toast.error('Failed to load subscription setup data')
  }
}

const openCreateModal = () => {
  resetForm()
  showModal.value = true
}

const saveSubscription = async () => {
  const payload = {
    user_mode: form.value.user_mode,
    user_id: form.value.user_mode === 'existing' ? Number(form.value.user_id) : null,
    new_user: form.value.user_mode === 'new' ? { ...form.value.new_user } : null,
    facility_id: Number(form.value.facility_id),
    plan_id: Number(form.value.plan_id),
    payment_method: form.value.payment_method,
    subscription_start_date: form.value.subscription_start_date,
    subscription_end_date: effectiveEndDate.value,
    is_blocked: Boolean(form.value.is_blocked),
  }

  try {
    await axios.post('/api/subscriptions', payload)
    toast.success('Subscription created successfully')
    showModal.value = false
    fetchSubscriptions()
    fetchMeta()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Failed to create subscription')
    }
  }
}

const toggleBlocked = async (item) => {
  try {
    const res = await axios.patch(`/api/subscriptions/${item.id}/toggle-blocked`)
    const updated = res.data.subscription
    subscriptions.value = subscriptions.value.map((subscription) =>
      subscription.id === updated.id ? updated : subscription
    )
    toast.success(updated.is_blocked ? 'Subscription blocked' : 'Subscription unblocked')
  } catch {
    toast.error('Failed to update subscription status')
  }
}

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString()
}

const formatPaymentMethod = (value) => {
  if (!value) return '-'
  return String(value).replaceAll('_', ' ').replace(/\b\w/g, (ch) => ch.toUpperCase())
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-LK', {
    style: 'currency',
    currency: 'LKR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value || 0))
}

const calculateAge = (dateValue) => {
  if (!dateValue) return null
  const dob = new Date(dateValue)
  if (Number.isNaN(dob.getTime())) return null
  const today = new Date()
  let age = today.getFullYear() - dob.getFullYear()
  const monthDiff = today.getMonth() - dob.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
    age--
  }
  return age >= 0 ? age : null
}

const planLabel = (plan) => {
  const frequency = String(plan.frequency || '').toUpperCase()
  const ageGroup = plan.age_group?.group_name ? ` - ${plan.age_group.group_name}` : ''
  return `${frequency}${ageGroup} (${formatCurrency(plan.price)})`
}

onMounted(() => {
  fetchMeta()
  fetchSubscriptions()
})
</script>
