<script>
  (function () {
    const nav = document.getElementById('siteNav')
    const body = document.body
    const transparent = body?.dataset?.transparentNav === 'true'

    const applyNavState = () => {
      if (!nav) return
      if (transparent) {
        if (window.scrollY > 30) {
          nav.classList.add('bg-gray-900/95', 'shadow-lg', 'backdrop-blur')
          nav.classList.remove('bg-transparent')
        } else {
          nav.classList.add('bg-transparent')
          nav.classList.remove('bg-gray-900/95', 'shadow-lg', 'backdrop-blur')
        }
      } else {
        nav.classList.add('bg-gray-800', 'shadow')
      }
    }

    applyNavState()
    window.addEventListener('scroll', applyNavState)

    const toggle = document.getElementById('facilities-toggle')
    const menu = document.getElementById('facilities-menu')
    const caret = document.getElementById('facilities-caret')
    const items = document.getElementById('facilities-menu-items')

    const closeMenu = () => {
      if (!menu) return
      menu.classList.add('hidden')
      if (caret) caret.classList.remove('rotate-180')
    }

    if (toggle && menu) {
      toggle.addEventListener('click', async (e) => {
        e.preventDefault()
        menu.classList.toggle('hidden')
        if (caret) caret.classList.toggle('rotate-180')

        if (items && items.childElementCount === 0) {
          try {
            const res = await fetch('/api/public/facilities')
            const data = await res.json()
            const facilities = data?.facilities || []
            items.innerHTML = facilities.length
              ? facilities.map((f) => {
                  return `<a href="/facilities/${f.id}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">${f.title}</a>`
                }).join('')
              : '<div class="px-4 py-3 text-sm text-slate-400">No facilities</div>'
          } catch {
            items.innerHTML = '<div class="px-4 py-3 text-sm text-slate-400">Failed to load</div>'
          }
        }
      })
    }

    document.addEventListener('click', (e) => {
      if (!menu || !toggle) return
      const dropdown = document.getElementById('facilities-dropdown')
      if (dropdown && !dropdown.contains(e.target)) {
        closeMenu()
      }
    })

  })()
</script>
