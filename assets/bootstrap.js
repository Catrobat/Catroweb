import { startStimulusApp } from 'vite-plugin-symfony/stimulus/helpers'

// Every page loads this file, so it registers no controllers: each page's entry registers its own
// (`app.register('<identifier>', Controller)`). Bundling all of them here made every page download every controller.
export const app = startStimulusApp()
