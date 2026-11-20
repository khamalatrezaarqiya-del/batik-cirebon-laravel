import { Link } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const IconCheck = () => (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
    <path d="m5 13 4.5 4.5L19 7" />
  </svg>
)

export default function OrderDetail({ order }) {
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const isNew = new URLSearchParams(window.location.search).get('new') === '1'

  const message = `Halo, konfirmasi pesanan *${order.order_code}\nAtas nama: ${order.recipient_name}\nTotal: ${formatRupiah(order.grand_total)}\nMetode: ${t('pay_' + order.payment_method)}`

  return (
    <ShopLayout title={order.order_code}>
      <div className="section">
        <SectionTitle>{order.order_code}</SectionTitle>

        {isNew && (
          <div className="success-banner">
            <IconCheck />
            {t('order_done')}
          </div>
        )}

        <div className="order-detail-card">
          <p><strong>{t('date_label')}:</strong> {new Date(order.created_at).toLocaleString('id-ID')}</p>
          <p><strong>{t('status_label')}:</strong> <span className={`status status-${order.status}`}>{t('status_' + order.status) || order.status}</span></p>
          <p><strong>{t('recipient_label')}:</strong> {order.recipient_name} ({order.phone})</p>
          <p><strong>{t('address_label')}:</strong> {order.shipping_address}</p>
          {order.note && <p><strong>{t('note')}:</strong> {order.note}</p>}
          <p><strong>{t('payment_label')}:</strong> {t('pay_' + order.payment_method)}</p>

          <h3>{t('label_items')}</h3>
          <ul>
            {order.items.map((i) => (
              <li key={i.id}>
                {i.product?.nama || 'Produk'} {i.size ? `(${i.size})` : ''} × {i.qty} = <strong>{formatRupiah(i.line_total)}</strong>
              </li>
            ))}
          </ul>

          <p className="order-total-line">
            <strong>Total: {formatRupiah(order.grand_total)}</strong>
          </p>

          <div className="order-actions" style={{ marginTop: 20, display: 'flex', gap: 12, flexWrap: 'wrap' }}>
            <a className="button" href={waLink(message)} target="_blank" rel="noreferrer">
              {t('confirm_wa')}
            </a>
            <Link className="button ghost" href="/katalog">← {t('continue_shopping')}</Link>
          </div>
        </div>
      </div>
    </ShopLayout>
  )
}
