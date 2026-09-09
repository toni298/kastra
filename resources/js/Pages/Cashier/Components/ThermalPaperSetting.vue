<script setup>
import { ref, onMounted } from 'vue'
import { getThermalPaperSize, setThermalPaperSize } from '@/Utils/thermalReceiptExport'

const paperSize = ref('58mm')

onMounted(() => {
  paperSize.value = getThermalPaperSize()
})

function onChange(size) {
  paperSize.value = size
  setThermalPaperSize(size)
}
</script>

<template>
  <div class="flex items-center gap-2">
    <label class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
      Kertas Struk:
    </label>
    <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
      <button
        v-for="size in ['58mm', '80mm']"
        :key="size"
        type="button"
        class="px-2.5 py-1 text-xs font-medium transition-colors"
        :class="[
          paperSize === size
            ? 'bg-indigo-600 text-white'
            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700',
        ]"
        @click="onChange(size)"
      >
        {{ size }}
      </button>
    </div>
  </div>
</template>
