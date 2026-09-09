<script setup>
import { useForm } from '@inertiajs/vue3'
import { ChevronDown, Search, X } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  role: { type: Object, default: null },
  permissions: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

const editing = Boolean(props.role)
const form = useForm({
  name: props.role?.name ?? '',
  permissions: props.role?.permissions?.map((permission) => permission.name) ?? [],
})

const searchQuery = ref('')

/**
 * Urutan aksi untuk sorting permission actions yang konsisten
 */
const ACTION_ORDER = [
  'view',
  'create',
  'edit',
  'delete',
  'deactivate',
  'adjust',
  'export',
  'images',
  'opname',
  'transfer',
  'assign_role',
  'assign_permission',
  'logo',
  'settings',
]

const actionLabel = (action) => {
  const labels = {
    view: 'Lihat',
    create: 'Tambah',
    edit: 'Ubah',
    delete: 'Hapus',
    deactivate: 'Nonaktifkan',
    adjust: 'Sesuaikan',
    export: 'Export',
    images: 'Gambar',
    opname: 'Opname',
    transfer: 'Transfer',
    assign_role: 'Assign Role',
    assign_permission: 'Assign Permission',
    logo: 'Logo',
    settings: 'Pengaturan',
  }
  return labels[action] ?? action
}

/**
 * Group & structure permissions dari backend data
 */
const menuStructure = computed(() => {
  const groups = {}

  for (const permission of props.permissions) {
    if (!permission.group) continue
    const key = permission.group
    if (!groups[key]) {
      groups[key] = { group: key, sort_order: permission.sort_order ?? 0, submenus: {} }
    }
    const subgroupKey = permission.subgroup ?? permission.group
    if (!groups[key].submenus[subgroupKey]) {
      groups[key].submenus[subgroupKey] = { subgroup: subgroupKey, permissions: [] }
    }
    groups[key].submenus[subgroupKey].permissions.push(permission)
  }

  return Object.values(groups)
    .sort((a, b) => a.sort_order - b.sort_order)
    .map((group) => ({
      ...group,
      submenus: Object.values(group.submenus).map((submenu) => ({
        ...submenu,
        permissions: submenu.permissions.slice().sort((a, b) => {
          const actionA = a.name.split('.').at(-1)
          const actionB = b.name.split('.').at(-1)
          return (
            (ACTION_ORDER.indexOf(actionA) === -1 ? 99 : ACTION_ORDER.indexOf(actionA)) -
            (ACTION_ORDER.indexOf(actionB) === -1 ? 99 : ACTION_ORDER.indexOf(actionB))
          )
        }),
      })),
    }))
})

/**
 * Filter permissions by search query
 */
const filteredMenuStructure = computed(() => {
  if (!searchQuery.value.trim()) return menuStructure.value

  const query = searchQuery.value.toLowerCase()
  return menuStructure.value
    .map((menu) => ({
      ...menu,
      submenus: menu.submenus
        .map((submenu) => ({
          ...submenu,
          permissions: submenu.permissions.filter(
            (p) => p.name.toLowerCase().includes(query) || p.group.toLowerCase().includes(query)
          ),
        }))
        .filter((submenu) => submenu.permissions.length > 0),
    }))
    .filter((menu) => menu.submenus.length > 0)
})

/**
 * Expanded state management
 */
const expandedMenus = ref(new Set(['Pembelian', 'Profil Bisnis', 'Role', 'Pengaturan']))

const toggleExpanded = (menuName) => {
  if (expandedMenus.value.has(menuName)) {
    expandedMenus.value.delete(menuName)
  } else {
    expandedMenus.value.add(menuName)
  }
}

/**
 * Permission selection helpers
 */
const submenuAllKeys = (submenu) => submenu.permissions.map((permission) => permission.name)

const togglePermission = (name) => {
  form.permissions = form.permissions.includes(name)
    ? form.permissions.filter((permissionName) => permissionName !== name)
    : [...form.permissions, name]
}

const submenuChecked = (submenu) => {
  const keys = submenuAllKeys(submenu)
  return keys.length > 0 && keys.every((key) => form.permissions.includes(key))
}

const submenuIndeterminate = (submenu) => {
  const keys = submenuAllKeys(submenu)
  return (
    keys.some((key) => form.permissions.includes(key)) &&
    !keys.every((key) => form.permissions.includes(key))
  )
}

const toggleSubmenu = (submenu) => {
  const keys = submenuAllKeys(submenu)
  form.permissions = submenuChecked(submenu)
    ? form.permissions.filter((name) => !keys.includes(name))
    : [...new Set([...form.permissions, ...keys])]
}

const menuAllKeys = (menu) => menu.submenus.flatMap((submenu) => submenuAllKeys(submenu))

const menuChecked = (menu) => {
  const keys = menuAllKeys(menu)
  return keys.length > 0 && keys.every((key) => form.permissions.includes(key))
}

const menuIndeterminate = (menu) => {
  const keys = menuAllKeys(menu)
  return (
    keys.some((key) => form.permissions.includes(key)) &&
    !keys.every((key) => form.permissions.includes(key))
  )
}

const toggleMenu = (menu) => {
  const keys = menuAllKeys(menu)
  form.permissions = menuChecked(menu)
    ? form.permissions.filter((name) => !keys.includes(name))
    : [...new Set([...form.permissions, ...keys])]
}

const countPermissionsInMenu = (menu) => {
  return menuAllKeys(menu).filter((key) => form.permissions.includes(key)).length
}

/**
 * Global select/deselect actions
 */
const selectAll = () => {
  const allPerms = filteredMenuStructure.value
    .flatMap((menu) => menu.submenus)
    .flatMap((submenu) => submenu.permissions)
    .map((perm) => perm.name)
  form.permissions = [...new Set([...form.permissions, ...allPerms])]
}

const deselectAll = () => {
  const filtered = filteredMenuStructure.value
    .flatMap((menu) => menu.submenus)
    .flatMap((submenu) => submenu.permissions)
    .map((perm) => perm.name)
  form.permissions = form.permissions.filter((p) => !filtered.includes(p))
}

/**
 * Set indeterminate state for checkboxes
 */
const setIndeterminate = (el, indeterminate) => {
  if (el) el.indeterminate = indeterminate
}

/**
 * Submit form
 */
const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') }
  editing
    ? form.put(route('roles.update', props.role.id), options)
    : form.post(route('roles.store'), options)
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="editing ? 'Edit Role' : 'Tambah Role Baru'"
    description="Atur nama dan permission untuk role perusahaan Anda."
    size="4xl"
    @update:model-value="emit('close')"
  >
    <form id="role-form" class="space-y-5" @submit.prevent="submit">
      <!-- Role Name Input -->
      <div>
        <Input
          v-model="form.name"
          label="Nama Role"
          placeholder="Contoh: supervisor, cashier, warehouse_staff"
          required
          :error="form.errors.name"
          hint="Gunakan huruf kecil, angka, tanda minus, atau underscore"
        />
      </div>

      <!-- Permissions Section -->
      <div>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Permission & Akses</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Pilih permission yang akan diberikan pada role ini
            </p>
          </div>
          <div class="text-sm font-medium text-slate-600 dark:text-slate-300">
            <span class="text-emerald-600">{{ form.permissions.length }}</span>
            <span class="text-slate-400">/{{ props.permissions.length }} dipilih</span>
          </div>
        </div>

        <!-- Search Bar -->
        <div
          class="mb-4 flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 dark:border-[#29476b] dark:bg-[#102542]"
        >
          <Search :size="16" class="text-slate-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari permission..."
            class="w-full border-0 bg-transparent py-2.5 text-sm focus:ring-0 dark:text-white"
          />
          <button
            v-if="searchQuery"
            type="button"
            @click="searchQuery = ''"
            class="rounded p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#0d2039]"
          >
            <X :size="16" />
          </button>
        </div>

        <!-- Quick Actions -->
        <div class="mb-3 flex gap-2">
          <button
            type="button"
            @click="selectAll"
            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-[#29476b] dark:bg-[#102542] dark:text-slate-300 dark:hover:bg-[#0d2039]"
          >
            Pilih Semua
          </button>
          <button
            type="button"
            @click="deselectAll"
            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-[#29476b] dark:bg-[#102542] dark:text-slate-300 dark:hover:bg-[#0d2039]"
          >
            Batal Semua
          </button>
        </div>

        <!-- Permission Groups -->
        <div
          class="max-h-[35rem] space-y-2 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-[#29476b] dark:bg-[#0d2039]"
        >
          <section
            v-for="menu in filteredMenuStructure"
            :key="menu.group"
            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-[#1d3859] dark:bg-[#102542]"
          >
            <!-- Menu Header -->
            <button
              type="button"
              class="flex w-full items-center gap-3 bg-slate-50 px-4 py-3 text-left dark:bg-[#0d2039]"
              @click="toggleExpanded(menu.group)"
            >
              <input
                :ref="(el) => setIndeterminate(el, menuIndeterminate(menu))"
                type="checkbox"
                :checked="menuChecked(menu)"
                class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                @change.stop="toggleMenu(menu)"
              />
              <div class="flex-1">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ menu.group }}</p>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                  {{ countPermissionsInMenu(menu) }}/{{ menuAllKeys(menu).length }}
                </span>
                <ChevronDown
                  :size="16"
                  :class="['transition', { 'rotate-180': expandedMenus.has(menu.group) }]"
                />
              </div>
            </button>

            <!-- Menu Items (Submenus) -->
            <div
              v-if="expandedMenus.has(menu.group)"
              class="divide-y divide-slate-100 dark:divide-[#1d3859]"
            >
              <div
                v-for="submenu in menu.submenus"
                :key="submenu.subgroup"
                class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-start sm:gap-4"
              >
                <!-- Submenu Checkbox -->
                <label
                  class="flex items-center gap-2 whitespace-nowrap pt-1 text-sm font-medium text-slate-700 dark:text-slate-200 sm:w-44 sm:shrink-0"
                >
                  <input
                    :ref="(el) => setIndeterminate(el, submenuIndeterminate(submenu))"
                    type="checkbox"
                    :checked="submenuChecked(submenu)"
                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                    @change="toggleSubmenu(submenu)"
                  />
                  <span>{{ submenu.subgroup }}</span>
                  <span class="text-xs font-normal text-slate-400">
                    ({{
                      submenu.permissions.filter((p) => form.permissions.includes(p.name)).length
                    }}/{{ submenu.permissions.length }})
                  </span>
                </label>

                <!-- Permission Checkboxes -->
                <div class="flex flex-wrap items-center gap-3 gap-y-2 sm:flex-1">
                  <label
                    v-for="permission in submenu.permissions"
                    :key="permission.id"
                    class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-emerald-50 dark:text-slate-300 dark:hover:bg-emerald-400/10"
                  >
                    <input
                      type="checkbox"
                      :checked="form.permissions.includes(permission.name)"
                      class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                      @change="togglePermission(permission.name)"
                    />
                    <span>{{ actionLabel(permission.name.split('.').at(-1)) }}</span>
                  </label>
                </div>
              </div>
            </div>
          </section>

          <!-- No Results Message -->
          <div
            v-if="filteredMenuStructure.length === 0"
            class="flex items-center justify-center rounded-lg bg-white py-8 text-center dark:bg-[#102542]"
          >
            <div>
              <p class="text-sm font-medium text-slate-600 dark:text-slate-300">
                Tidak ada permission yang sesuai
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400">Coba ubah pencarian Anda</p>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <p v-if="form.errors.permissions" class="mt-2 text-sm font-medium text-red-500">
          {{ form.errors.permissions }}
        </p>
      </div>
    </form>

    <!-- Modal Footer -->
    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button type="submit" form="role-form" :loading="form.processing">
        {{ editing ? 'Simpan Perubahan' : 'Buat Role' }}
      </Button>
    </template>
  </Modal>
</template>
