import { escapeAttr } from '../Components/HtmlEscape'

/**
 * Build a srcset string for one format from a variant set.
 *
 * @param {object} set  A single variant-set object (thumb/card/detail)
 * @param {'avif'|'webp'} format
 * @returns {string} e.g. "url 1x, url 2x" or ""
 */
function srcsetFor(set, format) {
  const parts = []
  const u1 = set[format + '_1x']
  const u2 = set[format + '_2x']
  if (u1) parts.push(u1 + ' 1x')
  if (u2) parts.push(u2 + ' 2x')
  return parts.join(', ')
}

/**
 * Pick the best single URL from a variant set (webp 1x fallback).
 *
 * @param {object|null} set
 * @returns {string|null}
 */
function bestUrl(set) {
  if (!set) return null
  return set.webp_1x || set.webp_2x || set.avif_1x || null
}

/**
 * Match `/images/default/{basename}-{variant}@{dpr}x.webp` and build a full
 * variant set so default/fallback images also get AVIF <source> elements.
 */
const DEFAULT_RE = /^(\/images\/default\/.+)-(thumb|card|detail)@[12]x\.webp(?:\?.*)?$/
function defaultSetFromUrl(url) {
  if (!url) return null
  const m = DEFAULT_RE.exec(url)
  if (!m) return null
  const base = m[1]
  const variant = m[2]
  return {
    avif_1x: `${base}-${variant}@1x.avif`,
    avif_2x: `${base}-${variant}@2x.avif`,
    webp_1x: `${base}-${variant}@1x.webp`,
    webp_2x: `${base}-${variant}@2x.webp`,
  }
}

/**
 * Resolve the variant set to use: API data first, then default-image fallback.
 */
function resolveSet(variants, size, fallback) {
  return (variants ? variants[size] : null) || defaultSetFromUrl(fallback)
}

/**
 * Append AVIF + WebP <source> elements to a <picture>, before `refNode`.
 */
function appendSources(picture, set, refNode, lazy = false) {
  const attr = lazy ? 'data-srcset' : 'srcset'
  for (const fmt of ['avif', 'webp']) {
    const ss = srcsetFor(set, fmt)
    if (ss) {
      const source = document.createElement('source')
      source.type = fmt === 'avif' ? 'image/avif' : 'image/webp'
      source.setAttribute(attr, ss)
      if (refNode) {
        picture.insertBefore(source, refNode)
      } else {
        picture.appendChild(source)
      }
    }
  }
}

/**
 * Build a <picture> HTML string with <source> elements for AVIF and WebP,
 * letting the browser pick the best format it supports.
 *
 * Pass `lazy: true` for lazysizes integration (data-srcset / data-src).
 *
 * @param {object|null} variants  The ImageVariants object from the API
 * @param {'thumb'|'card'|'detail'} size  Which variant tier
 * @param {string|null} fallback  Fallback src when no variants exist
 * @param {string} imgAttrs  Pre-built attribute string for the <img> (already escaped by caller)
 * @param {{lazy?: boolean}} options
 * @returns {string}  HTML string
 */
export function buildPictureHTML(variants, size, fallback, imgAttrs = '', { lazy = false } = {}) {
  const set = resolveSet(variants, size, fallback)
  const src = bestUrl(set) || fallback || ''
  const attrStr = imgAttrs ? ' ' + imgAttrs : ''
  const srcAttr = lazy ? 'data-src' : 'src'
  const srcsetAttr = lazy ? 'data-srcset' : 'srcset'

  if (!set) {
    return `<img ${srcAttr}="${escapeAttr(src)}"${attrStr}>`
  }

  let sources = ''
  const avif = srcsetFor(set, 'avif')
  if (avif) sources += `<source type="image/avif" ${srcsetAttr}="${escapeAttr(avif)}">`
  const webp = srcsetFor(set, 'webp')
  if (webp) sources += `<source type="image/webp" ${srcsetAttr}="${escapeAttr(webp)}">`

  return `<picture>${sources}<img ${srcAttr}="${escapeAttr(src)}"${attrStr}></picture>`
}

/**
 * Create a <picture> DOM element with proper <source> children.
 *
 * @param {object|null} variants
 * @param {'thumb'|'card'|'detail'} size
 * @param {string|null} fallback
 * @param {object} imgAttrs  Attribute key-value pairs for the <img>
 * @param {{lazy?: boolean}} options
 * @returns {HTMLPictureElement|HTMLImageElement}
 */
export function createPictureElement(
  variants,
  size,
  fallback,
  imgAttrs = {},
  { lazy = false } = {},
) {
  const set = resolveSet(variants, size, fallback)
  const src = bestUrl(set) || fallback || ''

  const img = document.createElement('img')
  if (lazy) {
    img.setAttribute('data-src', src)
  } else {
    img.src = src
  }
  for (const [key, value] of Object.entries(imgAttrs)) {
    if (value === true) {
      img.setAttribute(key, '')
    } else if (value !== false && value !== null && value !== undefined) {
      img.setAttribute(key, String(value))
    }
  }

  if (!set) return img

  const picture = document.createElement('picture')
  appendSources(picture, set, null, lazy)
  picture.appendChild(img)
  return picture
}

/**
 * Update an existing <img> (possibly inside a <picture>) with new variant URLs.
 * If the <img> is not inside a <picture> and variants exist, wraps it in one.
 *
 * @param {HTMLImageElement} imgElement
 * @param {object|null} variants
 * @param {'thumb'|'card'|'detail'} size
 * @param {string|null} fallback
 */
export function updatePictureSources(imgElement, variants, size, fallback) {
  const set = resolveSet(variants, size, fallback)
  const src = bestUrl(set) || fallback || ''
  imgElement.src = src

  let picture = imgElement.parentElement?.tagName === 'PICTURE' ? imgElement.parentElement : null

  if (!picture && set) {
    picture = document.createElement('picture')
    imgElement.replaceWith(picture)
    picture.appendChild(imgElement)
  }

  if (!picture) return

  picture.querySelectorAll('source').forEach((s) => s.remove())

  if (set) {
    appendSources(picture, set, imgElement)
  }
}
