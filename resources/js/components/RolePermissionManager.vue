<template>
  <div class="flex flex-col lg:flex-row w-full gap-3 p-3">
    <!-- Left Column: Roles -->
    <div class="w-full lg:w-4/12 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
      <h2 class="text-lg font-bold mb-4 dark:text-white flex items-center gap-2">
        <i class="fas fa-user-shield text-cyan-500"></i>
        Roles
      </h2>

      <!-- Floating Label Input + Add -->
      <div class="flex items-center gap-2 mb-6">
         <div class="relative w-full">
          <input
            type="text"
            id="role_name"
            v-model="roleForm.name"
            class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-cyan-500 focus:outline-none focus:ring-0 focus:border-cyan-600 peer"
            placeholder=" "
          />
          <label
            for="role_name"
            class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-gray-800 px-2
              peer-focus:px-2 peer-focus:text-cyan-600 peer-focus:dark:text-cyan-500
              peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2
              peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4"
          >
            Create Role
          </label>
        </div>
        <button
          @click="saveRole"
          class="bg-gradient-to-tr uppercase text-sm font-bold from-cyan-600 to-cyan-400 text-white px-4 py-3 rounded-lg hover:opacity-90 transition whitespace-nowrap"
        >
          + Create
        </button>
      </div>

      <!-- Role Radio List -->
      <ul class="grid w-full gap-4">
        <li v-for="role in roles" :key="role.id">
          <input
            type="radio"
            :id="'role-' + role.id"
            name="role"
            :value="role.id"
            v-model="selectedRoleId"
            class="hidden peer"
            @change="selectRole(role)"
          />
          <label
            :for="'role-' + role.id"
            class="inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer
                   dark:hover:text-gray-300 dark:border-gray-700 dark:peer-checked:text-cyan-500
                   peer-checked:border-cyan-600 dark:peer-checked:border-cyan-600 peer-checked:text-cyan-600
                   hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700 transition"
          >
            <div>
              <div class="text-lg font-semibold capitalize">{{ role.name }}</div>
              <div class="text-sm">System Role</div>
            </div>
            <svg class="w-5 h-5 ms-3 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
            </svg>
          </label>
        </li>
      </ul>

      <!-- Delete Role -->
      <div v-if="selectedRole" class="mt-5">
        <button
          @click="confirmDelete(selectedRole)"
          class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition w-full"
        >
          Delete "{{ selectedRole.name }}"
        </button>
      </div>
    </div>

    <!-- Right Column: Permissions -->
    <div v-if="selectedRole" class="w-full lg:w-8/12 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
      <h2 class="text-lg font-bold mb-6 dark:text-white flex items-center gap-2">
        <i class="fas fa-lock text-cyan-500"></i>
        Permissions for "{{ selectedRole.name }}"
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <div
          v-for="perm in permissions"
          :key="perm.id"
          class="flex items-center justify-between p-4 border border-gray-200 rounded-lg dark:border-gray-700 dark:bg-gray-800 bg-white hover:bg-gray-50 dark:hover:bg-gray-700 transition"
        >
          <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
            {{ perm.name }}
          </span>

          <!-- Custom cyan Toggle -->
          <label class="inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              class="sr-only peer"
              :value="perm.name"
              v-model="permissionState[perm.name]"
              @change="togglePermission(perm)"
            />
            <div
              class="relative w-11 h-6 rounded-full bg-gray-200 dark:bg-gray-700 transition-all duration-300
                     after:content-[''] after:absolute after:top-0.5 after:start-[2px]
                     after:w-5 after:h-5 after:bg-white after:rounded-full after:shadow-md after:transition-all
                     peer-focus:ring-4 peer-focus:ring-cyan-300 dark:peer-focus:ring-cyan-800
                     peer-checked:bg-cyan-600 dark:peer-checked:bg-cyan-600
                     peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full"
            ></div>
          </label>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="w-full lg:w-8/12 flex items-center justify-center text-gray-500 dark:text-gray-300">
      <p class="text-lg italic">Select a role to view permissions</p>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl w-80 text-center">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Confirm Delete</h3>
        <p class="text-gray-500 dark:text-gray-300 mb-4">
          Are you sure you want to delete <b>{{ roleToDelete?.name }}</b>?
        </p>
        <div class="flex justify-center gap-3">
          <button @click="deleteRole" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:opacity-90">Delete</button>
          <button
            @click="showDeleteModal = false"
            class="border border-gray-300 px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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

const loadRoles = async () => {
  const res = await axios.get('/api/roles')
  roles.value = res.data
}

const loadPermissions = async () => {
  const res = await axios.get('/api/permissions')
  permissions.value = res.data
}

// Create Role
const saveRole = async () => {
  if (!roleForm.value.name) return toast.error('Role name required')
  try {
    await axios.post('/api/roles', roleForm.value)
    toast.success('Role created successfully')
    roleForm.value.name = ''
    await loadRoles()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Error creating role')
  }
}

// Select Role + load permissions state
const selectRole = async (role) => {
  const res = await axios.get(`/api/roles/${role.id}`)
  selectedRole.value = res.data.role
  const assigned = res.data.permissions
  permissionState.value = {}
  permissions.value.forEach((p) => {
    permissionState.value[p.name] = assigned.includes(p.name)
  })
}

// Toggle Permission and Auto Sync
const togglePermission = async (perm) => {
  if (!selectedRole.value) return
  const permName = perm.name
  const active = permissionState.value[permName]

  const assigned = Object.keys(permissionState.value).filter((p) => permissionState.value[p])

  try {
    await axios.post(`/api/roles/${selectedRole.value.id}/sync`, { permissions: assigned })
    toast.success(`${permName} ${active ? 'granted' : 'revoked'}`)
  } catch {
    toast.error('Error syncing permission')
  }
}

// Delete Role
const confirmDelete = (role) => {
  roleToDelete.value = role
  showDeleteModal.value = true
}
const deleteRole = async () => {
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

<style scoped>
input[type='radio']:checked + label {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
}
</style>
