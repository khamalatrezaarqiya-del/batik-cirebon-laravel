import { useEffect, useRef, useState } from 'react'
import { Link, router, usePage } from '@inertiajs/react'
import { waLink } from '../Lib/whatsapp.js'
import { readCart } from '../Lib/cart.js'
import { makeT, readLang, writeLang } from '../Lib/i18n.js'

const TITLES = {
  '': 'nav_home',
  katalog: 'nav_catalog',
  keranjang: 'nav_cart',
  checkout: 'checkout_title',
  pesanan: 'orders_title',
  bantuan: 'footer_help',
  dashboard: 'account',
}

const IconCart = () => (
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
    <path d="M3 6h18" />
    <path d="M16 10a4 4 0 0 1-8 0" />
  </svg>
)

const IconUser = () => (
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <circle cx="12" cy="8" r="3.6" />
    <path d="M4.5 20a7.5 7.5 0 0 1 15 0" />
  </svg>
)

const IconSearch = () => (
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" aria-hidden="true">
    <circle cx="11" cy="11" r="6.5" />
    <path d="m20 20-3.6-3.6" />
  </svg>
)

const IconMenu = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" aria-hidden="true">
    <path d="M4 7h16M4 12h16M4 17h16" />
  </svg>
)

export default function ShopLayout({ children, title }) {
  const { url, props } = usePage()
  const auth = props?.auth?.user || null

  const [lang, setLang] = useState(() => readLang())
  const [cartCount, setCartCount] = useState(0)
  const [shrink, setShrink] = useState(false)
  const [open, setOpen] = useState(false)
  const [q, setQ] = useState('')
  const progress = useRef(null)

  const t = makeT(lang)
  const path = url.split('?')[0]
  const search = url.includes('?') ? '?' + url.split('?')[1] : ''

  const toggleLang = () => {
    const next = lang === 'id' ? 'en' : 'id'
    writeLang(next)
    setLang(next)
  }

  const recount = () => setCartCount(readCart().reduce((s, i) => s + i.qty, 0))

  useEffect(() => {
    recount()
    window.addEventListener('cart:change', recount)
    return () => window.removeEventListener('cart:change', recount)
  }, [])

  useEffect(() => {
    const onScroll = () => {
      setShrink(window.scrollY > 24)
      const max = document.documentElement.scrollHeight - window.innerHeight
      if (progress.current) {
        const ratio = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0
        progress.current.style.transform = `scaleX(${ratio})`
      }
    }
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)
    return () => {
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onScroll)
    }
  }, [])

  useEffect(() => { setOpen(false) }, [path, search])

  useEffect(() => {
    window.scrollTo(0, 0)
    const seg = path.split('/')[1] || ''
    const base = 'Batik Daffa'
    const label = title || (TITLES[seg] ? t(TITLES[seg]) : '')
    document.title = label ? `${label} · ${base}` : base
  }, [path, title, t])

  const submitSearch = (e) => {
    e.preventDefault()
    const term = q.trim()
    router.visit(term ? `/katalog?q=${encodeURIComponent(term)}` : '/katalog')
  }

  const navClass = (href) => {
    const [p, s] = href.split('?')
    const active = s ? path === p && search === '?' + s : path === p && !search
    return active ? 'active' : ''
  }

  return (
    <>
      <div className="scroll-progress" ref={progress} />

      <div className="announce">
        <div className="container">
          <span>{t('announce')}</span>
          <a href={waLink()} target="_blank" rel="noreferrer">{t('announce_cta')}</a>
        </div>
      </div>

      <header className={`hdr${shrink ? ' shrink' : ''}${open ? ' open' : ''}`}>
        <div className="hdr-inner">
          <button
            type="button"
            className="burger"
            onClick={() => setOpen((v) => !v)}
            aria-label={t('menu')}
            aria-expanded={open}
          >
            <IconMenu />
          </button>

          <Link className="logo" href="/">
            BATIK <span>DAFFA</span>
          </Link>

          <nav className="mainnav" aria-label="Navigasi utama">
            <Link href="/" className={navClass('/')}>{t('nav_home')}</Link>
            <Link href="/katalog" className={navClass('/katalog')}>{t('nav_catalog')}</Link>
            <Link href="/katalog?cat=kain" className={navClass('/katalog?cat=kain')}>{t('kain')}</Link>
            <Link href="/katalog?cat=pakaian" className={navClass('/katalog?cat=pakaian')}>{t('pakaian')}</Link>
            <Link href="/katalog?cat=aksesoris" className={navClass('/katalog?cat=aksesoris')}>{t('aksesoris')}</Link>
            <Link href="/pesanan" className={path.startsWith('/pesanan') ? 'active' : ''}>{t('nav_orders')}</Link>
          </nav>

          <form className="searchbar" onSubmit={submitSearch} role="search">
            <IconSearch />
            <input
              type="search"
              value={q}
              onChange={(e) => setQ(e.target.value)}
              placeholder={t('search_ph')}
              aria-label={t('search')}
            />
          </form>

          <div className="hdr-actions">
            <button type="button" className="icon-btn lang-btn" onClick={toggleLang} title="Ganti bahasa">
              {lang === 'id' ? 'EN' : 'ID'}
            </button>

            {auth ? (
              <Link className="icon-btn" href="/dashboard" title={t('account')}>
                <IconUser />
                <span className="label">{auth.name}</span>
              </Link>
            ) : (
              <Link className="icon-btn" href="/login">
                <IconUser />
                <span className="label">{t('login')}</span>
              </Link>
            )}

            <Link className="icon-btn solid" href="/keranjang" aria-label={t('nav_cart')}>
              <IconCart />
              <span className="count-dot">{cartCount}</span>
            </Link>
          </div>
        </div>
      </header>

      <main>{children}</main>

      <footer>
        <div className="footer-main">
          <div className="footer-col footer-brand">
            <Link className="logo" href="/">
              BATIK <span>DAFFA</span>
            </Link>
            <p>{t('footer_about')}</p>
            <div className="socials">
              <a href={waLink()} target="_blank" rel="noreferrer" aria-label="WhatsApp">WA</a>
              <a href="https://instagram.com/xxxx" target="_blank" rel="noreferrer" aria-label="Instagram">IG</a>
              <a href="https://facebook.com/xxxx" target="_blank" rel="noreferrer" aria-label="Facebook">FB</a>
            </div>
          </div>

          <div className="footer-col">
            <h3>{t('footer_category')}</h3>
            <Link href="/katalog">{t('all')}</Link>
            <Link href="/katalog?cat=kain">{t('kain')}</Link>
            <Link href="/katalog?cat=pakaian">{t('pakaian')}</Link>
            <Link href="/katalog?cat=aksesoris">{t('aksesoris')}</Link>
          </div>

          <div className="footer-col">
            <h3>{t('footer_help')}</h3>
            <Link href="/bantuan/cara-belanja">{t('help_how')}</Link>
            <Link href="/bantuan/ukuran">{t('help_size')}</Link>
            <Link href="/bantuan/pengiriman">{t('help_pay')}</Link>
            <Link href="/pesanan">{t('track_title')}</Link>
          </div>

          <div className="footer-col">
            <h3>{t('footer_contact')}</h3>
            <p>{t('address')}</p>
            <a href={waLink()} target="_blank" rel="noreferrer">WhatsApp Batik Daffa</a>
            <a href="mailto:halo@batikdaffa.com">halo@batikdaffa.com</a>
          </div>
        </div>

        <div className="footer-bottom">
          <span>© 2026 Batik Daffa. {t('rights')}</span>
          <span>{t('footer_note')}</span>
        </div>
      </footer>
    </>
  )
}
