import { useEffect, useRef, useState } from 'react'
import { Link } from '@inertiajs/react'
import { formatRupiah } from '../Lib/format.js'
import { ProductImage } from './Motif.jsx'

export default function ProductCard({ product, t, addToCart, maxId = 26 }) {
  const navigate = (url) => { window.location.href = url }
  const [done, setDone] = useState(false)
  const timer = useRef(null)

  useEffect(() => () => clearTimeout(timer.current), [])

  // Empat produk terakhir = barang baru, empat pertama = paling laku.
  const isNew = product.id > maxId - 4
  const isBest = product.id <= 4
  const badge = isNew ? t('badge_new') : isBest ? t('badge_best') : ''
  // Pakaian butuh ukuran — jangan tambah ke keranjang tanpa ukuran.
  const needsSize = product.kategori === 'pakaian'

  const quickAdd = () => {
    if (needsSize) return navigate(`/produk/${product.slug}`)
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
          <span className={`card-badge ${isNew ? 'new' : 'best'}`}>{badge}</span>
        )}
      </Link>

      <div className="card-body">
        <span className="card-cat">{t(product.kategori)}</span>
        <Link className="card-title" href={`/produk/${product.slug}`}>{product.nama}</Link>
        <div className="card-foot">
          <span className="price">{formatRupiah(product.harga)}</span>
          <button
            className={`card-add${done ? ' done' : ''}`}
            onClick={quickAdd}
            aria-label={needsSize ? `${t('size_label')} — ${product.nama}` : `${t('add_to_cart')} — ${product.nama}`}
            title={needsSize ? t('size_label') : t('add_to_cart')}
          >
            {done ? (
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.6" strokeLinecap="round" strokeLinejoin="round">
                <path d="m5 13 4.5 4.5L19 7" />
              </svg>
            ) : (
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round">
                <path d="M12 5v14M5 12h14" />
              </svg>
            )}
          </button>
        </div>
      </div>
    </article>
  )
}
