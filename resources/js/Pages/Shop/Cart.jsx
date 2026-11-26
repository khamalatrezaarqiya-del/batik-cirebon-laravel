import { Link } from '@inertiajs/react'
import { useEffect, useMemo, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import { ProductImage } from '../../Components/Motif.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import CheckoutSteps from '../../Components/CheckoutSteps.jsx'
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
  const totalQty = items.reduce((s, i) => s + i.qty, 0)

  if (!items.length) {
    return (
      <ShopLayout title={t('nav_cart')}>
        <div className="section">
          <SectionTitle>{t('nav_cart')}</SectionTitle>
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
        <CheckoutSteps current={1} />
        <SectionTitle subtitle={`${totalQty} item siap untuk dikonfirmasi.`}>{t('nav_cart')}</SectionTitle>

        <div className="checkout-layout">
          <div className="cart-list">
            {items.map((item) => (
              <div className="cart-item" key={item.key}>
                <Link href={`/produk/${item.slug}`} className="cart-item-thumb">
                  <ProductImage product={item} />
                </Link>
                <div className="grow">
                  <Link href={`/produk/${item.slug}`} className="cart-item-title">
                    <strong>{item.nama}</strong>
                  </Link>
                  <div className="cart-item-sub">
                    {item.size && <span className="drawer-pill">{t('size_label')} {item.size}</span>}
                    <span className="muted-text">{formatRupiah(item.harga)} / pcs</span>
                  </div>
                </div>
                <div className="cart-item-controls">
                  <div className="quantity">
                    <button type="button" onClick={() => setCart(changeQty(item.key, -1))} aria-label="Kurangi">−</button>
                    <span>{item.qty}</span>
                    <button type="button" onClick={() => setCart(changeQty(item.key, 1))} aria-label="Tambah" disabled={item.qty >= MAX_QTY}>+</button>
                  </div>
                  <strong className="price">{formatRupiah(item.lineTotal)}</strong>
                  <button type="button" className="remove" onClick={() => setCart(removeItem(item.key))}>{t('remove')}</button>
                </div>
                {item.qty >= MAX_QTY && <span className="qty-note">{t('qty_max')}</span>}
              </div>
            ))}
          </div>

          <aside className="summary">
            <h2>{t('order_summary')}</h2>
            <p>
              <span>{t('subtotal')} ({totalQty} item)</span>
              <strong>{formatRupiah(total)}</strong>
            </p>
            <p>
              <span>{t('shipping_label')}</span>
              <span className="muted-text">Dikonfirmasi WA</span>
            </p>
            <hr />
            <p className="total-row">
              <span>{t('cart_total')}</span>
              <strong>{formatRupiah(total)}</strong>
            </p>
            <small>{t('shipping_note')}</small>
            <Link className="button button-lg" href="/checkout" style={{ marginTop: 8 }}>{t('checkout')}</Link>
            <Link className="button ghost" href="/katalog">{t('continue_shopping')}</Link>
          </aside>
        </div>
      </div>
    </ShopLayout>
  )
}
