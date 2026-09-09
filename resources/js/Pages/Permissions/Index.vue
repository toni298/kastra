<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { Plus } from '@lucide/vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineProps({ permissions: { type: Array, default: () => [] } })
const form = useForm({ name: '' })
const submit = () => form.post(route('permissions.store'), { onSuccess: () => form.reset() })
</script>
<template>
  <Head title="Permission" /><AuthenticatedLayout
    ><template #header>Permission</template>
    <div class="space-y-5">
      <div>
        <h2 class="text-2xl font-semibold text-slate-950 dark:text-white">Permission</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
          Atur izin akses berbasis module.action.
        </p>
      </div>
      <section
        class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <form class="flex flex-col gap-3 sm:flex-row" @submit.prevent="submit">
          <Input
            v-model="form.name"
            label="Permission baru"
            placeholder="contoh: laporan.export"
            :error="form.errors.name"
          /><Button type="submit" class="shrink-0 self-end" :loading="form.processing"
            ><Plus :size="17" class="mr-2" />Tambah</Button
          >
        </form>
      </section>
      <section
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-[#29476b] dark:bg-[#102542]"
      >
        <table class="min-w-full">
          <thead
            class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-[#0d2039] dark:text-slate-300"
          >
            <tr>
              <th class="px-5 py-3">Permission</th>
              <th class="px-5 py-3">Role memakai</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
            <tr v-for="permission in permissions" :key="permission.id">
              <td class="px-5 py-4 font-medium text-slate-900 dark:text-white">
                {{ permission.name }}
              </td>
              <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-300">
                {{ permission.roles_count }} role
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </div></AuthenticatedLayout
  >
</template>
