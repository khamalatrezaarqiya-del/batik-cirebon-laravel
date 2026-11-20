import { Link, router, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import ProductCard from '../../Components/ProductCard.jsx'
import SectionTitle from '../../Components/SectionTitle.jsx'
import { useReveal } from '../../Hooks/useReveal.js'
import { addToCart } from '../../Lib/cart.js'
import { makeT, readLang } from '../../Lib/i18n.js'

const MOTIFS = ['all', 'mega', 'kawung', 'parang', 'truntum']

export default function Catalog({ products, categories = [], filters = {} }) {
  const [lang, setLang] = useState(() => readLang())
  const t = makeT(lang)
  const { url } = usePage()
  const cat = filters.cat || 'all'
  const motif = filters.motif || 'all'
  const term = filters.term || ''
  const sort = filters.sort || 'new'

  const [sortLocal, setSortLocal] = useState(sort)
  useEffect(() => setSortLocal(sort), [sort])
  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  useReveal([cat, term, sort, motif, products?.data])

  const setParam = (key, value) => {
    const params = new URLSearchParams()
    Object.entries(filters).forEach(([k, v]) => {
      if (v && v !== 'all') params.set(k, v)
    })
    if (key === 'cat' && value === 'all') params.delete('cat')
    else if (key === 'cat') params.set('cat', value)
    else if (key === 'q') { if (value) params.set('q', value) }
    else if (key === 'motif') { if (value !== 'all') params.set('motif', value) }
    else if (key === 'sort') { if (value !== 'new') params.set('sort', value) }

    const qs = params.toString()
    router.visit(`/katalog${qs ? '?' + qs : ''}`, { preserveState: true })
  }

  const resetAll = () => router.visit('/katalog', { preserveState: true })
  const hasFilter = cat !== 'all' || term !== '' || motif !== 'all'
  const maxId = Math.max(...(products?.data || []).map((p) => p.id), 26)

  return (
    <ShopLayout title={t('nav_catalog')}>
      <div className="section">
        <SectionTitle>{t('nav_catalog')}</SectionTitle>

        <div className="catalog-bar">
          <div className="filters">
            {[{ slug: 'all', nama: 'all' }, ...categories].map((c) => (
              <button
                key={c.slug}
                className={cat === c.slug ? 'filter active' : 'filter'}
                onClick={() => setParam('cat', c.slug)}
              >
                {c.slug === 'all' ? t('all') : c.nama}
              </button>
            ))}
          </div>

          <div className="sort-wrap">
            <select value={motif} onChange={(e) => setParam('motif', e.target.value)} aria-label={t('filter_motif')}>
              {MOTIFS.map((m) => (
                <option key={m} value={m}>
                  {m === 'all' ? t('motif_all') : m === 'mega' ? 'Mega Mendung' : m.charAt(0).toUpperCase() + m.slice(1)}
                </option>
              ))}
            </select>
            <label htmlFor="sort">{t('sort_label')}</label>
            <select id="sort" value={sortLocal} onChange={(e) => { setSortLocal(e.target.value); setParam('sort', e.target.value) }}>
              <option value="new">{t('sort_new')}</option>
              <option value="cheap">{t('sort_cheap')}</option>
              <option value="pricey">{t('sort_pricey')}</option>
            </select>
          </div>
        </div>

        <p className="result-count">
          {term ? <>
            {t('search_result')} “<b>{term}</b>” — {products?.total} {t('products_count')} ·{' '}
            <button className="text-link" onClick={() => setParam('q', '')} style={{ background: 'none', border: 0, cursor: 'pointer', padding: 0 }}>
              {t('reset_filter')}
            </button>
          </> : <>
            <b>{products?.total}</b> {t('products_count')}
            {hasFilter && <> ·{' '}
              <button className="text-link" onClick={resetAll} style={{ background: 'none', border: 0, cursor: 'pointer', padding: 0 }}>
                {t('reset_filter')}
              </button>
            </>}
          </>}
        </p>

        {!products?.data?.length ? (
          <div className="empty">
            <p>{t('no_result')}</p>
            <button className="button ghost" onClick={resetAll}>{t('reset_filter')}</button>
          </div>
        ) : (
          <>
            <div className="grid">
              {products.data.map((p) => (
                <ProductCard key={p.id} product={p} t={t} addToCart={addToCart} maxId={maxId} />
              ))}
            </div>

            {products.links.some((l) => l.url !== null) && (
              <nav className="pagination" style={{ display: 'flex', justifyContent: 'center', gap: 8, marginTop: 32 }}>
                {products.links.map((l, i) =>
                  l.url === null ? (
                    <span key={i} style={{ opacity: .5, padding: '8px 14px' }}>{l.label}</span>
                  ) : (
                    <Link key={i} href={l.url} className={l.active ? 'button' : 'button ghost'} style={{ padding: '8px 14px' }}>
                      {l.label.replace(/&amp;/g, '&')}
                    </Link>
                  )
                )}
              </nav>
            )}
          </>
        )}
      </div>
    </ShopLayout>
  )
}
