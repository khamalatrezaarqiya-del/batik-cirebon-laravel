export default function CheckoutSteps({ current = 1 }) {
  const steps = [
    { num: 1, label: 'Keranjang' },
    { num: 2, label: 'Pengiriman & Bayar' },
    { num: 3, label: 'Konfirmasi' },
  ]

  return (
    <ol className="checkout-steps" aria-label="Tahapan belanja">
      {steps.map((s) => {
        const state = s.num < current ? 'done' : s.num === current ? 'active' : 'upcoming'
        return (
          <li key={s.num} className={`checkout-step ${state}`} aria-current={s.num === current ? 'step' : undefined}>
            <span className="step-num">
              {s.num < current ? (
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.6" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                  <path d="m5 13 4 4L19 7" />
                </svg>
              ) : s.num}
            </span>
            <span className="step-label">{s.label}</span>
          </li>
        )
      })}
    </ol>
  )
}
