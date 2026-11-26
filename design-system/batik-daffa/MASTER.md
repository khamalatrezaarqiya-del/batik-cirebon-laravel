# Design System — Batik Daffa (Modern Editorial Minimalism)

Unified specification combining **TypeUI Minimal** (`minimal/SKILL.md` by `typeui.sh`: whitespace-led clarity, Inter display + Open Sans body, 4px spacing rhythm, WCAG 2.2 AA, explicit interaction states, semantic tokens over raw values) and **UI/UX Pro Max** (*E-commerce Luxury* + *UX Guidelines* for accessible carousels, 4:5 product imagery, responsive filter drawers, and reduced motion), adapted to **Batik Daffa** (Trusmi, Cirebon) with editorial campaign inspiration from **Batik Keris**.

## 1. Design Intent
Batik Daffa presents handcrafted Trusmi Cirebon batik as a calm, contemporary editorial boutique where campaign photography, motif detail, and effortless shopping discovery take center stage on warm ivory and crisp white surfaces.

## 2. Color Tokens (Semantic Layer — WCAG 2.2 AA)
| Role | Token | Value | Contrast / Usage |
| :--- | :--- | :--- | :--- |
| Primary Canvas | `--bg` | `#F7F5F0` | Warm ivory page ground |
| Secondary Surface | `--surface` | `#FFFFFF` | Product cards, inputs, drawers, header scrolled |
| Subtle Surface | `--surface-muted` | `#EFECE5` | Soft editorial dividers, subtle hover fill |
| Primary Ink | `--ink` | `#24231F` | Headings, prices, primary buttons (>14:1 on `#F7F5F0` / `#FFFFFF`) |
| Secondary Ink | `--ink-secondary` | `#5E5C57` | Supporting prose & captions (>6.0:1 AA on `#F7F5F0`) |
| Muted Ink | `--muted` | `#77756F` | Metadata, placeholders, secondary indicators (4.6:1 AA on `#FFFFFF`) |
| Primary Accent | `--accent` | `#69715B` | Muted olive — selective active indicators & availability |
| Accent Hover | `--accent-hover` | `#545B48` | Pressed/hover olive state |
| Secondary Accent | `--bronze` | `#A27B55` | Occasional editorial highlights & active slider indicator |
| Border | `--border` | `#E8E4DC` | 1px crisp structural hairline |
| Strong Border | `--border-strong` | `#D5CFC4` | Hover/focused interactive edges |

## 3. Typography (Playfair Display + Inter)
- **Editorial Headlines & Display (`--font-display`):** `Playfair Display` (400, 500, 600, 700, upright — no excessive italics) for brand wordmark, hero campaign titles, and editorial section/page headings.
- **Functional UI & Body (`--font-heading` / `--font-body`):** `Inter` (400, 500, 600, 700) for navigation, product names, prices (`font-variant-numeric: tabular-nums`), descriptions, buttons, forms, filters, and supporting copy (`15px` base, `1.65` line-height).

## 4. Spacing Rhythm (Strict 4px Scale)
`--space-1: 4px`, `--space-2: 8px`, `--space-3: 12px`, `--space-4: 16px`, `--space-6: 24px`, `--space-8: 32px`, `--space-12: 48px`, `--space-16: 64px`, `--space-20: 80px`.

## 5. Component Contracts
- **Header:** Sticky 68px navbar (`#F7F5F0` → `#FFFFFF` on scroll with 1px `#E8E4DC` hairline). Brand logo left, concise main links center (`Katalog`, `Kain`, `Pakaian`, `Aksesoris`, `Pesanan`), inline expandable search + language + Account + Cart icons (Lucide-style 1.65px stroke) right. Zero continuous animation.
- **Hero Campaign Carousel:** Full-width below navbar (`min-height: clamp(460px, 68vh, 660px)`), 3 editorial batik campaign slides, 6s autoplay, pause on hover/focus/off-viewport (`IntersectionObserver`), touch swipe, prev/next buttons, minimal pagination bars, strictly 1 headline + 1 concise description + 1 charcoal CTA positioned in negative space without obscuring subject, full `prefers-reduced-motion` support.
- **Product Card:** Clean white surface (`#FFFFFF`), strict `4:5` image aspect ratio, subtle hover scale (`1.03`), product name (`Inter`), price (`tabular-nums`), contextual badge only when sold out or new, unobtrusive quick-add button.
- **Catalog Filtering:** Desktop sticky filter sidebar + Mobile accessible bottom/side filter drawer (`Escape`, focus return, scroll lock) wired to real DB columns (`category`, `motif`, `price`, `stock`) and sorting (`new`, `best`, `cheap`, `pricey`).
- **Accessibility & QA Checklist:** WCAG 2.2 AA contrast, skip link, `:focus-visible` outlines, semantic landmarks, `aria-roledescription="carousel"`, keyboard controls, lazy-loaded below-fold images, zero horizontal overflow across 375 / 390 / 768 / 1024 / 1440px.
