import { Link } from '@inertiajs/react'
import { useEffect, useMemo, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import { ProductImage } from '../../Components/Motif.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { changeQty, readCart, removeItem, MAX_QTY } from '../../Lib/cart.js'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'

const IconCartEmpty = () => (
  <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
    <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 8H6" />
    <circle cx="10" cy="20" r="1.4" />
    <circle cx="18" cy="20" r="1.4" />
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
              {formatRupiah(item.harga)}
            </div>
            <div className="quantity">
              <button onClick={() => setCart(changeQty(item.key, -1))} aria-label="Kurangi">−</button>
              <span>{item.qty}</span>
              <button onClick={() => setCart(changeQty(item.key, 1))} aria-label="Tambah" disabled={item.qty >= MAX_QTY}>+</button>
            </div>
            {item.qty >= MAX_QTY && <span className="qty-note">{t('qty_max')}</span>}
            <button className="remove" onClick={() => setCart(removeItem(item.key))}>{t('remove')}</button>
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
