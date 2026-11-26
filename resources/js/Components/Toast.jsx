import { useEffect, useRef, useState } from 'react'
import { Link } from '@inertiajs/react'

const IconCheck = () => (
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="m5 13 4 4L19 7" />
  </svg>
)

const IconClose = () => (
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
    <path d="M18 6 6 18M6 6l12 12" />
  </svg>
)

export default function Toast({ onOpenCart }) {
  const [toast, setToast] = useState(null)
  const timer = useRef(null)

  useEffect(() => {
    const onToast = (e) => {
      setToast(e.detail)
      clearTimeout(timer.current)
      timer.current = setTimeout(() => setToast(null), 4000)
    }
    window.addEventListener('shop:toast', onToast)
    return () => {
      window.removeEventListener('shop:toast', onToast)
      clearTimeout(timer.current)
    }
  }, [])

  if (!toast) return null

  return (
    <div className="toast-wrap" role="status" aria-live="polite" aria-atomic="true">
      <div className="toast-card">
        <span className="toast-ico"><IconCheck /></span>
        <span className="toast-msg">{toast.message}</span>
        {toast.action && (
          toast.action.openDrawer && onOpenCart ? (
            <button
              type="button"
              className="toast-action"
              onClick={() => {
                setToast(null)
                onOpenCart()
              }}
            >
              {toast.action.label}
            </button>
          ) : (
            <Link className="toast-action" href={toast.action.href} onClick={() => setToast(null)}>
              {toast.action.label}
            </Link>
          )
        )}
        <button type="button" className="toast-close" onClick={() => setToast(null)} aria-label="Tutup notifikasi">
          <IconClose />
        </button>
      </div>
    </div>
  )
}
