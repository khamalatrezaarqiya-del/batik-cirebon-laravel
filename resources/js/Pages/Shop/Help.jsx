import { Link } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { makeT, readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const TOPICS = [
  { slug: 'cara-belanja', key: 'cara_belanja', nav: 'help_how' },
  { slug: 'ukuran', key: 'ukuran', nav: 'help_size' },
  { slug: 'pengiriman', key: 'pengiriman', nav: 'help_pay' },
]

const SIZE_ROWS = [
  ['S', '98 – 102 cm', '70 cm', '44 cm'],
  ['M', '104 – 108 cm', '72 cm', '46 cm'],
  ['L', '110 – 114 cm', '74 cm', '48 cm'],
  ['XL', '116 – 120 cm', '76 cm', '50 cm'],
]

export default function Help({ topic }) {
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
        <SectionTitle subtitle="Informasi lengkap mengenai pemesanan, panduan ukuran, pembayaran, dan pengiriman Batik Daffa.">
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
          </nav>

          <div className="help-card">
            <h3>{t(`help_${current.key}_title`)}</h3>
            <div className="prose">
              <p>{t(`help_${current.key}_p1`)}</p>
              <p>{t(`help_${current.key}_p2`)}</p>
            </div>

            {current.slug === 'ukuran' && (
              <div className="size-table-wrap" style={{ marginTop: 20 }}>
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
                    {SIZE_ROWS.map(([sz, chest, len, sh]) => (
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
            )}

            <div className="help-callout">
              <div>
                <strong>Butuh bantuan lebih lanjut?</strong>
                <p className="muted-text">Tim Batik Daffa siap membantu rekomendasi motif, ukuran, hingga pengiriman.</p>
              </div>
              <div className="help-callout-actions">
                <a className="button" href={waLink('Halo Batik Daffa, saya butuh bantuan mengenai pemesanan batik.')} target="_blank" rel="noreferrer">
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
