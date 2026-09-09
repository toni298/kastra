<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { UserPlus, Users } from '@lucide/vue'
import { ref, watch } from 'vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import UserDeleteModal from './UserDeleteModal.vue'
import UserFormModal from './UserFormModal.vue'
import UserTable from './Components/UserTable.vue'

const props = defineProps({
  users: { type: Object, required: true },
  roles: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const search = ref(props.filters.search ?? '')
const role = ref(props.filters.role ?? '')
const sortKey = ref(props.filters.sort ?? 'id')
const sortDirection = ref(props.filters.sort_direction ?? 'desc')
const selectedUser = ref(null)
const modal = ref(null)
const isNavigating = ref(false)
const can = (permission) => page.props.auth.permissions?.includes(permission)

const open = (kind, user = null) => {
  selectedUser.value = user
  modal.value = kind
}

const applyFilters = (perPage = props.users.per_page) => {
  isNavigating.value = true
  router.get(
    route('users.index'),
    {
      search: search.value || undefined,
      role: role.value || undefined,
      per_page: perPage,
      sort: sortKey.value,
      sort_direction: sortDirection.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        isNavigating.value = false
      },
    }
  )
}

const handleSearch = ({ search: value }) => {
  search.value = value
  applyFilters()
}
const handlePerPage = (value) => {
  applyFilters(value)
}

watch(role, () => applyFilters())
const handleSort = ({ key, direction }) => {
  sortKey.value = key
  sortDirection.value = direction
  applyFilters()
}

const navigate = ({ cursor, direction }) => {
  if (!cursor) return

  const baseUrl = route('users.index')
  const url = new URL(baseUrl, window.location.origin)
  url.searchParams.set('cursor', cursor)

  isNavigating.value = true
  router.visit(url.toString(), {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      isNavigating.value = false
    },
  })
}
</script>

<template>
  <Head title="Pengguna" />
  <AuthenticatedLayout>
    <template #header>Pengguna</template>
    <div class="space-y-5">
      <PageHeader title="Manajemen Pengguna" description="Kelola akun, role, dan akses tim.">
        <template #icon><Users :size="22" /></template>
        <template #actions>
          <Button v-if="can('users.create')" size="sm" @click="open('form')">
            <UserPlus :size="17" class="mr-2" />Tambah Pengguna
          </Button>
        </template>
      </PageHeader>

      <DataPanel>
        <UserTable
          :users="users"
          :loading="isNavigating"
          :search="search"
          :sort-key="sortKey"
          :sort-direction="sortDirection"
          :current-user-id="page.props.auth.user.id"
          :can-edit="can('users.edit')"
          :can-delete="can('users.delete')"
          @navigate="navigate"
          @filter="handleSearch"
          @per-page-change="handlePerPage"
          @sort="handleSort"
          @edit="open('form', $event)"
          @delete="open('delete', $event)"
        >
          <template #filters>
            <SelectInput v-model="role" aria-label="Filter berdasarkan role">
              <option value="">Semua role</option>
              <option v-for="item in roles" :key="item.id" :value="item.name">
                {{ item.name }}
              </option>
            </SelectInput>
          </template>
        </UserTable>
      </DataPanel>
    </div>

    <UserDeleteModal v-if="modal === 'delete'" :user="selectedUser" @close="modal = null" />
    <UserFormModal
      v-if="modal === 'form'"
      :user="selectedUser"
      :roles="roles"
      @close="modal = null"
    />
  </AuthenticatedLayout>
</template>
