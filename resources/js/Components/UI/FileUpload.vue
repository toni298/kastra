<script setup>
import { computed, onBeforeUnmount, onMounted, ref, useId, watch } from 'vue'
import { FileText, UploadCloud, X } from '@lucide/vue'

const props = defineProps({
  id: { type: String, default: '' },
  modelValue: { type: [Object, Array], default: null },
  accept: { type: String, default: '.jpg,.jpeg,.png,.webp' },
  label: { type: String, default: 'Pilih file' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  initialPreview: { type: String, default: '' },
  initialPreviews: { type: Array, default: () => [] },
  maxSize: { type: Number, default: 2 },
  maxFiles: { type: Number, default: 10 },
  multiple: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  showPreview: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue', 'select', 'error', 'remove-initial-preview'])

const generatedId = useId()
const input = ref(null)
const dropzone = ref(null)
const dragging = ref(false)
const dragCounter = ref(0)
const validationError = ref('')
const filePreviews = ref([])
const mounted = ref(false)

const inputId = computed(() => props.id || `file-upload-${generatedId}`)
const files = computed(() => {
  if (Array.isArray(props.modelValue)) return props.modelValue
  return props.modelValue ? [props.modelValue] : []
})
const hasSelectedFiles = computed(() => files.value.length > 0)
const displayError = computed(() => props.error || validationError.value)
const errorId = computed(() => `${inputId.value}-error`)
const hintId = computed(() => `${inputId.value}-hint`)
const describedBy = computed(() => (displayError.value ? errorId.value : hintId.value))
const defaultHint = computed(() => {
  const limit = `Maks. ${props.maxSize} MB${props.multiple ? ' per file' : ''}.`
  return props.hint || `Tarik file ke sini atau klik untuk memilih. ${limit}`
})

const savedPreviews = computed(() => {
  if (hasSelectedFiles.value) return []

  if (props.initialPreviews.length) {
    return props.initialPreviews.map((preview, index) => ({
      id: preview.id ?? index,
      name: preview.name ?? 'File tersimpan',
      url: preview.url ?? preview.preview_url ?? '',
      source: preview,
    }))
  }

  return props.initialPreview
    ? [{ id: 'initial-preview', name: 'File tersimpan', url: props.initialPreview, source: null }]
    : []
})

const previewItems = computed(() => {
  if (hasSelectedFiles.value) {
    return files.value.map((file, index) => ({
      id: `${file.name}-${file.lastModified}-${index}`,
      name: file.name,
      url: filePreviews.value[index]?.url ?? '',
      file,
      fileIndex: index,
      type: 'file',
    }))
  }

  return savedPreviews.value.map((preview) => ({ ...preview, type: 'saved' }))
})

const hasIntegratedPreview = computed(() => props.showPreview && previewItems.value.length > 0)
const canSelect = computed(
  () => !props.disabled && (!props.multiple || files.value.length < props.maxFiles)
)

const clearObjectUrls = () => {
  filePreviews.value.forEach((preview) => {
    if (preview.url) window.URL.revokeObjectURL(preview.url)
  })
  filePreviews.value = []
}

const syncPreviews = () => {
  if (!mounted.value) return

  clearObjectUrls()
  filePreviews.value = files.value.map((file) => {
    if (!file?.type?.startsWith('image/')) return { file, url: '' }

    return { file, url: window.URL.createObjectURL(file) }
  })
}

const acceptedFile = (file) => {
  const rules = props.accept
    .split(',')
    .map((rule) => rule.trim().toLowerCase())
    .filter(Boolean)

  if (!rules.length) return true

  const extension = `.${file.name.split('.').pop()?.toLowerCase()}`
  const mime = file.type.toLowerCase()

  return rules.some((rule) => {
    if (rule.startsWith('.')) return rule === extension
    if (rule.endsWith('/*')) return mime.startsWith(rule.slice(0, -1))
    return rule === mime
  })
}

const validate = (selectedFiles) => {
  if (props.multiple && selectedFiles.length > props.maxFiles) {
    return `Maksimal ${props.maxFiles} file dapat dipilih.`
  }

  if (selectedFiles.some((file) => !acceptedFile(file))) {
    return 'Tipe file tidak didukung.'
  }

  if (selectedFiles.some((file) => file.size > props.maxSize * 1024 * 1024)) {
    return `Ukuran setiap file maksimal ${props.maxSize} MB.`
  }

  return ''
}

const updateFiles = (selectedFiles) => {
  let nextFiles

  if (props.multiple) {
    const existing = files.value
    const remainingSlots = Math.max(0, props.maxFiles - existing.length)
    nextFiles = [...existing, ...selectedFiles.slice(0, remainingSlots)]
    if (selectedFiles.length > remainingSlots) {
      validationError.value = `Hanya ${remainingSlots} file tambahan yang dapat dipilih.`
      emit('error', validationError.value)
    } else {
      validationError.value = ''
    }
  } else {
    nextFiles = selectedFiles.slice(0, 1)
    validationError.value = ''
  }

  const error = validate(nextFiles)
  if (error) {
    validationError.value = error
    if (input.value) input.value.value = ''
    emit('error', error)
    emit('select', null)
    return
  }

  const value = props.multiple ? nextFiles : (nextFiles[0] ?? null)
  emit('update:modelValue', value)
  emit('select', value)
  if (nextFiles.length > 0) emit('remove-initial-preview', null)
}

const selectFile = (event) => {
  updateFiles(Array.from(event.target.files ?? []))
}

const openFilePicker = () => {
  if (!canSelect.value) return
  input.value?.click()
}

const onDragEnter = (event) => {
  if (!canSelect.value) return
  event.preventDefault()
  dragCounter.value += 1
  dragging.value = true
}

const onDragOver = (event) => {
  if (!canSelect.value) return
  event.preventDefault()
  event.dataTransfer.dropEffect = 'copy'
}

const onDragLeave = (event) => {
  event.preventDefault()
  dragCounter.value -= 1
  if (dragCounter.value <= 0) {
    dragCounter.value = 0
    dragging.value = false
  }
}

const onDrop = (event) => {
  dragCounter.value = 0
  dragging.value = false
  if (!canSelect.value) return
  event.preventDefault()
  updateFiles(Array.from(event.dataTransfer?.files ?? []))
}

const onKeydown = (event) => {
  if (!canSelect.value) return
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    openFilePicker()
  }
}

const removeFile = (index) => {
  if (props.disabled) return

  const nextFiles = files.value.filter((_, fileIndex) => fileIndex !== index)
  const value = props.multiple ? nextFiles : null
  validationError.value = ''
  if (input.value) input.value.value = ''
  emit('update:modelValue', value)
  emit('select', value)
}

const removePreview = (item) => {
  if (props.disabled) return
  if (item.type === 'file') {
    removeFile(item.fileIndex)
    return
  }

  emit('remove-initial-preview', item.source)
}

const formatSize = (bytes) => {
  if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}

watch(files, syncPreviews, { immediate: true })

onMounted(() => {
  mounted.value = true
  syncPreviews()
})

onBeforeUnmount(() => {
  clearObjectUrls()
  dragCounter.value = 0
})
</script>

<template>
  <div class="w-full">
    <input
      :id="inputId"
      ref="input"
      type="file"
      class="sr-only"
      :accept="accept"
      :multiple="multiple"
      :required="required && files.length === 0"
      :disabled="!canSelect"
      :aria-invalid="Boolean(displayError)"
      :aria-describedby="describedBy"
      @change="selectFile"
    />

    <div
      ref="dropzone"
      role="button"
      tabindex="0"
      class="relative flex h-48 w-full flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed text-center outline-none transition focus-visible:ring-4 focus-visible:ring-emerald-500/10"
      :class="[
        displayError
          ? 'border-red-400 bg-red-50/40 dark:bg-red-400/5'
          : dragging
            ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-400/10'
            : 'border-slate-300 hover:border-emerald-400 hover:bg-emerald-50/50 dark:border-[#29476b] dark:hover:border-emerald-400 dark:hover:bg-emerald-400/5',
        canSelect ? 'cursor-pointer' : 'cursor-default',
      ]"
      :aria-label="label"
      @click="openFilePicker"
      @keydown="onKeydown"
      @dragenter="onDragEnter"
      @dragover="onDragOver"
      @dragleave="onDragLeave"
      @drop="onDrop"
    >
      <template v-if="hasIntegratedPreview">
        <div
          :class="[
            'h-full w-full overflow-y-auto p-3',
            previewItems.length > 1 ? 'grid grid-cols-2 content-start gap-2 sm:grid-cols-3' : '',
          ]"
        >
          <div
            v-for="item in previewItems"
            :key="`${item.type}-${item.id}`"
            :class="[
              'group relative overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-[#29476b] dark:bg-[#0a1b33]',
              previewItems.length === 1 ? 'h-full' : 'aspect-square',
            ]"
          >
            <img
              v-if="item.url"
              :src="item.url"
              :alt="item.name"
              class="h-full w-full object-contain"
            />
            <div
              v-else
              class="flex h-full min-h-24 flex-col items-center justify-center gap-2 px-3 text-slate-500 dark:text-slate-300"
            >
              <FileText :size="24" />
              <span class="max-w-full truncate text-xs font-medium">{{ item.name }}</span>
              <span v-if="item.file" class="text-[11px]">{{ formatSize(item.file.size) }}</span>
            </div>
            <button
              type="button"
              class="absolute right-1.5 top-1.5 grid size-7 place-items-center rounded-full bg-slate-950/70 text-white shadow-sm transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-white disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="disabled"
              :aria-label="`Hapus ${item.name}`"
              @click.prevent.stop="removePreview(item)"
            >
              <X :size="14" />
            </button>
            <div
              v-if="item.url"
              class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/75 to-transparent px-2 pb-2 pt-5 text-left text-white"
            >
              <span class="block truncate text-[11px] font-medium">{{ item.name }}</span>
            </div>
          </div>
        </div>
      </template>

      <template v-else>
        <UploadCloud :size="26" class="text-emerald-600 dark:text-emerald-400" />
        <span class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-200">
          {{ label }}
          <span v-if="required" class="text-red-500">*</span>
        </span>
        <span :id="hintId" class="mt-1 px-5 text-xs text-slate-500 dark:text-slate-300">
          {{ defaultHint }}
        </span>
      </template>
    </div>

    <p v-if="displayError" :id="errorId" class="mt-2 text-sm font-medium text-red-500">
      {{ displayError }}
    </p>
  </div>
</template>
