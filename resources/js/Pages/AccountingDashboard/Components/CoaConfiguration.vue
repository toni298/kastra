<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { ArrowLeft, Download, Plus } from 'lucide-vue-next'
import { useToastify } from '@/Composables/useToastify'
import Button from '@/Components/UI/Button.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'
import CoaDeleteDialog from './CoaDeleteDialog.vue'
import CoaDetailPanel from './CoaDetailPanel.vue'
import CoaFormModal from './CoaFormModal.vue'
import CoaTreeView from './CoaTreeView.vue'
import { accountGroups } from '../Data/chartOfAccountsData'

const emit = defineEmits(['back'])
const toast = useToastify()
const groups = ref(
  accountGroups.map((group) => ({
    ...group,
    accounts: group.accounts.map((account) => ({ ...account })),
  }))
)
const search = ref('')
const groupFilter = ref('all')
const selected = ref(null)
const editing = ref(null)
const deleting = ref(null)
const formOpen = ref(false)
const expanded = reactive(Object.fromEntries(groups.value.map((group) => [group.id, true])))
const allAccounts = computed(() => groups.value.flatMap((group) => group.accounts))
const summary = computed(() => [
  ['Total Akun', allAccounts.value.length],
  ['Akun Aktif', allAccounts.value.filter((item) => item.status === 'Aktif').length],
  ['Akun Nonaktif', allAccounts.value.filter((item) => item.status !== 'Aktif').length],
  ['Akun Custom', allAccounts.value.filter((item) => !item.system).length],
])
const filteredGroups = computed(() => {
  const keyword = search.value.trim().toLocaleLowerCase('id-ID')
  return groups.value
    .filter((group) => groupFilter.value === 'all' || group.id === groupFilter.value)
    .map((group) => ({
      ...group,
      accounts: group.accounts.filter((account) =>
        `${account.name} ${account.code} ${account.parent}`
          .toLocaleLowerCase('id-ID')
          .includes(keyword)
      ),
    }))
    .filter((group) => group.accounts.length || (!keyword && groupFilter.value !== 'all'))
})
const openCreate = () => {
  editing.value = null
  formOpen.value = true
}
const openEdit = (account) => {
  const group = groups.value.find((item) => item.accounts.some((entry) => entry.id === account.id))
  editing.value = { ...account, groupId: group.id }
  formOpen.value = true
}
const closeForm = () => {
  formOpen.value = false
  editing.value = null
}
const saveAccount = (data) => {
  const duplicate = allAccounts.value.some(
    (item) => item.code === data.code && item.id !== editing.value?.id
  )
  if (duplicate) {
    toast.error('Kode akun sudah digunakan.')
    return
  }
  if (editing.value) {
    const oldGroup = groups.value.find((group) =>
      group.accounts.some((item) => item.id === editing.value.id)
    )
    const updated = {
      ...editing.value,
      ...data,
      used: editing.value.used,
      system: editing.value.system,
    }
    oldGroup.accounts = oldGroup.accounts.filter((item) => item.id !== editing.value.id)
    groups.value.find((group) => group.id === data.groupId).accounts.push(updated)
    selected.value = updated
    toast.success('Akun berhasil diperbarui.')
  } else {
    const account = { ...data, id: `custom-${Date.now()}`, used: 'Belum digunakan', system: false }
    groups.value.find((group) => group.id === data.groupId).accounts.push(account)
    expanded[data.groupId] = true
    selected.value = account
    toast.success('Akun berhasil ditambahkan.')
  }
  closeForm()
}
const deleteAccount = () => {
  if (!deleting.value || deleting.value.system) return
  const group = groups.value.find((item) =>
    item.accounts.some((account) => account.id === deleting.value.id)
  )
  group.accounts = group.accounts.filter((account) => account.id !== deleting.value.id)
  if (selected.value?.id === deleting.value.id) selected.value = null
  deleting.value = null
  toast.success('Akun berhasil dihapus.')
}
const toggleGroup = (groupId) => {
  expanded[groupId] = !expanded[groupId]
}
watch(search, (value) => {
  if (value.trim()) groups.value.forEach((group) => (expanded[group.id] = true))
})
</script>

<template>
  <div>
    <button
      class="mb-4 flex items-center gap-2 text-sm font-medium text-emerald-600"
      @click="emit('back')"
    >
      <ArrowLeft :size="17" />Kembali ke Pengaturan
    </button>
    <div class="flex flex-col justify-between gap-4 sm:flex-row">
      <div>
        <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Chart of Accounts</h2>
        <p class="mt-1 text-sm text-slate-500">
          Pahami struktur akun perusahaan dan pemetaan pembukuan dalam satu tampilan.
        </p>
      </div>
      <Button @click="openCreate"><Plus :size="16" class="mr-2" />Tambah Akun</Button>
    </div>
    <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="item in summary"
        :key="item[0]"
        class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <p class="text-xs font-medium text-slate-400">{{ item[0] }}</p>
        <p class="mt-2 text-2xl font-semibold dark:text-white">{{ item[1] }}</p>
      </article>
    </section>
    <section
      class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-[#29476b] dark:bg-[#102542]"
    >
      <div
        class="flex flex-wrap items-center gap-3 border-b border-slate-200 bg-slate-50/50 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]/60"
      >
        <SearchInput
          v-model="search"
          class="min-w-[220px] flex-1"
          placeholder="Cari nama, kode, atau parent akun..."
          aria-label="Cari akun"
        /><select
          v-model="groupFilter"
          class="rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <option value="all">Semua Kelompok</option>
          <option v-for="group in groups" :key="group.id" :value="group.id">
            {{ group.name }}
          </option></select
        ><Button variant="secondary" size="sm"><Download :size="16" class="mr-2" />Export</Button>
      </div>
      <div class="grid lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
        <CoaTreeView
          :groups="filteredGroups"
          :expanded="expanded"
          :selected-id="selected?.id ?? ''"
          @toggle="toggleGroup"
          @select="selected = $event"
        /><CoaDetailPanel :account="selected" @edit="openEdit" @delete="deleting = $event" />
      </div>
    </section>
    <CoaFormModal
      v-if="formOpen"
      :account="editing"
      :groups="groups"
      @close="closeForm"
      @submit="saveAccount"
    />
    <CoaDeleteDialog
      v-if="deleting"
      :account="deleting"
      @close="deleting = null"
      @confirm="deleteAccount"
    />
  </div>
</template>
