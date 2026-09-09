<script setup>
import { computed, ref } from 'vue'
import {
  ChevronDown,
  Copy,
  Eye,
  EyeOff,
  GripVertical,
  LayoutGrid,
  Plus,
  Trash2,
} from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'

const props = defineProps({
  sections: { type: Array, required: true }, // v-model:sections (localSections)
  sectionTypes: { type: Object, required: true },
  editable: { type: Boolean, default: false },
  dirty: { type: Boolean, default: false },
  processing: { type: Boolean, default: false },
})

const emit = defineEmits(['update:sections', 'dirty', 'save'])

const list = computed({
  get: () => props.sections,
  set: (v) => emit('update:sections', v),
})

const markDirty = () => emit('dirty')

const newKey = () => 'sec_' + Math.random().toString(36).slice(2, 10)

// ----- Operasi lego -----
const toggle = (i) => { list.value[i].enabled = !list.value[i].enabled; markDirty() }

const move = (i, dir) => {
  const j = i + dir
  if (j < 0 || j >= list.value.length) return
  const arr = [...list.value]
  ;[arr[i], arr[j]] = [arr[j], arr[i]]
  arr.forEach((s, idx) => (s.sort = idx))
  list.value = arr
  markDirty()
}

const remove = (i) => {
  const arr = [...list.value]
  arr.splice(i, 1)
  arr.forEach((s, idx) => (s.sort = idx))
  list.value = arr
  if (openIndex.value === i) openIndex.value = -1
  markDirty()
}

const duplicate = (i) => {
  const src = list.value[i]
  const copy = JSON.parse(JSON.stringify(src))
  copy.key = newKey()
  copy.label = `${src.label} (Salinan)`
  const arr = [...list.value]
  arr.splice(i + 1, 0, copy)
  arr.forEach((s, idx) => (s.sort = idx))
  list.value = arr
  markDirty()
}

const add = (type) => {
  const def = props.sectionTypes[type]
  if (!def) return
  const arr = [...list.value]
  arr.push({
    key: newKey(),
    type,
    label: def.label,
    enabled: true,
    sort: arr.length,
    config: JSON.parse(JSON.stringify(def.defaults ?? {})),
  })
  list.value = arr
  openIndex.value = arr.length - 1
  markDirty()
}

// ----- Konfigurasi per section -----
const openIndex = ref(-1)
const toggleConfig = (i) => { openIndex.value = openIndex.value === i ? -1 : i }

const isConfigurable = (sec) => props.sectionTypes[sec.type]?.configurable !== false

const cfg = (sec) => (sec.config ??= {})

const updateConfig = (sec, key, value) => { cfg(sec)[key] = value; markDirty() }

// items list (badges / advantages)
const addItem = (sec) => { (cfg(sec).items ??= []).push({ icon: 'zap', title: 'Judul Baru', desc: 'Deskripsi' }); markDirty() }
const removeItem = (sec, idx) => { cfg(sec).items.splice(idx, 1); markDirty() }

// testimonials
const addTestimonial = (sec) => { (cfg(sec).items ??= []).push({ name: 'Nama Pelanggan', role: '', rating: 5, quote: 'Tulis testimoni...' }); markDirty() }
const removeTestimonial = (sec, idx) => { cfg(sec).items.splice(idx, 1); markDirty() }

const iconOptions = [
  { value: 'truck', label: 'Truk' },
  { value: 'shield', label: 'Perisai' },
  { value: 'card', label: 'Kartu' },
  { value: 'refresh', label: 'Retur' },
  { value: 'headset', label: 'Headset' },
  { value: 'zap', label: 'Petir' },
  { value: 'ruler', label: 'Penggaris' },
  { value: 'percent', label: 'Persen' },
  { value: 'measure', label: 'Ukur' },
]
</script>

<template>
  <div>
    <p class="mb-3 text-[11px] text-slate-500 dark:text-slate-400">
      Susun, duplikat, sembunyikan, konfigurasi, atau hapus section di halaman beranda — seperti lego.
    </p>

    <div class="space-y-1.5">
      <div
        v-for="(sec, i) in list"
        :key="sec.key"
        class="rounded-lg border border-slate-200 bg-white dark:border-[#29476b] dark:bg-[#0a1b33]"
      >
        <!-- Baris utama -->
        <div class="flex items-center gap-2 px-2.5 py-2">
          <div class="flex flex-col">
            <button type="button" :disabled="!editable || i === 0" class="text-slate-300 hover:text-slate-500 disabled:opacity-30" @click="move(i, -1)">▲</button>
            <button type="button" :disabled="!editable || i === list.length - 1" class="text-slate-300 hover:text-slate-500 disabled:opacity-30" @click="move(i, 1)">▼</button>
          </div>
          <GripVertical :size="14" class="text-slate-300" />

          <button
            type="button"
            class="flex flex-1 items-center gap-1.5 text-left"
            @click="isConfigurable(sec) && toggleConfig(i)"
          >
            <span class="text-xs font-medium text-slate-700 dark:text-slate-200" :class="sec.enabled ? '' : 'opacity-40 line-through'">
              {{ sec.label }}
            </span>
            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-[#102542] dark:text-slate-400">{{ sectionTypes[sec.type]?.label || sec.type }}</span>
            <ChevronDown
              v-if="isConfigurable(sec)"
              :size="13"
              class="text-slate-400 transition-transform"
              :class="openIndex === i ? 'rotate-180' : ''"
            />
          </button>

          <button v-if="editable && isConfigurable(sec)" type="button" class="text-slate-400 hover:text-blue-600" title="Duplikat" @click="duplicate(i)"><Copy :size="14" /></button>
          <button v-if="editable" type="button" class="text-slate-400 hover:text-emerald-600" :title="sec.enabled ? 'Sembunyikan' : 'Tampilkan'" @click="toggle(i)">
            <component :is="sec.enabled ? Eye : EyeOff" :size="15" />
          </button>
          <button v-if="editable" type="button" class="text-slate-400 hover:text-red-500" title="Hapus" @click="remove(i)"><Trash2 :size="14" /></button>
        </div>

        <!-- Panel konfigurasi -->
        <div v-if="openIndex === i && isConfigurable(sec)" class="space-y-3 border-t border-slate-100 px-3 py-3 dark:border-[#1d3859]">
          <Input
            :model-value="sec.label"
            label="Nama Section"
            :disabled="!editable"
            @update:model-value="sec.label = $event; markDirty()"
          />

          <!-- categories / latest / promos -->
          <template v-if="['categories', 'latest', 'promos'].includes(sec.type)">
            <Input :model-value="cfg(sec).title" label="Judul" :disabled="!editable" @update:model-value="updateConfig(sec, 'title', $event)" />
            <Input v-if="sec.type !== 'categories'" :model-value="cfg(sec).link_label" label="Label Tautan" :disabled="!editable" @update:model-value="updateConfig(sec, 'link_label', $event)" />
            <Input v-if="sec.type !== 'categories'" :model-value="cfg(sec).limit" type="number" label="Jumlah Produk" :disabled="!editable" @update:model-value="updateConfig(sec, 'limit', Number($event))" />
          </template>

          <!-- badges -->
          <template v-else-if="sec.type === 'badges'">
            <div class="flex items-center justify-between">
              <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Item Badge</p>
              <button v-if="editable" type="button" class="flex items-center gap-1 text-[11px] font-medium text-emerald-600" @click="addItem(sec)"><Plus :size="12" /> Tambah</button>
            </div>
            <div v-for="(item, idx) in cfg(sec).items ?? []" :key="idx" class="space-y-1.5 rounded-md border border-slate-200 p-2 dark:border-[#29476b]">
              <div class="flex items-center gap-1.5">
                <select :value="item.icon" :disabled="!editable" class="rounded border border-slate-300 px-1.5 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @change="item.icon = $event.target.value; markDirty()">
                  <option v-for="o in iconOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
                </select>
                <input :value="item.title" :disabled="!editable" placeholder="Judul" class="flex-1 rounded border border-slate-300 px-2 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="item.title = $event.target.value; markDirty()" />
                <button v-if="editable" type="button" class="text-slate-400 hover:text-red-500" @click="removeItem(sec, idx)"><Trash2 :size="13" /></button>
              </div>
              <input :value="item.desc" :disabled="!editable" placeholder="Deskripsi" class="w-full rounded border border-slate-300 px-2 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="item.desc = $event.target.value; markDirty()" />
            </div>
          </template>

          <!-- advantages -->
          <template v-else-if="sec.type === 'advantages'">
            <Input :model-value="cfg(sec).eyebrow" label="Eyebrow" :disabled="!editable" @update:model-value="updateConfig(sec, 'eyebrow', $event)" />
            <Input :model-value="cfg(sec).title" label="Judul" :disabled="!editable" @update:model-value="updateConfig(sec, 'title', $event)" />
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Sub-judul</label>
              <textarea :value="cfg(sec).subtitle" rows="2" :disabled="!editable" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="updateConfig(sec, 'subtitle', $event.target.value)"></textarea>
            </div>
            <div class="flex items-center justify-between">
              <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Item Keunggulan</p>
              <button v-if="editable" type="button" class="flex items-center gap-1 text-[11px] font-medium text-emerald-600" @click="addItem(sec)"><Plus :size="12" /> Tambah</button>
            </div>
            <div v-for="(item, idx) in cfg(sec).items ?? []" :key="idx" class="space-y-1.5 rounded-md border border-slate-200 p-2 dark:border-[#29476b]">
              <div class="flex items-center gap-1.5">
                <select :value="item.icon" :disabled="!editable" class="rounded border border-slate-300 px-1.5 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @change="item.icon = $event.target.value; markDirty()">
                  <option v-for="o in iconOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
                </select>
                <input :value="item.title" :disabled="!editable" placeholder="Judul" class="flex-1 rounded border border-slate-300 px-2 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="item.title = $event.target.value; markDirty()" />
                <button v-if="editable" type="button" class="text-slate-400 hover:text-red-500" @click="removeItem(sec, idx)"><Trash2 :size="13" /></button>
              </div>
              <input :value="item.desc" :disabled="!editable" placeholder="Deskripsi" class="w-full rounded border border-slate-300 px-2 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="item.desc = $event.target.value; markDirty()" />
            </div>
          </template>

          <!-- promo_banner -->
          <template v-else-if="sec.type === 'promo_banner'">
            <Input :model-value="cfg(sec).eyebrow" label="Eyebrow" :disabled="!editable" @update:model-value="updateConfig(sec, 'eyebrow', $event)" />
            <Input :model-value="cfg(sec).title" label="Judul" :disabled="!editable" @update:model-value="updateConfig(sec, 'title', $event)" />
            <Input :model-value="cfg(sec).highlight" label="Highlight" :disabled="!editable" @update:model-value="updateConfig(sec, 'highlight', $event)" />
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Sub-judul</label>
              <textarea :value="cfg(sec).subtitle" rows="2" :disabled="!editable" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="updateConfig(sec, 'subtitle', $event.target.value)"></textarea>
            </div>
            <Input :model-value="cfg(sec).button_label" label="Label Tombol" :disabled="!editable" @update:model-value="updateConfig(sec, 'button_label', $event)" />
            <Input :model-value="cfg(sec).dark_title" label="Judul Panel Gelap" :disabled="!editable" @update:model-value="updateConfig(sec, 'dark_title', $event)" />
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Sub-judul Panel Gelap</label>
              <textarea :value="cfg(sec).dark_subtitle" rows="2" :disabled="!editable" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="updateConfig(sec, 'dark_subtitle', $event.target.value)"></textarea>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Teks Marquee (1 per baris)</label>
              <textarea :value="cfg(sec).marquee" rows="3" :disabled="!editable" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="updateConfig(sec, 'marquee', $event.target.value)"></textarea>
            </div>
          </template>

          <!-- testimonials -->
          <template v-else-if="sec.type === 'testimonials'">
            <Input :model-value="cfg(sec).eyebrow" label="Eyebrow" :disabled="!editable" @update:model-value="updateConfig(sec, 'eyebrow', $event)" />
            <Input :model-value="cfg(sec).title" label="Judul" :disabled="!editable" @update:model-value="updateConfig(sec, 'title', $event)" />
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Sub-judul</label>
              <textarea :value="cfg(sec).subtitle" rows="2" :disabled="!editable" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="updateConfig(sec, 'subtitle', $event.target.value)"></textarea>
            </div>
            <div class="flex items-center justify-between">
              <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Item Testimoni</p>
              <button v-if="editable" type="button" class="flex items-center gap-1 text-[11px] font-medium text-emerald-600" @click="addTestimonial(sec)"><Plus :size="12" /> Tambah</button>
            </div>
            <div v-for="(item, idx) in cfg(sec).items ?? []" :key="idx" class="space-y-1.5 rounded-md border border-slate-200 p-2 dark:border-[#29476b]">
              <div class="flex items-center gap-1.5">
                <input :value="item.name" :disabled="!editable" placeholder="Nama" class="flex-1 rounded border border-slate-300 px-2 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="item.name = $event.target.value; markDirty()" />
                <select :value="item.rating" :disabled="!editable" class="rounded border border-slate-300 px-1.5 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @change="item.rating = Number($event.target.value); markDirty()">
                  <option v-for="n in 5" :key="n" :value="n">{{ n }}★</option>
                </select>
                <button v-if="editable" type="button" class="text-slate-400 hover:text-red-500" @click="removeTestimonial(sec, idx)"><Trash2 :size="13" /></button>
              </div>
              <input :value="item.role" :disabled="!editable" placeholder="Peran / gelar (opsional)" class="w-full rounded border border-slate-300 px-2 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="item.role = $event.target.value; markDirty()" />
              <textarea :value="item.quote" rows="2" :disabled="!editable" placeholder="Isi testimoni" class="w-full rounded border border-slate-300 px-2 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="item.quote = $event.target.value; markDirty()"></textarea>
            </div>
          </template>

          <!-- richtext -->
          <template v-else-if="sec.type === 'richtext'">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Konten (HTML)</label>
              <textarea :value="cfg(sec).content" rows="6" :disabled="!editable" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono text-xs dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" @input="updateConfig(sec, 'content', $event.target.value)"></textarea>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Tambah section (semua type, boleh duplikat) -->
    <div class="mt-3">
      <p class="mb-1.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">Tambah Section:</p>
      <div class="flex flex-wrap gap-1.5">
        <button
          v-for="(def, type) in sectionTypes"
          :key="type"
          type="button"
          :disabled="!editable"
          class="flex items-center gap-1 rounded-lg border border-dashed border-emerald-300 px-2.5 py-1.5 text-[11px] font-medium text-emerald-700 transition hover:bg-emerald-50 disabled:opacity-50 dark:border-emerald-500/40 dark:text-emerald-400"
          @click="add(type)"
        >
          <Plus :size="12" /> {{ def.label }}
        </button>
      </div>
    </div>

    <Button v-if="editable" type="button" size="sm" class="mt-4 w-full" :disabled="!dirty" :loading="processing" @click="$emit('save')">
      Simpan Section
    </Button>
  </div>
</template>
