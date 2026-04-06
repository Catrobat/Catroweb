/**
 * Aligns retention tooltips based on the icon's horizontal position.
 * If the icon is in the right half of the viewport, the tooltip
 * anchors to the right; otherwise it anchors to the left.
 */
document.addEventListener(
  'pointerenter',
  (e) => {
    if (!(e.target instanceof Element)) return

    const wrap = e.target.closest('.retention-info-wrap')
    if (!wrap) return

    const tooltip = wrap.querySelector('.retention-tooltip')
    if (!tooltip) return

    const rect = wrap.getBoundingClientRect()
    if (rect.left > window.innerWidth / 2) {
      tooltip.classList.add('align-right')
    } else {
      tooltip.classList.remove('align-right')
    }
  },
  true,
)
