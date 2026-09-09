<script setup>
import { computed } from 'vue'
import { Lightbulb } from 'lucide-vue-next'

const props = defineProps({
  step: { type: Number, required: true },
  hasBranches: { type: Boolean, default: null },
  hasWarehouses: { type: Boolean, default: null },
  taxEnabled: { type: Boolean, default: null },
})

const tips = [
  'Pilihan jenis usaha dapat diubah kembali melalui menu Pengaturan.',
  'Gunakan identitas resmi agar dokumen bisnis tetap konsisten.',
  'Gunakan kode cabang singkat dan unik, misalnya CBG-JKT.',
  'Pisahkan gudang bila stok tersimpan di lokasi fisik berbeda.',
  'Pengaturan pajak masih dapat diperbarui setelah setup selesai.',
  'Data baru dibuat setelah Anda menekan tombol Selesaikan.',
]

const activeTip = computed(() => {
  if (props.step === 3) {
    if (props.hasBranches === true)
      return 'Lengkapi nama, kode unik, dan alamat untuk setiap cabang bisnis Anda.'
    if (props.hasBranches === false)
      return 'Anda dapat melanjutkan tanpa cabang. Data cabang masih bisa ditambahkan nanti melalui menu Pengaturan.'
    return 'Pilih apakah bisnis Anda memiliki cabang untuk menentukan data yang perlu dilengkapi.'
  }

  if (props.step === 4) {
    if (props.hasWarehouses === true)
      return 'Lengkapi nama, kode unik, dan alamat untuk setiap gudang penyimpanan Anda.'
    if (props.hasWarehouses === false)
      return 'Anda dapat melanjutkan tanpa gudang. Data gudang masih bisa ditambahkan nanti melalui menu Pengaturan.'
    return 'Pilih apakah bisnis Anda memiliki gudang untuk menentukan data yang perlu dilengkapi.'
  }

  if (props.step === 5) {
    if (props.taxEnabled === true)
      return 'Lengkapi tarif PPN dan metode pencatatan pajak yang digunakan bisnis Anda.'
    if (props.taxEnabled === false)
      return 'Anda dapat melanjutkan tanpa pajak. Pengaturan pajak masih bisa diaktifkan nanti.'
    return 'Pilih apakah bisnis Anda menggunakan pajak untuk menentukan pengaturan yang perlu dilengkapi.'
  }

  return tips[props.step - 1]
})
</script>
<template>
  <div
    class="mx-auto mt-7 flex max-w-4xl items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3.5 text-sm leading-6 text-emerald-800"
  >
    <span
      class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-white text-emerald-600 shadow-sm"
      ><Lightbulb class="h-4 w-4"
    /></span>
    <p><span class="font-medium">Tips setup:</span> {{ activeTip }}</p>
  </div>
</template>
