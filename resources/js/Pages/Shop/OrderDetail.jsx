import { Link, router, useForm, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import CheckoutSteps from '../../Components/CheckoutSteps.jsx'
import PaymentInstructions from '../../Components/PaymentInstructions.jsx'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const IconCheck = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="m5 13 4.5 4.5L19 7" />
  </svg>
)

const IconStar = ({ filled }) => (
  <svg
    width="24"
    height="24"
    viewBox="0 0 24 24"
    fill={filled ? 'currentColor' : 'none'}
    stroke="currentColor"
    strokeWidth="1.65"
    strokeLinecap="round"
    strokeLinejoin="round"
    aria-hidden="true"
  >
    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
  </svg>
)

const TIMELINE = ['baru', 'diproses', 'dikirim', 'selesai']
const RATING_LABELS = {
  1: 'Sangat Kurang',
  2: 'Kurang',
  3: 'Cukup',
  4: 'Puas',
  5: 'Sangat Puas',
}

export default function OrderDetail({ order, paymentInfo = null }) {
  const { props } = usePage()
  const flashSuccess = props?.flash?.success
  const storeWhatsapp = props?.storeWhatsapp || ''
  const [lang, setLang] = useState(() => readLang())
  const [completing, setCompleting] = useState(false)
  const [hoverStar, setHoverStar] = useState(0)
  const t = makeT(lang)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const isNewParam = new URLSearchParams(window.location.search).get('new') === '1'
  const bannerMessage = flashSuccess || (isNewParam ? t('order_done') : '')
  const statusIdx = TIMELINE.indexOf(order.status)
  const canConfirmComplete = order.status === 'diproses' || order.status === 'dikirim'
  const existingReview = order.reviews?.[0] || null

  const reviewForm = useForm({
    rating: 5,
    comment: '',
  })

  const confirmCompleted = () => {
    setCompleting(true)
    router.post(
      `/pesanan/${order.order_code}/selesai`,
      {},
      {
        preserveScroll: true,
        onFinish: () => setCompleting(false),
      }
    )
  }

  const submitReview = (e) => {
    e.preventDefault()
    reviewForm.post(`/pesanan/${order.order_code}/ulasan`, {
      preserveScroll: true,
    })
  }

  const helpMessage = `Halo Batik Daffa, saya membutuhkan bantuan terkait pesanan *${order.order_code}* atas nama ${order.recipient_name}.`

  return (
    <ShopLayout title={order.order_code}>
      <div className="section">
        {isNewParam && <CheckoutSteps current={3} />}
        <SectionTitle subtitle="Simpan kode pesanan ini untuk memantau status verifikasi dan pengiriman kapan saja.">
          {order.order_code}
        </SectionTitle>

        {bannerMessage && (
          <div className="success-banner" role="status">
            <IconCheck />
            <span>{bannerMessage}</span>
          </div>
        )}

        {order.status !== 'batal' && (
          <ol className="order-timeline" aria-label="Progres pesanan">
            {TIMELINE.map((st, idx) => {
              const reached = statusIdx >= idx
              return (
                <li key={st} className={`timeline-step${reached ? ' reached' : ''}${statusIdx === idx ? ' current' : ''}`}>
                  <span className="timeline-dot" aria-hidden="true" />
                  <span>{t('status_' + st)}</span>
                </li>
              )
            })}
          </ol>
        )}

        <div className="order-detail-card">
          <div className="order-meta-grid">
            <p><strong>{t('date_label')}</strong><span>{new Date(order.created_at).toLocaleString(lang === 'id' ? 'id-ID' : 'en-GB')}</span></p>
            <p><strong>{t('status_label')}</strong><span><span className={`status status-${order.status}`}>{t('status_' + order.status) || order.status}</span></span></p>
            <p><strong>{t('recipient_label')}</strong><span>{order.recipient_name} ({order.phone})</span></p>
            <p><strong>{t('payment_label')}</strong><span>{t('pay_' + order.payment_method)}</span></p>
            <p className="full-row"><strong>{t('address_label')}</strong><span>{order.shipping_address}</span></p>
            {order.note && <p className="full-row"><strong>{t('note')}</strong><span>{order.note}</span></p>}
          </div>

          <h3>{t('label_items')}</h3>
          <ul className="order-items-list">
            {order.items.map((i) => (
              <li key={i.id}>
                <span>{i.product?.nama || 'Produk'}{i.size ? ` (${i.size})` : ''} × {i.qty}</span>
                <strong>{formatRupiah(i.line_total)}</strong>
              </li>
            ))}
          </ul>

          {order.shipping_cost > 0 && (
            <p className="order-sub-line">
              <span>{t('shipping_label')}</span>
              <strong>{formatRupiah(order.shipping_cost)}</strong>
            </p>
          )}

          <p className="order-total-line">
            <span>{t('cart_total')}</span>
            <strong>{formatRupiah(order.grand_total)}</strong>
          </p>

          {(order.status === 'baru' || order.status === 'diproses') && paymentInfo && (
            <div style={{ marginTop: 24 }}>
              <PaymentInstructions
                method={order.payment_method}
                paymentInfo={paymentInfo}
                amount={order.grand_total}
              />
            </div>
          )}

          {/* Konfirmasi Pesanan Selesai dari sisi Konsumen */}
          {canConfirmComplete && (
            <div className="order-confirm-card">
              <div>
                <strong>Sudah menerima paket Batik Daffa Anda?</strong>
                <p className="muted-text">
                  Klik konfirmasi selesai setelah pesanan diterima dengan baik untuk memberikan bintang dan ulasan.
                </p>
              </div>
              <button
                type="button"
                className="button button-lg"
                onClick={confirmCompleted}
                disabled={completing}
                aria-busy={completing}
              >
                {completing ? 'Memproses…' : 'Konfirmasi Pesanan Selesai'}
              </button>
            </div>
          )}

          {/* Rating Bintang & Ulasan setelah Pesanan Selesai */}
          {order.status === 'selesai' && (
            <div className="review-box">
              {existingReview ? (
                <div className="review-saved">
                  <div className="review-saved-head">
                    <strong>Ulasan Anda</strong>
                    <div className="stars-static" aria-label={`Rating ${existingReview.rating} dari 5 bintang`}>
                      {[1, 2, 3, 4, 5].map((n) => (
                        <span key={n} className={n <= existingReview.rating ? 'star-on' : 'star-off'}>
                          <IconStar filled={n <= existingReview.rating} />
                        </span>
                      ))}
                      <span className="review-rating-text">
                        {existingReview.rating}/5 · {RATING_LABELS[existingReview.rating]}
                      </span>
                    </div>
                  </div>
                  {existingReview.comment && (
                    <p className="review-comment-text">“{existingReview.comment}”</p>
                  )}
                </div>
              ) : (
                <form className="review-form" onSubmit={submitReview}>
                  <div>
                    <h3>Beri Bintang &amp; Ulasan</h3>
                    <p className="muted-text">Bagikan pengalaman Anda mengenai kualitas kain, motif, dan layanan Batik Daffa.</p>
                  </div>

                  {reviewForm.errors.rating && (
                    <p className="error" role="alert">{reviewForm.errors.rating}</p>
                  )}

                  <div className="star-picker-row">
                    <div className="star-picker" role="radiogroup" aria-label="Pilih rating bintang 1 sampai 5">
                      {[1, 2, 3, 4, 5].map((n) => {
                        const active = n <= (hoverStar || reviewForm.data.rating)
                        return (
                          <button
                            key={n}
                            type="button"
                            role="radio"
                            aria-checked={reviewForm.data.rating === n}
                            aria-label={`${n} bintang (${RATING_LABELS[n]})`}
                            className={`star-btn${active ? ' active' : ''}`}
                            onMouseEnter={() => setHoverStar(n)}
                            onMouseLeave={() => setHoverStar(0)}
                            onClick={() => reviewForm.setData('rating', n)}
                          >
                            <IconStar filled={active} />
                          </button>
                        )
                      })}
                    </div>
                    <span className="review-rating-text">
                      {reviewForm.data.rating}/5 · {RATING_LABELS[reviewForm.data.rating]}
                    </span>
                  </div>

                  <label className="review-textarea-label">
                    <span>Ulasan (opsional)</span>
                    <textarea
                      rows="3"
                      value={reviewForm.data.comment}
                      onChange={(e) => reviewForm.setData('comment', e.target.value)}
                      placeholder="Ceritakan kualitas bahan, motif batik, jahitan, atau pengiriman…"
                      maxLength={1000}
                    />
                  </label>

                  <div>
                    <button
                      type="submit"
                      className="button button-lg"
                      disabled={reviewForm.processing}
                      aria-busy={reviewForm.processing}
                    >
                      {reviewForm.processing ? 'Mengirim…' : 'Kirim Bintang & Ulasan'}
                    </button>
                  </div>
                </form>
              )}
            </div>
          )}

          <div className="order-actions">
            <Link className="button ghost button-lg" href="/pesanan">{t('orders_title')}</Link>
            <Link className="button ghost button-lg" href="/katalog">{t('continue_shopping')}</Link>
          </div>

          <div className="order-help-box">
            <div>
              <strong>Butuh bantuan terkait pesanan ini?</strong>
              <p className="muted-text">
                Anda tidak perlu mengirim konfirmasi manual — admin Batik Daffa akan memverifikasi pesanan Anda. Jika ada kendala atau pertanyaan, silakan hubungi kami.
              </p>
            </div>
            <a className="button ghost" href={waLink(helpMessage, storeWhatsapp)} target="_blank" rel="noreferrer">
              {t('need_help_wa')}
            </a>
          </div>
        </div>
      </div>
    </ShopLayout>
  )
}
