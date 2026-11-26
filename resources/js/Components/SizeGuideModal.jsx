import { useEffect, useRef } from 'react'
import { waLink } from '../Lib/whatsapp.js'

const IconClose = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
    <path d="M18 6 6 18M6 6l12 12" />
  </svg>
)

const SIZE_ROWS = [
  ['S', '98 – 102 cm', '70 cm', '44 cm'],
  ['M', '104 – 108 cm', '72 cm', '46 cm'],
  ['L', '110 – 114 cm', '74 cm', '48 cm'],
  ['XL', '116 – 120 cm', '76 cm', '50 cm'],
]

export default function SizeGuideModal({ open, onClose, productName = '' }) {
  const closeRef = useRef(null)

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
            <h2 id="size-modal-title">Panduan Ukuran Busana</h2>
            <p className="muted-text">Standar potongan reguler Batik Daffa (toleransi jahit 1–2 cm).</p>
          </div>
          <button ref={closeRef} type="button" className="drawer-close" onClick={onClose} aria-label="Tutup panduan ukuran">
            <IconClose />
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
              {SIZE_ROWS.map(([sz, chest, len, sh]) => (
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
          <p className="muted-text">Ragu menentukan ukuran? Kirim lingkar dada &amp; tinggi badan ke tim kami.</p>
          <a
            className="button ghost"
            href={waLink(`Halo Batik Daffa, saya ingin konsultasi ukuran untuk ${productName || 'busana batik'}.`)}
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
