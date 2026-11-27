import { Link, usePage } from '@inertiajs/react'
import { useEffect, useMemo, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import HeroCarousel from '../../Components/HeroCarousel.jsx'
import ProductCard from '../../Components/ProductCard.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { useReveal } from '../../Hooks/useReveal.js'
import { addToCart } from '../../Lib/cart.js'
import { makeT, readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const CATEGORY_MEDIA = {
  kain: '/images/categories/kain.jpg',
  pakaian: '/images/categories/pakaian.jpg',
  aksesoris: '/images/categories/aksesoris.jpg',
}

const IconPrev = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="m15 18-6-6 6-6" />
  </svg>
)

const IconNext = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="m9 18 6-6-6-6" />
  </svg>
)

const BEST_PAGE_SIZE = 4

export default function Home({ featured = [], newest = [], categories = [], heroSlides = [], featuredBanner = null }) {
  const { props } = usePage()
  const storeWhatsapp = props?.storeWhatsapp || ''
  const [lang, setLang] = useState(() => readLang())
  const [bestPage, setBestPage] = useState(0)
  const t = makeT(lang)

  useReveal([bestPage])

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  const maxId = Math.max(...featured.map((p) => p.id), ...newest.map((p) => p.id), 26)

  const bestPages = useMemo(() => {
    if (!featured.length) return []
    const chunks = []
    for (let i = 0; i < featured.length; i += BEST_PAGE_SIZE) {
      chunks.push(featured.slice(i, i + BEST_PAGE_SIZE))
    }
    return chunks
  }, [featured])

  const safeBestPage = bestPages.length ? bestPage % bestPages.length : 0
  const activeBestItems = bestPages[safeBestPage] || []

  const prevBest = () => setBestPage((p) => (p - 1 + bestPages.length) % bestPages.length)
  const nextBest = () => setBestPage((p) => (p + 1) % bestPages.length)

  const banner = featuredBanner || {
    label: lang === 'en' ? 'Signature Capsule' : 'Koleksi Pilihan',
    title: lang === 'en' ? 'Sogan & Mega Mendung Essentials' : 'Harmoni Sogan & Mega Mendung',
    description:
      lang === 'en'
        ? 'Curated coastal and classic motifs in warm earth tones—crafted to move effortlessly from formal gatherings to everyday wear.'
        : 'Perpaduan motif pesisir Cirebon dan nuansa sogan hangat dalam kain lembaran, kemeja, serta selendang untuk beragam momen.',
    ctaLabel: lang === 'en' ? 'Explore Collection' : 'Lihat Koleksi Mega Mendung',
    ctaHref: '/katalog?motif=mega',
    image: '/images/campaigns/featured-collection.jpg',
  }

  return (
    <ShopLayout overlayHeader>
      {/* Section 1 — Full-Bleed 16:9 Hero Campaign Carousel (Navbar floats cleanly over top) */}
      <HeroCarousel lang={lang} slides={heroSlides} />

      {/* Section 2 — Shop by Category (Wide Asymmetric Editorial Mosaic) */}
      <section className="section">
        <SectionTitle action={<Link className="text-link" href="/katalog">{t('see_all')}</Link>}>
          {t('browse_category')}
        </SectionTitle>

        <div className="category-mosaic">
          {categories.map((cat, idx) => (
            <Link
              key={cat.slug}
              href={`/katalog?cat=${cat.slug}`}
              className={`category-tile${idx === 0 ? ' category-tile-lead' : ''} reveal`}
              style={{ '--stagger': `${idx * 60}ms` }}
            >
              <div className="category-tile-media">
                <img
                  src={cat.image || CATEGORY_MEDIA[cat.slug] || '/images/categories/kain.jpg'}
                  alt={cat.nama}
                  loading="lazy"
                />
              </div>
              <div className="category-tile-meta">
                <h3>{cat.nama}</h3>
                <span>{cat.products_count ?? 0} {t('products_count')}</span>
              </div>
            </Link>
          ))}
        </div>
      </section>

      {/* Section 3 — Best Sellers (Compact 3-Product Editorial Row like New Arrivals + Prev/Next) */}
      {activeBestItems.length > 0 && (
        <section className="section section-bestsellers">
          <SectionTitle
            subtitle={t('featured_sub')}
            action={
              <div className="section-nav-group">
                {bestPages.length > 1 && (
                  <div className="carousel-nav" aria-label="Navigasi pilihan terlaris">
                    <span className="carousel-counter">
                      {safeBestPage + 1} / {bestPages.length}
                    </span>
                    <button
                      type="button"
                      className="carousel-nav-btn"
                      onClick={prevBest}
                      aria-label="Produk terlaris sebelumnya"
                    >
                      <IconPrev />
                    </button>
                    <button
                      type="button"
                      className="carousel-nav-btn"
                      onClick={nextBest}
                      aria-label="Produk terlaris selanjutnya"
                    >
                      <IconNext />
                    </button>
                  </div>
                )}
                <Link className="text-link" href="/katalog?sort=best">{t('see_all')}</Link>
              </div>
            }
          >
            {t('featured')}
          </SectionTitle>

          <div className="bestsellers-mosaic" key={`best-page-${safeBestPage}`}>
            {activeBestItems.map((p, idx) => (
              <ProductCard
                key={p.id}
                product={p}
                t={t}
                addToCart={addToCart}
                index={idx}
              />
            ))}
          </div>
        </section>
      )}

      {/* Section 4 — Featured Collection (Wide Editorial Split) */}
      <section className="section">
        <div className="editorial-feature reveal">
          <div className="editorial-feature-media">
            <img src={banner.image} alt={banner.title} loading="lazy" />
          </div>
          <div className="editorial-feature-copy">
            <span className="editorial-label">{banner.label}</span>
            <h2>{banner.title}</h2>
            <p>{banner.description}</p>
            <Link className="button button-lg" href={banner.ctaHref}>
              {banner.ctaLabel}
            </Link>
          </div>
        </div>
      </section>

      {/* Section 5 — New Arrivals (Varied Full-Width Editorial Row) */}
      <section className="section">
        <SectionTitle
          subtitle={t('newest_sub')}
          action={<Link className="text-link" href="/katalog?sort=new">{t('see_all')}</Link>}
        >
          {t('newest')}
        </SectionTitle>

        <div className="newest-mosaic">
          {newest.map((p, idx) => (
            <ProductCard key={p.id} product={p} t={t} addToCart={addToCart} maxId={maxId} index={idx} />
          ))}
        </div>
      </section>

      {/* Section 6 — Highlighted Custom & Grosir B2B Order (Clean Human Copy, Zero Symbols, Positioned After New Arrivals) */}
      <section className="section">
        <div className="custom-highlight reveal">
          <div className="custom-highlight-copy">
            <span className="custom-highlight-kicker">
              {lang === 'en' ? 'Custom & Wholesale Service' : 'Layanan Custom & Grosir B2B'}
            </span>
            <h2>
              {lang === 'en' ? 'Looking for Custom Batik or Wholesale?' : 'Ingin Pesan Batik Custom atau Grosir?'}
            </h2>
            <p>
              {lang === 'en'
                ? 'Discuss custom motifs, fabrics (Viscose, Doby, ATBM, Cotton), personal measurements, family uniforms, or reseller and consignment partnerships directly with Batik Daffa.'
                : 'Diskusikan pilihan motif pesisiran, jenis bahan (Viscose, Doby, ATBM, Katun), ukuran pribadi, seragam instansi, hingga kerja sama grosir dan konsinyasi langsung bersama tim Batik Daffa.'}
            </p>
          </div>

          <div className="custom-highlight-action">
            <a
              className="button button-lg custom-wa-btn"
              href={waLink(
                'Halo Admin Batik Daffa, saya ingin bertanya mengenai pesanan batik custom, seragam, atau penawaran grosir. Bisa dibantu informasinya?',
                storeWhatsapp
              )}
              target="_blank"
              rel="noreferrer"
            >
              {lang === 'en' ? 'Ask & Order via WhatsApp' : 'Tanya & Pesan via WhatsApp'}
            </a>
            <Link className="button ghost button-lg custom-profile-btn" href="/tentang#grosir">
              {lang === 'en' ? 'About & B2B Info' : 'Profil & Info Grosir'}
            </Link>
          </div>
        </div>
      </section>
    </ShopLayout>
  )
}
