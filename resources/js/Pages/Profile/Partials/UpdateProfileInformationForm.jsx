import { Link, useForm, usePage } from '@inertiajs/react'

export default function UpdateProfileInformation({ mustVerifyEmail, status }) {
  const user = usePage().props.auth.user

  const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
    name: user.name,
    email: user.email,
  })

  const submit = (e) => {
    e.preventDefault()
    patch(route('profile.update'))
  }

  return (
    <section className="profile-section">
      <header className="profile-head">
        <h2>Informasi Profil</h2>
        <p className="muted-text">Perbarui nama tampilan dan alamat email akun Anda.</p>
      </header>

      <form onSubmit={submit} className="form form-inline">
        {recentlySuccessful && (
          <p className="ok" role="status">Perubahan profil berhasil disimpan.</p>
        )}
        {(errors.name || errors.email) && (
          <p className="error" role="alert">{errors.name || errors.email}</p>
        )}

        <label>
          <span>Nama Lengkap</span>
          <input
            id="name"
            value={data.name}
            onChange={(e) => setData('name', e.target.value)}
            required
            autoComplete="name"
            aria-invalid={Boolean(errors.name)}
          />
        </label>

        <label>
          <span>Email</span>
          <input
            id="email"
            type="email"
            value={data.email}
            onChange={(e) => setData('email', e.target.value)}
            required
            autoComplete="username"
            aria-invalid={Boolean(errors.email)}
          />
        </label>

        {mustVerifyEmail && user.email_verified_at === null && (
          <div className="profile-verify">
            <p className="muted-text">
              Alamat email Anda belum diverifikasi.{' '}
              <Link href={route('verification.send')} method="post" as="button" className="text-link inline-btn">
                Kirim ulang email verifikasi
              </Link>
            </p>
            {status === 'verification-link-sent' && (
              <p className="ok" role="status" style={{ marginTop: 8 }}>
                Tautan verifikasi baru telah dikirim ke alamat email Anda.
              </p>
            )}
          </div>
        )}

        <div className="profile-actions">
          <button type="submit" className="button" disabled={processing} aria-busy={processing}>
            {processing ? 'Menyimpan…' : 'Simpan Perubahan'}
          </button>
        </div>
      </form>
    </section>
  )
}
