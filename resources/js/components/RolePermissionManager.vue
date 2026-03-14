<template>
  <section class="p-3 sm:p-4">
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200/70 bg-gradient-to-br from-sky-50 to-white p-4 xl:flex-row dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
      <aside class="w-full rounded-2xl border border-slate-200 bg-white/90 p-4 xl:w-[340px] dark:border-slate-700 dark:bg-slate-950/80">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">Access Control</p>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Roles</h2>
          </div>
          <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-full bg-gradient-to-r from-sky-500 to-blue-600 px-2 text-sm font-bold text-white">{{ roles.length }}</span>
        </div>

        <div class="mt-4 flex gap-2">
          <input
            id="role_name"
            v-model="roleForm.name"
            type="text"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
            placeholder="Create new role"
            @keyup.enter="saveRole"
          />
          <button @click="saveRole" class="rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110">Add</button>
        </div>

        <div class="mt-4 grid max-h-[56vh] gap-2 overflow-y-auto pr-1">
          <button
            v-for="role in roles"
            :key="role.id"
            type="button"
            class="flex w-full items-center justify-between rounded-xl border px-3 py-3 text-left transition"
            :class="selectedRoleId === role.id
              ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-200 dark:border-sky-500/60 dark:bg-sky-500/10 dark:ring-sky-500/20'
              : 'border-slate-200 bg-white hover:border-sky-400 dark:border-slate-700 dark:bg-slate-900/70 dark:hover:border-sky-500/50'"
            @click="selectRole(role)"
          >
            <div>
              <p class="font-semibold text-slate-800 dark:text-slate-100">{{ toTitle(role.name) }}</p>
              <p class="text-xs text-slate-500">System role</p>
            </div>
            <i class="fa-solid fa-arrow-right text-xs text-sky-600" aria-hidden="true"></i>
          </button>
          <p v-if="!roles.length" class="text-sm text-slate-500">No roles found yet.</p>
        </div>

        <button
          v-if="selectedRole"
          @click="confirmDelete(selectedRole)"
          class="mt-4 w-full rounded-xl bg-gradient-to-r from-red-600 to-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110"
        >
          Delete "{{ selectedRole.name }}"
        </button>
      </aside>

      <main class="flex-1 rounded-2xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/80">
        <div v-if="selectedRole">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">Permission Matrix</p>
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Permissions for {{ toTitle(selectedRole.name) }}</h2>
            </div>
            <span class="inline-flex items-center rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-1 text-sm font-semibold text-white">{{ grantedCount }} Active</span>
          </div>

          <div class="mt-4">
            <input
              v-model="permissionQuery"
              type="text"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
              placeholder="Search permissions"
            />
          </div>

          <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <label
              v-for="perm in filteredPermissions"
              :key="perm.id"
              class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-amber-400 dark:border-slate-700 dark:bg-slate-900/70 dark:hover:border-amber-500/50"
            >
              <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ toTitle(perm.name.replaceAll('-', ' ')) }}</p>
                <p class="text-xs text-slate-500">{{ perm.name }}</p>
              </div>

              <input
                type="checkbox"
                class="peer sr-only"
                :value="perm.name"
                v-model="permissionState[perm.name]"
                @change="togglePermission(perm)"
              />
              <span class="relative h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-gradient-to-r peer-checked:from-amber-500 peer-checked:to-orange-500 dark:bg-slate-700">
                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
              </span>
            </label>
          </div>

          <p v-if="!filteredPermissions.length" class="mt-5 text-sm text-slate-500">
            No permissions match "{{ permissionQuery }}".
          </p>
        </div>

        <div v-else class="grid min-h-[45vh] place-content-center text-center">
          <i class="fa-solid fa-shield-halved text-2xl text-sky-500" aria-hidden="true"></i>
          <p class="mt-3 text-base font-semibold text-slate-900 dark:text-white">Select a role to manage permissions</p>
          <p class="mt-1 text-sm text-slate-500">Choose any role from the left panel to start updating access.</p>
        </div>
      </main>
    </div>

    <transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-5 text-center dark:border-slate-700 dark:bg-slate-900">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Delete Role</h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Are you sure you want to delete <b>{{ roleToDelete?.name }}</b>?
          </p>
          <div class="mt-5 flex justify-center gap-2">
            <button @click="deleteRole" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Delete</button>
            <button
              @click="showDeleteModal = false"
              class="rounded-lg border border-slate-300 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </transition>
  </section>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const roles = ref([])
const permissions = ref([])
const roleForm = ref({ name: '' })
const selectedRole = ref(null)
const selectedRoleId = ref(null)
const permissionState = ref({})
const showDeleteModal = ref(false)
const roleToDelete = ref(null)
const permissionQuery = ref('')

const filteredPermissions = computed(() => {
  const q = permissionQuery.value.trim().toLowerCase()
  if (!q) return permissions.value
  return permissions.value.filter((p) => p.name.toLowerCase().includes(q))
})

const grantedCount = computed(() => {
  return Object.values(permissionState.value).filter(Boolean).length
})

const loadRoles = async () => {
  try {
    const res = await axios.get('/api/roles')
    roles.value = res.data
  } catch {
    toast.error('Unable to load roles')
  }
}

const loadPermissions = async () => {
  try {
    const res = await axios.get('/api/permissions')
    permissions.value = res.data
  } catch {
    toast.error('Unable to load permissions')
  }
}

const toTitle = (value) => {
  return String(value || '')
    .split(' ')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

const saveRole = async () => {
  const name = roleForm.value.name.trim()
  if (!name) return toast.error('Role name required')
  try {
    await axios.post('/api/roles', { name })
    toast.success('Role created successfully')
    roleForm.value.name = ''
    await loadRoles()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Error creating role')
  }
}

const selectRole = async (role) => {
  try {
    const res = await axios.get(`/api/roles/${role.id}`)
    selectedRole.value = res.data.role
    selectedRoleId.value = role.id
    const assigned = res.data.permissions
    permissionState.value = {}
    permissions.value.forEach((p) => {
      permissionState.value[p.name] = assigned.includes(p.name)
    })
  } catch {
    toast.error('Unable to load role details')
  }
}

const togglePermission = async (perm) => {
  if (!selectedRole.value) return
  const permName = perm.name
  const previous = !permissionState.value[permName]
  const active = permissionState.value[permName]

  const assigned = Object.keys(permissionState.value).filter((p) => permissionState.value[p])

  try {
    await axios.post(`/api/roles/${selectedRole.value.id}/sync`, { permissions: assigned })
    toast.success(`${permName} ${active ? 'granted' : 'revoked'}`)
  } catch {
    permissionState.value[permName] = previous
    toast.error('Error syncing permission')
  }
}

const confirmDelete = (role) => {
  roleToDelete.value = role
  showDeleteModal.value = true
}

const deleteRole = async () => {
  if (!roleToDelete.value) return
  try {
    await axios.delete(`/api/roles/${roleToDelete.value.id}`)
    toast.success('Role deleted successfully')
    showDeleteModal.value = false
    if (selectedRole.value?.id === roleToDelete.value.id) {
      selectedRole.value = null
      selectedRoleId.value = null
    }
    await loadRoles()
  } catch {
    toast.error('Error deleting role')
  }
}

onMounted(() => {
  loadRoles()
  loadPermissions()
})
</script>
