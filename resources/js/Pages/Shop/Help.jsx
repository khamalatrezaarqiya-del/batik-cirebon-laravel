import { Link, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { makeT, readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const TOPICS = [
  { slug: 'cara-belanja', key: 'cara_belanja', nav: 'help_how' },
  { slug: 'ukuran', key: 'ukuran', nav: 'help_size' },
  { slug: 'pengiriman', key: 'pengiriman', nav: 'help_pay' },
  { slug: 'garansi', key: 'garansi', nav: 'help_garansi' },
]

const SIZE_ROWS_PRIA = [
  ['S', '100 – 104 cm', '70 cm', '44 cm'],
  ['M', '106 – 110 cm', '72 cm', '46 cm'],
  ['L', '112 – 116 cm', '74 cm', '48 cm'],
  ['XL', '118 – 122 cm', '76 cm', '50 cm'],
  ['XXL', '124 – 128 cm', '78 cm', '52 cm'],
]

const SIZE_ROWS_WANITA = [
  ['S', '90 – 94 cm', '65 cm', '38 cm'],
  ['M', '96 – 100 cm', '67 cm', '40 cm'],
  ['L', '102 – 106 cm', '69 cm', '42 cm'],
  ['XL', '108 – 112 cm', '71 cm', '44 cm'],
  ['XXL', '114 – 118 cm', '73 cm', '46 cm'],
]

export default function Help({ topic }) {
  const { props } = usePage()
  const storeWhatsapp = props?.storeWhatsapp || ''
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)
  const current = TOPICS.find((tp) => tp.slug === topic) || TOPICS[0]

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  return (
    <ShopLayout title={t('footer_help')}>
      <div className="section">
        <SectionTitle subtitle="Informasi lengkap mengenai pemesanan, panduan ukuran pria & wanita, pembayaran, pengiriman JNE/J&T/Tiki, dan garansi Batik Daffa.">
          {t('footer_help')}
        </SectionTitle>

        <div className="help-layout">
          <nav className="help-nav" aria-label="Topik bantuan">
            {TOPICS.map((tp) => {
              const active = tp.slug === current.slug
              return (
                <Link
                  key={tp.slug}
                  href={`/bantuan/${tp.slug}`}
                  className={active ? 'active' : ''}
                  aria-current={active ? 'page' : undefined}
                >
                  {t(tp.nav)}
                </Link>
              )
            })}
            <Link href="/tentang">Profil Usaha &amp; Grosir B2B</Link>
          </nav>

          <div className="help-card">
            <h3>{t(`help_${current.key}_title`)}</h3>
            <div className="prose">
              <p>{t(`help_${current.key}_p1`)}</p>
              <p>{t(`help_${current.key}_p2`)}</p>
            </div>

            {current.slug === 'ukuran' && (
              <>
                <h4 className="size-group-title">Busana Pria (Kemeja Reguler)</h4>
                <div className="size-table-wrap">
                  <table className="size-table">
                    <thead>
                      <tr>
                        <th>Ukuran</th>
                        <th>Lingkar Dada</th>
                        <th>Panjang Baju</th>
                        <th>Lebar Bahu</th>
                      </tr>
                    </thead>
                    <tbody>
                      {SIZE_ROWS_PRIA.map(([sz, chest, len, sh]) => (
                        <tr key={sz}>
                          <td><strong>{sz}</strong></td>
                          <td>{chest}</td>
                          <td>{len}</td>
                          <td>{sh}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                <h4 className="size-group-title">Busana Wanita (Blouse &amp; Dress)</h4>
                <div className="size-table-wrap">
                  <table className="size-table">
                    <thead>
                      <tr>
                        <th>Ukuran</th>
                        <th>Lingkar Dada</th>
                        <th>Panjang Baju</th>
                        <th>Lebar Bahu</th>
                      </tr>
                    </thead>
                    <tbody>
                      {SIZE_ROWS_WANITA.map(([sz, chest, len, sh]) => (
                        <tr key={sz}>
                          <td><strong>{sz}</strong></td>
                          <td>{chest}</td>
                          <td>{len}</td>
                          <td>{sh}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </>
            )}

            <div className="help-callout">
              <div>
                <strong>Butuh bantuan lebih lanjut?</strong>
                <p className="muted-text">
                  Workshop: Jl. Raya Panembahan No. 52/468, Panembahan, Kec. Plered, Kab. Cirebon, Jawa Barat 45154.
                </p>
              </div>
              <div className="help-callout-actions">
                <a
                  className="button"
                  href={waLink('Halo Batik Daffa, saya butuh bantuan mengenai pemesanan batik.', storeWhatsapp)}
                  target="_blank"
                  rel="noreferrer"
                >
                  Hubungi WhatsApp
                </a>
                {current.slug === 'pengiriman' ? (
                  <Link className="button ghost" href="/pesanan">{t('track_title')}</Link>
                ) : (
                  <Link className="button ghost" href="/katalog">{t('nav_catalog')}</Link>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </ShopLayout>
  )
}
