// Resolver tema storefront.
// Setiap tema = satu folder di ./Themes/<nama>. Folder harus mengekspor
// komponen dengan nama yang sama agar bisa dipilih secara dinamis.
// Untuk menambah tema baru: buat folder baru di Themes/ lalu daftarkan di sini.

import modern from './modern/index.js'

const themes = {
  modern,
  // 'minimal': minimal, // contoh tema baru
}

export function resolveTheme(template) {
  return themes[template] || themes.modern
}
