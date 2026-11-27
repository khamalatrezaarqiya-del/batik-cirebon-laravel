import { Link, router, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import { ProductImage } from '../../Components/Motif.jsx'
import ProductCard from '../../Components/ProductCard.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import SizeGuideModal from '../../Components/SizeGuideModal.jsx'
import { addToCart, MAX_QTY } from '../../Lib/cart.js'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const SIZES = ['S', 'M', 'L', 'XL', 'XXL']

const MOTIF_NAMES = {
  mega: 'Mega Mendung',
  singaan: 'Singaan',
  wadasan: 'Wadasan',
  truntum: 'Truntum',
  kawung: 'Kawung',
  parang: 'Parang',
  lainnya: 'Pesisiran Cirebon',
}

const BAHAN_NAMES = {
  viscose: 'Viscose',
  doby: 'Doby',
  atbm: 'ATBM (Alat Tenun Bukan Mesin)',
  katun: 'Katun',
}

const TEKNIK_NAMES = {
  tulis: 'Batik Tulis Murni',
  cap: 'Batik Cap',
  kombinasi: 'Kombinasi Cap & Tulis',
}

const GENDER_NAMES = {
  pria: 'Pria',
  wanita: 'Wanita',
  unisex: 'Unisex / Kain',
}

export default function ProductDetail({ product, related = [], reviews = [] }) {
  const { props } = usePage()
  const storeWhatsapp = props?.storeWhatsapp || ''
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)
  const [size, setSize] = useState('')
  const [qty, setQty] = useState(1)
  const [sizeModalOpen, setSizeModalOpen] = useState(false)
  const [sizeWarn, setSizeWarn] = useState(false)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const hasSizes = product.kategori === 'pakaian'
  const availableStock = typeof product.stok === 'number' ? product.stok : MAX_QTY
  const maxAllowed = Math.max(1, Math.min(MAX_QTY, availableStock))
  const isOutOfStock = availableStock <= 0
  const motifLabel = MOTIF_NAMES[product.motif] || product.motif
  const bahanLabel = BAHAN_NAMES[product.bahan] || null
  const teknikLabel = TEKNIK_NAMES[product.teknik] || null
  const genderLabel = GENDER_NAMES[product.gender] || null

  const handleAdd = (goCheckout = false) => {
    if (isOutOfStock) return
    if (hasSizes && !size) {
      setSizeWarn(true)
      return
    }
    setSizeWarn(false)
    addToCart(product.id, qty, hasSizes ? size : '', product)
    if (goCheckout) {
      router.visit('/checkout')
    }
  }

  const askOnWa = () =>
    waLink(
      `Halo Batik Daffa, saya ingin bertanya mengenai produk *${product.nama}*${hasSizes && size ? ` ukuran ${size}` : ''} (jumlah ${qty}) atau penawaran harga grosir/seragam.`,
      storeWhatsapp
    )

  return (
    <ShopLayout title={product.nama}>
      <div className="section">
        <nav className="crumb" aria-label="Breadcrumb">
          <Link href="/">{t('nav_home')}</Link>
          <span aria-hidden="true">/</span>
          <Link href="/katalog">{t('nav_catalog')}</Link>
          <span aria-hidden="true">/</span>
          <Link href={`/katalog?cat=${product.kategori}`}>{product.kategori_nama}</Link>
          <span aria-hidden="true">/</span>
          <span className="crumb-current" aria-current="page">{product.nama}</span>
        </nav>

        <div className="detail">
          <div className="detail-media">
            <ProductImage product={product} />
          </div>

          <div className="detail-info">
            <p className="detail-category">
              {product.kategori_nama}
              {genderLabel && hasSizes ? ` · ${genderLabel}` : ''}
            </p>
            <h1>{product.nama}</h1>
            <div className="detail-price">{formatRupiah(product.harga)}</div>

            {product.reviews_count > 0 && (
              <p className="product-rating-summary">
                <span className="star-inline" aria-hidden="true">★</span>
                <strong>{product.rating_avg}</strong>
                <span>({product.reviews_count} ulasan pembeli)</span>
              </p>
            )}

            <p className="stock-line">
              {isOutOfStock ? (
                <span className="status status-batal">{t('out_of_stock')}</span>
              ) : (
                <span className="stock-ok">
                  {t('in_stock')} · {availableStock} pcs
                </span>
              )}
            </p>

            <p className="description">{product.deskripsi || t('product_desc')}</p>

            {hasSizes && (
              <div className="detail-option-block">
                <div className="detail-option-head">
                  <span className="option-label">
                    {t('size_label')}{genderLabel && product.gender !== 'unisex' ? ` (${genderLabel})` : ''}
                  </span>
                  <button type="button" className="text-link inline-btn" onClick={() => setSizeModalOpen(true)}>
                    {t('help_size')}
                  </button>
                </div>
                <div className="option-group" role="group" aria-label={t('size_label')}>
                  {SIZES.map((s) => (
                    <button
                      type="button"
                      key={s}
                      className={size === s ? 'chip active' : 'chip'}
                      onClick={() => {
                        setSize(s)
                        setSizeWarn(false)
                      }}
                      aria-pressed={size === s}
                    >
                      {s}
                    </button>
                  ))}
                </div>
                {sizeWarn && <p className="qty-note" role="alert" style={{ marginTop: 8 }}>{t('size_required')}</p>}
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
              <button type="button" className="button button-lg" onClick={() => handleAdd(false)} disabled={isOutOfStock}>
                {isOutOfStock ? t('out_of_stock') : t('add_to_cart')}
              </button>
              {!isOutOfStock && (
                <button type="button" className="button ghost button-lg" onClick={() => handleAdd(true)}>
                  Beli Sekarang
                </button>
              )}
            </div>

            <p className="detail-wa-row">
              <a className="text-link" href={askOnWa()} target="_blank" rel="noreferrer">
                Tanyakan produk atau harga grosir (&gt;10 pcs) via WhatsApp
              </a>
            </p>

            <dl className="detail-specs">
              <div className="spec-row"><dt>Kategori</dt><dd>{product.kategori_nama}</dd></div>
              <div className="spec-row"><dt>Motif</dt><dd>{motifLabel}</dd></div>
              {bahanLabel && <div className="spec-row"><dt>Jenis Bahan</dt><dd>{bahanLabel}</dd></div>}
              {teknikLabel && <div className="spec-row"><dt>Teknik Produksi</dt><dd>{teknikLabel}</dd></div>}
              {genderLabel && <div className="spec-row"><dt>Model</dt><dd>{genderLabel}</dd></div>}
              <div className="spec-row"><dt>Workshop</dt><dd>{t('detail_made')}</dd></div>
              <div className="spec-row"><dt>Pengiriman</dt><dd>JNE, J&amp;T, Tiki (Kargo untuk grosir)</dd></div>
              <div className="spec-row"><dt>Garansi</dt><dd>Tukar ukuran &amp; garansi cacat produksi</dd></div>
            </dl>
          </div>
        </div>

        {reviews.length > 0 && (
          <section className="reviews-section">
            <SectionTitle subtitle={`Rata-rata ${product.rating_avg} dari 5 bintang (${product.reviews_count} ulasan terverifikasi).`}>
              Ulasan Pembeli
            </SectionTitle>
            <div className="reviews-grid">
              {reviews.map((rev) => (
                <article key={rev.id} className="review-card">
                  <div className="review-card-head">
                    <strong>{rev.reviewer_name}</strong>
                    <span className="review-card-stars" aria-label={`${rev.rating} dari 5 bintang`}>
                      {'★'.repeat(rev.rating)}{'☆'.repeat(5 - rev.rating)}
                    </span>
                  </div>
                  {rev.comment && <p className="review-card-body">{rev.comment}</p>}
                  <span className="review-card-date">
                    {new Date(rev.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                  </span>
                </article>
              ))}
            </div>
          </section>
        )}

        {related.length > 0 && (
          <section className="detail-related">
            <SectionTitle action={<Link className="text-link" href={`/katalog?cat=${product.kategori}`}>{t('see_all')}</Link>}>
              {product.kategori_nama} Lainnya
            </SectionTitle>
            <div className="grid">
              {related.map((p, idx) => (
                <ProductCard key={p.id} product={p} t={t} addToCart={addToCart} index={idx} />
              ))}
            </div>
          </section>
        )}
      </div>

      <SizeGuideModal
        open={sizeModalOpen}
        onClose={() => setSizeModalOpen(false)}
        productName={product.nama}
        defaultTab={product.gender === 'wanita' ? 'wanita' : 'pria'}
      />
    </ShopLayout>
  )
}
