<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ChevronDown, LogOut, Settings, ShieldCheck, UserRound } from 'lucide-vue-next'
import CashierNavigation from './CashierNavigation.vue'
import { useRoute } from 'ziggy-js'

const page = usePage()
const route = useRoute(page.props.ziggy)
const dateLabel = computed(() =>
  new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(
    new Date()
  )
)
const userName = computed(() => page.props.auth?.user?.name ?? 'Kasir')
const userEmail = computed(() => page.props.auth?.user?.email ?? '')
const roles = computed(() => page.props.auth?.roles ?? [])
const roleLabel = computed(() => roles.value[0] ?? 'Kasir')
const initials = computed(
  () =>
    userName.value
      ?.split(' ')
      .map((part) => part[0])
      .slice(0, 2)
      .join('')
      .toUpperCase() ?? 'K'
)
const menuOpen = ref(false)
const accountMenu = ref(null)
const closeOnOutside = (event) => {
  if (accountMenu.value && !accountMenu.value.contains(event.target)) menuOpen.value = false
}
const closeOnEscape = (event) => {
  if (event.key === 'Escape') menuOpen.value = false
}
const logout = () => {
  menuOpen.value = false
  router.post(route('logout'))
}
onMounted(() => {
  document.addEventListener('click', closeOnOutside)
  document.addEventListener('keydown', closeOnEscape)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', closeOnOutside)
  document.removeEventListener('keydown', closeOnEscape)
})
</script>

<template>
  <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
    <div class="flex min-h-[76px] items-center gap-5 px-5 xl:px-7">
      <Link
        :href="route('cashier')"
        class="flex shrink-0 items-center gap-2 border-r border-slate-200 pr-5"
      >
        <span
          class="grid h-8 w-8 place-items-center rounded-md bg-blue-600 text-lg font-bold text-white"
          >K</span
        >
        <span class="text-xl font-bold tracking-tight text-blue-700">Kastra</span>
      </Link>
      <CashierNavigation />
      <div class="ml-auto hidden items-center gap-4 xl:flex">
        <span
          class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700"
        >
          <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Shift: Aktif
        </span>
        <span class="text-xs font-medium text-slate-500">{{ dateLabel }}</span>
        <div
          ref="accountMenu"
          class="relative border-l border-slate-200 pl-3 dark:border-[#29476b]"
        >
          <button
            type="button"
            class="flex items-center gap-2 rounded-xl px-1 py-1 text-left hover:bg-slate-50 dark:hover:bg-[#102542]"
            :aria-expanded="menuOpen"
            aria-haspopup="menu"
            @click="menuOpen = !menuOpen"
          >
            <span
              class="grid size-9 place-items-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
              >{{ initials }}</span
            >
            <div class="hidden leading-tight xl:block">
              <p class="text-sm font-medium text-slate-900 dark:text-white">{{ userName }}</p>
              <p class="text-[12px] capitalize text-slate-500 dark:text-slate-400">
                {{ roleLabel }}
              </p>
            </div>
            <ChevronDown
              :size="15"
              :class="['text-slate-500 transition dark:text-slate-400', { 'rotate-180': menuOpen }]"
            />
          </button>
          <Transition name="cashier-dropdown">
            <div
              v-if="menuOpen"
              class="absolute right-0 top-[calc(100%+0.75rem)] z-50 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl shadow-slate-950/10 dark:border-[#29476b] dark:bg-[#102542] dark:shadow-black/30"
              role="menu"
            >
              <div class="border-b border-slate-100 px-3 py-2.5 dark:border-[#29476b]">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ userName }}</p>
                <p class="truncate text-xs text-slate-500 dark:text-slate-300">{{ userEmail }}</p>
              </div>
              <div class="py-2">
                <Link
                  :href="route('profile.edit')"
                  class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
                  role="menuitem"
                  @click="menuOpen = false"
                  ><UserRound :size="17" />Profil Saya</Link
                >
                <Link
                  :href="route('profile.edit')"
                  class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
                  role="menuitem"
                  @click="menuOpen = false"
                  ><Settings :size="17" />Pengaturan Akun</Link
                >
                <Link
                  :href="`${route('profile.edit')}#keamanan`"
                  class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
                  role="menuitem"
                  @click="menuOpen = false"
                  ><ShieldCheck :size="17" />Keamanan</Link
                >
              </div>
              <div class="border-t border-slate-100 pt-2 dark:border-[#29476b]">
                <button
                  type="button"
                  class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-400/10"
                  role="menuitem"
                  @click="logout"
                >
                  <LogOut :size="17" />Keluar
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.cashier-dropdown-enter-active,
.cashier-dropdown-leave-active {
  transition: all 0.16s ease;
}

.cashier-dropdown-enter-from,
.cashier-dropdown-leave-to {
  opacity: 0;
  transform: translateY(-0.5rem) scale(0.98);
}
</style>
