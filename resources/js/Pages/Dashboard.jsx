import { Link, router, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '@/Layouts/ShopLayout.jsx'
import SectionTitle from '@/Components/SectionTitle.jsx'
import { formatRupiah } from '@/Lib/format.js'
import { makeT, readLang } from '@/Lib/i18n.js'

export default function Dashboard({ orders = [] }) {
  const { props } = usePage()
  const user = props?.auth?.user
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const logout = () => router.post(route('logout'))

  return (
    <ShopLayout title={t('account')}>
      <div className="section">
        <SectionTitle>{t('account')}</SectionTitle>

        <div className="account-grid">
          <div className="card-box">
            <h2>{t('profile_title')}</h2>
            <p><strong>{user?.name}</strong></p>
            <p className="muted-text">{user?.email}</p>

            <div className="account-actions">
              {user?.is_admin && (
                <a className="button" href="/admin">Buka Panel Admin</a>
              )}
              <Link className="button ghost" href="/profile">Ubah Profil & Sandi</Link>
              <button type="button" className="button ghost" onClick={logout}>{t('logout')}</button>
            </div>
          </div>

          <div className="card-box">
            <h2>{t('orders_history')}</h2>
            {!orders.length ? (
              <p className="muted-text">{t('orders_empty')}</p>
            ) : (
              <div className="account-orders">
                {orders.map((o) => (
                  <Link key={o.id} href={`/pesanan/${o.order_code}`} className="account-order-row">
                    <div>
                      <strong>{o.order_code}</strong>
                      <span className="muted-text">{new Date(o.created_at).toLocaleDateString('id-ID')}</span>
                    </div>
                    <div className="account-order-right">
                      <span className={`status status-${o.status}`}>{t('status_' + o.status)}</span>
                      <strong className="price">{formatRupiah(o.grand_total)}</strong>
                    </div>
                  </Link>
                ))}
              </div>
            )}
            <p style={{ marginTop: 16 }}>
              <Link className="text-link" href="/pesanan">{t('see_all')}</Link>
            </p>
          </div>
        </div>
      </div>
    </ShopLayout>
  )
}
