export function formatRupiah(value) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, Number(value) || 0))
}

export function storeRoute(store, path = '') {
    return `/store/${store.company_slug}/${store.branch_slug}${path}`
}
