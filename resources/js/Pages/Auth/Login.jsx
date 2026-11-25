import { Link, useForm } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '@/Layouts/ShopLayout.jsx'
import { makeT, readLang } from '@/Lib/i18n.js'

export default function Login({ status, canResetPassword }) {
  const [lang, setLang] = useState(() => readLang())
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
          <h2>{t('login_title')}</h2>

          {status && <p className="ok">{status}</p>}
          {(errors.email || errors.password) && (
            <p className="error">{errors.email || errors.password}</p>
          )}

          <label className="auth-label">
            {t('email')}
            <input
              id="email"
              type="email"
              name="email"
              value={data.email}
              autoComplete="username"
              autoFocus
              required
              onChange={(e) => setData('email', e.target.value)}
            />
          </label>

          <label className="auth-label">
            {t('password')}
            <input
              id="password"
              type="password"
              name="password"
              value={data.password}
              autoComplete="current-password"
              required
              onChange={(e) => setData('password', e.target.value)}
            />
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

          <button className="button" type="submit" disabled={processing}>
            {t('login')}
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
