import { Link } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import ProductCard from '../../Components/ProductCard.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import Motif from '../../Components/Motif.jsx'
import { useReveal } from '../../Hooks/useReveal.js'
import { addToCart } from '../../Lib/cart.js'
import { makeT, readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const IconShield = () => (
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M12 3l7 3v6c0 4.4-3 7.6-7 9-4-1.4-7-4.6-7-9V6l7-3Z" />
    <path d="m9 12 2 2 4-4.5" />
  </svg>
)
const IconTruck = () => (
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M2 7h11v9H2zM13 10h4.5l3.5 3.4V16h-8z" />
    <circle cx="6.5" cy="18" r="1.6" />
    <circle cx="17" cy="18" r="1.6" />
  </svg>
)
const IconCard = () => (
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <rect x="2.5" y="5.5" width="19" height="13" rx="2.5" />
    <path d="M2.5 10h19" />
  </svg>
)
const IconChat = () => (
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M20 12a7.5 7.5 0 0 1-7.5 7.5L7 21l1-4.2A7.5 7.5 0 1 1 20 12Z" />
  </svg>
)

const TILES = [
  { cat: 'kain', motif: 'mega', base: '#5c3c24', accent: '#e4c9a2' },
  { cat: 'pakaian', motif: 'parang', base: '#4a3222', accent: '#dec098' },
  { cat: 'aksesoris', motif: 'truntum', base: '#6a4834', accent: '#ead6bc' },
]

export default function Home({ featured = [], newest = [], categories = [] }) {
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)
  useReveal()

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const countOf = (slug) => categories.find((c) => c.slug === slug)?.products_count ?? 0
  const maxId = Math.max(...featured.map((p) => p.id), ...newest.map((p) => p.id), 26)

  return (
    <ShopLayout>
      <section className="section">
        <div className="hero">
          <div className="hero-grid">
            <div className="hero-copy">
              <span className="hero-tagline">{t('hero_tag')}</span>
              <h1>{t('hero_title')}</h1>
              <p>{t('hero_sub')}</p>
              <div className="hero-actions">
                <Link className="button" href="/katalog">{t('hero_cta')}</Link>
                <a className="button ghost" href={waLink()} target="_blank" rel="noreferrer">{t('hero_cta2')}</a>
              </div>
            </div>

            <div className="hero-collage">
              <Link className="hero-art hero-art-main" href="/katalog?motif=mega">
                <Motif motif="mega" base="#4b3220" accent="#e5cba5" />
                <div className="hero-art-label">Mega Mendung Sogan</div>
              </Link>
              <div className="hero-art-pair">
                <Link className="hero-art hero-art-sub" href="/katalog?motif=kawung">
                  <Motif motif="kawung" base="#66452d" accent="#ecd6b7" />
                  <div className="hero-art-label">Kawung Klasik</div>
                </Link>
                <Link className="hero-art hero-art-sub" href="/katalog?motif=parang">
                  <Motif motif="parang" base="#3c291b" accent="#dec29d" />
                  <div className="hero-art-label">Parang Sogan</div>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="trust">
        <div className="trust-item">
          <div className="trust-ico"><IconShield /></div>
          <div><strong>{t('trust_quality')}</strong><small>{t('trust_quality_desc')}</small></div>
        </div>
        <div className="trust-item">
          <div className="trust-ico"><IconTruck /></div>
          <div><strong>{t('trust_ship')}</strong><small>{t('trust_ship_desc')}</small></div>
        </div>
        <div className="trust-item">
          <div className="trust-ico"><IconCard /></div>
          <div><strong>{t('trust_pay')}</strong><small>{t('trust_pay_desc')}</small></div>
        </div>
        <div className="trust-item">
          <div className="trust-ico"><IconChat /></div>
          <div><strong>{t('trust_help')}</strong><small>{t('trust_help_desc')}</small></div>
        </div>
      </section>

      <section className="section">
        <SectionTitle action={<Link className="text-link" href="/katalog">{t('see_all')}</Link>}>
          {t('browse_category')}
        </SectionTitle>

        <div className="tiles">
          {TILES.map((tile) => (
            <Link key={tile.cat} className="tile reveal" href={`/katalog?cat=${tile.cat}`}>
              <div className="tile-thumb">
                <Motif motif={tile.motif} base={tile.base} accent={tile.accent} />
              </div>
              <div className="tile-body">
                <strong>{t(tile.cat)}</strong>
                <span>{countOf(tile.cat)} {t('products_count')}</span>
              </div>
            </Link>
          ))}
        </div>

        <SectionTitle subtitle={t('featured_sub')} action={<Link className="text-link" href="/katalog">{t('see_all')}</Link>}>
          {t('featured')}
        </SectionTitle>
        <div className="grid">
          {featured.map((p) => (
            <ProductCard key={p.id} product={p} t={t} addToCart={addToCart} maxId={maxId} />
          ))}
        </div>

        <SectionTitle subtitle={t('newest_sub')} action={<Link className="text-link" href="/katalog">{t('see_all')}</Link>}>
          {t('newest')}
        </SectionTitle>
        <div className="grid">
          {newest.map((p) => (
            <ProductCard key={p.id} product={p} t={t} addToCart={addToCart} maxId={maxId} />
          ))}
        </div>
      </section>

      <section className="section">
        <div className="cta">
          <div>
            <h2>{t('cta_title')}</h2>
            <p>{t('cta_sub')}</p>
          </div>
          <a className="button" href={waLink()} target="_blank" rel="noreferrer">{t('cta_button')}</a>
        </div>
      </section>
    </ShopLayout>
  )
}
