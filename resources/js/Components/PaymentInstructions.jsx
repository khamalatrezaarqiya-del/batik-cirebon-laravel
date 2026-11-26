import { useState } from 'react'
import { formatRupiah } from '../Lib/format.js'

const IconCopy = () => (
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <rect x="9" y="9" width="13" height="13" rx="2" />
    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
  </svg>
)

const IconCheck = () => (
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="m5 13 4 4L19 7" />
  </svg>
)

export default function PaymentInstructions({ method, paymentInfo, amount = 0 }) {
  const [copied, setCopied] = useState(false)

  if (!paymentInfo) return null

  const copyAccount = async (raw) => {
    const clean = String(raw || '').replace(/\s+/g, '')
    try {
      await navigator.clipboard.writeText(clean)
      setCopied(true)
      window.setTimeout(() => setCopied(false), 1800)
    } catch {
      /* clipboard blocked */
    }
  }

  if (method === 'transfer') {
    const bank = paymentInfo.bank || {}
    return (
      <div className="pay-detail-card" role="region" aria-label="Detail Transfer Bank">
        <div className="pay-detail-head">
          <span className="pay-detail-badge">Transfer Bank</span>
          <strong>{bank.name}</strong>
        </div>

        <div className="pay-account-box">
          <div>
            <span className="pay-field-label">Nomor Rekening</span>
            <strong className="pay-account-number">{bank.account}</strong>
            <span className="pay-field-sub">a.n. {bank.holder}</span>
          </div>
          <button
            type="button"
            className={`button ghost pay-copy-btn${copied ? ' copied' : ''}`}
            onClick={() => copyAccount(bank.account)}
          >
            {copied ? <IconCheck /> : <IconCopy />}
            <span>{copied ? 'Tersalin' : 'Salin'}</span>
          </button>
        </div>

        {amount > 0 && (
          <div className="pay-amount-row">
            <span>Nominal Pembayaran</span>
            <strong>{formatRupiah(amount)}</strong>
          </div>
        )}

        {bank.note && <p className="pay-detail-note">{bank.note}</p>}
      </div>
    )
  }

  if (method === 'ewallet') {
    const qris = paymentInfo.qris || {}
    return (
      <div className="pay-detail-card" role="region" aria-label="Detail Pembayaran QRIS">
        <div className="pay-detail-head">
          <span className="pay-detail-badge">QRIS / E-Wallet</span>
          <strong>{qris.merchant}</strong>
        </div>

        <div className="pay-qris-layout">
          <div className="pay-qris-frame">
            <img src={qris.image || '/images/qris-default.svg'} alt={`QRIS ${qris.merchant || 'Batik Daffa'}`} />
          </div>
          <div className="pay-qris-meta">
            {amount > 0 && (
              <div className="pay-amount-row">
                <span>Nominal Pembayaran</span>
                <strong>{formatRupiah(amount)}</strong>
              </div>
            )}
            <p className="pay-detail-note">{qris.info}</p>
            <p className="pay-detail-note">
              Setelah pembayaran berhasil, pesanan Anda akan diverifikasi langsung oleh admin Batik Daffa.
            </p>
          </div>
        </div>
      </div>
    )
  }

  if (method === 'cod') {
    const cod = paymentInfo.cod || {}
    return (
      <div className="pay-detail-card" role="region" aria-label="Detail COD">
        <div className="pay-detail-head">
          <span className="pay-detail-badge">COD (Bayar di Tempat)</span>
        </div>
        {amount > 0 && (
          <div className="pay-amount-row">
            <span>Estimasi Pembayaran di Tempat</span>
            <strong>{formatRupiah(amount)}</strong>
          </div>
        )}
        <p className="pay-detail-note">{cod.note}</p>
      </div>
    )
  }

  return null
}
