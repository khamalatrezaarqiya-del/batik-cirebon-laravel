// Keranjang lokal: { k: 'id#size', id, size, qty, meta? }
const KEY = 'batik_cart'
export const MAX_QTY = 10
export const lineKey = (id, size) => (size ? `${id}#${size}` : String(id))

export function readCart() {
  try {
    const raw = localStorage.getItem(KEY)
    return raw ? JSON.parse(raw).map((i) => ({ ...i, k: i.k || lineKey(i.id, i.size) })) : []
  } catch { return [] }
}

export function writeCart(cart) {
  try { localStorage.setItem(KEY, JSON.stringify(cart)) } catch { /* ignore */ }
  window.dispatchEvent(new Event('cart:change'))
  return cart
}

export function notifyToast(message, action = null) {
  window.dispatchEvent(new CustomEvent('shop:toast', { detail: { message, action } }))
}

export function addToCart(id, qty = 1, size = '', product = null) {
  const cart = readCart()
  const k = lineKey(id, size)
  const meta = product
    ? {
        nama: product.nama,
        slug: product.slug,
        harga: product.harga,
        motif: product.motif,
        c1: product.c1,
        c2: product.c2,
        foto: product.foto || null,
        kategori_nama: product.kategori_nama || '',
      }
    : null

  const found = cart.find((i) => i.k === k)
  const next = found
    ? cart.map((i) => (i.k === k ? { ...i, qty: Math.min(i.qty + qty, MAX_QTY), meta: meta || i.meta } : i))
    : [...cart, { k, id, size, qty: Math.min(qty, MAX_QTY), ...(meta ? { meta } : {}) }]

  writeCart(next)
  if (product?.nama) {
    notifyToast(`${product.nama}${size ? ` (${size})` : ''} ditambahkan ke keranjang.`, {
      label: 'Lihat Keranjang',
      href: '/keranjang',
      openDrawer: true,
    })
  }
  return next
}

export function changeQty(k, delta) {
  return writeCart(
    readCart()
      .map((i) => (i.k === k ? { ...i, qty: Math.min(i.qty + delta, MAX_QTY) } : i))
      .filter((i) => i.qty > 0),
  )
}

export function removeItem(k) { return writeCart(readCart().filter((i) => i.k !== k)) }
export function clearCart() { return writeCart([]) }

/** Ambil harga, nama, stok, dan foto terbaru dari server agar Mini Cart selalu sinkron dengan Admin. */
export async function syncCartFromServer() {
  const current = readCart()
  if (!current.length) return current

  const ids = [...new Set(current.map((i) => i.id).filter(Boolean))]
  if (!ids.length) return current

  try {
    const res = await fetch(`/api/cart-sync?ids=${encodeURIComponent(ids.join(','))}`, {
      headers: { Accept: 'application/json' },
      cache: 'no-store',
    })
    if (!res.ok) return current
    const payload = await res.json()
    const byId = Object.fromEntries((payload.products || []).map((p) => [p.id, p]))

    let changed = false
    const updated = []
    for (const item of current) {
      const fresh = byId[item.id]
      if (!fresh) {
        changed = true
        continue
      }
      const nextMeta = {
        nama: fresh.nama,
        slug: fresh.slug,
        harga: fresh.harga,
        motif: fresh.motif,
        c1: fresh.c1,
        c2: fresh.c2,
        foto: fresh.foto || null,
        kategori_nama: fresh.kategori_nama || '',
      }
      if (
        !item.meta ||
        item.meta.harga !== nextMeta.harga ||
        item.meta.nama !== nextMeta.nama ||
        item.meta.slug !== nextMeta.slug ||
        item.meta.foto !== nextMeta.foto
      ) {
        changed = true
      }
      updated.push({ ...item, meta: nextMeta })
    }

    if (changed) {
      return writeCart(updated)
    }
    return current
  } catch {
    return current
  }
}
