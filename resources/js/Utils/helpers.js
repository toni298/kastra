const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}

const formatDate = (date) => {
  if (!date) return ''

  const parsedDate = new Date(date)
  const day = String(parsedDate.getDate()).padStart(2, '0')
  const month = String(parsedDate.getMonth() + 1).padStart(2, '0')
  const year = parsedDate.getFullYear()

  return `${day}/${month}/${year}`
}

const formatQty = (value) => {
  const number = Number(value)

  return Number.isFinite(number)
    ? number.toLocaleString('id-ID', { maximumFractionDigits: 3 })
    : '-'
}

export { formatCurrency, formatDate, formatQty }
