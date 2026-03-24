<template>
  <section v-if="!embedded" class="relative flex items-center justify-center bg-cover  bg-center" style="background-image: url('/images/indoor.jpg');">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative px-6 text-center text-white">
      <h1 class="text-4xl font-bold md:text-6xl">Book Your Slot</h1>
      <p class="mx-auto mt-3 max-w-2xl text-base text-slate-100 md:text-lg">Step through your booking request and submit for admin approval.</p>
    </div>
  </section>

  <section class="bg-white py-20 text-slate-900">
    <div class="w-full">
      <div class="rounded-3xl border border-slate-200 bg-zinc-200 p-6 shadow-2xl md:p-8">
        <div class="rounded-2xl border border-cyan-100 bg-white/80 px-4 py-4">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">Reservation Form</p>
              <p class="mt-1 text-sm font-medium text-slate-700">Step {{ Math.min(currentStep, 4) }} of 4</p>
            </div>
            <div class="rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
              {{ stepTitles[Math.min(currentStep, 4) - 1] }}
            </div>
          </div>

          <div class="mt-4 grid grid-cols-4 gap-2">
            <div v-for="(title, idx) in stepTitles" :key="title" class="flex items-center gap-2">
              <div
                class="inline-grid h-7 w-7 shrink-0 place-items-center rounded-full border text-xs font-bold transition"
                :class="stepCircleClass(idx + 1)"
              >
                <i v-if="currentStep > idx + 1" class="fas fa-check text-[10px]"></i>
                <span v-else>{{ idx + 1 }}</span>
              </div>
              <div class="hidden min-w-0 sm:block">
                <p class="truncate text-[11px] font-semibold" :class="currentStep >= idx + 1 ? 'text-cyan-700' : 'text-slate-500'">{{ title }}</p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="currentStep === 1" class="mt-5 grid gap-5 md:grid-cols-2">
          <div>
            <label class="mb-1 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-gray-600">Facility</label>
            <select
              v-if="!isFacilityLocked"
              v-model.number="form.facility_id"
              @blur="touchField('facility_id')"
              class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none transition"
              :class="inputClass(showStep1Error('facility_id'))"
            >
              <option :value="null">Select a facility</option>
              <option v-for="facility in facilities" :key="facility.id" :value="Number(facility.id)">
                {{ facility.title }}
              </option>
            </select>
            <input
              v-else
              type="text"
              :value="selectedFacilityTitle || boundFacilityTitle || 'Loading facility...'"
              disabled
              class="w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-700"
            />
            <p v-if="showStep1Error('facility_id')" class="mt-1 text-xs text-red-600">{{ step1Errors.facility_id }}</p>
          </div>

          <div>
            <label class="mb-1 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-gray-600">Start Date Time</label>
            <input
              v-model="form.start_at"
              @blur="touchField('start_at')"
              type="datetime-local"
              class="reservation-datetime w-full rounded-xl border px-3 py-2.5 text-sm outline-none transition"
              :class="inputClass(showStep1Error('start_at'))"
            />
            <p v-if="showStep1Error('start_at')" class="mt-1 text-xs text-red-600">{{ step1Errors.start_at }}</p>
          </div>

          <div>
            <label class="mb-1 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-gray-600">End Date Time</label>
            <input
              v-model="form.end_at"
              @blur="touchField('end_at')"
              type="datetime-local"
              class="reservation-datetime w-full rounded-xl border px-3 py-2.5 text-sm outline-none transition"
              :class="inputClass(showStep1Error('end_at'))"
            />
            <p v-if="showStep1Error('end_at')" class="mt-1 text-xs text-red-600">{{ step1Errors.end_at }}</p>
          </div>

          <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Availability</p>
            <div v-if="availabilityState === 'idle'" class="mt-2 text-sm text-slate-600">Click "Next" to check availability for selected time.</div>
            <div v-else-if="availabilityState === 'checking'" class="mt-2 text-sm text-slate-600">Checking availability...</div>
            <div v-else-if="availabilityState === 'available'" class="mt-2 space-y-2 text-sm">
              <p class="font-semibold text-emerald-700">{{ availabilityMessage || 'Selected time is available. Continue to next step.' }}</p>
              <ul v-if="accessNotice.length" class="list-disc space-y-1 pl-5 text-slate-700">
                <li v-for="note in accessNotice" :key="note">{{ note }}</li>
              </ul>
            </div>
            <div v-else-if="availabilityState === 'unavailable'" class="mt-2 space-y-3 text-sm text-amber-800">
              <p>{{ availabilityMessage || 'Selected time is not available. Choose one of the nearest available times to continue.' }}</p>
              <ul v-if="availabilityReasons.length" class="list-disc space-y-1 pl-5 text-sm text-amber-900">
                <li v-for="reason in availabilityReasons" :key="reason">{{ reason }}</li>
              </ul>
              <ul v-if="accessNotice.length" class="list-disc space-y-1 pl-5 text-sm text-slate-700">
                <li v-for="note in accessNotice" :key="note">{{ note }}</li>
              </ul>
              <div v-if="availabilitySuggestions.length" class="grid gap-2">
                <button
                  v-for="slot in availabilitySuggestions"
                  :key="`${slot.start_at}_${slot.end_at}`"
                  type="button"
                  class="rounded-xl border px-3 py-2 text-left transition"
                  :class="selectedSuggestionKey === `${slot.start_at}_${slot.end_at}` ? 'border-cyan-500 bg-cyan-50 ring-2 ring-cyan-100' : 'border-slate-300 bg-white hover:border-cyan-300'"
                  @click="selectSuggestion(slot)"
                >
                  <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Suggested Slot</p>
                  <p class="mt-1 font-semibold text-slate-800">{{ formatDateTime(slot.start_at) }} to {{ formatDateTime(slot.end_at) }}</p>
                </button>
              </div>
              <p v-else class="text-sm text-slate-600">No nearby slots found in the next 14 days.</p>
            </div>
          </div>

          <div class="md:col-span-2 flex items-center justify-end gap-2">
            <button
              v-if="availabilityState === 'unavailable' && selectedSuggestion"
              type="button"
              class="rounded-xl border border-cyan-500 bg-cyan-50 px-4 py-2 font-semibold text-cyan-700 transition hover:bg-cyan-100"
              @click="agreeSuggestionAndContinue"
            >
              Agree & Continue
            </button>
            <button
              type="button"
              class="rounded-xl bg-cyan-600 px-4 py-2 font-semibold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="loading || checkingAvailability"
              @click="checkAvailabilityAndNext"
            >
              {{ checkingAvailability ? 'Checking...' : 'Next' }}
            </button>
          </div>
        </div>

        <div v-else-if="currentStep === 2" class="mt-5 grid gap-5 md:grid-cols-2">
          <div>
            <label class="mb-1 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-gray-600">Name</label>
            <input
              v-model.trim="form.name"
              @blur="touchField('name')"
              type="text"
              class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none transition"
              :class="inputClass(showStep2Error('name'))"
              placeholder="Your full name"
            />
            <p v-if="showStep2Error('name')" class="mt-1 text-xs text-red-600">{{ step2Errors.name }}</p>
          </div>

          <div>
            <label class="mb-1 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-gray-600">Phone</label>
            <input
              v-model.trim="form.phone"
              @blur="touchField('phone')"
              type="text"
              class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none transition"
              :class="inputClass(showStep2Error('phone'))"
              placeholder="0771234567"
            />
            <p v-if="showStep2Error('phone')" class="mt-1 text-xs text-red-600">{{ step2Errors.phone }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-gray-600">Email (Optional)</label>
            <input
              v-model.trim="form.email"
              @blur="touchField('email')"
              type="email"
              class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none transition"
              :class="inputClass(showStep2Error('email'))"
              placeholder="name@example.com"
            />
            <p v-if="showStep2Error('email')" class="mt-1 text-xs text-red-600">{{ step2Errors.email }}</p>
          </div>

          <div class="md:col-span-2 flex items-center justify-between gap-2">
            <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-100" @click="currentStep = 1">Back</button>
            <button type="button" class="rounded-xl bg-cyan-600 px-4 py-2 font-semibold text-white transition hover:bg-cyan-700" @click="goToStep3">Next</button>
          </div>
        </div>

        <div v-else-if="currentStep === 3" class="mt-5 grid gap-5">
          <div>
            <label class="mb-2 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-gray-600">Price Plan</label>
            <div v-if="facilityPlans.length === 0" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-sm text-slate-500">
              No pricing plans found for this facility.
            </div>

            <div v-else class="grid gap-3 sm:grid-cols-2">
              <label
                v-for="plan in facilityPlans"
                :key="plan.id"
                class="group relative cursor-pointer rounded-2xl border bg-white p-4 transition"
                :class="Number(form.price_plan_id) === Number(plan.id)
                  ? 'border-cyan-500 bg-cyan-50 shadow-md ring-2 ring-cyan-200'
                  : 'border-slate-200 hover:border-cyan-300 hover:bg-slate-50'"
                @click="touchField('price_plan_id')"
              >
                <input v-model.number="form.price_plan_id" type="radio" name="price_plan_id" :value="plan.id" class="sr-only" />

                <span
                  class="absolute right-3 top-3 inline-grid h-6 w-6 place-items-center rounded-full border text-xs transition"
                  :class="Number(form.price_plan_id) === Number(plan.id)
                    ? 'border-cyan-600 bg-cyan-600 text-white'
                    : 'border-slate-300 bg-white text-transparent group-hover:border-cyan-400'"
                >
                  <i class="fas fa-check text-[10px]"></i>
                </span>

                <div class="flex items-start justify-between gap-2 pr-8">
                  <div>
                    <p class="text-sm font-bold text-slate-900">{{ prettifyRangeType(plan.range_type) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Plan ID: {{ plan.id }}</p>
                  </div>
                  <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ formatCurrency(plan.price) }}</span>
                </div>
                <div class="mt-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700">
                  <p>
                    Deposit:
                    <span class="font-semibold">{{ plan.is_deposit_required ? `Required (${formatCurrency(plan.deposit_amount || 0)})` : 'Optional' }}</span>
                  </p>
                </div>
              </label>
            </div>
            <p v-if="showStep3Error" class="mt-1 text-xs text-red-600">{{ step3Error }}</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Extra Items</p>
            <p class="mt-1 text-sm text-slate-600">Add optional items available for this facility.</p>

            <div v-if="availableFacilityExtraItems.length === 0" class="mt-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-sm text-slate-500">
              No extra items configured for this facility.
            </div>

            <div v-else class="mt-3 space-y-3">
              <div
                v-for="item in availableFacilityExtraItems"
                :key="item.id"
                class="rounded-xl border border-slate-200 bg-slate-50 p-3"
              >
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <div>
                    <p class="text-sm font-semibold text-slate-800">{{ item.name }}</p>
                    <p class="text-xs text-slate-500">{{ formatCurrency(item.price_per_unit) }} per {{ item.unit_type }}</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <label :for="`extra_${item.id}`" class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Units</label>
                    <input
                      :id="`extra_${item.id}`"
                      v-model.number="selectedExtraItemUnits[item.id]"
                      type="number"
                      min="0"
                      step="0.5"
                      class="w-24 rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-800 outline-none ring-cyan-200 focus:border-cyan-500"
                      placeholder="0"
                    />
                  </div>
                </div>
                <p v-if="Number(selectedExtraItemUnits[item.id] || 0) > 0" class="mt-2 text-xs text-slate-600">
                  Line total: <span class="font-semibold">{{ formatCurrency(Number(selectedExtraItemUnits[item.id] || 0) * Number(item.price_per_unit || 0)) }}</span>
                </p>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between gap-2">
            <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-100" @click="currentStep = 2">Back</button>
            <button type="button" class="rounded-xl bg-cyan-600 px-4 py-2 font-semibold text-white transition hover:bg-cyan-700" @click="goToStep4">Continue</button>
          </div>
        </div>

        <div v-else-if="currentStep === 4" class="mt-5 grid gap-5">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Booking Summary</p>
            <div class="mt-3 grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
              <p>Facility: <span class="font-semibold">{{ selectedFacilityTitle || '-' }}</span></p>
              <p>Plan: <span class="font-semibold">{{ selectedPlan ? prettifyRangeType(selectedPlan.range_type) : '-' }}</span></p>
              <p>Start: <span class="font-semibold">{{ formatDateTime(form.start_at) }}</span></p>
              <p>End: <span class="font-semibold">{{ formatDateTime(form.end_at) }}</span></p>
              <p>Actual Duration: <span class="font-semibold">{{ durationHours.toFixed(2) }} h</span></p>
              <p>Billable Hours: <span class="font-semibold">{{ billableUnits.toFixed(2) }} h</span></p>
              <p>Unit Price: <span class="font-semibold">{{ formatCurrency(unitPrice) }}</span></p>
              <p>Base Amount: <span class="font-semibold">{{ formatCurrency(baseReservationTotal) }}</span></p>
              <p>Extra Items Total: <span class="font-semibold">{{ formatCurrency(extraItemsTotal) }}</span></p>
              <p>Total Amount: <span class="font-semibold">{{ formatCurrency(calculatedTotal) }}</span></p>
              <p>Deposit: <span class="font-semibold">{{ formatCurrency(computedDepositAmount) }}</span></p>
              <p>Remaining Balance: <span class="font-semibold">{{ formatCurrency(remainingBalance) }}</span></p>
            </div>

            <div v-if="selectedExtraItems.length" class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
              <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Selected Extra Items</p>
              <ul class="mt-2 space-y-1 text-sm text-slate-700">
                <li v-for="item in selectedExtraItems" :key="item.facility_extra_item_id">
                  {{ item.name }} ({{ item.units }} {{ item.unit_type }}) - {{ formatCurrency(item.line_total) }}
                </li>
              </ul>
            </div>
          </div>

          <div class="flex items-center justify-between gap-2">
            <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-100" @click="currentStep = 3">Back</button>
            <button type="button" class="rounded-xl bg-cyan-600 px-4 py-2 font-semibold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60" :disabled="submitting" @click="submitReservation">
              {{ submitting ? 'Processing...' : (computedDepositAmount > 0 ? 'Pay Deposit & Send Request' : 'Send Booking Request') }}
            </button>
          </div>
        </div>

        <div v-else-if="currentStep === 5 && lastReservation" class="mt-5 grid gap-5">
          <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-semibold text-emerald-800">Booking request sent successfully.</p>
            <p class="mt-1 text-sm text-emerald-700">We received your request and will get back to you soon.</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Submitted Booking Details</p>
            <div class="mt-3 grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
              <p>Booking ID: <span class="font-semibold">#{{ lastReservation.id }}</span></p>
              <p>Status: <span class="font-semibold capitalize">{{ lastReservation.status }}</span></p>
              <p>Facility: <span class="font-semibold">{{ lastReservation.facility?.title || selectedFacilityTitle }}</span></p>
              <p>Plan: <span class="font-semibold">{{ prettifyRangeType(lastReservation.price_plan?.range_type || selectedPlan?.range_type) }}</span></p>
              <p>Start: <span class="font-semibold">{{ formatDateTime(lastReservation.day_range?.start_at || form.start_at) }}</span></p>
              <p>End: <span class="font-semibold">{{ formatDateTime(lastReservation.day_range?.end_at || form.end_at) }}</span></p>
              <p>Name: <span class="font-semibold">{{ lastReservation.name }}</span></p>
              <p>Phone: <span class="font-semibold">{{ lastReservation.phone }}</span></p>
              <p>Email: <span class="font-semibold">{{ lastReservation.email || '-' }}</span></p>
              <p>Deposit: <span class="font-semibold">{{ formatCurrency(lastReservation.deposit_amount) }}</span></p>
              <p>Extra Items Total: <span class="font-semibold">{{ formatCurrency(extraItemsTotal) }}</span></p>
              <p>Total Amount: <span class="font-semibold">{{ formatCurrency(lastReservation.reservation_amount) }}</span></p>
            </div>
          </div>

          <div class="flex justify-end">
            <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-100" @click="startNewRequest">Create Another Request</button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
  facilityId: {
    type: [String, Number],
    default: null,
  },
  embedded: {
    type: Boolean,
    default: false,
  },
})

const toast = useToast()
const stepTitles = ['Time & Availability', 'Your Details', 'Pricing Plan', 'Review']

const loading = ref(false)
const submitting = ref(false)
const checkingAvailability = ref(false)
const facilities = ref([])
const pricePlans = ref([])
const workingHours = ref([])
const facilityExtraItems = ref([])
const currentStep = ref(1)
const availabilityState = ref('idle')
const availabilitySuggestions = ref([])
const availabilityMessage = ref('')
const availabilityReasons = ref([])
const accessNotice = ref([])
const selectedSuggestion = ref(null)
const lastReservation = ref(null)

const touched = ref({
  facility_id: false,
  start_at: false,
  end_at: false,
  name: false,
  phone: false,
  email: false,
  price_plan_id: false,
})

const isFacilityLocked = computed(() => Boolean(props.embedded && boundFacilityId.value))

const attempted = ref({
  step1: false,
  step2: false,
  step3: false,
})

const form = ref({
  user_id: null,
  name: '',
  phone: '',
  email: '',
  facility_id: null,
  price_plan_id: null,
  start_at: '',
  end_at: '',
})
const selectedExtraItemUnits = ref({})

const touchField = (field) => {
  touched.value[field] = true
}

const boundFacilityId = computed(() => {
  if (props.facilityId === null || props.facilityId === undefined || props.facilityId === '') return null
  return Number(props.facilityId)
})

const boundFacilityTitle = computed(() => {
  if (!boundFacilityId.value) return ''
  return facilities.value.find((f) => Number(f.id) === Number(boundFacilityId.value))?.title || ''
})

const selectedFacilityTitle = computed(() => {
  if (!form.value.facility_id) return ''
  return facilities.value.find((f) => Number(f.id) === Number(form.value.facility_id))?.title || ''
})

const facilityPlans = computed(() => {
  if (!form.value.facility_id) return []
  return pricePlans.value.filter((plan) => Number(plan.facility_id) === Number(form.value.facility_id))
})

const availableFacilityExtraItems = computed(() => {
  if (!form.value.facility_id) return []
  return facilityExtraItems.value.filter((item) => Number(item.facility_id) === Number(form.value.facility_id))
})

const selectedPlan = computed(() => {
  if (!form.value.price_plan_id) return null
  return facilityPlans.value.find((plan) => Number(plan.id) === Number(form.value.price_plan_id)) || null
})

const workingHourMap = computed(() => {
  const map = new Map()
  ;(Array.isArray(workingHours.value) ? workingHours.value : []).forEach((item) => {
    map.set(String(item.day), item)
  })
  return map
})

const selectedSuggestionKey = computed(() => {
  if (!selectedSuggestion.value) return ''
  return `${selectedSuggestion.value.start_at}_${selectedSuggestion.value.end_at}`
})

const parseDate = (value) => {
  if (!value) return null
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? null : date
}

const normalizeDateTime = (value) => {
  const parsed = parseDate(value)
  if (!parsed) return null
  const year = parsed.getFullYear()
  const month = String(parsed.getMonth() + 1).padStart(2, '0')
  const day = String(parsed.getDate()).padStart(2, '0')
  const hours = String(parsed.getHours()).padStart(2, '0')
  const minutes = String(parsed.getMinutes()).padStart(2, '0')
  return `${year}-${month}-${day}T${hours}:${minutes}`
}

const weekDayName = (date) => {
  const names = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']
  return names[date.getDay()]
}

const isPhoneValid = (value) => /^[0-9+()\-\s]{7,20}$/.test(String(value || ''))
const isEmailValid = (value) => (!value ? true : /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value)))

const step1Errors = computed(() => {
  const result = {}
  if (!form.value.facility_id) result.facility_id = 'Select a facility.'

  const startDate = parseDate(form.value.start_at)
  const endDate = parseDate(form.value.end_at)

  if (!startDate) result.start_at = 'Start date and time is required.'
  if (!endDate) result.end_at = 'End date and time is required.'
  if (startDate && endDate && endDate <= startDate) result.end_at = 'End date/time must be after start date/time.'

  return result
})

const step2Errors = computed(() => {
  const result = {}
  if (!form.value.name || form.value.name.length < 2) result.name = 'Name is required (minimum 2 characters).'
  if (!form.value.phone || !isPhoneValid(form.value.phone)) result.phone = 'Enter a valid phone number.'
  if (!isEmailValid(form.value.email)) result.email = 'Enter a valid email address.'
  return result
})

const step3Error = computed(() => {
  if (!form.value.price_plan_id) return 'Select a pricing plan.'
  if (!selectedPlan.value) return 'Selected plan must belong to the selected facility.'
  return ''
})

const showStep1Error = (field) => Boolean(step1Errors.value[field]) && (attempted.value.step1 || touched.value[field])
const showStep2Error = (field) => Boolean(step2Errors.value[field]) && (attempted.value.step2 || touched.value[field])
const showStep3Error = computed(() => Boolean(step3Error.value) && (attempted.value.step3 || touched.value.price_plan_id))

const durationHours = computed(() => {
  const startDate = parseDate(form.value.start_at)
  const endDate = parseDate(form.value.end_at)
  if (!startDate || !endDate || endDate <= startDate) return 0
  return (endDate.getTime() - startDate.getTime()) / (1000 * 60 * 60)
})

const calculatePerHourBillableUnits = (startDate, endDate) => {
  let cursor = new Date(startDate.getTime())
  let units = 0

  while (cursor < endDate) {
    const dayEnd = new Date(cursor.getFullYear(), cursor.getMonth(), cursor.getDate() + 1, 0, 0, 0, 0)
    const segmentEnd = dayEnd < endDate ? dayEnd : endDate
    const dayName = weekDayName(cursor)
    const hour = workingHourMap.value.get(dayName)

    if (hour && !hour.is_blocked && hour.start_time && hour.end_time) {
      const [startHour, startMinute] = String(hour.start_time).slice(0, 5).split(':').map(Number)
      const [endHour, endMinute] = String(hour.end_time).slice(0, 5).split(':').map(Number)

      const windowStart = new Date(cursor.getFullYear(), cursor.getMonth(), cursor.getDate(), startHour, startMinute, 0, 0)
      const windowEnd = new Date(cursor.getFullYear(), cursor.getMonth(), cursor.getDate(), endHour, endMinute, 0, 0)

      if (windowEnd > windowStart) {
        const overlapStart = cursor > windowStart ? cursor : windowStart
        const overlapEnd = segmentEnd < windowEnd ? segmentEnd : windowEnd

        if (overlapEnd > overlapStart) {
          const hours = (overlapEnd.getTime() - overlapStart.getTime()) / (1000 * 60 * 60)
          units += Math.min(10, hours)
        }
      }
    }

    cursor = new Date(segmentEnd.getTime())
  }

  return units
}

const billableUnits = computed(() => {
  const startDate = parseDate(form.value.start_at)
  const endDate = parseDate(form.value.end_at)
  if (!startDate || !endDate || endDate <= startDate) return 0
  return calculatePerHourBillableUnits(startDate, endDate)
})

const unitPrice = computed(() => Number(selectedPlan.value?.price || 0))
const baseReservationTotal = computed(() => billableUnits.value * unitPrice.value)
const selectedExtraItems = computed(() => {
  return availableFacilityExtraItems.value
    .map((item) => {
      const units = Number(selectedExtraItemUnits.value[item.id] || 0)
      if (units <= 0) return null

      const pricePerUnit = Number(item.price_per_unit || 0)
      return {
        facility_extra_item_id: Number(item.id),
        facility_id: Number(item.facility_id),
        name: item.name,
        price_per_unit: pricePerUnit,
        unit_type: item.unit_type,
        units,
        line_total: Number((units * pricePerUnit).toFixed(2)),
      }
    })
    .filter(Boolean)
})
const extraItemsTotal = computed(() => {
  return selectedExtraItems.value.reduce((total, item) => total + Number(item.line_total || 0), 0)
})
const calculatedTotal = computed(() => baseReservationTotal.value + extraItemsTotal.value)
const computedDepositAmount = computed(() => {
  if (!selectedPlan.value?.is_deposit_required) return 0
  return Number(selectedPlan.value.deposit_amount || 0)
})
const remainingBalance = computed(() => Math.max(0, calculatedTotal.value - computedDepositAmount.value))

const inputClass = (showError) => {
  return showError
    ? 'border-red-300 bg-red-50 text-red-900 ring-red-200'
    : 'border-slate-300 bg-white text-slate-900 ring-cyan-200'
}

const stepCircleClass = (stepNumber) => {
  if (currentStep.value > stepNumber) return 'border-cyan-600 bg-cyan-600 text-white'
  if (currentStep.value === stepNumber) return 'border-cyan-500 bg-cyan-100 text-cyan-700'
  return 'border-slate-300 bg-white text-slate-500'
}

const prettifyRangeType = (type) => {
  if (type === 'per_hour') return 'Per Hour'
  return '-'
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-LK', {
    style: 'currency',
    currency: 'LKR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value || 0))
}

const formatDateTime = (value) => {
  const date = parseDate(value)
  if (!date) return '-'
  return date.toLocaleString()
}

const fetchMeta = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/public/reservations/meta')
    facilities.value = Array.isArray(res.data.facilities) ? res.data.facilities : []
    pricePlans.value = Array.isArray(res.data.price_plans) ? res.data.price_plans : []
    workingHours.value = Array.isArray(res.data.working_hours) ? res.data.working_hours : []
    facilityExtraItems.value = Array.isArray(res.data.facility_extra_items) ? res.data.facility_extra_items : []

    if (res.data.user?.id) {
      form.value.user_id = res.data.user.id
      form.value.name = res.data.user.name || ''
      form.value.email = res.data.user.email || ''
      form.value.phone = res.data.user.phone || ''
    }

    if (boundFacilityId.value) {
      form.value.facility_id = boundFacilityId.value
    } else if (!form.value.facility_id && facilities.value.length) {
      form.value.facility_id = Number(facilities.value[0].id)
    }
  } catch {
    toast.error('Failed to load reservation form details.')
  } finally {
    loading.value = false
  }
}

const selectSuggestion = (slot) => {
  selectedSuggestion.value = slot
}

const applySelectedSuggestion = () => {
  if (!selectedSuggestion.value) return false

  const start = normalizeDateTime(selectedSuggestion.value.start_at)
  const end = normalizeDateTime(selectedSuggestion.value.end_at)
  if (!start || !end) return false

  form.value.start_at = start
  form.value.end_at = end
  availabilityState.value = 'available'
  availabilityMessage.value = 'Suggested time selected and available.'
  availabilityReasons.value = []
  accessNotice.value = []
  availabilitySuggestions.value = []
  selectedSuggestion.value = null
  return true
}

const checkAvailabilityAndNext = async () => {
  attempted.value.step1 = true

  if (Object.keys(step1Errors.value).length) {
    toast.error('Please fill required date/time fields first.')
    return
  }

  try {
    checkingAvailability.value = true
    availabilityState.value = 'checking'

    const res = await axios.post('/api/public/reservations/availability', {
      facility_id: Number(form.value.facility_id),
      start_at: form.value.start_at,
      end_at: form.value.end_at,
    })

    const available = Boolean(res.data.available)
    availabilitySuggestions.value = Array.isArray(res.data.suggestions) ? res.data.suggestions : []
    availabilityReasons.value = Array.isArray(res.data.reasons) ? res.data.reasons : []
    accessNotice.value = Array.isArray(res.data.access_notice) ? res.data.access_notice : []
    availabilityMessage.value = res.data.message || ''

    if (available) {
      availabilityState.value = 'available'
      currentStep.value = 2
      return
    }

    availabilityState.value = 'unavailable'
    toast.error(availabilityMessage.value || 'Selected time range is not available.')
  } catch (err) {
    availabilityState.value = 'idle'
    availabilityReasons.value = []
    accessNotice.value = []
    availabilityMessage.value = err.response?.data?.message || ''
    if (err.response?.status === 422) {
      const errors = err.response.data.errors || {}
      const messages = Object.values(errors).flat()
      if (messages.length) {
        messages.forEach((msg) => toast.error(msg))
      } else {
        toast.error(err.response.data.message || 'Availability check failed.')
      }
    } else {
      toast.error('Availability check failed.')
    }
  } finally {
    checkingAvailability.value = false
  }
}

const agreeSuggestionAndContinue = () => {
  if (!applySelectedSuggestion()) {
    toast.error('Please select a suggested slot to continue.')
    return
  }

  currentStep.value = 2
}

const goToStep3 = () => {
  attempted.value.step2 = true

  if (Object.keys(step2Errors.value).length) {
    toast.error('Please complete your details before continuing.')
    return
  }

  currentStep.value = 3
}

const goToStep4 = () => {
  attempted.value.step3 = true

  if (step3Error.value) {
    toast.error(step3Error.value)
    return
  }

  currentStep.value = 4
}

const submitReservation = async () => {
  if (!selectedPlan.value) {
    attempted.value.step3 = true
    touched.value.price_plan_id = true
    toast.error('Please select a valid pricing plan.')
    currentStep.value = 3
    return
  }

  try {
    submitting.value = true
    const res = await axios.post('/api/public/reservations/initiate-payment', {
      user_id: form.value.user_id,
      facility_id: Number(form.value.facility_id),
      price_plan_id: Number(form.value.price_plan_id),
      start_at: form.value.start_at,
      end_at: form.value.end_at,
      name: form.value.name,
      phone: form.value.phone,
      email: form.value.email || null,
      deposit_amount: Number(computedDepositAmount.value || 0),
      reservation_amount: Number(calculatedTotal.value || 0),
      extra_items: selectedExtraItems.value.map((item) => ({
        facility_extra_item_id: Number(item.facility_extra_item_id),
        units: Number(item.units),
      })),
    })

    const checkoutUrl = res.data?.payment?.checkout_url
    if (checkoutUrl) {
      window.location.href = checkoutUrl
      return
    }

    lastReservation.value = res.data.reservation || null
    currentStep.value = 5
    toast.success('Reservation request sent successfully.')
  } catch (err) {
    if (err.response?.status === 422) {
      const errors = err.response.data.errors || {}
      const messages = Object.values(errors).flat()
      if (messages.length) {
        messages.forEach((msg) => toast.error(msg))
      } else {
        toast.error(err.response.data.message || 'Failed to send reservation request.')
      }

      if (err.response.data.message?.toLowerCase().includes('not available')) {
        currentStep.value = 1
      }
    } else {
      toast.error('Failed to send reservation request.')
    }
  } finally {
    submitting.value = false
  }
}

const startNewRequest = () => {
  const keep = {
    user_id: form.value.user_id,
    name: form.value.name,
    phone: form.value.phone,
    email: form.value.email,
  }

  form.value = {
    ...keep,
    facility_id: boundFacilityId.value || null,
    price_plan_id: null,
    start_at: '',
    end_at: '',
  }
  selectedExtraItemUnits.value = {}

  touched.value = {
    facility_id: false,
    start_at: false,
    end_at: false,
    name: false,
    phone: false,
    email: false,
    price_plan_id: false,
  }

  attempted.value = {
    step1: false,
    step2: false,
    step3: false,
  }

  availabilityState.value = 'idle'
  availabilityMessage.value = ''
  availabilityReasons.value = []
  accessNotice.value = []
  availabilitySuggestions.value = []
  selectedSuggestion.value = null
  lastReservation.value = null
  currentStep.value = 1
}

watch(
  () => form.value.facility_id,
  () => {
    if (!facilityPlans.value.some((plan) => Number(plan.id) === Number(form.value.price_plan_id))) {
      form.value.price_plan_id = null
    }

    availabilityState.value = 'idle'
    availabilityMessage.value = ''
    availabilityReasons.value = []
    accessNotice.value = []
    availabilitySuggestions.value = []
    selectedSuggestion.value = null
    selectedExtraItemUnits.value = {}
  }
)

watch(
  () => [form.value.start_at, form.value.end_at],
  () => {
    availabilityState.value = 'idle'
    availabilityMessage.value = ''
    availabilityReasons.value = []
    accessNotice.value = []
    availabilitySuggestions.value = []
    selectedSuggestion.value = null
  }
)

watch(
  () => boundFacilityId.value,
  (newFacilityId) => {
    if (newFacilityId && isFacilityLocked.value) {
      form.value.facility_id = Number(newFacilityId)
    }
  },
  { immediate: true }
)

watch(
  () => isFacilityLocked.value,
  (locked) => {
    if (locked && boundFacilityId.value) {
      form.value.facility_id = Number(boundFacilityId.value)
    }
  },
  { immediate: true }
)

onMounted(fetchMeta)
</script>

<style scoped>
.reservation-datetime {
  color-scheme: light;
}

.reservation-datetime::-webkit-calendar-picker-indicator {
  filter: invert(0);
  opacity: 0.95;
  cursor: pointer;
}
</style>
