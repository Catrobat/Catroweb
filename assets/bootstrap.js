import { startStimulusApp } from 'vite-plugin-symfony/stimulus/helpers'

export const app = startStimulusApp()

// vite-plugin-symfony's registerControllers() mis-handles eagerly-globbed
// ESM modules whose default export is a Stimulus Controller class — it routes
// them through the third-party "controller infos" branch (which expects
// { enabled, identifier, controller }) and silently skips because
// default.enabled is undefined. Register each class directly instead.
//
// ajax_controller.js is excluded — it's a shared base class extended by
// other controllers, not itself a Stimulus controller for the DOM.
const modules = import.meta.glob(
  ['./controllers/**/*_controller.js', '!./controllers/ajax_controller.js'],
  { eager: true },
)

for (const [path, mod] of Object.entries(modules)) {
  if (!mod.default) continue
  const identifier = path
    .replace(/^.*?controllers\//, '')
    .replace(/_controller\.js$/, '')
    .replace(/_/g, '-')
    .replace(/\//g, '--')
    .toLowerCase()
  app.register(identifier, mod.default)
}
