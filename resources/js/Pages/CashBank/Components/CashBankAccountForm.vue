<script setup>
import { computed, reactive, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Landmark } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'

const props = defineProps({
  item: { type: Object, default: null },
  branches: { type: Array, default: () => [] },
  settings: { type: Array, default: () => [] },
})
const emit = defineEmits(['close', 'saved'])
const submitted = ref(false)
const confirmReplacement = ref(false)
const hasMultipleBranches = computed(() => props.branches.length > 1)
const existingSetting = computed(
  () => props.settings.find((setting) => setting.cash_bank_account_id === props.item?.id) ?? null
)
const form = reactive({
  name: props.item?.name ?? '',
  accountType: props.item?.account_type === 'e_wallet' ? 'E-Wallet' : 'Bank',
  bank: props.item?.bank ?? '',
  number: props.item?.number === '-' ? '' : (props.item?.number ?? ''),
  holder: props.item?.holder ?? '',
  openingBalance: props.item?.opening_balance ?? 0,
  canReceiveMoney: existingSetting.value?.can_receive_money ?? true,
  canSendMoney: existingSetting.value?.can_send_money ?? false,
  isAllBranches: existingSetting.value?.is_all_branches ?? true,
  branchId: existingSetting.value?.branch_id ?? '',
})
const valid = computed(
  () =>
    form.name &&
    (form.canReceiveMoney || form.canSendMoney) &&
    (form.isAllBranches || form.branchId)
)
const currentScopeSettings = computed(() =>
  props.settings.filter(
    (setting) =>
      (form.isAllBranches ? !setting.branch_id : setting.branch_id === form.branchId) &&
      setting.cash_bank_account_id !== props.item?.id
  )
)
const replaces = computed(() => ({
  receive:
    form.canReceiveMoney &&
    currentScopeSettings.value.some((setting) => setting.is_default_receive),
  payment:
    form.canSendMoney && currentScopeSettings.value.some((setting) => setting.is_default_payment),
}))
const replacementLabel = computed(() =>
  [replaces.value.receive ? 'Kas Masuk' : null, replaces.value.payment ? 'Kas Keluar' : null]
    .filter(Boolean)
    .join('/')
)
const save = (replaceDefault = false) => {
  const type = { Bank: 'bank', 'E-Wallet': 'e_wallet' }[form.accountType]
  const payload = {
    name: form.name,
    type,
    bank_name: form.bank || null,
    account_number: form.number || null,
    account_holder: form.holder || null,
    currency: 'IDR',
    opening_balance: Number(form.openingBalance || 0),
    is_active: true,
    can_receive_money: form.canReceiveMoney,
    can_send_money: form.canSendMoney,
    is_all_branches: hasMultipleBranches.value ? form.isAllBranches : true,
    branch_id: hasMultipleBranches.value && !form.isAllBranches ? form.branchId : null,
    replace_default: replaceDefault,
  }
  useForm(payload).submit(
    props.item ? 'put' : 'post',
    props.item
      ? route('cash-bank.accounts.update', props.item.id)
      : route('cash-bank.accounts.store'),
    { preserveScroll: true, onSuccess: () => emit('saved') }
  )
}
const submit = () => {
  submitted.value = true
  if (!valid.value) return
  if (replaces.value.receive || replaces.value.payment) {
    confirmReplacement.value = true
    return
  }
  save()
}
const keepExistingDefault = () => {
  confirmReplacement.value = false
  save()
}
const replaceExistingDefault = () => {
  confirmReplacement.value = false
  save(true)
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="item ? 'Edit Rekening' : 'Tambah Rekening'"
    :description="
      item ? 'Perbarui data dan penggunaan rekening.' : 'Tambahkan kas, bank, atau dompet digital.'
    "
    size="xl"
    @update:model-value="emit('close')"
  >
    <div
      class="mb-5 flex gap-3 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300"
    >
      <Landmark :size="19" />Data akan disimpan ke database perusahaan.
    </div>
    <form id="account-form" class="space-y-6" @submit.prevent="submit">
      <section>
        <div class="mb-4">
          <h3 class="font-semibold text-slate-950 dark:text-white">Informasi Rekening</h3>
          <p class="mt-1 text-xs text-slate-500">Data utama rekening perusahaan.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <Input
            v-model="form.name"
            label="Nama Rekening"
            placeholder="Contoh: Kas Operasional"
            required
          /><label
            ><span class="text-sm font-medium text-slate-700 dark:text-slate-200"
              >Jenis Rekening *</span
            ><select
              v-model="form.accountType"
              class="mt-1.5 w-full rounded-xl border-slate-300 px-3.5 py-3 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
            >
              <option>Bank</option>
              <option>E-Wallet</option>
            </select></label
          ><Input
            v-if="form.accountType === 'Bank'"
            v-model="form.bank"
            label="Nama Bank"
            placeholder="Contoh: Bank BCA"
          /><Input
            v-model="form.number"
            label="Nomor Rekening"
            placeholder="Contoh: 1234567890"
          /><Input
            v-model="form.holder"
            label="Atas Nama"
            placeholder="Contoh: Ahmadi"
          /><CurrencyInput v-model="form.openingBalance" currency="IDR" label="Saldo Awal" />
        </div>
      </section>
      <section class="border-t border-slate-200 pt-6 dark:border-[#29476b]">
        <div class="mb-4">
          <h3 class="font-semibold text-slate-950 dark:text-white">Penggunaan Rekening</h3>
          <p class="mt-1 text-xs text-slate-500">Pilih minimal satu fungsi rekening.</p>
        </div>
        <div class="flex flex-wrap gap-4">
          <label class="inline-flex items-center gap-2 text-sm font-medium dark:text-slate-200"
            ><input
              v-model="form.canReceiveMoney"
              type="checkbox"
              class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
            />Kas Masuk</label
          ><label class="inline-flex items-center gap-2 text-sm font-medium dark:text-slate-200"
            ><input
              v-model="form.canSendMoney"
              type="checkbox"
              class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
            />Kas Keluar</label
          >
        </div>
      </section>
      <section
        v-if="hasMultipleBranches"
        class="border-t border-slate-200 pt-6 dark:border-[#29476b]"
      >
        <div class="mb-4">
          <h3 class="font-semibold text-slate-950 dark:text-white">Digunakan Untuk</h3>
          <p class="mt-1 text-xs text-slate-500">Pilih cakupan cabang untuk rekening ini.</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
          <label
            class="flex cursor-pointer items-center gap-3 rounded-xl border p-4"
            :class="
              form.isAllBranches
                ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-400/10'
                : 'border-slate-200 dark:border-[#29476b]'
            "
            ><input
              v-model="form.isAllBranches"
              type="radio"
              :value="true"
              @change="form.branchId = ''"
            />Semua Cabang</label
          ><label
            class="flex cursor-pointer items-center gap-3 rounded-xl border p-4"
            :class="
              !form.isAllBranches
                ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-400/10'
                : 'border-slate-200 dark:border-[#29476b]'
            "
            ><input v-model="form.isAllBranches" type="radio" :value="false" />Cabang
            Tertentu</label
          >
        </div>
        <AsyncSelect
          v-if="!form.isAllBranches"
          v-model="form.branchId"
          class="mt-4"
          label="Cabang"
          :endpoint="route('search.cabang')"
          :initial-options="branches.map((branch) => ({ id: branch.id, text: branch.name }))"
          placeholder="Cari dan pilih cabang..."
          required
        />
      </section>
      <p
        v-if="submitted && !valid"
        class="rounded-xl bg-red-50 p-3 text-sm font-medium text-red-600 dark:bg-red-400/10 dark:text-red-300"
      >
        Lengkapi semua field wajib dan pilih minimal satu penggunaan rekening.
      </p>
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="account-form">Simpan</Button></template
    >
  </Modal>
  <Modal
    v-if="confirmReplacement"
    :model-value="true"
    title="Ganti Rekening Default?"
    description="Sudah terdapat rekening default untuk fungsi ini pada cakupan yang dipilih."
    size="md"
    :close-on-overlay="false"
    @update:model-value="confirmReplacement = false"
    ><p class="text-sm text-slate-600 dark:text-slate-300">
      Sudah terdapat rekening default untuk {{ replacementLabel }} pada cakupan ini. Apakah Anda
      ingin menggantinya dengan rekening ini?
    </p>
    <template #footer
      ><Button variant="secondary" @click="keepExistingDefault">Tidak, tetap simpan</Button
      ><Button @click="replaceExistingDefault">Ya, ganti default</Button></template
    ></Modal
  >
</template>
