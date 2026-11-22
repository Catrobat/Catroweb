import { registerControllers, startStimulusApp } from 'vite-plugin-symfony/stimulus/helpers'

export const app = startStimulusApp()

// Not eager: each controller is its own chunk, fetched the first time an element using it appears. Every page
// loads this file and few use any controller, so eager controllers only competed with the page's LCP image.
registerControllers(app, import.meta.glob('./controllers/**/*_controller.js'))
