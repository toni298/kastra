<script setup>
import axios from 'axios'
import { computed, ref } from 'vue'
import { FileSpreadsheet, RefreshCcw, UploadCloud } from '@lucide/vue'
import Button from '@/Components/UI/Button.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import Modal from '@/Components/UI/Modal.vue'
import { useToastify } from '@/Composables/useToastify'

const emit = defineEmits(['close', 'imported'])

const toast = useToastify()
const step = ref(1)
const file = ref(null)
const preview = ref(null)
const importProgress = ref(0)
const importing = ref(false)
const localRoute = (name) => route(name, {}, false)

const totalRows = computed(() => preview.value?.total ?? 0)
const validRows = computed(() => preview.value?.valid_rows ?? [])
const invalidRows = computed(() => preview.value?.invalid_rows ?? [])
const canImport = computed(() => validRows.value.length > 0)
const summaryLabel = computed(() => {
  if (!preview.value) return 'Belum ada data'

  return `${validRows.value.length} valid • ${invalidRows.value.length} invalid dari ${totalRows.value} baris`
})

const resetToUpload = () => {
  step.value = 1
  preview.value = null
  importProgress.value = 0
  importing.value = false
}

const startPreview = async () => {
  if (!file.value) {
    toast.error('Pilih file Excel/CSV terlebih dahulu.')
    return
  }

  const formData = new FormData()
  formData.append('file', file.value)

  try {
    const response = await axios.post(localRoute('products.import.preview'), formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    preview.value = response.data
    step.value = 2
    importProgress.value = 0
  } catch (error) {
    const message = error?.response?.data?.message || 'File tidak dapat diproses.'
    toast.error(message)
  }
}

const startImport = async () => {
  if (!preview.value || !canImport.value) {
    toast.warning('Tidak ada baris yang valid untuk diimport.')
    return
  }

  const rows = validRows.value.map((item) => item.data)
  const chunkSize = 50
  importProgress.value = 0
  importing.value = true

  try {
    for (let offset = 0; offset < rows.length; offset += chunkSize) {
      const chunk = rows.slice(offset, offset + chunkSize)
      await axios.post(localRoute('products.import.commit'), { rows: chunk })
      importProgress.value = Math.min(100, ((offset + chunk.length) / rows.length) * 100)
    }

    toast.success(`Import selesai: ${rows.length} produk berhasil ditambahkan.`)
    emit('imported')
    emit('close')
  } catch (error) {
    const message = error?.response?.data?.message || 'Import produk gagal diproses.'
    toast.error(message)
  } finally {
    importing.value = false
  }
}

const downloadTemplate = () => {
  window.location.assign(localRoute('products.import.template'))
}
</script>

<template>
  <Modal
    :model-value="true"
    title="Import Produk"
    description="Impor produk dari file Excel atau CSV."
    size="screen"
    @update:model-value="emit('close')"
  >
    <div class="space-y-6">
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-200">
          <span
            :class="[
              'grid h-8 w-8 place-items-center rounded-full text-xs font-semibold',
              step === 1 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700',
            ]"
            >1</span
          >
          <span>Upload</span>
        </div>
        <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
        <div class="flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-200">
          <span
            :class="[
              'grid h-8 w-8 place-items-center rounded-full text-xs font-semibold',
              step === 2 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700',
            ]"
            >2</span
          >
          <span>Preview</span>
        </div>
        <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
        <div class="flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-200">
          <span
            :class="[
              'grid h-8 w-8 place-items-center rounded-full text-xs font-semibold',
              step === 3 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700',
            ]"
            >3</span
          >
          <span>Import</span>
        </div>
      </div>

      <div v-if="step === 1" class="space-y-5">
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 dark:border-slate-700 dark:bg-[#0a1b33]">
          <div class="mb-4 flex items-center justify-between gap-3">
            <div>
              <h3 class="text-base font-semibold text-slate-900 dark:text-white">Pilih file data produk</h3>
              <p class="mt-1 text-sm text-slate-500">Format yang didukung: .xlsx, .xls, .csv</p>
            </div>
            <Button variant="secondary" size="sm" @click="downloadTemplate">
              <FileSpreadsheet :size="16" class="mr-2" />Download Template
            </Button>
          </div>
          <FileUpload
            v-model="file"
            accept=".xlsx,.xls,.csv"
            :max-size="20"
            label="Pilih file Excel / CSV"
            hint="Ukuran maksimal 20 MB."
          />
        </div>
      </div>

      <div v-else-if="step === 2" class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Preview hasil import</h3>
            <p class="text-sm text-slate-500">{{ summaryLabel }}</p>
          </div>
          <div class="flex gap-2">
            <Button variant="secondary" size="sm" @click="resetToUpload">
              <RefreshCcw :size="16" class="mr-2" />Ulang upload
            </Button>
            <Button size="sm" :disabled="!canImport" @click="step = 3">
              <UploadCloud :size="16" class="mr-2" />Lanjut import
            </Button>
          </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-[#29476b]">
          <div class="max-h-[24rem] overflow-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left dark:divide-slate-700">
              <thead class="bg-slate-50 dark:bg-[#0a1b33]">
                <tr class="text-xs uppercase tracking-wide text-slate-600 dark:text-slate-300">
                  <th class="px-3 py-3 font-semibold">Baris</th>
                  <th class="px-3 py-3 font-semibold">Nama</th>
                  <th class="px-3 py-3 font-semibold">SKU</th>
                  <th class="px-3 py-3 font-semibold">Satuan</th>
                  <th class="px-3 py-3 font-semibold">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-[#0b1730]">
                <tr v-if="!validRows.length && !invalidRows.length">
                  <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">Tidak ada data</td>
                </tr>
                <template v-for="row in validRows" :key="`valid-${row.row_number}`">
                  <tr class="bg-emerald-50/40 dark:bg-emerald-950/10">
                    <td class="px-3 py-2 text-sm text-slate-700 dark:text-slate-200">{{ row.row_number }}</td>
                    <td class="px-3 py-2 text-sm text-slate-700 dark:text-slate-200">{{ row.data.name }}</td>
                    <td class="px-3 py-2 text-sm text-slate-700 dark:text-slate-200">{{ row.data.sku }}</td>
                    <td class="px-3 py-2 text-sm text-slate-700 dark:text-slate-200">{{ row.data.unit || 'PCS' }}</td>
                    <td class="px-3 py-2 text-sm font-medium text-emerald-600">Valid</td>
                  </tr>
                </template>
                <template v-for="row in invalidRows" :key="`invalid-${row.row_number}`">
                  <tr class="bg-red-50/40 dark:bg-red-950/10">
                    <td class="px-3 py-2 text-sm text-slate-700 dark:text-slate-200">{{ row.row_number }}</td>
                    <td class="px-3 py-2 text-sm text-slate-700 dark:text-slate-200">{{ row.row.name || '-' }}</td>
                    <td class="px-3 py-2 text-sm text-slate-700 dark:text-slate-200">{{ row.row.sku || '-' }}</td>
                    <td class="px-3 py-2 text-sm text-slate-700 dark:text-slate-200">{{ row.row.unit || '-' }}</td>
                    <td class="px-3 py-2 text-sm font-medium text-red-600">Invalid</td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="invalidRows.length" class="rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-500/60 dark:bg-red-950/20">
          <h4 class="mb-2 text-sm font-semibold text-red-700 dark:text-red-200">Catatan error</h4>
          <ul class="space-y-2 text-sm text-red-700 dark:text-red-200">
            <li v-for="row in invalidRows.slice(0, 5)" :key="`error-${row.row_number}`">
              Baris {{ row.row_number }}: {{ row.errors.join(', ') }}
            </li>
          </ul>
        </div>
      </div>

      <div v-else class="space-y-5">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-[#0a1b33]">
          <div class="mb-3 flex items-center justify-between text-sm text-slate-600 dark:text-slate-300">
            <span>Progress import</span>
            <span>{{ Math.round(importProgress) }}%</span>
          </div>
          <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
            <div
              class="h-full rounded-full bg-emerald-600 transition-all duration-300"
              :style="{ width: `${importProgress}%` }"
            ></div>
          </div>
          <p class="mt-3 text-sm text-slate-500">
            Memproses {{ validRows.length }} produk yang valid untuk ditambahkan.
          </p>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex w-full justify-end gap-2">
        <Button variant="secondary" @click="emit('close')">Batal</Button>
        <Button v-if="step === 1" :disabled="!file" :loading="false" @click="startPreview">
          <UploadCloud :size="16" class="mr-2" />Preview Data
        </Button>
        <Button v-else-if="step === 2" :disabled="!canImport" @click="step = 3">
          <UploadCloud :size="16" class="mr-2" />Lanjutkan
        </Button>
        <Button v-else :loading="importing" @click="startImport">
          <UploadCloud :size="16" class="mr-2" />Mulai Import
        </Button>
      </div>
    </template>
  </Modal>
</template>
