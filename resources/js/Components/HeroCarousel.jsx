import { useCallback, useEffect, useRef, useState } from 'react'
import { Link } from '@inertiajs/react'

const AUTOPLAY_MS = 6000

const SLIDES = {
  id: [
    {
      id: 'mega-mendung',
      image: '/images/campaigns/hero-1.jpg',
      alt: 'Kain batik tulis Mega Mendung Sogan Batik Daffa terhampar di atas batu travertine',
      focal: '72% 45%',
      headline: 'Warisan Mega Mendung',
      description: 'Kain batik tulis dan cap pilihan karya pengrajin Trusmi, Cirebon.',
      ctaLabel: 'Jelajahi Koleksi Kain',
      ctaHref: '/katalog?cat=kain',
    },
    {
      id: 'busana-kontemporer',
      image: '/images/campaigns/hero-2.jpg',
      alt: 'Koleksi busana batik kontemporer Batik Daffa dengan potongan rapi dan warna sogan hangat',
      focal: '72% 20%',
      headline: 'Busana Batik Kontemporer',
      description: 'Potongan rapi dan sejuk untuk keseharian maupun acara formal.',
      ctaLabel: 'Lihat Koleksi Pakaian',
      ctaHref: '/katalog?cat=pakaian',
    },
    {
      id: 'ketelitian-canting',
      image: '/images/campaigns/hero-3.jpg',
      alt: 'Lipatan kain batik Kawung dan Parang bersama canting kuningan di atas linen hangat',
      focal: '68% 48%',
      headline: 'Ketelitian di Setiap Canting',
      description: 'Pewarnaan sogan klasik di atas katun primis dengan karakter motif yang otentik.',
      ctaLabel: 'Belanja Sekarang',
      ctaHref: '/katalog',
    },
  ],
  en: [
    {
      id: 'mega-mendung',
      image: '/images/campaigns/hero-1.jpg',
      alt: 'Hand-drawn Mega Mendung Sogan batik fabric draped over travertine stone',
      focal: '72% 45%',
      headline: 'Mega Mendung Heritage',
      description: 'Hand-drawn and stamped batik textiles crafted by Trusmi artisans in Cirebon.',
      ctaLabel: 'Explore Fabrics',
      ctaHref: '/katalog?cat=kain',
    },
    {
      id: 'busana-kontemporer',
      image: '/images/campaigns/hero-2.jpg',
      alt: 'Contemporary tailored Indonesian sogan batik apparel in a warm minimalist pavilion',
      focal: '72% 20%',
      headline: 'Contemporary Batik Wear',
      description: 'Clean, breathable tailoring designed for everyday elegance and formal occasions.',
      ctaLabel: 'Shop Apparel',
      ctaHref: '/katalog?cat=pakaian',
    },
    {
      id: 'ketelitian-canting',
      image: '/images/campaigns/hero-3.jpg',
      alt: 'Folded Kawung and Parang batik tulis fabrics beside a brass canting tool on ivory linen',
      focal: '68% 48%',
      headline: 'Craft in Every Stroke',
      description: 'Classic soga dyeing on fine primis cotton with enduring artisanal character.',
      ctaLabel: 'Shop Collection',
      ctaHref: '/katalog',
    },
  ],
}

const IconChevronLeft = () => (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="m15 18-6-6 6-6" />
  </svg>
)

const IconChevronRight = () => (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="m9 18 6-6-6-6" />
  </svg>
)

export default function HeroCarousel({ lang = 'id', slides: dynamicSlides = [] }) {
  const slides = dynamicSlides?.length ? dynamicSlides : (SLIDES[lang] || SLIDES.id)
  const [index, setIndex] = useState(0)

  useEffect(() => {
    if (index >= slides.length) setIndex(0)
  }, [slides.length, index])
  const [paused, setPaused] = useState(false)
  const [inView, setInView] = useState(true)
  const [reducedMotion, setReducedMotion] = useState(false)

  const sectionRef = useRef(null)
  const touchStartX = useRef(null)
  const touchStartY = useRef(null)

  const goTo = useCallback(
    (next) => {
      setIndex((prev) => {
        const len = slides.length
        const target = typeof next === 'function' ? next(prev) : next
        return ((target % len) + len) % len
      })
    },
    [slides.length]
  )

  const prevSlide = useCallback(() => goTo((i) => i - 1), [goTo])
  const nextSlide = useCallback(() => goTo((i) => i + 1), [goTo])

  useEffect(() => {
    const mq = window.matchMedia('(prefers-reduced-motion: reduce)')
    const sync = () => setReducedMotion(mq.matches)
    sync()
    mq.addEventListener?.('change', sync)
    return () => mq.removeEventListener?.('change', sync)
  }, [])

  useEffect(() => {
    const node = sectionRef.current
    if (!node || typeof IntersectionObserver === 'undefined') return
    const io = new IntersectionObserver(
      (entries) => {
        setInView(entries[0]?.isIntersecting ?? true)
      },
      { threshold: 0.25 }
    )
    io.observe(node)
    return () => io.disconnect()
  }, [])

  useEffect(() => {
    if (paused || !inView || reducedMotion) return
    const id = window.setInterval(() => {
      setIndex((prev) => (prev + 1) % slides.length)
    }, AUTOPLAY_MS)
    return () => window.clearInterval(id)
  }, [paused, inView, reducedMotion, slides.length])

  const onKeyDown = (e) => {
    if (e.key === 'ArrowLeft') {
      e.preventDefault()
      prevSlide()
    } else if (e.key === 'ArrowRight') {
      e.preventDefault()
      nextSlide()
    }
  }

  const onTouchStart = (e) => {
    const t = e.touches?.[0]
    if (!t) return
    touchStartX.current = t.clientX
    touchStartY.current = t.clientY
  }

  const onTouchEnd = (e) => {
    if (touchStartX.current === null) return
    const t = e.changedTouches?.[0]
    if (!t) return
    const dx = t.clientX - touchStartX.current
    const dy = t.clientY - (touchStartY.current ?? t.clientY)
    touchStartX.current = null
    touchStartY.current = null
    if (Math.abs(dx) > 42 && Math.abs(dx) > Math.abs(dy)) {
      if (dx < 0) nextSlide()
      else prevSlide()
    }
  }

  return (
    <section
      ref={sectionRef}
      className="hero-carousel"
      aria-roledescription="carousel"
      aria-label={lang === 'en' ? 'Batik Daffa campaign highlights' : 'Kampanye koleksi Batik Daffa'}
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      onFocus={() => setPaused(true)}
      onBlur={(e) => {
        if (!e.currentTarget.contains(e.relatedTarget)) setPaused(false)
      }}
      onKeyDown={onKeyDown}
      onTouchStart={onTouchStart}
      onTouchEnd={onTouchEnd}
    >
      <div className="hero-stage" aria-live="off">
        {slides.map((slide, i) => {
          const active = i === index
          return (
            <div
              key={slide.id}
              className={`hero-slide${active ? ' active' : ''}`}
              role="group"
              aria-roledescription="slide"
              aria-label={`${i + 1} / ${slides.length}`}
              aria-hidden={!active}
            >
              <div className="hero-slide-media">
                <img
                  src={slide.image}
                  alt={slide.alt}
                  style={{ '--focal': slide.focal }}
                  loading={i === 0 ? 'eager' : 'lazy'}
                  fetchPriority={i === 0 ? 'high' : 'auto'}
                  decoding={i === 0 ? 'sync' : 'async'}
                />
              </div>

              <div className="hero-slide-overlay">
                <div className="container">
                  <div className="hero-slide-content">
                    {i === 0 ? <h1>{slide.headline}</h1> : <h2>{slide.headline}</h2>}
                    <p>{slide.description}</p>
                    <Link
                      className="button button-lg"
                      href={slide.ctaHref}
                      tabIndex={active ? 0 : -1}
                    >
                      {slide.ctaLabel}
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          )
        })}
      </div>

      <div className="hero-controls">
        <div className="container hero-controls-inner">
          <div className="hero-dots" role="tablist" aria-label={lang === 'en' ? 'Choose campaign slide' : 'Pilih slide kampanye'}>
            {slides.map((slide, i) => {
              const active = i === index
              return (
                <button
                  key={slide.id}
                  type="button"
                  role="tab"
                  className={`hero-dot${active ? ' active' : ''}`}
                  aria-selected={active}
                  aria-label={`${lang === 'en' ? 'Slide' : 'Slide'} ${i + 1}: ${slide.headline}`}
                  onClick={() => goTo(i)}
                >
                  <span className="hero-dot-bar" />
                </button>
              )
            })}
          </div>

          <div className="hero-arrows">
            <button
              type="button"
              className="hero-arrow"
              onClick={prevSlide}
              aria-label={lang === 'en' ? 'Previous slide' : 'Slide sebelumnya'}
            >
              <IconChevronLeft />
            </button>
            <button
              type="button"
              className="hero-arrow"
              onClick={nextSlide}
              aria-label={lang === 'en' ? 'Next slide' : 'Slide berikutnya'}
            >
              <IconChevronRight />
            </button>
          </div>
        </div>
      </div>
    </section>
  )
}
