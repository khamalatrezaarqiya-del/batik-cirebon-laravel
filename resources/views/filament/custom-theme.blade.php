<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --bd-bg: #F7F5F0;
    --bd-surface: #FFFFFF;
    --bd-surface-muted: #EFECE5;
    --bd-border: #E8E4DC;
    --bd-ink: #24231F;
    --bd-muted: #77756F;
    --bd-olive: #69715B;
    --bd-olive-dark: #535A47;
    --bd-bronze: #A27B55;
  }

  /* Warm ivory canvas & minimalist flat hairline elevation */
  body.fi-body {
    background-color: var(--bd-bg) !important;
    color: var(--bd-ink);
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
  }

  .fi-topbar nav {
    background-color: var(--bd-surface) !important;
    border-bottom: 1px solid var(--bd-border) !important;
    box-shadow: none !important;
  }

  .fi-sidebar {
    background-color: var(--bd-surface) !important;
    border-right: 1px solid var(--bd-border) !important;
  }

  .fi-sidebar-header {
    background-color: var(--bd-surface) !important;
    border-bottom: 1px solid var(--bd-border) !important;
    box-shadow: none !important;
  }

  /* Playfair Display for editorial brand & page headings */
  .fi-logo {
    font-family: 'Playfair Display', Georgia, serif !important;
    font-weight: 600 !important;
    letter-spacing: 0.12em !important;
    color: var(--bd-ink) !important;
  }

  .fi-header-heading {
    font-family: 'Playfair Display', Georgia, serif !important;
    font-weight: 500 !important;
    letter-spacing: -0.015em !important;
    color: var(--bd-ink) !important;
  }

  /* Clean hairline cards, stats, sections, and tables */
  .fi-wi-stats-overview-stat,
  .fi-section,
  .fi-ta-ctn,
  .fi-simple-main {
    background-color: var(--bd-surface) !important;
    border: 1px solid var(--bd-border) !important;
    box-shadow: 0 1px 2px rgba(36, 35, 31, 0.03) !important;
    border-radius: 8px !important;
  }

  .fi-wi-stats-overview-stat-value,
  .fi-ta-text-item-label {
    font-variant-numeric: tabular-nums;
  }

  /* Active sidebar item: quiet warm surface with olive accent */
  .fi-sidebar-item-active > a {
    background-color: var(--bd-bg) !important;
    color: var(--bd-ink) !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
  }

  .fi-sidebar-item-active svg {
    color: var(--bd-olive) !important;
  }

  /* Primary charcoal / olive buttons */
  .fi-btn-color-primary {
    background-color: var(--bd-ink) !important;
    color: #FFFFFF !important;
    border-radius: 6px !important;
  }

  .fi-btn-color-primary:hover {
    background-color: var(--bd-olive) !important;
  }

  /* Clean table header */
  .fi-ta-header-cell {
    background-color: var(--bd-bg) !important;
  }

  /* Clean login shell */
  .fi-simple-layout {
    background-color: var(--bd-bg) !important;
  }

  .fi-simple-header-heading {
    font-family: 'Playfair Display', Georgia, serif !important;
    font-weight: 500 !important;
    color: var(--bd-ink) !important;
  }
</style>
