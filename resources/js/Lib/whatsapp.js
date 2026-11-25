// Nomor WA dari client belum ada (tunggu wawancara). Ganti di SATU tempat ini saja.
export const WA_NUMBER = 'xxxxxxxxxxxx'

export const WA = `https://wa.me/${WA_NUMBER}`

export const waLink = (pesan) =>
  `${WA}?text=${encodeURIComponent(pesan || 'Halo, saya mau tanya soal koleksi Batik Daffa.')}`
