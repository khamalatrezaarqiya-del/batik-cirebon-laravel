import { Link, useForm, usePage } from '@inertiajs/react'
import { useEffect, useMemo, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { clearCart, readCart } from '../../Lib/cart.js'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'

const PAYMENTS = ['transfer', 'ewallet', 'cod']

export default function Checkout({ products = [] }) {
  const { props } = usePage()
  const user = props?.auth?.user || null
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)
  const [cart, setCart] = useState(() => readCart())

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const byId = useMemo(() => Object.fromEntries(products.map((p) => [p.id, p])), [products])
  const items = cart
    .map((i) => {
      const p = byId[i.id]
      return p ? { ...p, key: i.k, size: i.size, qty: i.qty, lineTotal: p.harga * i.qty } : null
    })
    .filter(Boolean)
  const total = items.reduce((s, i) => s + i.lineTotal, 0)

  const form = useForm({
    recipient_name: user?.name || '',
    phone: '',
    shipping_address: '',
    note: '',
    payment_method: 'transfer',
    items: [],
  })

  const submit = (e) => {
    e.preventDefault()
    form.transform((data) => ({
      ...data,
      items: items.map((i) => ({ product_id: i.id, size: i.size || null, qty: i.qty })),
    }))
    form.post('/checkout', {
      onSuccess: () => {
        clearCart()
        setCart([])
      },
    })
  }

  if (!items.length) {
    return (
      <ShopLayout title={t('checkout_title')}>
        <div className="section">
          <div className="empty">
            <p>{t('cart_empty')}</p>
            <Link className="button" href="/katalog">{t('continue_shopping')}</Link>
          </div>
        </div>
      </ShopLayout>
    )
  }

  return (
    <ShopLayout title={t('checkout_title')}>
      <div className="section">
        <SectionTitle>{t('checkout_title')}</SectionTitle>
        <div className="checkout-layout">
          <form className="form" onSubmit={submit}>
            <h2>{t('checkout_recipient')}</h2>

            {Object.keys(form.errors).length > 0 && (
              <p className="error">{Object.values(form.errors)[0]}</p>
            )}

            <label>
              {t('recipient_name')}
              <input
                name="recipient_name"
                value={form.data.recipient_name}
                onChange={(e) => form.setData('recipient_name', e.target.value)}
                required
                autoComplete="name"
              />
            </label>
            <label>
              {t('recipient_phone')}
              <input
                name="phone"
                type="tel"
                value={form.data.phone}
                onChange={(e) => form.setData('phone', e.target.value)}
                placeholder="08xxxxxxxxxx"
                required
                autoComplete="tel"
              />
            </label>
            <label>
              {t('shipping_address')}
              <textarea
                name="shipping_address"
                rows="3"
                value={form.data.shipping_address}
                onChange={(e) => form.setData('shipping_address', e.target.value)}
                required
                autoComplete="street-address"
              />
            </label>
            <label>
              {t('note')}
              <textarea
                name="note"
                rows="2"
                value={form.data.note}
                onChange={(e) => form.setData('note', e.target.value)}
              />
            </label>

            <h3>{t('payment_method')}</h3>
            <div className="radio-group">
              {PAYMENTS.map((p) => (
                <label className={`radio-row${form.data.payment_method === p ? ' selected' : ''}`} key={p}>
                  <input
                    type="radio"
                    name="payment_method"
                    value={p}
                    checked={form.data.payment_method === p}
                    onChange={() => form.setData('payment_method', p)}
                  />
                  <span>{t('pay_' + p)}</span>
                </label>
              ))}
            </div>

            <button className="button" type="submit" disabled={form.processing}>
              {t('place_order')}
            </button>
          </form>

          <div className="summary">
            <h2>{t('order_summary')}</h2>
            {items.map((i) => (
              <p key={i.key}>
                <span>{i.nama}{i.size ? ` (${i.size})` : ''} ×{i.qty}</span>
                <strong>{formatRupiah(i.lineTotal)}</strong>
              </p>
            ))}
            <hr />
            <p className="total-row">
              <span>{t('cart_total')}</span>
              <strong>{formatRupiah(total)}</strong>
            </p>
            <small>{t('shipping_note')}</small>
          </div>
        </div>
      </div>
    </ShopLayout>
  )
}
