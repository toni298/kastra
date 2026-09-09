import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'
import * as XLSX from 'xlsx'

export function formatCurrency(value) {
  const sign = value < 0 ? '-' : ''
  return `${sign}Rp ${Math.abs(value).toLocaleString('id-ID')}`
}

function buildExportFilename(report, extension) {
  const title = (report?.title || 'laporan')
    .trim()
    .replace(/[^\p{L}\p{N}\s\-_]+/gu, '')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\s+/g, '-')

  const from = String(report?.period?.from || 'tanggal')
    .replaceAll('/', '-')
    .replaceAll('\\', '-')
  const to = String(report?.period?.to || 'tanggal')
    .replaceAll('/', '-')
    .replaceAll('\\', '-')

  return report?.filename || `${title}_${from}_${to}.${extension}`
}

function formatPeriodDate(value) {
  const match = String(value ?? '').match(/^(\d{4})-(\d{2})-(\d{2})$/)
  if (!match) return String(value ?? '-')

  const [, year, month, day] = match
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'UTC',
  }).format(new Date(`${year}-${month}-${day}T00:00:00Z`))
}

export function exportToPdf(report) {
  const doc = new jsPDF({
    orientation: report.pdfOptions?.orientation || 'portrait',
    unit: report.pdfOptions?.unit || 'mm',
    format: report.pdfOptions?.format || 'a4',
  })
  const pageWidth = doc.internal.pageSize.getWidth()
  let y = 20

  doc.setFontSize(16)
  doc.setFont('helvetica', 'bold')
  doc.text(report.title, 14, y)

  y += 8
  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.text(
    `Periode: ${formatPeriodDate(report.period.from)} \u2013 ${formatPeriodDate(report.period.to)}`,
    14,
    y
  )

  y += 10

  if (report.sections) {
    report.sections.forEach((section) => {
      if (y > 270) {
        doc.addPage()
        y = 20
      }
      doc.setFont('helvetica', 'bold')
      doc.setFontSize(11)
      doc.text(section.label, 14, y)
      y += 6

      doc.setFont('helvetica', 'normal')
      doc.setFontSize(10)
      section.rows.forEach((row) => {
        if (y > 275) {
          doc.addPage()
          y = 20
        }
        doc.text(row[0], 18, y)
        doc.text(formatCurrency(row[1]), pageWidth - 14, y, { align: 'right' })
        y += 5
      })

      y += 2
      doc.setFont('helvetica', 'bold')
      doc.text(`Total ${section.label}`, 18, y)
      doc.text(formatCurrency(section.total), pageWidth - 14, y, { align: 'right' })
      y += 8
    })
  }

  if (report.columns && report.rows) {
    if (report.pdfTable) {
      autoTable(doc, {
        startY: y,
        head: [report.columns],
        body: report.rows.map((row) =>
          row.map((cell, index) => {
            if (typeof cell === 'number') return formatCurrency(cell)
            return report.pdfTable.formatters?.[index]
              ? report.pdfTable.formatters[index](cell)
              : String(cell)
          })
        ),
        margin: { left: 14, right: 14 },
        tableWidth: 'auto',
        styles: {
          font: 'helvetica',
          fontSize: 8,
          cellPadding: 2.5,
          overflow: 'ellipsize',
          lineColor: [210, 214, 220],
          lineWidth: 0.2,
          textColor: [30, 41, 59],
          ...report.pdfTable.styles,
        },
        headStyles: {
          fillColor: [16, 185, 129],
          textColor: [255, 255, 255],
          fontStyle: 'bold',
          ...report.pdfTable.headStyles,
        },
        columnStyles: report.pdfTable.columnStyles,
      })
      y = doc.lastAutoTable.finalY + 8
    } else {
      doc.setFont('helvetica', 'bold')
      doc.setFontSize(10)
      const colWidth = (pageWidth - 28) / report.columns.length
      report.columns.forEach((col, i) => {
        doc.text(col, 14 + i * colWidth, y)
      })
      y += 5

      doc.setFont('helvetica', 'normal')
      report.rows.forEach((row) => {
        if (y > 275) {
          doc.addPage()
          y = 20
        }
        row.forEach((cell, i) => {
          const text = typeof cell === 'number' ? formatCurrency(cell) : String(cell)
          doc.text(text, 14 + i * colWidth, y)
        })
        y += 5
      })
      y += 5
    }
  }

  if (report.summary) {
    if (y > 260) {
      doc.addPage()
      y = 20
    }
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(11)
    doc.text('Ringkasan', 14, y)
    y += 6
    doc.setFontSize(10)
    report.summary.forEach((row, index) => {
      if (y > 275) {
        doc.addPage()
        y = 20
      }
      doc.text(row[0], 18, y)
      const value =
        report.summaryCurrency?.[index] === false ? String(row[1]) : formatCurrency(row[1])
      doc.text(value, pageWidth - 14, y, { align: 'right' })
      y += 5
    })
  }

  doc.save(buildExportFilename(report, 'pdf'))
}

export function exportToExcel(report) {
  const currencyFormat = '"Rp "#,##0'
  const data = [[report.title], [`Periode: ${report.period.from} - ${report.period.to}`], []]

  if (report.sections) {
    report.sections.forEach((section) => {
      data.push([section.label])
      section.rows.forEach((row) => {
        data.push([row[0], row[1]])
      })
      data.push([`Total ${section.label}`, section.total], [])
    })
  }

  if (report.columns) {
    data.push(report.columns)
    report.rows.forEach((row) => {
      data.push(row)
    })
    data.push([])
  }

  if (report.summary) {
    data.push(['Ringkasan'])
    report.summary.forEach((row, index) => {
      data.push([row[0], report.summaryCurrency?.[index] === false ? String(row[1]) : row[1]])
    })
  }

  const ws = XLSX.utils.aoa_to_sheet(data)

  const maxRows = data.length
  const maxCols = Math.max(...data.map((row) => row.length))
  const cellWidths = new Array(maxCols).fill(0)

  for (let r = 0; r < maxRows; r += 1) {
    for (let c = 0; c < maxCols; c += 1) {
      const rawValue = data[r]?.[c]
      if (rawValue === undefined || rawValue === null) continue

      let textLength
      if (typeof rawValue === 'number') {
        textLength = formatCurrency(rawValue).length
      } else {
        textLength = String(rawValue).length
      }

      const isHeaderish =
        r === 0 ||
        (data[r].length === 1 && data[r][0]) ||
        (data[r][0] && String(data[r][0]).includes('Total')) ||
        data[r][0] === 'Ringkasan'

      const weight = isHeaderish ? 1.15 : 1
      const padded = textLength * weight + 4
      const capped = Math.min(padded, 48)
      cellWidths[c] = Math.max(cellWidths[c], capped)
    }
  }

  ws['!cols'] = cellWidths.map((w) => ({ wch: Math.max(10, w) }))

  for (let r = 0; r < maxRows; r += 1) {
    for (let c = 0; c < maxCols; c += 1) {
      const cellAddress = XLSX.utils.encode_cell({ r, c })
      const cell = ws[cellAddress]
      if (!cell) continue

      if (typeof cell.v === 'number') {
        cell.z = currencyFormat
      }
    }
  }

  const range = XLSX.utils.decode_range(ws['!ref'])
  ws['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: range.e.c } }]

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, report.title.substring(0, 31))
  XLSX.writeFile(wb, buildExportFilename(report, 'xlsx'))
}
