// Keranjang lokal: { key: 'id#size', id, size, qty }
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

export function addToCart(id, qty = 1, size = '') {
  const cart = readCart()
  const k = lineKey(id, size)
  const found = cart.find((i) => i.k === k)
  const next = found
    ? cart.map((i) => (i.k === k ? { ...i, qty: Math.min(i.qty + qty, MAX_QTY) } : i))
    : [...cart, { k, id, size, qty: Math.min(qty, MAX_QTY) }]
  return writeCart(next)
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
