import { createLazyController, startStimulusApp } from 'vite-plugin-symfony/stimulus/helpers'

export const app = startStimulusApp()

// Each controller is its own chunk, fetched when an element using it first appears: every page loads this file,
// and few pages use any controller, so eager controllers only competed with the page's own images for bandwidth.
// Registered directly rather than via registerControllers(), which mis-handles modules whose default export is a
// Controller class.
for (const [path, load] of Object.entries(import.meta.glob('./controllers/**/*_controller.js'))) {
  const identifier = path
    .replace(/^.*?controllers\//, '')
    .replace(/_controller\.js$/, '')
    .replace(/_/g, '-')
    .replace(/\//g, '--')
    .toLowerCase()
  app.register(identifier, createLazyController(load))
}
