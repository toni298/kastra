<script setup>
import { ChevronDown, ChevronRight, Folder, FolderOpen, Landmark } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
  groups: { type: Array, default: () => [] },
  expanded: { type: Object, required: true },
  selectedId: { type: String, default: '' },
})
const emit = defineEmits(['toggle', 'select'])
</script>

<template>
  <div class="min-h-[520px] p-3 sm:p-4" role="tree" aria-label="Struktur Chart of Accounts">
    <div v-if="!groups.length" class="grid min-h-[440px] place-items-center px-6 text-center">
      <div>
        <Landmark class="mx-auto size-10 text-slate-300" />
        <p class="mt-3 font-medium text-slate-800 dark:text-white">Tidak ada data</p>
        <p class="mt-1 text-sm text-slate-500">Akun tidak ditemukan untuk filter ini.</p>
      </div>
    </div>
    <div v-for="group in groups" v-else :key="group.id" class="mb-2">
      <button
        type="button"
        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition hover:bg-slate-100 dark:hover:bg-[#163354]"
        :aria-expanded="expanded[group.id]"
        @click="emit('toggle', group.id)"
      >
        <component
          :is="expanded[group.id] ? ChevronDown : ChevronRight"
          :size="17"
          class="text-slate-400"
        />
        <component
          :is="expanded[group.id] ? FolderOpen : Folder"
          :size="19"
          class="text-emerald-600"
        />
        <span class="min-w-0 flex-1">
          <span class="block font-semibold text-slate-900 dark:text-white">{{ group.name }}</span>
          <span class="block text-xs text-slate-400">Kode {{ group.code }}</span>
        </span>
        <span
          class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500 dark:bg-[#0a1b33]"
          >{{ group.accounts.length }}</span
        >
      </button>
      <div
        v-if="expanded[group.id]"
        class="relative ml-7 border-l border-slate-200 pl-4 dark:border-[#29476b]"
      >
        <button
          v-for="account in group.accounts"
          :key="account.id"
          type="button"
          role="treeitem"
          :aria-selected="selectedId === account.id"
          :class="[
            'relative my-1 flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition',
            selectedId === account.id
              ? 'bg-emerald-50 ring-1 ring-emerald-200 dark:bg-emerald-400/10 dark:ring-emerald-500/30'
              : 'hover:bg-slate-50 dark:hover:bg-[#163354]/70',
          ]"
          @click="emit('select', account)"
        >
          <span
            class="absolute -left-4 top-1/2 w-4 border-t border-slate-200 dark:border-[#29476b]"
          ></span>
          <Landmark
            :size="17"
            :class="selectedId === account.id ? 'text-emerald-600' : 'text-slate-400'"
          />
          <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{
              account.name
            }}</span>
            <span class="block text-xs font-medium text-slate-400">{{ account.code }}</span>
          </span>
          <Badge :variant="account.status === 'Aktif' ? 'success' : 'error'">{{
            account.status
          }}</Badge>
        </button>
      </div>
    </div>
  </div>
</template>
