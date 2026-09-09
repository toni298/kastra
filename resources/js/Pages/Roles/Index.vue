<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { AlertCircle, FilePenLine, Plus, Search, ShieldCheck, Trash2 } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'
import Button from '@/Components/UI/Button.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import DataTablePagination from '@/Components/UI/DataTablePagination.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import RoleDeleteModal from './RoleDeleteModal.vue'
import RoleFormModal from './RoleFormModal.vue'

const props = defineProps({
  roles: { type: Object, default: () => ({ data: [], meta: {} }) },
  permissions: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const selected = ref(null)
const modal = ref(null)
const search = ref(props.filters.search ?? '')

const can = (permission) => page.props.auth.permissions?.includes(permission)
const roleRows = computed(() => props.roles?.data ?? [])
const roleMeta = computed(() => props.roles?.meta ?? {})

const open = (kind, role = null) => {
  selected.value = role
  modal.value = kind
}

const applySearch = useDebounceFn(
  () =>
    router.get(
      route('roles.index'),
      { search: search.value || undefined },
      { preserveState: true, preserveScroll: true, replace: true }
    ),
  400
)

watch(search, applySearch)

/**
 * Statistics
 */
const totalRoles = computed(() => roleMeta.value.total ?? roleRows.value.length)
const totalUsers = computed(
  () =>
    props.stats.users_count ??
    roleRows.value.reduce((sum, role) => sum + (role.users_count ?? 0), 0)
)
const totalPermissions = computed(() => props.permissions.length)

/**
 * Permission preview untuk card
 */
const getPermissionPreview = (permissions, max = 3) => {
  if (!permissions || permissions.length === 0) return []
  return permissions.slice(0, max).map((p) => p.name.split('.')[1])
}
const navigateRoles = (url) => {
  if (url) router.get(url, {}, { preserveState: true, preserveScroll: true, replace: true })
}
</script>

<template>
  <Head title="Role & Akses" />
  <AuthenticatedLayout>
    <template #header>Role & Akses</template>

    <div class="space-y-5">
      <!-- Header Section -->
      <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <div class="flex items-center gap-3">
            <span
              class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
            >
              <ShieldCheck :size="22" />
            </span>
            <div>
              <h2 class="text-2xl font-semibold text-slate-950 dark:text-white">Role & Akses</h2>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
                Kelola role dan permission untuk perusahaan Anda
              </p>
            </div>
          </div>
        </div>
        <Button v-if="can('roles.create')" @click="open('form')">
          <Plus :size="17" class="mr-2" />Tambah Role
        </Button>
      </div>

      <!-- Statistics Cards -->
      <div class="grid gap-4 sm:grid-cols-3">
        <div
          class="rounded-lg border border-slate-200 bg-white p-4 dark:border-[#29476b] dark:bg-[#102542]"
        >
          <div
            class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
          >
            Total Role
          </div>
          <div class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
            {{ totalRoles }}
          </div>
        </div>
        <div
          class="rounded-lg border border-slate-200 bg-white p-4 dark:border-[#29476b] dark:bg-[#102542]"
        >
          <div
            class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
          >
            Total Pengguna
          </div>
          <div class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
            {{ totalUsers }}
          </div>
        </div>
        <div
          class="rounded-lg border border-slate-200 bg-white p-4 dark:border-[#29476b] dark:bg-[#102542]"
        >
          <div
            class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
          >
            Total Permission
          </div>
          <div class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
            {{ totalPermissions }}
          </div>
        </div>
      </div>

      <!-- Information Banner -->
      <div
        class="flex gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-400/20 dark:bg-blue-400/10"
      >
        <span
          class="grid size-9 shrink-0 place-items-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-400/20 dark:text-blue-300"
        >
          <AlertCircle :size="18" />
        </span>
        <div class="text-sm text-blue-900 dark:text-blue-200">
          <strong>Tips:</strong> Atur permission dengan cermat. Role tidak dapat dihapus jika sedang
          digunakan pengguna. Lebih baik buat role baru daripada mengubah role yang sudah banyak
          digunakan.
        </div>
      </div>

      <!-- Search Bar -->
      <div
        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <Search :size="16" class="text-slate-400" />
        <input
          v-model="search"
          type="text"
          class="w-full border-0 bg-transparent py-2.5 text-sm focus:ring-0 dark:text-white"
          placeholder="Cari role..."
        />
      </div>

      <!-- Role Grid -->
      <div v-if="roleRows.length" class="grid gap-4 lg:grid-cols-3">
        <article
          v-for="role in roleRows"
          :key="role.id"
          class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
        >
          <!-- Role Header -->
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="text-lg font-semibold capitalize text-slate-950 dark:text-white">
                {{ role.name }}
              </h3>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ role.users_count }}
                <span>{{ role.users_count === 1 ? 'pengguna' : 'pengguna' }}</span>
              </p>
            </div>
          </div>

          <!-- Permission Stats -->
          <div class="mt-4 space-y-3 rounded-lg bg-slate-50 p-3 dark:bg-[#0d2039]">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                Permission Aktif
              </span>
              <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                {{ role.permissions?.length ?? 0 }}
              </span>
            </div>

            <!-- Permission Preview -->
            <div
              v-if="role.permissions && role.permissions.length > 0"
              class="flex flex-wrap gap-1.5"
            >
              <span
                v-for="(perm, i) in getPermissionPreview(role.permissions, 3)"
                :key="i"
                class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
              >
                {{ perm }}
              </span>
              <span
                v-if="(role.permissions?.length ?? 0) > 3"
                class="inline-flex items-center rounded-full bg-slate-200 px-2 py-1 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300"
              >
                +{{ role.permissions.length - 3 }} lainnya
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-5 flex items-center gap-2 pt-4">
            <Button
              v-if="can('roles.edit')"
              variant="secondary"
              size="sm"
              class="flex-1"
              @click="open('form', role)"
            >
              <FilePenLine :size="16" class="mr-2" />Edit
            </Button>
            <IconButton
              v-if="can('roles.delete')"
              label="Hapus role"
              variant="danger"
              @click="open('delete', role)"
            >
              <Trash2 :size="17" />
            </IconButton>
          </div>
        </article>
      </div>
      <DataTablePagination
        v-if="roleRows.length"
        :current-page="roleMeta.current_page"
        :last-page="roleMeta.last_page"
        :from="roleMeta.from"
        :to="roleMeta.to"
        :total="roleMeta.total"
        :links="props.roles?.links ?? []"
        :first-page-url="props.roles?.first_page_url"
        :last-page-url="props.roles?.last_page_url"
        :previous-url="props.roles?.prev_page_url"
        :next-url="props.roles?.next_page_url"
        @navigate="navigateRoles"
      />

      <!-- Empty State -->
      <section
        v-else
        class="rounded-2xl border border-slate-200 bg-white dark:border-[#29476b] dark:bg-[#102542]"
      >
        <EmptyState
          icon="shield-check"
          title="Belum ada role"
          message="Buat role pertama untuk mengelola akses pengguna Anda."
        />
      </section>
    </div>

    <!-- Modals -->
    <RoleFormModal
      v-if="modal === 'form'"
      :role="selected"
      :permissions="permissions"
      @close="modal = null"
    />
    <RoleDeleteModal v-if="modal === 'delete'" :role="selected" @close="modal = null" />
  </AuthenticatedLayout>
</template>
