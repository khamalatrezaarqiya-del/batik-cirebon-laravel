import { Link, router, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'

const IconPackage = () => (
  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M12 2 2 7l10 5 10-5-10-5Z" />
    <path d="M2 17l10 5 10-5" />
    <path d="M2 12l10 5 10-5" />
  </svg>
)

export default function Orders({ orders = [], lookupCode = '', lookupResult = null, lookupNotFound = false }) {
  const { props } = usePage()
  const auth = props?.auth?.user || null
  const [lang, setLang] = useState(() => readLang())
  const [code, setCode] = useState(lookupCode)
  const t = makeT(lang)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const submitTrack = (e) => {
    e.preventDefault()
    const clean = code.trim().toUpperCase()
    if (!clean) return
    router.visit(`/pesanan?code=${encodeURIComponent(clean)}`, { preserveState: true })
  }

  return (
    <ShopLayout title={t('orders_title')}>
      <div className="section">
        <SectionTitle subtitle={t('track_sub')}>{t('orders_title')}</SectionTitle>

        <div className="track-card">
          <form className="track-form" onSubmit={submitTrack}>
            <input
              type="text"
              value={code}
              onChange={(e) => setCode(e.target.value)}
              placeholder={t('track_placeholder')}
              aria-label={t('track_title')}
            />
            <button className="button" type="submit">{t('track_button')}</button>
          </form>

          {lookupNotFound && (
            <p className="error" style={{ marginTop: 12 }}>{t('not_found_order')}</p>
          )}

          {lookupResult && (
            <Link className="order" href={`/pesanan/${lookupResult.order_code}`} style={{ marginTop: 16, marginBottom: 0 }}>
              <div className="grow">
                <strong>{lookupResult.order_code}</strong> · {new Date(lookupResult.created_at).toLocaleString(lang === 'id' ? 'id-ID' : 'en-GB')}
                <br />
                {lookupResult.recipient_name} · {(lookupResult.items || []).map((i) => `${i.product?.nama || 'Produk'}${i.size ? ` (${i.size})` : ''} ×${i.qty}`).join(', ')}
                <br />
                <span className={`status status-${lookupResult.status}`}>{t('status_' + lookupResult.status)}</span>
              </div>
              <strong className="price">{formatRupiah(lookupResult.grand_total)}</strong>
            </Link>
          )}
        </div>

        <SectionTitle>{t('orders_history')}</SectionTitle>

        {!auth ? (
          <div className="empty">
            <div className="empty-icon"><IconPackage /></div>
            <p>{t('login_required')}</p>
            <Link className="button" href="/login">{t('go_login')}</Link>
          </div>
        ) : !orders.length ? (
          <div className="empty">
            <div className="empty-icon"><IconPackage /></div>
            <p>{t('orders_empty')}</p>
            <Link className="button ghost" href="/katalog">{t('continue_shopping')}</Link>
          </div>
        ) : (
          <div className="orders-list">
            {orders.map((order) => (
              <Link className="order" key={order.id} href={`/pesanan/${order.order_code}`}>
                <div className="grow">
                  <strong>{order.order_code}</strong> · {new Date(order.created_at).toLocaleString(lang === 'id' ? 'id-ID' : 'en-GB')}
                  <br />
                  {(order.items || []).map((i) => `${i.product?.nama || 'Produk'}${i.size ? ` (${i.size})` : ''} ×${i.qty}`).join(', ')}
                  <br />
                  <span className={`status status-${order.status}`}>{t('status_' + order.status)}</span>
                </div>
                <strong className="price">{formatRupiah(order.grand_total)}</strong>
              </Link>
            ))}
          </div>
        )}
      </div>
    </ShopLayout>
  )
}
