import { Link, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const IconCheck = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="m5 13 4.5 4.5L19 7" />
  </svg>
)

export default function OrderDetail({ order }) {
  const { props } = usePage()
  const flashSuccess = props?.flash?.success
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const isNew = Boolean(flashSuccess) || new URLSearchParams(window.location.search).get('new') === '1'

  const itemLines = (order.items || [])
    .map((i) => `- ${i.product?.nama || 'Produk'}${i.size ? ` (${i.size})` : ''} x${i.qty}`)
    .join('\n')

  const message = `Halo Batik Daffa, konfirmasi pesanan *${order.order_code}*\nAtas nama: ${order.recipient_name}\n${itemLines}\nTotal: ${formatRupiah(order.grand_total)}\nMetode: ${t('pay_' + order.payment_method)}`

  return (
    <ShopLayout title={order.order_code}>
      <div className="section">
        <SectionTitle>{order.order_code}</SectionTitle>

        {isNew && (
          <div className="success-banner">
            <IconCheck />
            <span>{t('order_done')}</span>
          </div>
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

          <div className="order-actions">
            <a className="button" href={waLink(message)} target="_blank" rel="noreferrer">
              {t('confirm_wa')}
            </a>
            <Link className="button ghost" href="/pesanan">{t('orders_title')}</Link>
            <Link className="button ghost" href="/katalog">{t('continue_shopping')}</Link>
          </div>
        </div>
      </div>
    </ShopLayout>
  )
}
