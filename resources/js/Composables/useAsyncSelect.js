import axios from 'axios'
import { useDebounceFn } from '@vueuse/core'
import { computed, onBeforeUnmount, ref, watch } from 'vue'

const normalizeOptions = (options) =>
  (Array.isArray(options) ? options : []).filter(
    (option) => option && option.id !== undefined && typeof option.text === 'string'
  )

export const useAsyncSelect = (props, emit) => {
  const optionSource = () => [...props.initialOptions, ...props.additionalOptions]
  const options = ref(normalizeOptions(optionSource()))
  const selectedOption = ref(null)
  const searchTerm = ref('')
  const open = ref(false)
  const loading = ref(false)
  const loadingMore = ref(false)
  const hasMore = ref(false)
  const nextCursor = ref(null)
  const requestFailed = ref(false)
  let requestController = null
  let requestSequence = 0

  const selectedText = computed(() => selectedOption.value?.text ?? '')

  const syncSelected = () => {
    const available = normalizeOptions(optionSource())
    const selected = [...available, ...options.value].find(
      (option) => String(option.id) === String(props.modelValue)
    )

    selectedOption.value = selected ?? null
    if (!open.value) searchTerm.value = selected?.text ?? ''
  }

  const requestOptions = async ({ append = false } = {}) => {
    const term = searchTerm.value.trim()
    requestController?.abort()
    requestController = new window.AbortController()
    const sequence = ++requestSequence

    if (term.length < props.minimumInputLength) {
      options.value = props.clearItems ? [] : normalizeOptions(optionSource())
      hasMore.value = false
      nextCursor.value = null
      loading.value = false
      loadingMore.value = false
      return
    }

    requestFailed.value = false
    if (!append) options.value = props.clearItems ? [] : normalizeOptions(optionSource())
    append ? (loadingMore.value = true) : (loading.value = true)

    try {
      const response = await axios.get(props.endpoint, {
        params: {
          [props.searchParam]: term || undefined,
          [props.cursorParam]: append ? nextCursor.value : undefined,
        },
        signal: requestController.signal,
      })

      if (sequence !== requestSequence) return

      const data = response.data ?? {}
      const results = normalizeOptions(data.results)
      const merged = append ? [...options.value, ...results] : results
      options.value = [...new Map(merged.map((option) => [String(option.id), option])).values()]
      nextCursor.value = data.next_cursor ?? null
      hasMore.value = Boolean(data.pagination?.more && nextCursor.value)
    } catch (error) {
      if (error?.code === 'ERR_CANCELED') return
      requestFailed.value = true
      emit('error', error)
    } finally {
      if (sequence === requestSequence) {
        loading.value = false
        loadingMore.value = false
      }
    }
  }

  const debouncedSearch = useDebounceFn(() => {
    if (open.value) requestOptions()
  }, props.debounce)

  const show = () => {
    if (props.disabled || open.value) return
    open.value = true
    searchTerm.value = ''
    requestOptions()
  }

  const hide = () => {
    open.value = false
    searchTerm.value = selectedText.value
  }

  const selectOption = (option) => {
    selectedOption.value = option
    searchTerm.value = option.text
    emit('update:modelValue', option.id)
    emit('change', option)
    hide()
  }

  const clear = () => {
    selectedOption.value = null
    searchTerm.value = ''
    emit('update:modelValue', null)
    emit('change', null)
  }

  const loadMore = () => {
    if (hasMore.value && !loading.value && !loadingMore.value) {
      requestOptions({ append: true })
    }
  }

  watch(searchTerm, (value, previous) => {
    if (!open.value || value === previous) return
    if (value === '' && previous === selectedText.value) return
    nextCursor.value = null
    hasMore.value = false
    debouncedSearch()
  })
  watch(() => [props.modelValue, props.initialOptions, props.additionalOptions, props.clearItems], syncSelected, {
    immediate: true,
    deep: true,
  })
  watch(
    () => props.endpoint,
    () => {
       options.value = props.clearItems ? [] : normalizeOptions(optionSource())
      nextCursor.value = null
      hasMore.value = false
      if (open.value) requestOptions()
    }
  )

  onBeforeUnmount(() => {
    requestController?.abort()
  })

  return {
    options,
    selectedOption,
    searchTerm,
    open,
    loading,
    loadingMore,
    hasMore,
    requestFailed,
    selectedText,
    show,
    hide,
    selectOption,
    clear,
    loadMore,
    requestOptions,
  }
}
