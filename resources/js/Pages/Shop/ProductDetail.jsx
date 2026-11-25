import { Link, router } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import { ProductImage } from '../../Components/Motif.jsx'
import ProductCard from '../../Components/ProductCard.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { addToCart, MAX_QTY } from '../../Lib/cart.js'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const SIZES = ['S', 'M', 'L', 'XL']

export default function ProductDetail({ product, related = [] }) {
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)
  const [size, setSize] = useState('')
  const [qty, setQty] = useState(1)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const hasSizes = product.kategori === 'pakaian'
  const availableStock = typeof product.stok === 'number' ? product.stok : MAX_QTY
  const maxAllowed = Math.max(1, Math.min(MAX_QTY, availableStock))
  const isOutOfStock = availableStock <= 0
  const maxId = Math.max(product.id, ...related.map((p) => p.id), 26)

  const add = () => {
    if (isOutOfStock) return
    addToCart(product.id, qty, hasSizes ? size : '')
    router.visit('/keranjang')
  }

  const askOnWa = () =>
    waLink(`Halo Batik Daffa, saya mau tanya produk *${product.nama}*${hasSizes && size ? ` ukuran ${size}` : ''} (jumlah ${qty}).`)

  return (
    <ShopLayout title={product.nama}>
      <div className="section">
        <p className="crumb">
          <Link href="/">{t('nav_home')}</Link> / <Link href="/katalog">{t('nav_catalog')}</Link> /{' '}
          <Link href={`/katalog?cat=${product.kategori}`}>{product.kategori_nama}</Link> / <span>{product.nama}</span>
        </p>

        <div className="detail">
          <div className="detail-media">
            <ProductImage product={product} />
          </div>

          <div className="detail-info">
            <p className="category">{product.kategori_nama}</p>
            <h1>{product.nama}</h1>
            <div className="detail-price">{formatRupiah(product.harga)}</div>
            <p className="stock-line">
              {isOutOfStock ? (
                <span className="status status-batal">{t('out_of_stock')}</span>
              ) : (
                <span>{t('stock_left')}: <strong>{availableStock}</strong></span>
              )}
            </p>
            <p className="description">{product.deskripsi || t('product_desc')}</p>

            {hasSizes && (
              <div className="option-row">
                <span className="option-label">{t('size_label')}</span>
                <div className="option-group">
                  {SIZES.map((s) => (
                    <button type="button" key={s} className={size === s ? 'chip active' : 'chip'} onClick={() => setSize(s)} aria-pressed={size === s}>
                      {s}
                    </button>
                  ))}
                </div>
              </div>
            )}

            <div className="option-row">
              <span className="option-label">{t('qty')}</span>
              <div className="quantity">
                <button type="button" onClick={() => setQty((q) => Math.max(1, q - 1))} aria-label="Kurangi" disabled={isOutOfStock}>−</button>
                <span>{qty}</span>
                <button type="button" onClick={() => setQty((q) => Math.min(maxAllowed, q + 1))} aria-label="Tambah" disabled={isOutOfStock || qty >= maxAllowed}>+</button>
              </div>
              {!isOutOfStock && qty >= maxAllowed && <span className="qty-note">{t('qty_max')}</span>}
            </div>

            <div className="detail-buy">
              <button type="button" className="button" onClick={add} disabled={isOutOfStock || (hasSizes && !size)}>
                {isOutOfStock ? t('out_of_stock') : t('add_to_cart')}
              </button>
              <a className="button ghost" href={askOnWa()} target="_blank" rel="noreferrer">WhatsApp</a>
            </div>
            {!isOutOfStock && hasSizes && !size && <p className="qty-note" style={{ marginTop: 8 }}>{t('size_required')}</p>}

            <div className="detail-meta">
              <p>{t('detail_made')}</p>
              <p>{t('detail_care')}</p>
              <p>{t('shipping_note')}</p>
              <p><Link className="text-link" href="/bantuan/ukuran">{t('help_size')}</Link></p>
            </div>

            <p style={{ marginTop: 20 }}>
              <Link className="back-link" href="/katalog">{t('back_to_catalog')}</Link>
            </p>
          </div>
        </div>

        {related.length > 0 && (
          <>
            <SectionTitle action={<Link className="text-link" href={`/katalog?cat=${product.kategori}`}>{t('see_all')}</Link>}>
              {product.kategori_nama}
            </SectionTitle>
            <div className="grid">
              {related.map((p) => (
                <ProductCard key={p.id} product={p} t={t} addToCart={addToCart} maxId={maxId} />
              ))}
            </div>
          </>
        )}
      </div>
    </ShopLayout>
  )
}
