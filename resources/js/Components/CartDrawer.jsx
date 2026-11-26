import { useEffect, useRef } from 'react'
import { Link } from '@inertiajs/react'
import { ProductImage } from './Motif.jsx'
import { changeQty, MAX_QTY, removeItem, syncCartFromServer } from '../Lib/cart.js'
import { formatRupiah } from '../Lib/format.js'

const IconClose = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
    <path d="M18 6 6 18M6 6l12 12" />
  </svg>
)

const IconBag = () => (
  <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
    <path d="M3 6h18" />
    <path d="M16 10a4 4 0 0 1-8 0" />
  </svg>
)

export default function CartDrawer({ open, onClose, cart = [], t }) {
  const closeRef = useRef(null)

  useEffect(() => {
    if (!open) return
    syncCartFromServer()
    const prev = document.activeElement
    closeRef.current?.focus()
    const onKey = (e) => {
      if (e.key === 'Escape') onClose()
    }
    document.body.style.overflow = 'hidden'
    window.addEventListener('keydown', onKey)
    return () => {
      document.body.style.overflow = ''
      window.removeEventListener('keydown', onKey)
      if (prev && typeof prev.focus === 'function') prev.focus()
    }
  }, [open, onClose])

  if (!open) return null

  const totalQty = cart.reduce((s, i) => s + i.qty, 0)
  const hasAllPrices = cart.length > 0 && cart.every((i) => typeof i.meta?.harga === 'number')
  const subtotal = hasAllPrices ? cart.reduce((s, i) => s + i.meta.harga * i.qty, 0) : null

  return (
    <div className="drawer-backdrop" onClick={onClose} role="presentation">
      <aside
        className="drawer-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="drawer-cart-title"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="drawer-head">
          <div>
            <h2 id="drawer-cart-title">{t('nav_cart')}</h2>
            <span className="drawer-sub">{totalQty} {t('label_items').toLowerCase()}</span>
          </div>
          <button
            ref={closeRef}
            type="button"
            className="drawer-close"
            onClick={onClose}
            aria-label="Tutup keranjang"
          >
            <IconClose />
          </button>
        </div>

        <div className="drawer-body">
          {!cart.length ? (
            <div className="drawer-empty">
              <div className="empty-icon"><IconBag /></div>
              <p>{t('cart_empty')}</p>
              <Link className="button" href="/katalog" onClick={onClose}>
                {t('continue_shopping')}
              </Link>
            </div>
          ) : (
            <div className="drawer-list">
              {cart.map((item) => {
                const meta = item.meta
                return (
                  <div className="drawer-item" key={item.k}>
                    <div className="drawer-thumb">
                      {meta ? (
                        <ProductImage product={meta} />
                      ) : (
                        <div className="drawer-thumb-ph" aria-hidden="true">BD</div>
                      )}
                    </div>
                    <div className="drawer-info">
                      <div className="drawer-item-top">
                        {meta?.slug ? (
                          <Link href={`/produk/${meta.slug}`} className="drawer-name" onClick={onClose}>
                            {meta.nama}
                          </Link>
                        ) : (
                          <span className="drawer-name">Produk #{item.id}</span>
                        )}
                        <button
                          type="button"
                          className="drawer-remove"
                          onClick={() => removeItem(item.k)}
                          aria-label={`${t('remove')} ${meta?.nama || ''}`}
                        >
                          {t('remove')}
                        </button>
                      </div>
                      <div className="drawer-meta">
                        {item.size && <span className="drawer-pill">{t('size_label')} {item.size}</span>}
                        {typeof meta?.harga === 'number' && (
                          <span className="price">{formatRupiah(meta.harga * item.qty)}</span>
                        )}
                      </div>
                      <div className="quantity quantity-sm">
                        <button type="button" onClick={() => changeQty(item.k, -1)} aria-label="Kurangi">−</button>
                        <span>{item.qty}</span>
                        <button type="button" onClick={() => changeQty(item.k, 1)} aria-label="Tambah" disabled={item.qty >= MAX_QTY}>+</button>
                      </div>
                    </div>
                  </div>
                )
              })}
            </div>
          )}
        </div>

        {cart.length > 0 && (
          <div className="drawer-foot">
            {subtotal !== null && (
              <div className="drawer-total-row">
                <span>{t('subtotal')}</span>
                <strong>{formatRupiah(subtotal)}</strong>
              </div>
            )}
            <p className="drawer-note">{t('shipping_note')}</p>
            <div className="drawer-actions">
              <Link className="button" href="/checkout" onClick={onClose}>
                {t('checkout')}
              </Link>
              <Link className="button ghost" href="/keranjang" onClick={onClose}>
                Buka Halaman Keranjang
              </Link>
            </div>
          </div>
        )}
      </aside>
    </div>
  )
}
