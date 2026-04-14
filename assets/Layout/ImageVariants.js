/**
 * Extracts a usable image URL from an API ImageVariants object.
 *
 * The API returns image fields as:
 *   { thumb: {avif_1x, avif_2x, webp_1x, webp_2x}, card: {...}, detail: {...} }
 *
 * This helper picks the WebP 1x URL for a given size tier, with fallbacks.
 *
 * @param {object|null} variants  The ImageVariants object from the API
 * @param {'thumb'|'card'|'detail'} size  Which variant tier to use
 * @param {string|null} fallback  URL to return when variants is null/empty
 * @returns {string|null}
 */
export function getImageUrl(variants, size = 'card', fallback = null) {
  if (!variants) return fallback
  const set = variants[size]
  if (!set) return fallback
  return set.webp_1x || set.webp_2x || set.avif_1x || fallback
}

/**
 * Builds a srcset string from an ImageVariantSet for use in <img srcset>.
 *
 * @param {object|null} variants  The ImageVariants object
 * @param {'thumb'|'card'|'detail'} size  Which variant tier
 * @returns {string} srcset attribute value, or empty string
 */
export function getImageSrcset(variants, size = 'card') {
  if (!variants) return ''
  const set = variants[size]
  if (!set) return ''
  const parts = []
  if (set.avif_1x) parts.push(`${set.avif_1x} 1x`)
  if (set.avif_2x) parts.push(`${set.avif_2x} 2x`)
  if (!parts.length) {
    if (set.webp_1x) parts.push(`${set.webp_1x} 1x`)
    if (set.webp_2x) parts.push(`${set.webp_2x} 2x`)
  }
  return parts.join(', ')
}
