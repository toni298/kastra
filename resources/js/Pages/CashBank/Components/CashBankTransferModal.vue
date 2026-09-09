<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({ modelValue: { type: Boolean, default: false }, accounts: { type: Array, default: () => [] } })
const emit = defineEmits(['update:modelValue', 'saved'])
const form = useForm({ source_account_id: null, destination_account_id: null, amount: '', transfer_date: new Date().toISOString().slice(0, 10), reference: '', note: '' })
const accountOptions = computed(() => props.accounts.map((account) => ({ id: account.id, text: [account.name, account.bank].filter(Boolean).join(' - ') })))
const sourceAccount = computed(() => props.accounts.find((account) => account.id === form.source_account_id))
const sourceBalance = computed(() => sourceAccount.value?.balance_value ?? 0)
const sourceBalanceHint = computed(() => form.source_account_id ? `Saldo tersedia: Rp ${Number(sourceBalance.value).toLocaleString('id-ID')}` : 'Pilih rekening asal untuk melihat saldo tersedia.')
const destinationOptions = computed(() => accountOptions.value.filter((account) => account.id !== form.source_account_id))
const destinationEndpoint = computed(() => `${route('cash-bank.accounts.search')}?exclude_id=${encodeURIComponent(form.source_account_id ?? '')}`)
watch(() => form.source_account_id, (accountId) => { if (form.destination_account_id === accountId) form.destination_account_id = null; if (form.amount > sourceBalance.value) form.amount = sourceBalance.value })
const close = () => { emit('update:modelValue', false); form.reset(); form.clearErrors() }
const submit = () => form.post(route('cash-bank.transfers.store'), { preserveScroll: true, onSuccess: () => { close(); emit('saved') } })
</script>
<template><Modal :model-value="modelValue" title="Transfer Dana" description="Pindahkan dana antar rekening perusahaan." size="xl" @update:model-value="close"><form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit"><AsyncSelect v-model="form.source_account_id" label="Rekening Asal" :initial-options="accountOptions" :endpoint="route('cash-bank.accounts.search')" :error="form.errors.source_account_id" required /><AsyncSelect v-model="form.destination_account_id" label="Rekening Tujuan" :initial-options="destinationOptions" :endpoint="destinationEndpoint" :error="form.errors.destination_account_id" required /><CurrencyInput v-model="form.amount" label="Nominal" :min="1" :max="sourceBalance" :hint="sourceBalanceHint" :error="form.errors.amount" required /><DatePicker v-model="form.transfer_date" label="Tanggal Transfer" :error="form.errors.transfer_date" /><label class="sm:col-span-2 text-sm font-medium dark:text-slate-200">Referensi<input v-model="form.reference" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]" /><p v-if="form.errors.reference" class="mt-1 text-xs text-red-600">{{ form.errors.reference }}</p></label><label class="sm:col-span-2 text-sm font-medium dark:text-slate-200">Catatan<textarea v-model="form.note" rows="3" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"></textarea></label><div class="sm:col-span-2 flex justify-end gap-2 border-t pt-4"><Button type="button" variant="secondary" size="sm" @click="close">Batal</Button><Button type="submit" size="sm" :loading="form.processing">Simpan Transfer</Button></div></form></Modal></template>
