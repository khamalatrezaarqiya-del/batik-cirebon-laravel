// Harga belum dikonfirmasi client: angka tetap dipakai untuk hitung keranjang,
// hanya tampilannya yang disamarkan. Ubah ke false setelah harga final.
const PRICE_PLACEHOLDER = true

export const formatRupiah = (n) =>
  PRICE_PLACEHOLDER
    ? 'Rp ' + Number(n).toLocaleString('id-ID').replace(/\d/g, 'x').replace(/x+/g, '---')
    : 'Rp ' + Number(n).toLocaleString('id-ID')
