// Placeholder gambar produk: motif batik digambar langsung sebagai SVG inline.
// Tidak pakai data-URI, jadi tidak bisa "hilang" karena encoding.
// Kalau produk sudah punya foto asli, pakai komponen ProductImage di bawah.

const MEGA = (accent) => (
  <g fill="none" stroke={accent} strokeLinecap="round">
    {[
      ['M6 72 c 0-30 40-30 40 0 c 0-38 52-38 52 0 c 0-30 40-30 40 0', 14, 0.14],
      ['M10 68 c 0-26 34-26 34 0 c 0-32 44-32 44 0 c 0-26 34-26 34 0', 10, 0.24],
      ['M14 64 c 0-20 28-20 28 0 c 0-26 36-26 36 0 c 0-20 28-20 28 0', 7, 0.36],
      ['M18 60 c 0-14 22-14 22 0 c 0-18 28-18 28 0 c 0-14 22-14 22 0', 4, 0.5],
    ].map(([d, w, o], i) => (
      <path key={i} d={d} strokeWidth={w} opacity={o} />
    ))}
  </g>
)

const KAWUNG = (accent) => (
  <>
    <ellipse cx="30" cy="30" rx="22" ry="30" fill="none" stroke={accent} strokeWidth="4" opacity=".5" />
    <ellipse cx="90" cy="90" rx="22" ry="30" fill="none" stroke={accent} strokeWidth="4" opacity=".5" />
    <ellipse cx="90" cy="30" rx="22" ry="30" fill="none" stroke={accent} strokeWidth="4" opacity=".3" />
    <ellipse cx="30" cy="90" rx="22" ry="30" fill="none" stroke={accent} strokeWidth="4" opacity=".3" />
    <circle cx="60" cy="60" r="4" fill={accent} opacity=".6" />
  </>
)

const PARANG = (accent) => (
  <>
    <path d="M-10 110 L110 -10" stroke={accent} strokeWidth="14" opacity=".4" />
    <path d="M30 130 L130 30" stroke={accent} strokeWidth="14" opacity=".25" />
    <path d="M-10 70 L70 -10" stroke={accent} strokeWidth="3" opacity=".6" />
  </>
)

const TRUNTUM = (accent) => (
  <>
    {[
      [30, 30],
      [90, 90],
      [90, 30],
      [30, 90],
    ].map(([cx, cy], i) => (
      <g key={i} transform={`translate(${cx} ${cy})`} opacity={i < 2 ? 0.55 : 0.3}>
        <circle r="4" fill={accent} />
        {[0, 72, 144, 216, 288].map((a) => (
          <ellipse key={a} cx="0" cy="-13" rx="5" ry="10" fill={accent} transform={`rotate(${a})`} />
        ))}
      </g>
    ))}
  </>
)

const WADASAN = (accent) => (
  <g fill="none" stroke={accent} strokeLinecap="round" strokeLinejoin="round">
    <path d="M12 96 L36 62 L58 76 L86 38 L108 66" strokeWidth="6" opacity=".45" />
    <path d="M22 106 L44 76 L64 88 L90 54 L112 78" strokeWidth="3.5" opacity=".32" />
    <circle cx="36" cy="40" r="5" fill={accent} opacity=".45" />
    <circle cx="88" cy="24" r="3.5" fill={accent} opacity=".35" />
  </g>
)

const MOTIFS = { mega: MEGA, kawung: KAWUNG, parang: PARANG, truntum: TRUNTUM, wadasan: WADASAN }

export default function Motif({ motif = 'mega', base = '#6b3d1f', accent = '#e6b871', className = '' }) {
  const safeBase = (base || '#6b3d1f').replace(/[^a-zA-Z0-9]/g, '')
  const safeAccent = (accent || '#e6b871').replace(/[^a-zA-Z0-9]/g, '')
  const id = `m-${motif}-${safeBase}-${safeAccent}`
  const tile = MOTIFS[motif]?.(accent) || MEGA(accent)

  return (
    <svg className={className} viewBox="0 0 400 500" preserveAspectRatio="xMidYMid slice" role="img" aria-hidden="true">
      <defs>
        <pattern id={id} width="120" height="120" patternUnits="userSpaceOnUse">
          {tile}
        </pattern>
      </defs>
      <rect width="400" height="500" fill={base} />
      <rect width="400" height="500" fill={`url(#${id})`} />
    </svg>
  )
}

function resolvePhotoSrc(foto) {
  if (!foto) return null
  if (/^(https?:\/\/|\/)/.test(foto)) return foto
  return `/storage/${foto}`
}

// Produk dengan foto asli → <img>, produk tanpa foto → motif.
export function ProductImage({ product, className = '' }) {
  const src = resolvePhotoSrc(product?.foto)
  if (src) return <img className={className} src={src} alt={product.nama} loading="lazy" />
  return <Motif motif={product?.motif} base={product?.c1} accent={product?.c2} className={className} />
}
