import { startStimulusApp } from 'vite-plugin-symfony/stimulus/helpers'

export const app = startStimulusApp()

// vite-plugin-symfony@8's registerControllers() mis-handles eagerly-globbed
// ESM modules whose default export is a Stimulus Controller class — it routes
// them through the third-party "controller infos" branch (which expects
// { enabled, identifier, controller }) and silently skips when default.enabled
// is undefined. We register each class directly to sidestep that.
for (const [path, mod] of Object.entries(
  import.meta.glob('./controllers/**/*_controller.js', { eager: true }),
)) {
  if (!mod.default) continue
  const identifier = path
    .replace(/^.*?controllers\//, '')
    .replace(/_controller\.js$/, '')
    .replace(/_/g, '-')
    .replace(/\//g, '--')
    .toLowerCase()
  app.register(identifier, mod.default)
}
