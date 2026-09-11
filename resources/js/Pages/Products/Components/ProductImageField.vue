<script setup>
import { computed, ref, watch } from 'vue'
import FileUpload from '@/Components/UI/FileUpload.vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  initialImages: { type: Array, default: () => [] },
  error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'removed'])

const existingImages = ref([...props.initialImages])
const selectedFiles = ref([...props.modelValue])

const totalSlots = 10
const remainingSlots = computed(() => Math.max(0, totalSlots - existingImages.value.length))

watch(
  () => props.modelValue,
  (val) => {
    selectedFiles.value = Array.isArray(val) ? [...val] : []
  },
  { deep: true }
)

const removeExisting = (image) => {
  if (!image?.id) return

  existingImages.value = existingImages.value.filter((item) => item.id !== image.id)
  emit('removed', image.id)
}

const handleUpdate = (value) => {
  selectedFiles.value = Array.isArray(value) ? value : []
  emit('update:modelValue', value)
}
</script>

<template>
  <div>
    <FileUpload
      :model-value="selectedFiles"
      :initial-previews="existingImages"
      multiple
      accept="image/jpeg,image/png,image/webp"
      label="Tarik foto atau klik untuk memilih"
      hint="JPG, PNG, atau WebP. Maksimal 10 foto dan 5 MB per file."
      :max-size="5"
      :max-files="remainingSlots"
      :show-preview="true"
      :error="error"
      @update:model-value="handleUpdate"
      @remove-initial-preview="removeExisting"
    />
  </div>
</template>
