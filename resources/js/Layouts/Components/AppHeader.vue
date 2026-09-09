<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  ChevronDown,
  CircleHelp,
  LogOut,
  Menu,
  Moon,
  Search,
  Settings,
  ShieldCheck,
  Sun,
  UserRound,
} from '@lucide/vue'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useTheme } from '@/Composables/useTheme'

const emit = defineEmits(['menu'])
const page = usePage()
const user = computed(() => page.props.auth?.user)
const roles = computed(() => page.props.auth?.roles ?? [])
const roleLabel = computed(() => (roles.value.length ? roles.value[0] : 'Tanpa role'))
const menuOpen = ref(false)
const accountMenu = ref(null)
const initials = computed(
  () =>
    user.value?.name
      ?.split(' ')
      .map((part) => part[0])
      .slice(0, 2)
      .join('')
      .toUpperCase() ?? 'U'
)
const { isDark, toggleTheme } = useTheme()
const closeOnOutside = (event) => {
  if (accountMenu.value && !accountMenu.value.contains(event.target)) menuOpen.value = false
}
const logout = () => router.post(route('logout'))
onMounted(() => document.addEventListener('click', closeOnOutside))
onBeforeUnmount(() => document.removeEventListener('click', closeOnOutside))
</script>

<template>
  <header
    class="sticky top-0 z-30 flex h-[72px] items-center gap-4 border-b border-slate-200 bg-white/95 px-5 backdrop-blur-xl dark:border-[#1d3859] dark:bg-[#0a1b33]/95 lg:px-8"
  >
    <button class="text-slate-600 dark:text-slate-200 lg:hidden" @click="emit('menu')">
      <Menu :size="22" />
    </button>
    <slot name="title"></slot>
    <div class="mx-auto hidden w-full max-w-md md:block">
      <div
        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-400 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <Search :size="17" /><span class="text-sm">Cari menu, data, laporan...</span
        ><kbd class="ml-auto text-[11px]">Ctrl + K</kbd>
      </div>
    </div>
    <div class="ml-auto flex items-center gap-2">
      <button
        class="grid size-9 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-[#102542]"
        :aria-label="isDark ? 'Mode terang' : 'Mode malam'"
        @click="toggleTheme"
      >
        <Sun v-if="isDark" :size="18" /><Moon v-else :size="18" /></button
      >
      <button class="hidden size-9 place-items-center text-slate-600 dark:text-slate-200 sm:grid">
        <CircleHelp :size="18" />
      </button>
      <div
        ref="accountMenu"
        class="relative ml-1 border-l border-slate-200 pl-3 dark:border-[#29476b]"
      >
        <button
          class="flex items-center gap-2 rounded-xl px-1 py-1 text-left hover:bg-slate-50 dark:hover:bg-[#102542]"
          @click="menuOpen = !menuOpen"
        >
          <span
            class="grid size-9 place-items-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
            >{{ initials }}</span
          >
          <div class="hidden leading-tight xl:block">
            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ user?.name }}</p>
            <p class="text-[12px] capitalize text-slate-500 dark:text-slate-400">
              {{ roleLabel }}
            </p>
          </div>
          <ChevronDown
            :size="15"
            :class="['hidden text-slate-500 transition xl:block', { 'rotate-180': menuOpen }]"
          />
        </button>
        <Transition name="dropdown"
          ><div
            v-if="menuOpen"
            class="absolute right-0 top-[calc(100%+0.75rem)] w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl shadow-slate-950/10 dark:border-[#29476b] dark:bg-[#102542] dark:shadow-black/30"
          >
            <div class="border-b border-slate-100 px-3 py-2.5 dark:border-[#29476b]">
              <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ user?.name }}</p>
              <p class="truncate text-xs text-slate-500 dark:text-slate-300">{{ user?.email }}</p>
            </div>
            <div class="py-2">
              <Link
                :href="route('profile.edit')"
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
                @click="menuOpen = false"
                ><UserRound :size="17" />Profil Saya</Link
              ><Link
                :href="route('profile.edit')"
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
                @click="menuOpen = false"
                ><Settings :size="17" />Pengaturan Akun</Link
              ><Link
                :href="`${route('profile.edit')}#keamanan`"
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
                @click="menuOpen = false"
                ><ShieldCheck :size="17" />Keamanan</Link
              >
            </div>
            <div class="border-t border-slate-100 pt-2 dark:border-[#29476b]">
              <button
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-400/10"
                @click="logout"
              >
                <LogOut :size="17" />Keluar
              </button>
            </div>
          </div></Transition
        >
      </div>
    </div>
  </header>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.16s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-0.5rem) scale(0.98);
}
</style>
