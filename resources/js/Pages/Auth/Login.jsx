import { Link, useForm } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '@/Layouts/ShopLayout.jsx'
import { makeT, readLang } from '@/Lib/i18n.js'

const IconEye = ({ open }) =>
  open ? (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
      <circle cx="12" cy="12" r="3" />
    </svg>
  ) : (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
      <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
      <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
      <line x1="2" x2="22" y1="2" y2="22" />
    </svg>
  )

export default function Login({ status, canResetPassword }) {
  const [lang, setLang] = useState(() => readLang())
  const [showPw, setShowPw] = useState(false)
  const t = makeT(lang)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const { data, setData, post, processing, errors, reset } = useForm({
    email: '',
    password: '',
    remember: false,
  })

  const submit = (e) => {
    e.preventDefault()
    post(route('login'), {
      onFinish: () => reset('password'),
    })
  }

  return (
    <ShopLayout title={t('login')}>
      <div className="section">
        <form className="form auth-form" onSubmit={submit}>
          <div>
            <span className="auth-kicker">Akun Batik Daffa</span>
            <h2>{t('login_title')}</h2>
            <p className="auth-sub">Masuk untuk memantau riwayat pesanan dan checkout lebih cepat.</p>
          </div>

          {status && <p className="ok" role="status">{status}</p>}
          {(errors.email || errors.password) && (
            <p className="error" role="alert">{errors.email || errors.password}</p>
          )}

          <label className="auth-label">
            <span>{t('email')}</span>
            <input
              id="email"
              type="email"
              name="email"
              value={data.email}
              autoComplete="username"
              autoFocus
              required
              placeholder="nama@email.com"
              aria-invalid={Boolean(errors.email)}
              onChange={(e) => setData('email', e.target.value)}
            />
          </label>

          <label className="auth-label">
            <span>{t('password')}</span>
            <div className="password-wrap">
              <input
                id="password"
                type={showPw ? 'text' : 'password'}
                name="password"
                value={data.password}
                autoComplete="current-password"
                required
                aria-invalid={Boolean(errors.password)}
                onChange={(e) => setData('password', e.target.value)}
              />
              <button
                type="button"
                className="password-toggle"
                onClick={() => setShowPw((v) => !v)}
                aria-label={showPw ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'}
                aria-pressed={showPw}
              >
                <IconEye open={showPw} />
              </button>
            </div>
          </label>

          <div className="auth-options">
            <label className="check-row">
              <input
                type="checkbox"
                name="remember"
                checked={data.remember}
                onChange={(e) => setData('remember', e.target.checked)}
              />
              <span>Ingat saya</span>
            </label>

            {canResetPassword && (
              <Link href={route('password.request')} className="text-link">
                Lupa kata sandi?
              </Link>
            )}
          </div>

          <button className="button button-lg" type="submit" disabled={processing} aria-busy={processing}>
            {processing ? 'Memproses…' : t('login')}
          </button>

          <p className="form-switch">
            {t('no_account')}{' '}
            <Link href={route('register')}>{t('register')}</Link>
          </p>
        </form>
      </div>
    </ShopLayout>
  )
}
