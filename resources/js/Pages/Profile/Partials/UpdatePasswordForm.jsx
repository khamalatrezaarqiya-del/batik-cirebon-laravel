import { useForm } from '@inertiajs/react'
import { useRef } from 'react'

export default function UpdatePasswordForm() {
  const passwordInput = useRef()
  const currentPasswordInput = useRef()

  const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
  })

  const updatePassword = (e) => {
    e.preventDefault()

    put(route('password.update'), {
      preserveScroll: true,
      onSuccess: () => reset(),
      onError: (errs) => {
        if (errs.password) {
          reset('password', 'password_confirmation')
          passwordInput.current?.focus()
        }
        if (errs.current_password) {
          reset('current_password')
          currentPasswordInput.current?.focus()
        }
      },
    })
  }

  const firstErr = errors.current_password || errors.password || errors.password_confirmation

  return (
    <section className="profile-section">
      <header className="profile-head">
        <h2>Ubah Kata Sandi</h2>
        <p className="muted-text">Gunakan kombinasi kata sandi minimal 8 karakter agar akun tetap aman.</p>
      </header>

      <form onSubmit={updatePassword} className="form form-inline">
        {recentlySuccessful && (
          <p className="ok" role="status">Kata sandi berhasil diperbarui.</p>
        )}
        {firstErr && <p className="error" role="alert">{firstErr}</p>}

        <label>
          <span>Kata Sandi Saat Ini</span>
          <input
            id="current_password"
            ref={currentPasswordInput}
            value={data.current_password}
            onChange={(e) => setData('current_password', e.target.value)}
            type="password"
            autoComplete="current-password"
            aria-invalid={Boolean(errors.current_password)}
          />
        </label>

        <label>
          <span>Kata Sandi Baru</span>
          <input
            id="password"
            ref={passwordInput}
            value={data.password}
            onChange={(e) => setData('password', e.target.value)}
            type="password"
            autoComplete="new-password"
            aria-invalid={Boolean(errors.password)}
          />
        </label>

        <label>
          <span>Konfirmasi Kata Sandi Baru</span>
          <input
            id="password_confirmation"
            value={data.password_confirmation}
            onChange={(e) => setData('password_confirmation', e.target.value)}
            type="password"
            autoComplete="new-password"
            aria-invalid={Boolean(errors.password_confirmation)}
          />
        </label>

        <div className="profile-actions">
          <button type="submit" className="button" disabled={processing} aria-busy={processing}>
            {processing ? 'Menyimpan…' : 'Perbarui Kata Sandi'}
          </button>
        </div>
      </form>
    </section>
  )
}
