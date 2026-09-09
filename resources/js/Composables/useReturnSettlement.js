import { computed } from 'vue'

export const useReturnSettlement = (items, resolution, replacements, settlement) => {
  const totalReturnValue = computed(() =>
    items.value
      .filter((item) => item.selected && item.return_quantity > 0)
      .reduce((sum, item) => sum + Number(item.return_quantity) * Number(item.unit_price), 0)
  )

  const replacementTotal = computed(() =>
    replacements.value.reduce(
      (sum, item) => sum + (Number(item.quantity) || 0) * (Number(item.unit_price) || 0),
      0
    )
  )

  const difference = computed(() => totalReturnValue.value - replacementTotal.value)

  const showSettlementChoice = computed(
    () => resolution.value === 'ganti_produk' && difference.value > 0
  )

  const refundAmount = computed(() => {
    if (resolution.value === 'refund') return totalReturnValue.value
    if (resolution.value === 'ganti_produk' && difference.value > 0) {
      return settlement.value === 'potong_tagihan' ? 0 : difference.value
    }
    return 0
  })

  const customerCreditAmount = computed(() => {
    if (resolution.value === 'potong_tagihan') return totalReturnValue.value
    if (resolution.value === 'ganti_produk' && difference.value > 0) {
      return settlement.value === 'potong_tagihan' ? difference.value : 0
    }
    return 0
  })

  const customerPaysAmount = computed(() => {
    if (resolution.value === 'ganti_produk' && difference.value < 0) {
      return Math.abs(difference.value)
    }
    return 0
  })

  const requiresCustomer = computed(
    () => customerCreditAmount.value > 0 || resolution.value === 'potong_tagihan'
  )

  return {
    totalReturnValue,
    replacementTotal,
    difference,
    showSettlementChoice,
    refundAmount,
    customerCreditAmount,
    customerPaysAmount,
    requiresCustomer,
  }
}
