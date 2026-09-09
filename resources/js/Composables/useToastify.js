/**
 * Wrapper client-side untuk vue-toastification.
 *
 * vue-toastification adalah modul CommonJS yang tidak mendukung named export
 * saat di-bundle untuk SSR. Composable ini memastikan modul hanya diakses
 * di browser (client-side) via dynamic import, sehingga tidak memicu error
 * SyntaxError saat SSR.
 */
const isClient = typeof window !== 'undefined'

let toastPromise = null

const getToast = () => {
  if (!isClient) return Promise.resolve(null)
  if (!toastPromise) {
    toastPromise = import('vue-toastification').then((mod) => {
      const useToast = mod.useToast ?? mod.default?.useToast
      return useToast()
    })
  }
  return toastPromise
}

const call =
  (method) =>
  (...args) => {
    if (!isClient) return
    getToast().then((toast) => toast?.[method]?.(...args))
  }

export function useToastify() {
  return {
    success: call('success'),
    error: call('error'),
    warning: call('warning'),
    info: call('info'),
  }
}
