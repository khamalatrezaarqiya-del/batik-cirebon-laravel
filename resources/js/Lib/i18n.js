import { I18N } from '../Data/i18n.js'

export function makeT(lang) {
  const dict = I18N[lang] || I18N.id
  return (key) => dict[key] ?? I18N.id[key] ?? key
}

export function readLang() {
  try { return localStorage.getItem('batik_lang') || 'id' } catch { return 'id' }
}

export function writeLang(lang) {
  try { localStorage.setItem('batik_lang', lang) } catch { /* ignore */ }
  document.documentElement.lang = lang
  window.dispatchEvent(new Event('lang:change'))
}
