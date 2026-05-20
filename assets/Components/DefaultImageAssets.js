/* global globalConfiguration */

/**
 * Resolves a default-image asset URL by key. Source-of-truth is the
 * `globalConfiguration.defaultImageAssets` object emitted from
 * templates/Layout/Base.html.twig — that goes through Symfony's asset() and
 * gets cache-busted via VersionStrategy (?v=APP_VERSION).
 *
 * @param {'avatarThumb'|'screenshotCard'|'screenshotDetail2x'|'thumbnailCard'} key
 * @returns {string} Asset URL, or empty string if the key is missing.
 */
export function defaultImageAsset(key) {
  return globalConfiguration?.defaultImageAssets?.[key] ?? ''
}
