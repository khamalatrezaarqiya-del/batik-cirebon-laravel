import { Link, useForm } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '@/Layouts/ShopLayout.jsx'
import { makeT, readLang } from '@/Lib/i18n.js'

export default function Register() {
  const [lang, setLang] = useState(() => readLang())
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
          <h2>{t('register_title')}</h2>

          {firstError && <p className="error">{firstError}</p>}

          <label className="auth-label">
            {t('full_name')}
            <input
              id="name"
              name="name"
              value={data.name}
              autoComplete="name"
              autoFocus
              required
              onChange={(e) => setData('name', e.target.value)}
            />
          </label>

          <label className="auth-label">
            {t('email')}
            <input
              id="email"
              type="email"
              name="email"
              value={data.email}
              autoComplete="username"
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
              autoComplete="new-password"
              minLength="8"
              required
              onChange={(e) => setData('password', e.target.value)}
            />
          </label>

          <label className="auth-label">
            Konfirmasi Kata Sandi
            <input
              id="password_confirmation"
              type="password"
              name="password_confirmation"
              value={data.password_confirmation}
              autoComplete="new-password"
              minLength="8"
              required
              onChange={(e) => setData('password_confirmation', e.target.value)}
            />
          </label>

          <button className="button" type="submit" disabled={processing}>
            {t('register')}
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
