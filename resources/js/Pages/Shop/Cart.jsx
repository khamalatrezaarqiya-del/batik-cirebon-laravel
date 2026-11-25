import { Link } from '@inertiajs/react'
import { useEffect, useMemo, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import { ProductImage } from '../../Components/Motif.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { changeQty, readCart, removeItem, MAX_QTY } from '../../Lib/cart.js'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'

const IconCartEmpty = () => (
  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
    <path d="M3 6h18" />
    <path d="M16 10a4 4 0 0 1-8 0" />
  </svg>
)

export default function Cart({ products = [] }) {
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)
  const [cart, setCart] = useState(() => readCart())

  useEffect(() => {
    const sync = () => setCart(readCart())
    window.addEventListener('cart:change', sync)
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => {
      window.removeEventListener('cart:change', sync)
      window.removeEventListener('lang:change', onLang)
    }
  }, [])

  const byId = useMemo(() => Object.fromEntries(products.map((p) => [p.id, p])), [products])
  const items = cart
    .map((i) => {
      const p = byId[i.id]
      return p ? { ...p, key: i.k, size: i.size, qty: i.qty, lineTotal: p.harga * i.qty } : null
    })
    .filter(Boolean)

  const total = items.reduce((s, i) => s + i.lineTotal, 0)

  if (!items.length) {
    return (
      <ShopLayout title={t('nav_cart')}>
        <div className="section">
          <div className="empty">
            <div className="empty-icon"><IconCartEmpty /></div>
            <p>{t('cart_empty')}</p>
            <Link className="button" href="/katalog">{t('continue_shopping')}</Link>
          </div>
        </div>
      </ShopLayout>
    )
  }

  return (
    <ShopLayout title={t('nav_cart')}>
      <div className="section">
        <SectionTitle>{t('nav_cart')}</SectionTitle>
        {items.map((item) => (
          <div className="cart-item" key={item.key}>
            <ProductImage product={item} />
            <div className="grow">
              <strong>{item.nama}</strong>
              {item.size && <span className="cart-size"> · {item.size}</span>}
              <br />
              <span className="muted-text">{formatRupiah(item.harga)}</span>
            </div>
            <div className="quantity">
              <button type="button" onClick={() => setCart(changeQty(item.key, -1))} aria-label="Kurangi">−</button>
              <span>{item.qty}</span>
              <button type="button" onClick={() => setCart(changeQty(item.key, 1))} aria-label="Tambah" disabled={item.qty >= MAX_QTY}>+</button>
            </div>
            {item.qty >= MAX_QTY && <span className="qty-note">{t('qty_max')}</span>}
            <button type="button" className="remove" onClick={() => setCart(removeItem(item.key))}>{t('remove')}</button>
          </div>
        ))}
        <div className="cart-total">
          <span>{t('cart_total')}: {formatRupiah(total)}</span>
          <Link className="button" href="/checkout">{t('checkout')}</Link>
        </div>
      </div>
    </ShopLayout>
  )
}
