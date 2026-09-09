/**
 * Thermal Receipt — HTML + CSS custom (gaya Indomaret)
 * Buka di tab baru untuk review, tidak pakai PDF
 */

const PAPER_SIZES = {
  '58mm': { width: 58, label: '58mm' },
  '80mm': { width: 80, label: '80mm' },
}

const STORAGE_KEY = 'cashier_thermal_paper_size'

export function getThermalPaperSize() {
  return localStorage.getItem(STORAGE_KEY) || '58mm'
}

export function setThermalPaperSize(size) {
  if (PAPER_SIZES[size]) {
    localStorage.setItem(STORAGE_KEY, size)
  }
}

/**
 * Generate HTML struk thermal gaya Indomaret
 * @param {Window} preOpenedTab - tab yang dibuka saat user klik submit
 * @param {Object} transaction - data transaksi dari flash invoiceData
 * @param {Object} branch - data cabang
 * @param {Object} company - data perusahaan
 * @param {Object} payment - { method, amount, change }
 * @param {String} paperSize - '58mm' | '80mm'
 */
export function autoPrintThermalReceipt(preOpenedTab, transaction, branch, company, payment = {}, paperSize) {
  const size = paperSize || getThermalPaperSize()
  const html = buildReceiptHTML(transaction, branch, company, payment, size)

  const targetTab = (preOpenedTab && !preOpenedTab.closed)
    ? preOpenedTab
    : window.open('', '_blank')

  if (targetTab) {
    targetTab.document.open()
    targetTab.document.write(html)
    targetTab.document.close()

    // Auto-print setelah konten dimuat, lalu auto-close setelah print
    targetTab.onload = () => {
      setTimeout(() => {
        targetTab.print()
      }, 300)
    }
    // Fallback jika onload tidak ter-trigger (document.write)
    setTimeout(() => {
      targetTab.print()
    }, 500)

    // Auto-close setelah print selesai atau dibatalkan
    targetTab.onafterprint = () => {
      targetTab.close()
    }
  }
}

/**
 * Build HTML struk lengkap
 */
function buildReceiptHTML(transaction, branch, company, payment, paperSize) {
  const is80 = paperSize === '80mm'
  const paperWidth = is80 ? 80 : 58
  const fontSize = is80 ? '12px' : '10px'
  const fontSizeSm = is80 ? '10px' : '8px'
  const fontSizeXs = is80 ? '9px' : '7px'
  const storeName = (branch?.name || company?.name || 'TOKO').toUpperCase()
  const address = branch?.address || ''
  const city = [branch?.city, branch?.province].filter(Boolean).join(', ')
  const phone = branch?.phone || ''
  const transNumber = transaction.transaction_number || '-'
  const transDate = formatReceiptDate(transaction.transaction_date)
  const cashierName = transaction.cashier_name || '-'
  const customerName = customerLabel(transaction)
  const items = transaction.details || []
  const discount = Number(transaction.discount || 0)
  const tax = Number(transaction.tax || 0)
  const shipping = Number(transaction.shipping_cost || 0)
  const subtotal = items.reduce((s, i) => s + (i.subtotal || 0), 0)
  const total = Number(transaction.total || subtotal - discount + tax + shipping)
  const methodLabel = paymentMethodLabel(payment.method || transaction.payment_method)
  const paymentAmount = Number(payment.amount || total)
  const change = Number(payment.change || 0)

  return `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk - ${transNumber}</title>
<style>
  @page {
    size: ${paperWidth}mm auto;
    margin: 0;
  }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Courier New', Courier, monospace;
    font-size: ${fontSize};
    color: #000;
    background: #fff;
    width: ${paperWidth}mm;
    margin: 0 auto;
    padding: 4mm 2mm;
    line-height: 1.4;
  }
  .text-center { text-align: center; }
  .text-bold { font-weight: bold; }
  .text-sm { font-size: ${fontSizeSm}; }
  .text-xs { font-size: ${fontSizeXs}; }
  .divider {
    border-top: 1px dashed #000;
    margin: 2mm 0;
  }
  .divider-double {
    border-top: 2px solid #000;
    margin: 2mm 0;
  }
  .row {
    display: flex;
    justify-content: space-between;
    padding: 0.5mm 0;
  }
  .item-name { padding: 0.5mm 0 0 0; }
  .item-detail {
    display: flex;
    justify-content: space-between;
    padding-left: 4mm;
    font-size: ${fontSizeSm};
  }
  .total-row {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: ${is80 ? '13px' : '11px'};
    padding: 1mm 0;
  }
  .header { margin-bottom: 1mm; }
  .header .store-name {
    font-size: ${is80 ? '14px' : '12px'};
    font-weight: bold;
    letter-spacing: 1px;
  }
  .info-section { margin: 1mm 0; }
  .info-row {
    display: flex;
    font-size: ${fontSizeSm};
  }
  .info-row .label { width: 18mm; flex-shrink: 0; }
  .info-row .value { flex: 1; }
  .items-section { margin: 1mm 0; }
  .summary-section { margin: 1mm 0; }
  .payment-section { margin: 1mm 0; }
  .footer { margin-top: 2mm; }

  /* Print styles */
  @media print {
    body { width: ${paperWidth}mm; padding: 2mm 1mm; }
    .no-print { display: none !important; }
  }
</style>
</head>
<body>

  <!-- HEADER -->
  <div class="header text-center">
    <div class="store-name">${storeName}</div>
    ${address ? `<div class="text-sm">${escapeHtml(address)}</div>` : ''}
    ${city ? `<div class="text-sm">${escapeHtml(city)}</div>` : ''}
    ${phone ? `<div class="text-sm">Telp: ${escapeHtml(phone)}</div>` : ''}
  </div>

  <div class="divider"></div>

  <!-- INFO TRANSAKSI -->
  <div class="info-section">
    <div class="info-row"><span class="label">No. Struk</span><span class="value">: ${escapeHtml(transNumber)}</span></div>
    <div class="info-row"><span class="label">Tanggal</span><span class="value">: ${transDate}</span></div>
    <div class="info-row"><span class="label">Kasir</span><span class="value">: ${escapeHtml(cashierName)}</span></div>
    <div class="info-row"><span class="label">Pelanggan</span><span class="value">: ${escapeHtml(customerName)}</span></div>
  </div>

  <div class="divider"></div>

  <!-- ITEMS -->
  <div class="items-section">
    ${items.map(item => {
      const name = item.name || item.product || '-'
      const qty = item.quantity || item.qty || 0
      const price = item.unit_price || item.price || 0
      const itemSubtotal = item.subtotal || qty * price
      return `
    <div class="item-name">${escapeHtml(name)}</div>
    <div class="item-detail">
      <span>${formatNum(qty)} x ${formatNum(price)}</span>
      <span>${formatNum(itemSubtotal)}</span>
    </div>`
    }).join('')}
  </div>

  <div class="divider"></div>

  <!-- SUMMARY -->
  <div class="summary-section">
    <div class="row"><span>Subtotal</span><span>${formatNum(subtotal)}</span></div>
    ${discount > 0 ? `<div class="row"><span>Diskon</span><span>-${formatNum(discount)}</span></div>` : ''}
    ${tax > 0 ? `<div class="row"><span>Pajak</span><span>${formatNum(tax)}</span></div>` : ''}
    ${shipping > 0 ? `<div class="row"><span>Ongkir</span><span>${formatNum(shipping)}</span></div>` : ''}
  </div>

  <div class="divider-double"></div>

  <!-- TOTAL -->
  <div class="total-row">
    <span>TOTAL</span>
    <span>Rp ${formatNum(total)}</span>
  </div>

  <div class="divider"></div>

  <!-- PEMBAYARAN -->
  <div class="payment-section">
    <div class="row"><span>${methodLabel}</span><span>${formatNum(paymentAmount)}</span></div>
    ${change > 0 ? `<div class="row"><span>Kembalian</span><span>${formatNum(change)}</span></div>` : ''}
  </div>

  <div class="divider"></div>

  <!-- FOOTER -->
  <div class="footer text-center">
    <div class="text-sm">Terima kasih telah berbelanja!</div>
    <div class="text-xs">Barang yang sudah dibeli</div>
    <div class="text-xs">tidak dapat dikembalikan.</div>
  </div>

</body>
</html>`
}

// ============= Helper Functions =============

function formatNum(num) {
  if (num === null || num === undefined) return '0'
  return Number(num).toLocaleString('id-ID')
}

function formatReceiptDate(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  return `${day}/${month}/${year} ${hours}:${minutes}`
}

function customerLabel(transaction) {
  if (transaction.customer_detail?.name) return transaction.customer_detail.name
  if (typeof transaction.customer === 'string') return transaction.customer
  if (transaction.customer?.name) return transaction.customer.name
  return 'Umum'
}

function paymentMethodLabel(method) {
  const labels = {
    cash: 'Tunai',
    transfer: 'Transfer',
    qris: 'QRIS',
    ewallet: 'E-Wallet',
    card: 'Kartu',
  }
  return labels[method] || method || 'Tunai'
}

function escapeHtml(str) {
  if (!str) return ''
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
}
