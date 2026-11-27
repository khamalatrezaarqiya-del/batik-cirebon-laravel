import { Link, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import ShopLayout from '../../Layouts/ShopLayout.jsx'
import { readLang } from '../../Lib/i18n.js'
import { waLink } from '../../Lib/whatsapp.js'

const WORKSHOP_ADDRESS =
  'Jl. Raya Panembahan No. 52/468, Panembahan, Kec. Plered, Kabupaten Cirebon, Jawa Barat 45154'
const MAPS_URL =
  'https://www.google.com/maps/search/?api=1&query=Jl.+Raya+Panembahan+No.+52%2F468+Panembahan+Plered+Cirebon+45154'
const IG_URL = 'https://www.instagram.com/daffabatikcirebon/'

const MATERIALS = [
  {
    name: 'Viscose',
    desc: 'Jatuh lembut, sejuk, dan berkilau halus. Favorit untuk selendang, blouse, dress, hingga busana kondangan.',
  },
  {
    name: 'Doby',
    desc: 'Memiliki tekstur tenun timbul khas yang elegan dan berwibawa untuk kemeja formal maupun kain lembaran.',
  },
  {
    name: 'ATBM (Alat Tenun Bukan Mesin)',
    desc: 'Tenunan tangan eksklusif untuk karya batik tulis murni kelas koleksi dengan karakter serat alami yang istimewa.',
  },
  {
    name: 'Katun',
    desc: 'Nyaman, menyerap keringat, dan tahan lama — ideal untuk pemakaian harian maupun seragam instansi.',
  },
]

const TECHNIQUES = [
  {
    name: 'Batik Tulis Murni',
    desc: 'Digores langsung dengan canting tembaga secara manual untuk menghasilkan motif bernilai seni tinggi dan eksklusif.',
  },
  {
    name: 'Batik Cap',
    desc: 'Menggunakan cap tembaga presisi dengan harga terjangkau — cocok untuk kebutuhan harian, selendang, maupun seragam.',
  },
  {
    name: 'Kombinasi Cap & Tulis',
    desc: 'Memadukan kerangka motif cap dengan detail isen-isen canting tembaga untuk keseimbangan keindahan dan nilai.',
  },
]

export default function About() {
  const { props } = usePage()
  const storeWhatsapp = props?.storeWhatsapp || ''
  const [lang, setLang] = useState(() => readLang())

  useEffect(() => {
    const onLang = () => setLang(readLang())
    window.addEventListener('lang:change', onLang)
    return () => window.removeEventListener('lang:change', onLang)
  }, [])

  return (
    <ShopLayout title={lang === 'en' ? 'About Batik Daffa' : 'Profil Usaha'}>
      <div className="section about-page">
        {/* Editorial Header */}
        <header className="about-hero">
          <span className="editorial-label">Profil Usaha · Panembahan, Cirebon</span>
          <h1>Rumah Batik Pesisiran Cirebon Sejak Tahun 2000-an</h1>
          <p className="about-lead">
            Batik Daffa adalah rumah produksi batik pesisiran di Kecamatan Plered, Kabupaten Cirebon yang melestarikan
            corak khas seperti Mega Mendung dan Singaan — memadukan ketekunan pengrajin lokal dengan kebutuhan pasar
            ritel maupun kemitraan grosir berskala nasional hingga mancanegara.
          </p>
        </header>

        {/* Key Workshop Facts */}
        <section className="about-stats" aria-label="Ringkasan usaha Batik Daffa">
          <div className="about-stat">
            <strong>Sejak 2000-an</strong>
            <span>Berdiri dan terus memproduksi langsung dari workshop keluarga di Panembahan, Plered.</span>
          </div>
          <div className="about-stat">
            <strong>12 Pengrajin Lokal</strong>
            <span>2 pengrajin cap, 7–8 pengrajin tulis &amp; isen-isen, serta 2 bagian pewarnaan dan ngelorot.</span>
          </div>
          <div className="about-stat">
            <strong>30–50 Potong / Minggu</strong>
            <span>Kapasitas produksi rutin di rumah produksi dengan kendali mutu langsung oleh pemilik.</span>
          </div>
          <div className="about-stat">
            <strong>Rp 55 rb – &gt; Rp 10 jt</strong>
            <span>Pilihan lengkap mulai selendang batik cap harian hingga kain batik tulis ATBM eksklusif.</span>
          </div>
        </section>

        {/* Craftsmanship Split */}
        <section className="about-story">
          <div className="about-story-media">
            <img
              src="/images/campaigns/hero-3.jpg"
              alt="Kain batik tulis dan cap karya pengrajin Batik Daffa Cirebon"
              loading="lazy"
            />
          </div>
          <div className="about-story-copy">
            <span className="editorial-label">Identitas &amp; Rekam Jejak</span>
            <h2>Corak Pesisiran Cirebon yang Otentik</h2>
            <p>
              Berbeda dari batik pedalaman, identitas Batik Daffa berakar pada corak pesisiran Cirebon yang ekspresif
              dan hangat — dengan andalan motif Mega Mendung, Singaan, dan Wadasan. Seluruh tahapan, mulai dari
              pengecapan, pencantingan isen-isen, pewarnaan, hingga ngelorot, dikerjakan langsung di workshop kami.
            </p>
            <p>
              Karya Batik Daffa aktif berpartisipasi dalam pameran kerajinan berskala nasional seperti Inacraft di JCC
              Jakarta, dipercaya oleh distributor dan tengkulak di Surabaya serta Jawa Tengah, hingga menembus pasar
              ekspor tidak langsung ke Malaysia dan Myanmar melalui jaringan mitra di Thamrin City.
            </p>
          </div>
        </section>

        {/* 4 Materials */}
        <section className="about-block">
          <div className="about-block-head">
            <span className="editorial-label">Material Pilihan</span>
            <h2>Empat Jenis Bahan Utama</h2>
          </div>
          <div className="about-grid-4">
            {MATERIALS.map((item) => (
              <article key={item.name} className="about-card">
                <h3>{item.name}</h3>
                <p>{item.desc}</p>
              </article>
            ))}
          </div>
        </section>

        {/* 3 Production Techniques */}
        <section className="about-block">
          <div className="about-block-head">
            <span className="editorial-label">Proses Pengerjaan</span>
            <h2>Tiga Teknik Produksi</h2>
          </div>
          <div className="about-grid-3">
            {TECHNIQUES.map((item) => (
              <article key={item.name} className="about-card">
                <h3>{item.name}</h3>
                <p>{item.desc}</p>
              </article>
            ))}
          </div>
        </section>

        {/* B2B, Grosir, Reseller & Konsinyasi */}
        <section id="grosir" className="about-b2b">
          <div className="about-b2b-head">
            <span className="editorial-label">Kemitraan B2B &amp; Grosir</span>
            <h2>Melayani Distributor, Reseller, Seragam &amp; Konsinyasi</h2>
            <p>
              Selain melayani pembeli ritel perorangan, Batik Daffa terbuka untuk kerja sama bisnis jangka panjang
              dengan toko batik, distributor daerah, reseller, maupun pengadaan seragam instansi dan keluarga.
            </p>
          </div>

          <div className="about-grid-3">
            <div className="about-b2b-item">
              <h3>Beli Putus &amp; Grosir Partai Besar</h3>
              <p>
                Melayani pemesanan jumlah banyak hingga skala karungan (sekitar 200 potong per karung) dengan
                penawaran harga grosir kompetitif langsung dari rumah produksi.
              </p>
            </div>
            <div className="about-b2b-item">
              <h3>Skema Titip Jual (Konsinyasi)</h3>
              <p>
                Tersedia mekanisme kerja sama konsinyasi bagi mitra toko dan reseller sesuai kesepakatan bersama
                dengan pemilik Batik Daffa.
              </p>
            </div>
            <div className="about-b2b-item">
              <h3>Fleksibilitas DP 50% &amp; Garansi Mutu</h3>
              <p>
                Pesanan bernominal besar maupun custom dapat dimulai dengan DP 50% (transfer bank). Pengiriman
                reguler via JNE, J&amp;T, Tiki atau kargo, dilengkapi garansi tukar ukuran dan cacat produksi.
              </p>
            </div>
          </div>

          <div className="about-b2b-actions">
            <a
              className="button button-lg custom-wa-btn"
              href={waLink(
                'Halo Admin Batik Daffa, saya ingin berkonsultasi mengenai kemitraan B2B / pemesanan grosir / konsinyasi batik. Bisa dibantu infonya?',
                storeWhatsapp
              )}
              target="_blank"
              rel="noreferrer"
            >
              Konsultasi Grosir &amp; B2B via WhatsApp
            </a>
            <Link className="button ghost button-lg about-b2b-ghost" href="/katalog">
              Lihat Katalog Produk
            </Link>
          </div>
        </section>

        {/* Workshop Location & Contact */}
        <section className="about-location">
          <div>
            <span className="editorial-label">Lokasi Workshop</span>
            <h2>Kunjungi Rumah Produksi Batik Daffa</h2>
            <p className="about-address">{WORKSHOP_ADDRESS}</p>
            <p className="muted-text">
              Terbuka untuk kunjungan pemilihan kain langsung, diskusi seragam, maupun temu kerja sama distributor.
            </p>
          </div>
          <div className="about-location-actions">
            <a className="button button-lg" href={MAPS_URL} target="_blank" rel="noreferrer">
              Buka di Google Maps
            </a>
            <a className="button ghost button-lg" href={IG_URL} target="_blank" rel="noreferrer">
              Instagram @daffabatikcirebon
            </a>
          </div>
        </section>
      </div>
    </ShopLayout>
  )
}
