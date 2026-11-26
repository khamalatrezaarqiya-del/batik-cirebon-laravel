import { Link, router } from '@inertiajs/react'
import { useEffect, useRef, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import ProductCard from '../../Components/ProductCard.jsx'
import { useReveal } from '../../Hooks/useReveal.js'
import { addToCart } from '../../Lib/cart.js'
import { makeT, readLang } from '../../Lib/i18n.js'

const MOTIFS = [
  { key: 'all', labelKey: 'motif_all' },
  { key: 'mega', label: 'Mega Mendung' },
  { key: 'kawung', label: 'Kawung' },
  { key: 'parang', label: 'Parang' },
  { key: 'truntum', label: 'Truntum' },
  { key: 'wadasan', label: 'Wadasan' },
]

const PRICE_RANGES = [
  { key: 'all', labelKey: 'price_all' },
  { key: 'under-200', labelKey: 'price_under_200' },
  { key: '200-350', labelKey: 'price_200_350' },
  { key: 'above-350', labelKey: 'price_above_350' },
]

const IconFilter = () => (
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M4 6h16M7 12h10M10 18h4" />
  </svg>
)

const IconSearch = () => (
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <circle cx="11" cy="11" r="7" />
    <path d="m20 20-3.8-3.8" />
  </svg>
)

const IconClose = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" aria-hidden="true">
    <path d="M18 6 6 18M6 6l12 12" />
  </svg>
)

function formatPageLabel(label, lang = 'id') {
  const raw = String(label)
    .replace(/&laquo;/g, '‹')
    .replace(/&raquo;/g, '›')
    .replace(/&amp;/g, '&')
    .trim()

  if (/pagination\.previous|previous|sebelum/i.test(raw)) {
    return lang === 'en' ? '‹ Previous' : '‹ Sebelum'
  }
  if (/pagination\.next|next|berikut|selanjut/i.test(raw)) {
    return lang === 'en' ? 'Next ›' : 'Selanjutnya ›'
  }
  return raw
}

function FilterGroups({ categories, cat, motif, price, stock, setParam, t }) {
  return (
    <div className="filter-groups">
      <div className="filter-group">
        <h3>{t('footer_category')}</h3>
        <div className="filter-options" role="group" aria-label={t('footer_category')}>
          <button
            type="button"
            className={`filter-opt${cat === 'all' ? ' active' : ''}`}
            aria-pressed={cat === 'all'}
            onClick={() => setParam('cat', 'all')}
          >
            <span>{t('all')}</span>
          </button>
          {categories.map((c) => (
            <button
              type="button"
              key={c.slug}
              className={`filter-opt${cat === c.slug ? ' active' : ''}`}
              aria-pressed={cat === c.slug}
              onClick={() => setParam('cat', c.slug)}
            >
              <span>{c.nama}</span>
              {typeof c.products_count === 'number' && (
                <span className="filter-opt-count">{c.products_count}</span>
              )}
            </button>
          ))}
        </div>
      </div>

      <div className="filter-group">
        <h3>{t('filter_motif')}</h3>
        <div className="filter-options" role="group" aria-label={t('filter_motif')}>
          {MOTIFS.map((m) => (
            <button
              type="button"
              key={m.key}
              className={`filter-opt${motif === m.key ? ' active' : ''}`}
              aria-pressed={motif === m.key}
              onClick={() => setParam('motif', m.key)}
            >
              <span>{m.labelKey ? t(m.labelKey) : m.label}</span>
            </button>
          ))}
        </div>
      </div>

      <div className="filter-group">
        <h3>{t('filter_price')}</h3>
        <div className="filter-options" role="group" aria-label={t('filter_price')}>
          {PRICE_RANGES.map((p) => (
            <button
              type="button"
              key={p.key}
              className={`filter-opt${price === p.key ? ' active' : ''}`}
              aria-pressed={price === p.key}
              onClick={() => setParam('price', p.key)}
            >
              <span>{t(p.labelKey)}</span>
            </button>
          ))}
        </div>
      </div>

      <div className="filter-group">
        <h3>{t('filter_stock')}</h3>
        <label className="filter-check">
          <input
            type="checkbox"
            checked={stock === 'in_stock'}
            onChange={(e) => setParam('stock', e.target.checked ? 'in_stock' : 'all')}
          />
          <span>{t('stock_only')}</span>
        </label>
      </div>
    </div>
  )
}

export default function Catalog({ products, categories = [], filters = {} }) {
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)

  const cat = filters.cat || 'all'
  const motif = filters.motif || 'all'
  const price = filters.price || 'all'
  const stock = filters.stock || 'all'
  const term = filters.term || ''
  const sort = filters.sort || 'new'

  const [searchTerm, setSearchTerm] = useState(term)
  const [drawerOpen, setDrawerOpen] = useState(false)
  const closeDrawerRef = useRef(null)

  useEffect(() => setSearchTerm(term), [term])

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  useEffect(() => {
    if (!drawerOpen) return
    const prev = document.activeElement
    closeDrawerRef.current?.focus()
    const onKey = (e) => {
      if (e.key === 'Escape') setDrawerOpen(false)
    }
    document.body.style.overflow = 'hidden'
    window.addEventListener('keydown', onKey)
    return () => {
      document.body.style.overflow = ''
      window.removeEventListener('keydown', onKey)
      if (prev && typeof prev.focus === 'function') prev.focus()
    }
  }, [drawerOpen])

  useReveal([cat, term, sort, motif, price, stock, products?.data])

  const setParam = (key, value) => {
    const next = {
      cat,
      motif,
      price,
      stock,
      q: term,
      sort,
      [key]: value,
    }
    const params = new URLSearchParams()
    if (next.cat && next.cat !== 'all') params.set('cat', next.cat)
    if (next.motif && next.motif !== 'all') params.set('motif', next.motif)
    if (next.price && next.price !== 'all') params.set('price', next.price)
    if (next.stock && next.stock !== 'all') params.set('stock', next.stock)
    if (next.q) params.set('q', next.q)
    if (next.sort && next.sort !== 'new') params.set('sort', next.sort)

    const qs = params.toString()
    router.visit(`/katalog${qs ? '?' + qs : ''}`, { preserveState: true, preserveScroll: true })
  }

  const submitSearch = (e) => {
    e.preventDefault()
    setParam('q', searchTerm.trim())
  }

  const resetAll = () => {
    setSearchTerm('')
    setDrawerOpen(false)
    router.visit('/katalog', { preserveState: true })
  }

  const activeFilterCount =
    (cat !== 'all' ? 1 : 0) +
    (motif !== 'all' ? 1 : 0) +
    (price !== 'all' ? 1 : 0) +
    (stock !== 'all' ? 1 : 0) +
    (term !== '' ? 1 : 0)

  const maxId = Math.max(...(products?.data || []).map((p) => p.id), 26)
  const activeCatName = categories.find((c) => c.slug === cat)?.nama || cat
  const activeMotifObj = MOTIFS.find((m) => m.key === motif)
  const activeMotifName = activeMotifObj?.label || motif
  const activePriceObj = PRICE_RANGES.find((p) => p.key === price)

  return (
    <ShopLayout title={t('nav_catalog')}>
      <div className="section">
        <div className="catalog-head">
          <div>
            <h1>{cat !== 'all' ? activeCatName : t('nav_catalog')}</h1>
            <p className="result-count">
              {term ? (
                <>
                  {t('search_result')} “<b>{term}</b>” · <b>{products?.total ?? 0}</b> {t('products_count')}
                </>
              ) : (
                <>
                  <b>{products?.total ?? 0}</b> {t('products_count')}
                </>
              )}
            </p>
          </div>

          <div className="catalog-head-controls">
            <form className="catalog-search" onSubmit={submitSearch} role="search">
              <IconSearch />
              <input
                type="search"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                placeholder={t('search_ph')}
                aria-label={t('search')}
              />
              {searchTerm && (
                <button
                  type="button"
                  className="search-clear"
                  onClick={() => {
                    setSearchTerm('')
                    if (term) setParam('q', '')
                  }}
                  aria-label="Hapus kata pencarian"
                >
                  <IconClose />
                </button>
              )}
            </form>

            <button
              type="button"
              className="button ghost mobile-filter-btn"
              onClick={() => setDrawerOpen(true)}
            >
              <IconFilter />
              <span>{t('filter_title')}</span>
              {activeFilterCount > 0 && <span className="filter-badge">{activeFilterCount}</span>}
            </button>

            <div className="sort-wrap">
              <label htmlFor="sort" className="sr-only">{t('sort_label')}</label>
              <select
                id="sort"
                value={sort}
                onChange={(e) => setParam('sort', e.target.value)}
                aria-label={t('sort_label')}
              >
                <option value="new">{t('sort_new')}</option>
                <option value="best">{t('sort_best')}</option>
                <option value="cheap">{t('sort_cheap')}</option>
                <option value="pricey">{t('sort_pricey')}</option>
              </select>
            </div>
          </div>
        </div>

        <div className="catalog-shell">
          <aside className="catalog-sidebar" aria-label={t('filter_title')}>
            <div className="catalog-sidebar-head">
              <h2>{t('filter_title')}</h2>
              {activeFilterCount > 0 && (
                <button type="button" className="text-link inline-btn" onClick={resetAll}>
                  {t('reset_filter')}
                </button>
              )}
            </div>
            <FilterGroups
              categories={categories}
              cat={cat}
              motif={motif}
              price={price}
              stock={stock}
              setParam={setParam}
              t={t}
            />
          </aside>

          <div className="catalog-main">
            {activeFilterCount > 0 && (
              <div className="active-chips" role="region" aria-label="Filter aktif">
                {cat !== 'all' && (
                  <button type="button" className="active-chip" onClick={() => setParam('cat', 'all')}>
                    <span>{activeCatName}</span>
                    <span aria-hidden="true">×</span>
                  </button>
                )}
                {motif !== 'all' && (
                  <button type="button" className="active-chip" onClick={() => setParam('motif', 'all')}>
                    <span>{activeMotifName}</span>
                    <span aria-hidden="true">×</span>
                  </button>
                )}
                {price !== 'all' && activePriceObj && (
                  <button type="button" className="active-chip" onClick={() => setParam('price', 'all')}>
                    <span>{t(activePriceObj.labelKey)}</span>
                    <span aria-hidden="true">×</span>
                  </button>
                )}
                {stock === 'in_stock' && (
                  <button type="button" className="active-chip" onClick={() => setParam('stock', 'all')}>
                    <span>{t('stock_only')}</span>
                    <span aria-hidden="true">×</span>
                  </button>
                )}
                {term !== '' && (
                  <button type="button" className="active-chip" onClick={() => setParam('q', '')}>
                    <span>“{term}”</span>
                    <span aria-hidden="true">×</span>
                  </button>
                )}
                <button type="button" className="text-link inline-btn" onClick={resetAll}>
                  {t('reset_filter')}
                </button>
              </div>
            )}

            {!products?.data?.length ? (
              <div className="empty">
                <p>{t('no_result')}</p>
                <button type="button" className="button" onClick={resetAll}>{t('reset_filter')}</button>
              </div>
            ) : (
              <>
                <div className="grid catalog-grid">
                  {products.data.map((p, idx) => (
                    <ProductCard key={p.id} product={p} t={t} addToCart={addToCart} maxId={maxId} index={idx} />
                  ))}
                </div>

                {products.last_page > 1 && (
                  <nav className="pagination" aria-label="Halaman katalog">
                    {products.links.map((l, i) =>
                      l.url === null ? (
                        <span key={i} className="page-item disabled">{formatPageLabel(l.label, lang)}</span>
                      ) : (
                        <Link key={i} href={l.url} className={l.active ? 'page-item active' : 'page-item'}>
                          {formatPageLabel(l.label, lang)}
                        </Link>
                      )
                    )}
                  </nav>
                )}
              </>
            )}
          </div>
        </div>
      </div>

      {drawerOpen && (
        <div className="drawer-backdrop" onClick={() => setDrawerOpen(false)} role="presentation">
          <aside
            className="drawer-panel filter-drawer"
            role="dialog"
            aria-modal="true"
            aria-labelledby="filter-drawer-title"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="drawer-head">
              <div>
                <h2 id="filter-drawer-title">{t('filter_title')}</h2>
                <span className="drawer-sub">{products?.total ?? 0} {t('products_count')}</span>
              </div>
              <button
                ref={closeDrawerRef}
                type="button"
                className="drawer-close"
                onClick={() => setDrawerOpen(false)}
                aria-label="Tutup filter"
              >
                <IconClose />
              </button>
            </div>

            <div className="drawer-body">
              <FilterGroups
                categories={categories}
                cat={cat}
                motif={motif}
                price={price}
                stock={stock}
                setParam={setParam}
                t={t}
              />
            </div>

            <div className="drawer-foot">
              <div className="drawer-actions">
                <button type="button" className="button" onClick={() => setDrawerOpen(false)}>
                  Tampilkan {products?.total ?? 0} {t('products_count')}
                </button>
                {activeFilterCount > 0 && (
                  <button type="button" className="button ghost" onClick={resetAll}>
                    {t('reset_filter')}
                  </button>
                )}
              </div>
            </div>
          </aside>
        </div>
      )}
    </ShopLayout>
  )
}
