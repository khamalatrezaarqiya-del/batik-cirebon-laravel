import { Link } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '@/Layouts/ShopLayout.jsx'
import SectionTitle from '@/Components/SectionTitle.jsx'
import { makeT, readLang } from '@/Lib/i18n.js'
import DeleteUserForm from './Partials/DeleteUserForm'
import UpdatePasswordForm from './Partials/UpdatePasswordForm'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm'

export default function Edit({ mustVerifyEmail, status }) {
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  return (
    <ShopLayout title={t('profile_title')}>
      <div className="section">
        <SectionTitle
          subtitle="Kelola informasi akun, kata sandi, dan keamanan profil Anda."
          action={<Link className="text-link" href="/dashboard">← Kembali ke Akun</Link>}
        >
          {t('profile_title')}
        </SectionTitle>

        <div className="profile-stack">
          <div className="card-box">
            <UpdateProfileInformationForm mustVerifyEmail={mustVerifyEmail} status={status} />
          </div>

          <div className="card-box">
            <UpdatePasswordForm />
          </div>

          <div className="card-box danger-zone">
            <DeleteUserForm />
          </div>
        </div>
      </div>
    </ShopLayout>
  )
}
