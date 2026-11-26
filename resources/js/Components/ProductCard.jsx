import { useEffect, useRef, useState } from 'react'
import { Link, router } from '@inertiajs/react'
import { formatRupiah } from '../Lib/format.js'
import { ProductImage } from './Motif.jsx'

export default function ProductCard({
  product,
  t,
  addToCart,
  index = 0,
}) {
  const [done, setDone] = useState(false)
  const timer = useRef(null)

  useEffect(() => () => clearTimeout(timer.current), [])

  const isOutOfStock = typeof product.stok === 'number' && product.stok <= 0
  const showBest = !isOutOfStock && Boolean(product.is_best_seller)
  const showNew = !isOutOfStock && Boolean(product.is_new)
  const needsSize = product.kategori === 'pakaian'

  const quickAdd = () => {
    if (isOutOfStock) return
    if (needsSize) return router.visit(`/produk/${product.slug}`)
    addToCart(product.id, 1, '', product)
    setDone(true)
    clearTimeout(timer.current)
    timer.current = setTimeout(() => setDone(false), 1400)
  }

  return (
    <article
      className="card reveal"
      style={{ '--stagger': `${Math.min(index, 7) * 40}ms` }}
    >
      <div className="card-media-wrap">
        <Link className="card-media" href={`/produk/${product.slug}`} aria-label={product.nama}>
          <ProductImage product={product} />

          {(isOutOfStock || showBest || showNew) && (
            <div className="card-badges">
              {isOutOfStock ? (
                <span className="card-badge sold">{t('out_of_stock')}</span>
              ) : (
                <>
                  {showBest && <span className="card-badge best">{t('badge_best')}</span>}
                  {showNew && <span className="card-badge new">{t('badge_new')}</span>}
                </>
              )}
            </div>
          )}
        </Link>

        <button
          type="button"
          className={`card-add${done ? ' done' : ''}`}
          onClick={quickAdd}
          disabled={isOutOfStock}
          aria-label={needsSize ? `${t('size_label')} ${product.nama}` : `${t('add_to_cart')} ${product.nama}`}
          title={isOutOfStock ? t('out_of_stock') : needsSize ? t('size_label') : t('add_to_cart')}
        >
          {done ? (
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="m5 13 4.5 4.5L19 7" />
            </svg>
          ) : (
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" aria-hidden="true">
              <path d="M12 5v14M5 12h14" />
            </svg>
          )}
        </button>
      </div>

      <div className="card-body">
        <Link className="card-title" href={`/produk/${product.slug}`}>{product.nama}</Link>
        <span className="price">{formatRupiah(product.harga)}</span>
      </div>
    </article>
  )
}
