import { useEffect, useRef, useState } from 'react'
import { Link, router } from '@inertiajs/react'
import { formatRupiah } from '../Lib/format.js'
import { ProductImage } from './Motif.jsx'

export default function ProductCard({ product, t, addToCart, maxId = 26 }) {
  const [done, setDone] = useState(false)
  const timer = useRef(null)

  useEffect(() => () => clearTimeout(timer.current), [])

  const isOutOfStock = typeof product.stok === 'number' && product.stok <= 0
  const isNew = product.id > maxId - 4
  const isBest = product.id <= 4
  const badge = isOutOfStock ? t('out_of_stock') : isNew ? t('badge_new') : isBest ? t('badge_best') : ''
  const needsSize = product.kategori === 'pakaian'

  const quickAdd = () => {
    if (isOutOfStock) return
    if (needsSize) return router.visit(`/produk/${product.slug}`)
    addToCart(product.id)
    setDone(true)
    clearTimeout(timer.current)
    timer.current = setTimeout(() => setDone(false), 1200)
  }

  return (
    <article className="card reveal">
      <Link className="card-media" href={`/produk/${product.slug}`} aria-label={product.nama}>
        <ProductImage product={product} />
        {badge && (
          <span className={`card-badge ${isOutOfStock ? 'sold' : isNew ? 'new' : 'best'}`}>{badge}</span>
        )}
      </Link>

      <div className="card-body">
        <span className="card-cat">{product.kategori_nama || t(product.kategori)}</span>
        <Link className="card-title" href={`/produk/${product.slug}`}>{product.nama}</Link>
        <div className="card-foot">
          <span className="price">{formatRupiah(product.harga)}</span>
          <button
            type="button"
            className={`card-add${done ? ' done' : ''}`}
            onClick={quickAdd}
            disabled={isOutOfStock}
            aria-label={needsSize ? `${t('size_label')} ${product.nama}` : `${t('add_to_cart')} ${product.nama}`}
            title={isOutOfStock ? t('out_of_stock') : needsSize ? t('size_label') : t('add_to_cart')}
          >
            {done ? (
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <path d="m5 13 4.5 4.5L19 7" />
              </svg>
            ) : (
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14" />
              </svg>
            )}
          </button>
        </div>
      </div>
    </article>
  )
}
