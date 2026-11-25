import { Link } from '@inertiajs/react'

export default function SectionTitle({ children, subtitle, action }) {
  return (
    <div className="section-title">
      <div>
        <h2>{children}</h2>
        {subtitle && <p className="section-sub">{subtitle}</p>}
      </div>
      {action}
    </div>
  )
}

const IconEmpty = () => (
  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <circle cx="12" cy="12" r="9" />
    <path d="M9 10h.01M15 10h.01M9.5 15a3.5 3.5 0 0 1 5 0" />
  </svg>
)

export function Empty({ text, link, linkText }) {
  return (
    <div className="section">
      <div className="empty">
        <div className="empty-icon"><IconEmpty /></div>
        <p>{text}</p>
        {link && <Link className="button" href={link}>{linkText}</Link>}
      </div>
    </div>
  )
}
