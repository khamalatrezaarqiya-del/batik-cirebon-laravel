import { Link } from '@inertiajs/react'

export default function SectionTitle({ children, action }) {
  return (
    <div className="section-title">
      <h2>{children}</h2>
      <div className="section-line" />
      {action}
    </div>
  )
}

const IconEmpty = () => (
  <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
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
        {link && <Link className="button" to={link}>{linkText}</Link>}
      </div>
    </div>
  )
}
