import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'

/**
 * Generate invoice PDF sesuai template: Header (black band) → Customer → Barang → Summary (center-right) → Payment & Address (stacked gray box) → Footer
 */
export function generateInvoicePDF(transaction, branch, company) {
  console.log('[Invoice] Transaction data:', transaction)
  console.log('[Invoice] Branch data:', branch)
  console.log('[Invoice] Company data:', company)

  const doc = new jsPDF({
    orientation: 'portrait',
    unit: 'mm',
    format: 'a4',
  })

  const pageWidth = doc.internal.pageSize.getWidth()
  const pageHeight = doc.internal.pageSize.getHeight()
  const margin = 14
  const contentWidth = pageWidth - margin * 2
  let yPos = 0

  // ============= 1. HEADER — Full-width black band =============
  const headerBandHeight = 28
  doc.setFillColor(30, 30, 30)
  doc.rect(0, 0, pageWidth, headerBandHeight, 'F')

  // Logo + Nama perusahaan (kiri, putih)
  let headerTextX = margin
  if (company?.logo_path) {
    try {
      const logoUrl = company.logo_path.startsWith('http')
        ? company.logo_path
        : `/storage/${company.logo_path}`
      const logoSize = 14
      doc.addImage(logoUrl, 'PNG', margin, (headerBandHeight - logoSize) / 2, logoSize, logoSize)
      headerTextX = margin + logoSize + 4
    } catch (error) {
      console.warn('[Invoice] Logo gagal dimuat:', company.logo_path, error)
    }
  }

  doc.setTextColor(255, 255, 255)
  doc.setFontSize(14)
  doc.setFont(undefined, 'bold')
  doc.text(branch?.name || company?.name || 'PT/CV', headerTextX, headerBandHeight / 2 + 1)

  // Invoice number (kanan atas, putih)
  const invoiceNumber = `Invoice #${transaction.transaction_number || '-'}`
  doc.setFontSize(11)
  doc.setFont(undefined, 'bold')
  doc.text(invoiceNumber, pageWidth - margin, headerBandHeight / 2 - 3, { align: 'right' })

  // Tanggal (kanan bawah, putih)
  const invoiceDate = formatDateLocal(transaction.transaction_date)
  doc.setFontSize(9)
  doc.setFont(undefined, 'normal')
  doc.text(invoiceDate, pageWidth - margin, headerBandHeight / 2 + 5, { align: 'right' })

  doc.setTextColor(0, 0, 0)
  yPos = headerBandHeight + 8

  // ============= 2. INFORMASI CUSTOMER =============
  doc.setFontSize(10)
  doc.setFont(undefined, 'bold')
  doc.text('Kepada', margin, yPos)

  yPos += 6
  doc.setFontSize(9)

  const custName = customerName(transaction.customer)
  doc.setFont(undefined, 'bold')
  doc.text('Nama', margin, yPos)
  doc.setFont(undefined, 'normal')
  doc.text(`: ${custName}`, margin + 18, yPos)

  yPos += 5
  doc.setFont(undefined, 'bold')
  doc.text('Alamat', margin, yPos)
  doc.setFont(undefined, 'normal')
  const alamat = getCustomerAddress(transaction)
  const alamatDisplay = alamat && alamat.trim() ? alamat : '-'
  const alamatLines = doc.splitTextToSize(`: ${alamatDisplay}`, contentWidth - 18)
  doc.text(alamatLines, margin + 18, yPos)
  yPos += Math.max(alamatLines.length * 4.5, 4.5)

  yPos += 1
  doc.setFont(undefined, 'bold')
  doc.text('Nomor Hp', margin, yPos)
  doc.setFont(undefined, 'normal')
  const phone = getCustomerPhone(transaction)
  const phoneDisplay = phone && phone.trim() ? phone : '-'
  doc.text(`: ${phoneDisplay}`, margin + 18, yPos)

  yPos += 10

  // ============= 3. DETAIL BARANG - TABEL =============
  const items = transaction.details || transaction.items || []

  const tableData = items.map((item) => {
    const qty = item.quantity || item.qty || 0
    const unitName = item.unit_name || item.satuan_name || item.unit || ''
    const displayQty = unitName ? `${formatNumber(qty)} ${unitName}` : formatNumber(qty)
    return [
      item.product || item.name || item.product_name || '-',
      displayQty,
      `Rp. ${formatNumber(item.unit_price || item.price || 0)}`,
      `Rp. ${formatNumber(item.subtotal || qty * (item.unit_price || item.price || 0))}`,
    ]
  })

  autoTable(doc, {
    startY: yPos,
    head: [['Barang', 'Jumlah', 'Harga', 'Total']],
    body: tableData,
    headStyles: {
      fillColor: [255, 255, 255],
      textColor: [0, 0, 0],
      fontStyle: 'bold',
      fontSize: 9,
      cellPadding: 4,
      halign: 'center',
      lineColor: [200, 200, 200],
      lineWidth: 0.3,
    },
    bodyStyles: {
      fillColor: [255, 255, 255],
      fontSize: 9,
      textColor: [0, 0, 0],
      cellPadding: 4,
      lineColor: [200, 200, 200],
      lineWidth: 0.3,
      halign: 'center',
    },
    columnStyles: {
      0: { cellWidth: contentWidth * 0.42, halign: 'center' },
      1: { cellWidth: contentWidth * 0.16, halign: 'center' },
      2: { cellWidth: contentWidth * 0.21, halign: 'center' },
      3: { cellWidth: contentWidth * 0.21, halign: 'center' },
    },
    margin: { left: margin, right: margin },
    tableLineWidth: 0.3,
    tableLineColor: [200, 200, 200],
  })

  yPos = doc.lastAutoTable.finalY + 0

  // ============= 4. RINGKASAN — autoTable agar sejajar dengan tabel barang =============
  const calculatedSubtotal = calculateSubtotal(transaction)
  const subtotalAmount =
    calculatedSubtotal > 0
      ? calculatedSubtotal
      : (transaction.total || 0) -
        (transaction.tax || 0) -
        (transaction.discount || 0) +
        (transaction.shipping_cost || 0)

  const ongkirText =
    (transaction.shipping_cost || 0) > 0
      ? `Rp. ${formatNumber(transaction.shipping_cost)}`
      : 'Free Ongkir'

  autoTable(doc, {
    startY: yPos,
    body: [
      [
        { content: 'Subtotal', colSpan: 3, styles: { halign: 'center', fontStyle: 'bold' } },
        {
          content: `Rp. ${formatNumber(subtotalAmount)}`,
          styles: { halign: 'center', fontStyle: 'bold' },
        },
      ],
      ...(transaction.discount && transaction.discount > 0
        ? [
            [
              {
                content: 'Diskon',
                colSpan: 3,
                styles: { halign: 'center', fontStyle: 'bold' },
              },
              {
                content: `Rp. ${formatNumber(transaction.discount)}`,
                styles: { halign: 'center', fontStyle: 'bold' },
              },
            ],
          ]
        : []),
      ...(transaction.tax && transaction.tax > 0
        ? [
            [
              {
                content: 'Pajak',
                colSpan: 3,
                styles: { halign: 'center', fontStyle: 'bold' },
              },
              {
                content: `Rp. ${formatNumber(transaction.tax)}`,
                styles: { halign: 'center', fontStyle: 'bold' },
              },
            ],
          ]
        : []),
      [
        {
          content: 'Biaya Pengiriman',
          colSpan: 3,
          styles: { halign: 'center', fontStyle: 'bold' },
        },
        { content: ongkirText, styles: { halign: 'center', fontStyle: 'bold' } },
      ],
      [
        {
          content: 'Total',
          colSpan: 3,
          styles: {
            halign: 'center',
            fontStyle: 'bold',
            fillColor: [30, 30, 30],
            textColor: [255, 255, 255],
          },
        },
        {
          content: `Rp. ${formatNumber(transaction.total || subtotalAmount)}`,
          styles: {
            halign: 'center',
            fontStyle: 'bold',
            fillColor: [30, 30, 30],
            textColor: [255, 255, 255],
          },
        },
      ],
    ],
    bodyStyles: {
      fontSize: 9,
      textColor: [0, 0, 0],
      cellPadding: 2.5,
      lineColor: [200, 200, 200],
      lineWidth: 0.3,
    },
    columnStyles: {
      0: { cellWidth: contentWidth * 0.42 },
      1: { cellWidth: contentWidth * 0.16 },
      2: { cellWidth: contentWidth * 0.21 },
      3: { cellWidth: contentWidth * 0.21 },
    },
    margin: { left: margin, right: margin },
    tableLineWidth: 0.3,
    tableLineColor: [200, 200, 200],
    theme: 'grid',
  })

  yPos = doc.lastAutoTable.finalY + 8

  // ============= 5. INFORMASI PEMBAYARAN & ALAMAT TOKO — Stacked dengan border =============
  const bottomBoxWidth = contentWidth
  const boxFillColor = [220, 220, 220]
  const boxPadding = 3
  const lineHeight = 4.5

  // --- Box: Informasi Pembayaran ---
  let paymentYPos = yPos
  let paymentBoxHeight = 6 // header height

  // Hitung tinggi box berdasarkan konten
  if (transaction.payment) {
    paymentBoxHeight += lineHeight * 3 // Metode, Status, Jumlah
    if (transaction.payment.amount > 0) {
      paymentBoxHeight += lineHeight
    }
  } else {
    paymentBoxHeight += lineHeight
  }
  paymentBoxHeight += 2 // bottom padding

  // Background box
  doc.setFillColor(boxFillColor[0], boxFillColor[1], boxFillColor[2])
  doc.rect(margin, paymentYPos, bottomBoxWidth, paymentBoxHeight, 'F')

  // Border soft dan profesional
  doc.setDrawColor(180, 180, 180)
  doc.setLineWidth(0.2)
  doc.rect(margin, paymentYPos, bottomBoxWidth, paymentBoxHeight, 'S')

  // Header diperbesar dan lebih tegas
  doc.setFontSize(11)
  doc.setFont(undefined, 'bold')
  doc.setTextColor(0, 0, 0)
  doc.text('Informasi Pembayaran:', margin + boxPadding, paymentYPos + 4.5)

  paymentYPos += 6
  doc.setFontSize(9)
  doc.setFont(undefined, 'bold')

  if (transaction.payment) {
    doc.text(
      `Metode: ${paymentMethod(transaction.payment.method)}`,
      margin + boxPadding,
      paymentYPos + lineHeight
    )
    paymentYPos += lineHeight
    doc.text(
      `Status: ${paymentStatus(transaction.payment.status)}`,
      margin + boxPadding,
      paymentYPos + lineHeight
    )
    paymentYPos += lineHeight
    if (transaction.payment.amount > 0) {
      doc.text(
        `Jumlah Dibayar: Rp. ${formatNumber(transaction.payment.amount)}`,
        margin + boxPadding,
        paymentYPos + lineHeight
      )
      paymentYPos += lineHeight
    }
  } else {
    doc.text('Belum ada pembayaran', margin + boxPadding, paymentYPos + lineHeight)
    paymentYPos += lineHeight
  }

  yPos += paymentBoxHeight + 5

  // --- Box: Alamat Toko / Pengembalian ---
  let addressYPos = yPos

  const addressParts = []
  if (branch?.address) addressParts.push(branch.address)
  if (branch?.city) addressParts.push(branch.city)

  // Gabungkan province dan postal_code
  let provincePostal = ''
  if (branch?.province) provincePostal += branch.province
  if (branch?.postal_code) {
    provincePostal += (provincePostal ? ' ' : '') + branch.postal_code
  }
  if (provincePostal) addressParts.push(provincePostal)

  const storeAddress = addressParts.length > 0 ? addressParts.join(',\n') : 'Belum ada alamat'
  const addressLines = doc.splitTextToSize(storeAddress, bottomBoxWidth - boxPadding * 2)

  const addressBoxHeight = 6 + addressLines.length * lineHeight + 2

  // Background box
  doc.setFillColor(boxFillColor[0], boxFillColor[1], boxFillColor[2])
  doc.rect(margin, addressYPos, bottomBoxWidth, addressBoxHeight, 'F')

  // Border soft dan profesional
  doc.setDrawColor(180, 180, 180)
  doc.setLineWidth(0.2)
  doc.rect(margin, addressYPos, bottomBoxWidth, addressBoxHeight, 'S')

  // Header diperbesar dan lebih tegas
  doc.setFontSize(11)
  doc.setFont(undefined, 'bold')
  doc.setTextColor(0, 0, 0)
  doc.text('Alamat Toko / Pengembalian:', margin + boxPadding, addressYPos + 4.5)

  addressYPos += 6
  doc.setFontSize(9)
  doc.setFont(undefined, 'bold')

  // Address content dengan text lebih tegas
  addressLines.forEach((line, index) => {
    doc.text(line, margin + boxPadding, addressYPos + (index + 1) * lineHeight)
  })

  // ============= 6. FOOTER =============
  doc.setFontSize(9)
  doc.setFont(undefined, 'bold')
  doc.setTextColor(0, 0, 0)
  doc.text('Terimakasih sudah berbelanja!', pageWidth / 2, pageHeight - 10, { align: 'center' })

  return doc
}

/**
 * Generate dan download invoice PDF
 */
export function generateAndDownloadInvoicePDF(transaction, branch, company) {
  const doc = generateInvoicePDF(transaction, branch, company)
  const fileName = `Invoice-${transaction.transaction_number}.pdf`
  doc.save(fileName)
}

/**
 * Generate invoice PDF dan return blob URL untuk preview
 */
export function generateInvoicePDFBlob(transaction, branch, company) {
  const doc = generateInvoicePDF(transaction, branch, company)
  return doc.output('bloburl')
}

// ============= Helper Functions =============

function formatDateLocal(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  const day = date.getDate()
  const months = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
  ]
  const month = months[date.getMonth()]
  const year = date.getFullYear()
  return `${day} ${month} ${year}`
}

function formatNumber(num) {
  if (num === null || num === undefined) return '0'
  return Number(num).toLocaleString('id-ID')
}

function calculateSubtotal(transaction) {
  const details = transaction.details || []
  return details.reduce((sum, item) => sum + (item.subtotal || 0), 0)
}

function customerName(customer) {
  if (!customer) return 'Penjualan Umum'
  if (typeof customer === 'string') return customer
  return customer.name || 'Penjualan Umum'
}

function getCustomerAddress(transaction) {
  if (transaction.customer_detail?.address) {
    return transaction.customer_detail.address
  }
  if (typeof transaction.customer === 'object' && transaction.customer?.address) {
    return transaction.customer.address
  }
  return ''
}

function getCustomerPhone(transaction) {
  if (transaction.customer_detail?.phone) {
    return transaction.customer_detail.phone
  }
  if (typeof transaction.customer === 'object' && transaction.customer?.telp) {
    return transaction.customer.telp
  }
  return ''
}

function paymentMethod(method) {
  const methods = {
    cash: 'Tunai',
    card: 'Kartu',
    transfer: 'Transfer',
  }
  return methods[method] || method || '-'
}

function paymentStatus(status) {
  const statuses = {
    paid: 'Lunas',
    unpaid: 'Belum Lunas',
    partial: 'Dipartial',
  }
  return statuses[status] || status || '-'
}
