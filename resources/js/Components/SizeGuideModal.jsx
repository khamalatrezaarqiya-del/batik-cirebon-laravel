import { useEffect, useRef, useState } from 'react'
import { usePage } from '@inertiajs/react'
import { waLink } from '../Lib/whatsapp.js'

const IconClose = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
    <path d="M18 6 6 18M6 6l12 12" />
  </svg>
)

const SIZE_ROWS_PRIA = [
  ['S', '100 – 104 cm', '70 cm', '44 cm'],
  ['M', '106 – 110 cm', '72 cm', '46 cm'],
  ['L', '112 – 116 cm', '74 cm', '48 cm'],
  ['XL', '118 – 122 cm', '76 cm', '50 cm'],
  ['XXL', '124 – 128 cm', '78 cm', '52 cm'],
]

const SIZE_ROWS_WANITA = [
  ['S', '90 – 94 cm', '65 cm', '38 cm'],
  ['M', '96 – 100 cm', '67 cm', '40 cm'],
  ['L', '102 – 106 cm', '69 cm', '42 cm'],
  ['XL', '108 – 112 cm', '71 cm', '44 cm'],
  ['XXL', '114 – 118 cm', '73 cm', '46 cm'],
]

export default function SizeGuideModal({ open, onClose, productName = '', defaultTab = 'pria' }) {
  const { props } = usePage()
  const storeWhatsapp = props?.storeWhatsapp || ''
  const [tab, setTab] = useState(defaultTab)
  const closeRef = useRef(null)

  useEffect(() => {
    setTab(defaultTab)
  }, [defaultTab, open])

  useEffect(() => {
    if (!open) return
    const prev = document.activeElement
    closeRef.current?.focus()
    const onKey = (e) => {
      if (e.key === 'Escape') onClose()
    }
    document.body.style.overflow = 'hidden'
    window.addEventListener('keydown', onKey)
    return () => {
      document.body.style.overflow = ''
      window.removeEventListener('keydown', onKey)
      if (prev && typeof prev.focus === 'function') prev.focus()
    }
  }, [open, onClose])

  if (!open) return null

  const rows = tab === 'wanita' ? SIZE_ROWS_WANITA : SIZE_ROWS_PRIA

  return (
    <div className="modal-backdrop" onClick={onClose} role="presentation">
      <div
        className="modal-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="size-modal-title"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="modal-head">
          <div>
            <h2 id="size-modal-title">Panduan Ukuran Pria &amp; Wanita</h2>
            <p className="muted-text">Standar potongan reguler Batik Daffa (toleransi jahit 1–2 cm).</p>
          </div>
          <button ref={closeRef} type="button" className="drawer-close" onClick={onClose} aria-label="Tutup panduan ukuran">
            <IconClose />
          </button>
        </div>

        <div className="size-tabs" role="tablist" aria-label="Pilih panduan ukuran">
          <button
            type="button"
            role="tab"
            aria-selected={tab === 'pria'}
            className={`size-tab${tab === 'pria' ? ' active' : ''}`}
            onClick={() => setTab('pria')}
          >
            Busana Pria (Kemeja)
          </button>
          <button
            type="button"
            role="tab"
            aria-selected={tab === 'wanita'}
            className={`size-tab${tab === 'wanita' ? ' active' : ''}`}
            onClick={() => setTab('wanita')}
          >
            Busana Wanita (Blouse / Dress)
          </button>
        </div>

        <div className="size-table-wrap">
          <table className="size-table">
            <thead>
              <tr>
                <th>Ukuran</th>
                <th>Lingkar Dada</th>
                <th>Panjang Baju</th>
                <th>Lebar Bahu</th>
              </tr>
            </thead>
            <tbody>
              {rows.map(([sz, chest, len, sh]) => (
                <tr key={sz}>
                  <td><strong>{sz}</strong></td>
                  <td>{chest}</td>
                  <td>{len}</td>
                  <td>{sh}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="modal-foot">
          <p className="muted-text">Tersedia garansi penukaran ukuran atau jahit ukuran custom via WhatsApp.</p>
          <a
            className="button ghost"
            href={waLink(`Halo Batik Daffa, saya ingin konsultasi ukuran (${tab}) untuk ${productName || 'busana batik'}.`, storeWhatsapp)}
            target="_blank"
            rel="noreferrer"
          >
            Tanya via WhatsApp
          </a>
        </div>
      </div>
    </div>
  )
}
