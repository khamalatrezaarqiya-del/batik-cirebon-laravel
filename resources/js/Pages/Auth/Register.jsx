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

export default function Register() {
  const [lang, setLang] = useState(() => readLang())
  const [showPw, setShowPw] = useState(false)
  const [showConfirm, setShowConfirm] = useState(false)
  const t = makeT(lang)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  })

  const submit = (e) => {
    e.preventDefault()
    post(route('register'), {
      onFinish: () => reset('password', 'password_confirmation'),
    })
  }

  const firstError = errors.name || errors.email || errors.password || errors.password_confirmation

  return (
    <ShopLayout title={t('register')}>
      <div className="section">
        <form className="form auth-form" onSubmit={submit}>
          <div>
            <span className="auth-kicker">Keanggotaan Batik Daffa</span>
            <h2>{t('register_title')}</h2>
            <p className="auth-sub">Daftar gratis untuk menyimpan riwayat belanja dan status pengiriman.</p>
          </div>

          {firstError && <p className="error" role="alert">{firstError}</p>}

          <label className="auth-label">
            <span>{t('full_name')}</span>
            <input
              id="name"
              name="name"
              value={data.name}
              autoComplete="name"
              autoFocus
              required
              placeholder="Nama lengkap Anda"
              aria-invalid={Boolean(errors.name)}
              onChange={(e) => setData('name', e.target.value)}
            />
          </label>

          <label className="auth-label">
            <span>{t('email')}</span>
            <input
              id="email"
              type="email"
              name="email"
              value={data.email}
              autoComplete="username"
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
                autoComplete="new-password"
                minLength="8"
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
            <span className="field-hint">Minimal 8 karakter.</span>
          </label>

          <label className="auth-label">
            <span>Konfirmasi Kata Sandi</span>
            <div className="password-wrap">
              <input
                id="password_confirmation"
                type={showConfirm ? 'text' : 'password'}
                name="password_confirmation"
                value={data.password_confirmation}
                autoComplete="new-password"
                minLength="8"
                required
                aria-invalid={Boolean(errors.password_confirmation)}
                onChange={(e) => setData('password_confirmation', e.target.value)}
              />
              <button
                type="button"
                className="password-toggle"
                onClick={() => setShowConfirm((v) => !v)}
                aria-label={showConfirm ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'}
                aria-pressed={showConfirm}
              >
                <IconEye open={showConfirm} />
              </button>
            </div>
          </label>

          <button className="button button-lg" type="submit" disabled={processing} aria-busy={processing}>
            {processing ? 'Mendaftarkan…' : t('register')}
          </button>

          <p className="form-switch">
            {t('have_account')}{' '}
            <Link href={route('login')}>{t('login')}</Link>
          </p>
        </form>
      </div>
    </ShopLayout>
  )
}
