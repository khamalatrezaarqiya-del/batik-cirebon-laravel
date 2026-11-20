import { Link } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { makeT, readLang } from '../../Lib/i18n.js'

const TOPICS = [
  { slug: 'cara-belanja', key: 'cara_belanja', nav: 'help_how' },
  { slug: 'ukuran', key: 'ukuran', nav: 'help_size' },
  { slug: 'pengiriman', key: 'pengiriman', nav: 'help_pay' },
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
        <SectionTitle>{t('footer_help')}</SectionTitle>
        <div className="help-layout">
          <nav className="help-nav">
            {TOPICS.map((tp) => (
              <Link key={tp.slug} href={`/bantuan/${tp.slug}`} className={tp.slug === current.slug ? 'active' : ''}>
                {t(tp.nav)}
              </Link>
            ))}
          </nav>
          <div>
            <h3 style={{ fontSize: '1.15rem', fontWeight: 700, marginBottom: 14, color: 'var(--tinta)' }}>
              {t(`help_${current.key}_title`)}
            </h3>
            <div className="prose">
              <p>{t(`help_${current.key}_p1`)}</p>
              <p>{t(`help_${current.key}_p2`)}</p>
            </div>
          </div>
        </div>
      </div>
    </ShopLayout>
  )
}
