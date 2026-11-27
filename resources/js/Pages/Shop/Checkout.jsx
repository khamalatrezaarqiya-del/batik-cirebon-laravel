import { Link, useForm, usePage } from '@inertiajs/react'
import { useEffect, useMemo, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import CheckoutSteps from '../../Components/CheckoutSteps.jsx'
import PaymentInstructions from '../../Components/PaymentInstructions.jsx'
import { clearCart, readCart } from '../../Lib/cart.js'
import { formatRupiah } from '../../Lib/format.js'
import { makeT, readLang } from '../../Lib/i18n.js'

const ALL_PAYMENTS = [
  { key: 'transfer', desc: 'Nomor rekening langsung tampil di bawah setelah dipilih (m-Banking / BCA).' },
  { key: 'dp50', desc: 'Bayar DP 50% terlebih dahulu untuk pesanan nominal besar, seragam, atau custom.' },
  { key: 'ewallet', desc: 'Scan QRIS langsung (GoPay, OVO, DANA, ShopeePay, m-Banking).' },
  { key: 'cod', desc: 'Bayar tunai langsung saat paket tiba di alamat tujuan.' },
]

export default function Checkout({ products = [], paymentInfo = null }) {
  const { props } = usePage()
  const user = props?.auth?.user || null
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)
  const [cart, setCart] = useState(() => readCart())

  const availablePayments = useMemo(() => {
    const enabled = paymentInfo?.enabled || { transfer: true, dp50: true, ewallet: false, cod: false }
    const filtered = ALL_PAYMENTS.filter((p) => enabled[p.key])
    return filtered.length ? filtered : [ALL_PAYMENTS[0]]
  }, [paymentInfo])

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

  const [idempotencyKey] = useState(() =>
    typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function'
      ? crypto.randomUUID()
      : `chk-${Date.now()}-${Math.random().toString(36).slice(2, 12)}`
  )

  const form = useForm({
    recipient_name: user?.name || '',
    phone: '',
    shipping_address: '',
    note: '',
    payment_method: availablePayments[0]?.key || 'transfer',
    items: [],
    idempotency_key: '',
  })

  useEffect(() => {
    if (!availablePayments.some((p) => p.key === form.data.payment_method)) {
      form.setData('payment_method', availablePayments[0]?.key || 'transfer')
    }
  }, [availablePayments])

  const submit = (e) => {
    e.preventDefault()
    form.transform((data) => ({
      ...data,
      idempotency_key: idempotencyKey,
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
          <SectionTitle>{t('checkout_title')}</SectionTitle>
          <div className="empty">
            <p>{t('cart_empty')}</p>
            <Link className="button" href="/katalog">{t('continue_shopping')}</Link>
          </div>
        </div>
      </ShopLayout>
    )
  }

  const errorList = Object.entries(form.errors)

  return (
    <ShopLayout title={t('checkout_title')}>
      <div className="section">
        <CheckoutSteps current={2} />
        <SectionTitle subtitle="Lengkapi alamat dan pilih metode pembayaran. Admin kami akan mengonfirmasi dan menghubungi Anda setelah pesanan tercatat.">
          {t('checkout_title')}
        </SectionTitle>

        <div className="checkout-layout">
          <form className="form" onSubmit={submit}>
            <h2>{t('checkout_recipient')}</h2>

            {errorList.length > 0 && (
              <div className="error" role="alert">
                {errorList[0][1]}
              </div>
            )}

            <label>
              <span>{t('recipient_name')} <abbr className="req" title="Wajib diisi">*</abbr></span>
              <input
                name="recipient_name"
                value={form.data.recipient_name}
                onChange={(e) => form.setData('recipient_name', e.target.value)}
                required
                autoComplete="name"
                aria-invalid={Boolean(form.errors.recipient_name)}
              />
            </label>
            <label>
              <span>{t('recipient_phone')} <abbr className="req" title="Wajib diisi">*</abbr></span>
              <input
                name="phone"
                type="tel"
                value={form.data.phone}
                onChange={(e) => form.setData('phone', e.target.value)}
                placeholder="08xxxxxxxxxx"
                required
                autoComplete="tel"
                aria-invalid={Boolean(form.errors.phone)}
              />
              <span className="field-hint">Admin Batik Daffa akan menghubungi nomor ini untuk konfirmasi pengiriman &amp; resi.</span>
            </label>
            <label>
              <span>{t('shipping_address')} <abbr className="req" title="Wajib diisi">*</abbr></span>
              <textarea
                name="shipping_address"
                rows="3"
                value={form.data.shipping_address}
                onChange={(e) => form.setData('shipping_address', e.target.value)}
                placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, kode pos…"
                required
                autoComplete="street-address"
                aria-invalid={Boolean(form.errors.shipping_address)}
              />
            </label>
            <label>
              <span>{t('note')}</span>
              <textarea
                name="note"
                rows="2"
                value={form.data.note}
                onChange={(e) => form.setData('note', e.target.value)}
                placeholder="Contoh: pilih ekspedisi JNE / J&T / Tiki, bungkus kado, dsb."
              />
            </label>

            <h3>{t('payment_method')}</h3>
            <div className="radio-group">
              {availablePayments.map((p) => (
                <label className={`radio-row${form.data.payment_method === p.key ? ' selected' : ''}`} key={p.key}>
                  <input
                    type="radio"
                    name="payment_method"
                    value={p.key}
                    checked={form.data.payment_method === p.key}
                    onChange={() => form.setData('payment_method', p.key)}
                  />
                  <span className="radio-body">
                    <strong>{t('pay_' + p.key)}</strong>
                    <small>{p.desc}</small>
                  </span>
                </label>
              ))}
            </div>

            <PaymentInstructions
              method={form.data.payment_method}
              paymentInfo={paymentInfo}
              amount={total}
            />

            <button className="button button-lg" type="submit" disabled={form.processing} aria-busy={form.processing}>
              {form.processing ? 'Memproses Pesanan…' : t('place_order')}
            </button>
          </form>

          <aside className="summary">
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
            <Link className="text-link" href="/keranjang" style={{ marginTop: 4 }}>← Ubah Keranjang</Link>
          </aside>
        </div>
      </div>
    </ShopLayout>
  )
}
