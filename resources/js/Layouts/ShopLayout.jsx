import { useEffect, useRef, useState } from 'react'
import { Link, router, usePage } from '@inertiajs/react'
import { waLink } from '../Lib/whatsapp.js'
import { readCart, syncCartFromServer } from '../Lib/cart.js'
import { makeT, readLang, writeLang } from '../Lib/i18n.js'
import Toast from '../Components/Toast.jsx'
import CartDrawer from '../Components/CartDrawer.jsx'

const TITLES = {
  '': 'nav_home',
  katalog: 'nav_catalog',
  tentang: 'nav_about',
  keranjang: 'nav_cart',
  checkout: 'checkout_title',
  pesanan: 'orders_title',
  bantuan: 'footer_help',
  dashboard: 'account',
  profile: 'profile_title',
}

const MAPS_URL =
  'https://www.google.com/maps/search/?api=1&query=Jl.+Raya+Panembahan+No.+52%2F468+Panembahan+Plered+Cirebon+45154'
const IG_URL = 'https://www.instagram.com/daffabatikcirebon/'

const IconCart = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
    <path d="M3 6h18" />
    <path d="M16 10a4 4 0 0 1-8 0" />
  </svg>
)

const IconUser = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <circle cx="12" cy="8" r="3.75" />
    <path d="M4.5 20a7.5 7.5 0 0 1 15 0" />
  </svg>
)

const IconSearch = () => (
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <circle cx="11" cy="11" r="7" />
    <path d="m20 20-3.8-3.8" />
  </svg>
)

const IconMenu = () => (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" aria-hidden="true">
    <path d="M4 7h16M4 12h16M4 17h16" />
  </svg>
)

const IconClose = () => (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" aria-hidden="true">
    <path d="M18 6 6 18M6 6l12 12" />
  </svg>
)

export default function ShopLayout({ children, title, overlayHeader = false }) {
  const { url, props } = usePage()
  const auth = props?.auth?.user || null
  const storeWhatsapp = props?.storeWhatsapp || ''
  const navCategories = props?.navCategories?.length
    ? props.navCategories
    : [
        { slug: 'kain', nama: 'Kain' },
        { slug: 'pakaian', nama: 'Pakaian' },
        { slug: 'aksesoris', nama: 'Aksesoris' },
      ]

  const [lang, setLang] = useState(() => readLang())
  const [cart, setCart] = useState(() => readCart())
  const [drawerOpen, setDrawerOpen] = useState(false)
  const [pastHero, setPastHero] = useState(!overlayHeader)
  const [menuOpen, setMenuOpen] = useState(false)
  const [searchOpen, setSearchOpen] = useState(false)
  const [q, setQ] = useState('')
  const searchInputRef = useRef(null)

  const t = makeT(lang)
  const path = url.split('?')[0]
  const search = url.includes('?') ? '?' + url.split('?')[1] : ''
  const cartCount = cart.reduce((s, i) => s + i.qty, 0)

  const toggleLang = () => {
    const next = lang === 'id' ? 'en' : 'id'
    writeLang(next)
    setLang(next)
  }

  useEffect(() => {
    const syncCart = () => setCart(readCart())
    syncCart()
    syncCartFromServer()
    window.addEventListener('cart:change', syncCart)
    return () => window.removeEventListener('cart:change', syncCart)
  }, [])

  // Otomatis sinkronkan foto, harga, produk & keranjang saat kembali dari tab Admin ke tab Toko Web
  useEffect(() => {
    const isSyncablePage =
      path === '/' ||
      path === '/katalog' ||
      path.startsWith('/produk/') ||
      path === '/keranjang'

    if (!isSyncablePage) return

    let hiddenAt = 0
    const onVisibility = () => {
      if (document.visibilityState === 'hidden') {
        hiddenAt = Date.now()
        return
      }
      if (document.visibilityState === 'visible' && hiddenAt > 0 && Date.now() - hiddenAt > 1500) {
        hiddenAt = 0
        syncCartFromServer()
        router.reload({ preserveScroll: true, preserveState: true })
      }
    }
    document.addEventListener('visibilitychange', onVisibility)
    return () => document.removeEventListener('visibilitychange', onVisibility)
  }, [path])

  // Header transparan menyatu dengan slider di beranda; berubah menjadi header solid saat slider tergeser ke bawah
  useEffect(() => {
    if (!overlayHeader) {
      setPastHero(true)
      return
    }

    const evaluate = () => {
      const hero = document.querySelector('.hero-carousel')
      if (hero) {
        const rect = hero.getBoundingClientRect()
        setPastHero(rect.bottom <= 74)
      } else {
        setPastHero(window.scrollY > 420)
      }
    }

    evaluate()
    window.addEventListener('scroll', evaluate, { passive: true })
    window.addEventListener('resize', evaluate, { passive: true })
    return () => {
      window.removeEventListener('scroll', evaluate)
      window.removeEventListener('resize', evaluate)
    }
  }, [overlayHeader, path])

  useEffect(() => {
    setMenuOpen(false)
    setDrawerOpen(false)
    setSearchOpen(false)
  }, [path, search])

  useEffect(() => {
    if (searchOpen) {
      searchInputRef.current?.focus()
    }
  }, [searchOpen])

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'instant' })
  }, [path])

  useEffect(() => {
    const tr = makeT(lang)
    const seg = path.split('/')[1] || ''
    const base = 'Batik Daffa'
    const label = title || (TITLES[seg] ? tr(TITLES[seg]) : '')
    document.title = label ? `${label} · ${base}` : base
  }, [path, title, lang])

  const submitSearch = (e) => {
    e.preventDefault()
    const term = q.trim()
    setSearchOpen(false)
    router.visit(term ? `/katalog?q=${encodeURIComponent(term)}` : '/katalog')
  }

  const navClass = (href) => {
    const [p, s] = href.split('?')
    const active = s ? path === p && search.includes(s) : path === p && !search.includes('cat=')
    return active ? 'active' : ''
  }

  const handleCartTrigger = () => {
    if (path === '/keranjang' || path === '/checkout') {
      router.visit('/keranjang')
      return
    }
    setDrawerOpen(true)
  }

  const isOverlayFloating = overlayHeader && !pastHero && !menuOpen && !searchOpen

  return (
    <>
      <a href="#main-content" className="skip-link">
        {lang === 'en' ? 'Skip to main content' : 'Langsung ke konten utama'}
      </a>

      <header
        className={`hdr${overlayHeader ? ' hdr-home' : ''}${isOverlayFloating ? ' hdr-transparent' : ' scrolled'}${menuOpen ? ' open' : ''}`}
      >
        <div className="hdr-inner">
          <button
            type="button"
            className="burger"
            onClick={() => setMenuOpen((v) => !v)}
            aria-label={t('menu')}
            aria-expanded={menuOpen}
          >
            {menuOpen ? <IconClose /> : <IconMenu />}
          </button>

          <Link className="logo" href="/">
            BATIK <span>DAFFA</span>
          </Link>

          <nav className="mainnav" aria-label="Navigasi utama">
            <Link href="/" className={path === '/' ? 'active' : ''}>{t('nav_home')}</Link>
            <Link href="/katalog" className={navClass('/katalog')}>{t('nav_catalog')}</Link>
            {navCategories.slice(0, 4).map((cat) => (
              <Link key={cat.slug} href={`/katalog?cat=${cat.slug}`} className={navClass(`/katalog?cat=${cat.slug}`)}>
                {lang === 'en' && ['kain', 'pakaian', 'aksesoris'].includes(cat.slug) ? t(cat.slug) : cat.nama}
              </Link>
            ))}
            <Link href="/tentang" className={path === '/tentang' ? 'active' : ''}>{t('nav_about')}</Link>
            <Link href="/pesanan" className={path.startsWith('/pesanan') ? 'active' : ''}>{t('nav_orders')}</Link>
          </nav>

          <div className="hdr-actions">
            <button
              type="button"
              className={`icon-btn${searchOpen ? ' active' : ''}`}
              onClick={() => setSearchOpen((v) => !v)}
              aria-label={t('search')}
              aria-expanded={searchOpen}
            >
              <IconSearch />
            </button>

            <button
              type="button"
              className="icon-btn lang-btn"
              onClick={toggleLang}
              aria-label={lang === 'id' ? 'Switch to English' : 'Ganti ke Bahasa Indonesia'}
            >
              {lang === 'id' ? 'EN' : 'ID'}
            </button>

            <Link
              className="icon-btn"
              href={auth ? '/dashboard' : '/login'}
              aria-label={auth ? `${t('account')}: ${auth.name}` : t('login')}
              title={auth ? auth.name : t('login')}
            >
              <IconUser />
              <span className="hdr-action-label">{auth ? auth.name.split(' ')[0] : t('login')}</span>
            </Link>

            <button
              type="button"
              className={`icon-btn cart-trigger${cartCount > 0 ? ' has-items' : ''}`}
              onClick={handleCartTrigger}
              aria-label={`${t('nav_cart')}, ${cartCount} item`}
            >
              <IconCart />
              {cartCount > 0 && <span className="count-dot" aria-hidden="true">{cartCount}</span>}
            </button>
          </div>
        </div>

        {searchOpen && (
          <div className="hdr-search-bar">
            <div className="container">
              <form className="search-inline-form" onSubmit={submitSearch} role="search">
                <IconSearch />
                <input
                  ref={searchInputRef}
                  type="search"
                  value={q}
                  onChange={(e) => setQ(e.target.value)}
                  placeholder={t('search_ph')}
                  aria-label={t('search')}
                />
                {q && (
                  <button type="button" className="search-clear" onClick={() => setQ('')} aria-label="Bersihkan pencarian">
                    <IconClose />
                  </button>
                )}
                <button type="submit" className="button">{t('search')}</button>
              </form>
            </div>
          </div>
        )}
      </header>

      <main id="main-content" tabIndex="-1">{children}</main>

      <footer className="site-footer">
        <div className="container footer-grid">
          <div className="footer-brand-block">
            <Link className="logo" href="/">
              BATIK <span>DAFFA</span>
            </Link>
            <p className="footer-tagline">{t('footer_about')}</p>
            <p className="footer-location">{t('address')}</p>
          </div>

          <div className="footer-nav-columns">
            <div className="footer-group">
              <h3>{t('footer_category')}</h3>
              <ul>
                <li><Link href="/katalog">{t('all')}</Link></li>
                {navCategories.map((cat) => (
                  <li key={cat.slug}>
                    <Link href={`/katalog?cat=${cat.slug}`}>
                      {lang === 'en' && ['kain', 'pakaian', 'aksesoris'].includes(cat.slug) ? t(cat.slug) : cat.nama}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>

            <div className="footer-group">
              <h3>{t('footer_help')}</h3>
              <ul>
                <li><Link href="/tentang">{lang === 'en' ? 'About & Wholesale (B2B)' : 'Profil Usaha & Grosir B2B'}</Link></li>
                <li><Link href="/bantuan/cara-belanja">{t('help_how')}</Link></li>
                <li><Link href="/bantuan/ukuran">{t('help_size')}</Link></li>
                <li><Link href="/bantuan/pengiriman">{t('help_pay')}</Link></li>
                <li><Link href="/bantuan/garansi">{t('help_garansi')}</Link></li>
                <li><Link href="/pesanan">{t('track_title')}</Link></li>
              </ul>
            </div>

            <div className="footer-group">
              <h3>{t('footer_contact')}</h3>
              <ul>
                <li><a href={waLink('', storeWhatsapp)} target="_blank" rel="noreferrer">WhatsApp</a></li>
                <li><a href={IG_URL} target="_blank" rel="noreferrer">Instagram (@daffabatikcirebon)</a></li>
                <li><a href={MAPS_URL} target="_blank" rel="noreferrer">Google Maps Workshop</a></li>
              </ul>
            </div>
          </div>
        </div>

        <div className="footer-bar">
          <div className="container footer-bar-inner">
            <span>© 2026 Batik Daffa. {t('rights')}</span>
            <span>Panembahan, Kec. Plered, Kab. Cirebon · Sejak 2000-an</span>
          </div>
        </div>
      </footer>

      <CartDrawer open={drawerOpen} onClose={() => setDrawerOpen(false)} cart={cart} t={t} />
      <Toast onOpenCart={() => setDrawerOpen(true)} />
    </>
  )
}
