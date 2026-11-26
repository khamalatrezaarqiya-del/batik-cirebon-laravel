// Nomor WA default (bisa diubah langsung dari Admin -> Penjualan -> Rekening & QRIS).
export const WA_NUMBER = 'xxxxxxxxxxxx'

export const WA = `https://wa.me/${WA_NUMBER}`

export const waLink = (pesan, customNumber = '') => {
  const clean = String(customNumber || '').replace(/\D/g, '')
  const target = clean.length >= 9 ? (clean.startsWith('0') ? '62' + clean.slice(1) : clean) : WA_NUMBER
  return `https://wa.me/${target}?text=${encodeURIComponent(pesan || 'Halo, saya mau tanya soal koleksi Batik Daffa.')}`
}
