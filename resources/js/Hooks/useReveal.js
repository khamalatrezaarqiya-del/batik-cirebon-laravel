import { useEffect } from 'react'

// Efek reveal saat scroll. Hanya elemen di bawah layar yang disembunyikan,
// jadi konten tidak pernah "tak terlihat tapi bisa dipencet".
export function useReveal(deps = []) {
  useEffect(() => {
    if (!('IntersectionObserver' in window)) return

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible')
            observer.unobserve(entry.target)
          }
        })
      },
      { threshold: 0.08 },
    )

    document.querySelectorAll('.reveal').forEach((node) => {
      if (node.getBoundingClientRect().top > window.innerHeight - 40) {
        node.classList.add('pre')
        observer.observe(node)
      }
    })

    return () => observer.disconnect()
  }, deps)
}
