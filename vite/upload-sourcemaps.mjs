// Uploads built sourcemaps to Bugsnag. No-op when BUGSNAG_API_KEY is unset
// (local dev). Exits non-zero on real upload failures so CI fails loudly.

const apiKey = process.env.BUGSNAG_API_KEY
if (!apiKey) {
  process.exit(0)
}

const { uploadBrowser } = await import('@bugsnag/source-maps')

try {
  await uploadBrowser({
    apiKey,
    appVersion: process.env.APP_VERSION || '',
    overwrite: true,
    directory: 'public/build',
    baseUrl: '*/build',
  })
  console.log('[bugsnag] sourcemaps uploaded')
} catch (err) {
  console.error('[bugsnag] sourcemap upload failed:', err.message)
  process.exit(1)
}
