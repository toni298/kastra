<script setup>
import { computed, ref } from 'vue'
import FileUpload from '@/Components/UI/FileUpload.vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  initialImages: { type: Array, default: () => [] },
  error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'removed'])

const existingImages = ref([...props.initialImages])
const remainingSlots = computed(() => Math.max(0, 10 - existingImages.value.length))

const removeExisting = (image) => {
  existingImages.value = existingImages.value.filter((item) => item.id !== image.id)
  emit('removed', image.id)
}
</script>

<template>
  <div>
    <FileUpload
      :model-value="modelValue"
      :initial-previews="existingImages"
      multiple
      accept="image/jpeg,image/png,image/webp"
      label="Tarik foto atau klik untuk memilih"
      hint="JPG, PNG, atau WebP. Maksimal 10 foto dan 5 MB per file."
      :max-size="5"
      :max-files="remainingSlots"
      :show-preview="true"
      :error="error"
      @update:model-value="emit('update:modelValue', $event)"
      @remove-initial-preview="removeExisting"
    />
  </div>
</template>
