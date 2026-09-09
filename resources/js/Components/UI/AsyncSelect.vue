<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useId, watch } from 'vue'
import { Check, ChevronDown, LoaderCircle, Plus, Search, X } from '@lucide/vue'
import { useAsyncSelect } from '@/Composables/useAsyncSelect'

const props = defineProps({
  id: { type: String, default: '' },
  modelValue: { type: [String, Number], default: null },
  endpoint: { type: String, required: true },
  initialOptions: { type: Array, default: () => [] },
  additionalOptions: { type: Array, default: () => [] },
  clearItems: { type: Boolean, default: false },
  addable: { type: Boolean, default: false },
  addLabel: { type: String, default: 'Tambah data baru' },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  placeholder: { type: String, default: 'Cari dan pilih data...' },
  name: { type: String, default: '' },
  searchParam: { type: String, default: 'search' },
  cursorParam: { type: String, default: 'cursor' },
  debounce: { type: Number, default: 400 },
  minimumInputLength: { type: Number, default: 0 },
  clearable: { type: Boolean, default: true },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change', 'error', 'add'])

const root = ref(null)
const control = ref(null)
const input = ref(null)
const dropdown = ref(null)
const activeIndex = ref(-1)
const dropdownStyle = ref({})
const generatedId = useId()
const inputId = computed(() => props.id || `async-select-${generatedId}`)
const listboxId = computed(() => `${inputId.value}-listbox`)
const errorId = computed(() => `${inputId.value}-error`)
const hintId = computed(() => `${inputId.value}-hint`)
const describedBy = computed(() =>
  props.error ? errorId.value : props.hint ? hintId.value : undefined
)

const {
  options,
  searchTerm,
  open,
  loading,
  loadingMore,
  hasMore,
  requestFailed,
  show,
  hide,
  selectOption,
  clear,
  loadMore,
  requestOptions,
} = useAsyncSelect(props, emit)

const addItems = (items) => {
  const values = Array.isArray(items) ? items : [items]
  options.value = [
    ...new Map(
      [...options.value, ...values]
        .filter((item) => item && item.id !== undefined && typeof item.text === 'string')
        .map((item) => [String(item.id), item])
    ).values(),
  ]
}
const clearAllItems = () => {
  options.value = []
  clear()
}
const activeOptionId = computed(() =>
  activeIndex.value >= 0 ? `${inputId.value}-option-${activeIndex.value}` : undefined
)

const positionDropdown = () => {
  if (!control.value || typeof window === 'undefined') return
  const rect = control.value.getBoundingClientRect()
  const availableBelow = window.innerHeight - rect.bottom
  const openAbove = availableBelow < 280 && rect.top > availableBelow
  const maxHeight = Math.max(160, Math.min(320, openAbove ? rect.top - 12 : availableBelow - 12))

  dropdownStyle.value = {
    position: 'fixed',
    left: `${rect.left}px`,
    top: openAbove ? 'auto' : `${rect.bottom + 6}px`,
    bottom: openAbove ? `${window.innerHeight - rect.top + 6}px` : 'auto',
    width: `${rect.width}px`,
    maxHeight: `${maxHeight}px`,
    zIndex: 100,
  }
}

const openDropdown = async () => {
  show()
  await nextTick()
  positionDropdown()
  input.value?.focus({ preventScroll: true })
}

defineExpose({
  addItems,
  clearItems: clearAllItems,
  requestOptions: () => requestOptions(),
  open: openDropdown,
})

const closeDropdown = () => {
  activeIndex.value = -1
  hide()
}

const moveActive = (step) => {
  if (!open.value) {
    openDropdown()
    return
  }
  if (!options.value.length) return
  activeIndex.value = (activeIndex.value + step + options.value.length) % options.value.length
  nextTick(() =>
    document.getElementById(activeOptionId.value)?.scrollIntoView({ block: 'nearest' })
  )
}

const handleKeydown = (event) => {
  if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
    event.preventDefault()
    moveActive(event.key === 'ArrowDown' ? 1 : -1)
    return
  }
  if (event.key === 'Enter' && open.value && activeIndex.value >= 0) {
    event.preventDefault()
    selectOption(options.value[activeIndex.value])
    return
  }
  if (event.key === 'Escape' && open.value) {
    event.preventDefault()
    event.stopPropagation()
    closeDropdown()
  }
  if (event.key === 'Tab') closeDropdown()
}

const handleScroll = (event) => {
  const element = event.currentTarget
  if (element.scrollHeight - element.scrollTop - element.clientHeight < 48) loadMore()
}

const handleDocumentPointer = (event) => {
  if (!root.value?.contains(event.target) && !dropdown.value?.contains(event.target))
    closeDropdown()
}

const clearValue = () => {
  clear()
  input.value?.focus()
}
const handleAdd = () => {
  hide()
  emit('add')
}

watch(open, async (value) => {
  if (!value) return
  activeIndex.value = -1
  await nextTick()
  positionDropdown()
})
watch(options, () => {
  activeIndex.value = options.value.length ? 0 : -1
})

onMounted(() => {
  document.addEventListener('pointerdown', handleDocumentPointer)
  window.addEventListener('resize', positionDropdown)
  window.addEventListener('scroll', positionDropdown, true)
})
onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', handleDocumentPointer)
  window.removeEventListener('resize', positionDropdown)
  window.removeEventListener('scroll', positionDropdown, true)
})
</script>

<template>
  <div ref="root" class="w-full" :data-async-select-open="open ? 'true' : undefined">
    <label
      v-if="label"
      :for="inputId"
      class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
    >
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>

    <div
      ref="control"
      :class="[
        'flex h-[46px] items-center rounded-xl border bg-white transition focus-within:ring-4 dark:bg-[#0a1b33]',
        error
          ? 'border-red-500 focus-within:border-red-500 focus-within:ring-red-500/10'
          : 'border-slate-300 focus-within:border-emerald-500 focus-within:ring-emerald-500/10 dark:border-[#29476b]',
        disabled ? 'cursor-not-allowed bg-slate-100 opacity-70 dark:bg-[#102542]' : '',
      ]"
      @click="openDropdown"
    >
      <Search :size="17" class="ml-3.5 shrink-0 text-slate-400" />
      <input
        :id="inputId"
        ref="input"
        v-model="searchTerm"
        type="text"
        role="combobox"
        autocomplete="off"
        class="min-w-0 flex-1 border-0 bg-transparent px-2.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:ring-0 dark:text-white"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required && !modelValue"
        :pattern="required && !modelValue ? '(?!)' : undefined"
        :aria-expanded="open"
        :aria-controls="listboxId"
        :aria-activedescendant="activeOptionId"
        :aria-invalid="Boolean(error)"
        :aria-describedby="describedBy"
        aria-autocomplete="list"
        @focus="openDropdown"
        @keydown="handleKeydown"
      />
      <input v-if="name" type="hidden" :name="name" :value="modelValue ?? ''" />
      <button
        v-if="clearable && modelValue && !disabled"
        type="button"
        class="grid size-8 shrink-0 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-[#163354] dark:hover:text-white"
        aria-label="Hapus pilihan"
        @click.stop="clearValue"
      >
        <X :size="16" />
      </button>
      <LoaderCircle v-if="loading" :size="17" class="mr-3 animate-spin text-emerald-600" />
      <ChevronDown
        v-else
        :size="17"
        :class="['mr-3 shrink-0 text-slate-400 transition', open ? 'rotate-180' : '']"
      />
    </div>

    <Teleport to="body">
      <div
        v-if="open"
        ref="dropdown"
        :style="dropdownStyle"
        class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/15 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <ul
          :id="listboxId"
          role="listbox"
          class="min-h-0 flex-1 overflow-y-auto p-1.5"
          @scroll="handleScroll"
        >
          <template v-if="loading">
            <li
              v-for="index in 4"
              :key="index"
              class="m-1 h-9 animate-pulse rounded-lg bg-slate-100 dark:bg-[#163354]"
            ></li>
          </template>
          <li
            v-for="(option, index) in options"
            v-else
            :id="`${inputId}-option-${index}`"
            :key="option.id"
            role="option"
            :aria-selected="String(option.id) === String(modelValue)"
            :class="[
              'flex cursor-pointer items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors',
              index === activeIndex
                ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200'
                : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-[#163354]',
            ]"
            @pointerenter="activeIndex = index"
            @click="selectOption(option)"
          >
            <span class="truncate">{{ option.text }}</span>
            <Check
              v-if="String(option.id) === String(modelValue)"
              :size="16"
              class="shrink-0 text-emerald-600"
            />
          </li>
          <li
            v-if="!loading && !options.length"
            class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-300"
          >
            {{ requestFailed ? 'Gagal memuat data' : 'Tidak ada data' }}
          </li>
          <li v-if="addable" class="border-t border-slate-100 p-1.5 dark:border-[#29476b]">
            <button
              type="button"
              class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm font-semibold text-emerald-700 hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-400/10"
              @click.stop="handleAdd"
            >
              <Plus :size="16" />{{ addLabel }}
            </button>
          </li>
          <li
            v-if="loadingMore"
            class="flex items-center justify-center gap-2 px-3 py-3 text-sm text-slate-500"
          >
            <LoaderCircle :size="16" class="animate-spin" /> Memuat data berikutnya
          </li>
          <li v-else-if="hasMore" class="px-3 py-2 text-center text-xs text-slate-400">
            Gulir untuk memuat data berikutnya
          </li>
        </ul>
      </div>
    </Teleport>

    <p v-if="error" :id="errorId" class="mt-1.5 text-sm font-medium text-red-500">{{ error }}</p>
    <p v-else-if="hint" :id="hintId" class="mt-1.5 text-xs text-slate-500 dark:text-slate-300">
      {{ hint }}
    </p>
  </div>
</template>
