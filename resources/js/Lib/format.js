export const formatRupiah = (n) =>
  'Rp ' + Math.round(Number(n) || 0).toLocaleString('id-ID')
