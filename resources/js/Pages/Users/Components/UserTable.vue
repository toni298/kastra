<script setup>
import { FilePenLine, ShieldCheck, UserRound, UserX } from '@lucide/vue'
import Badge from '@/Components/UI/Badge.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import DataTableSortHeader from '@/Components/UI/DataTableSortHeader.vue'
import IconButton from '@/Components/UI/IconButton.vue'

const props = defineProps({
  users: { type: Object, required: true },
  search: { type: String, default: '' },
  sortKey: { type: String, default: 'id' },
  sortDirection: { type: String, default: 'desc' },
  loading: { type: Boolean, default: false },
  currentUserId: { type: String, required: true },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
})

const emit = defineEmits(['navigate', 'filter', 'per-page-change', 'sort', 'edit', 'delete'])

const navigate = (url) => {
  if (url) emit('navigate', url)
}

const initials = (name) =>
  name
    .split(' ')
    .map((part) => part[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()

const canDeleteUser = (user) =>
  props.canDelete &&
  user.id !== props.currentUserId &&
  !user.roles?.some((role) => role.name === 'owner')
</script>

<template>
  <DataTable
    :items="props.users.data ?? []"
    :pagination="users"
    :per-page="users.per_page"
    :loading="loading"
    searchable
    :search="search"
    :sort-key="sortKey"
    :sort-direction="sortDirection"
    search-placeholder="Cari nama atau email..."
    empty-icon="users"
    empty-title="Tidak ada data"
    empty-message="Belum ada pengguna yang sesuai pencarian."
    @navigate="emit('navigate', $event)"
    @filter="emit('filter', $event)"
    @per-page-change="emit('per-page-change', $event)"
    @sort="emit('sort', $event)"
  >
    <template #filters><slot name="filters"></slot></template>
    <template #thead="{ sort, sortKey: activeSort, sortDirection: direction }">
      <tr class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">
        <th>
          <DataTableSortHeader
            label="Pengguna"
            :active="activeSort === 'name'"
            :direction="direction"
            @sort="sort({ key: 'name', sortable: true })"
          >
            <template #icon><UserRound :size="14" /></template>
          </DataTableSortHeader>
        </th>
        <th>
          <span class="flex items-center gap-2"><ShieldCheck :size="14" />Role</span>
        </th>
        <th class="text-right">Aksi</th>
      </tr>
    </template>
    <tr v-for="user in users.data ?? []" :key="user.id">
      <td class="px-5 py-4">
        <div class="flex items-center gap-3">
          <span
            class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
            >{{ initials(user.name) }}</span
          >
          <div class="min-w-0">
            <p class="truncate font-medium text-slate-900 dark:text-white">{{ user.name }}</p>
            <p class="truncate text-sm text-slate-500 dark:text-slate-300">{{ user.email }}</p>
          </div>
        </div>
      </td>
      <td class="px-5 py-4">
        <Badge variant="success">{{ user.roles?.[0]?.name ?? 'Belum ada' }}</Badge>
      </td>
      <td class="px-5 py-4">
        <div class="flex justify-end gap-1">
          <IconButton
            v-if="canEdit"
            label="Edit pengguna"
            variant="info"
            @click="emit('edit', user)"
            ><FilePenLine :size="17"
          /></IconButton>
          <IconButton
            v-if="canDeleteUser(user)"
            label="Hapus pengguna"
            variant="danger"
            @click="emit('delete', user)"
            ><UserX :size="17"
          /></IconButton>
        </div>
      </td>
    </tr>
  </DataTable>
</template>
