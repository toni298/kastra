<script setup>
import { router, useForm } from '@inertiajs/vue3'
import { Trash2 } from 'lucide-vue-next'
import { ref } from 'vue'
import Button from '@/Components/UI/Button.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import { useAuthorization } from '@/Composables/useAuthorization'

const props = defineProps({ company: { type: Object, required: true } })
const { can } = useAuthorization()

const form = useForm({ logo: null })
const removing = ref(false)

const upload = () =>
  form.post(route('company.logo.store', props.company.id), {
    preserveScroll: true,
    onSuccess: () => form.reset('logo'),
  })
const remove = () => {
  removing.value = true
  router.delete(route('company.logo.destroy', props.company.id), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onFinish: () => {
      removing.value = false
    },
  })
}
</script>

<template>
  <div class="space-y-5 p-5 sm:p-6">
    <div>
      <p class="font-medium text-slate-900 dark:text-white">Logo perusahaan</p>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
        Logo tampil pada dokumen dan aplikasi.
      </p>
    </div>
    <FileUpload
      v-model="form.logo"
      accept=".jpg,.jpeg,.png,.webp"
      label="Unggah logo baru"
      :initial-preview="company.logo_url"
      :error="form.errors.logo"
      :max-size="2"
    />
  </div>
  <FormActions>
    <Button
      v-if="can('company.logo') && company.logo_url"
      variant="danger"
      :loading="removing"
      @click="remove"
    >
      <Trash2 :size="16" class="mr-2" />Hapus Logo
    </Button>
    <Button
      v-if="can('company.logo')"
      :disabled="!form.logo"
      :loading="form.processing"
      @click="upload"
      >Simpan Logo</Button
    >
  </FormActions>
</template>
