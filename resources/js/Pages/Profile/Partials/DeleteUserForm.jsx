import { useForm } from '@inertiajs/react'
import { useEffect, useRef, useState } from 'react'

export default function DeleteUserForm() {
  const [confirming, setConfirming] = useState(false)
  const passwordInput = useRef()

  const { data, setData, delete: destroy, processing, reset, errors, clearErrors } = useForm({
    password: '',
  })

  const closeModal = () => {
    setConfirming(false)
    clearErrors()
    reset()
  }

  useEffect(() => {
    if (!confirming) return
    const onKey = (e) => {
      if (e.key === 'Escape') closeModal()
    }
    window.addEventListener('keydown', onKey)
    passwordInput.current?.focus()
    return () => window.removeEventListener('keydown', onKey)
  }, [confirming])

  const deleteUser = (e) => {
    e.preventDefault()
    destroy(route('profile.destroy'), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => passwordInput.current?.focus(),
      onFinish: () => reset(),
    })
  }

  return (
    <section className="profile-section">
      <header className="profile-head">
        <h2>Hapus Akun</h2>
        <p className="muted-text">
          Menghapus akun akan menghapus data profil secara permanen. Pastikan Anda sudah mencatat kode pesanan penting sebelumnya.
        </p>
      </header>

      <div className="profile-actions">
        <button type="button" className="button danger" onClick={() => setConfirming(true)}>
          Hapus Akun Permanen
        </button>
      </div>

      {confirming && (
        <div className="modal-backdrop" onClick={closeModal} role="presentation">
          <div
            className="modal-panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-modal-title"
            onClick={(e) => e.stopPropagation()}
          >
            <form onSubmit={deleteUser} className="form form-inline">
              <h2 id="delete-modal-title">Konfirmasi Penghapusan Akun</h2>
              <p className="muted-text">
                Masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun ini secara permanen.
              </p>

              {errors.password && <p className="error" role="alert">{errors.password}</p>}

              <label>
                <span>Kata Sandi</span>
                <input
                  id="delete_password"
                  type="password"
                  name="password"
                  ref={passwordInput}
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  placeholder="Masukkan kata sandi"
                  required
                />
              </label>

              <div className="profile-actions">
                <button type="button" className="button ghost" onClick={closeModal}>
                  Batal
                </button>
                <button type="submit" className="button danger" disabled={processing}>
                  {processing ? 'Menghapus…' : 'Ya, Hapus Akun'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </section>
  )
}
