import { MediaLibrary } from './custom/MediaLibrary'

require('../styles/custom/media_library.scss')

const mediaLibrary = document.getElementById('js-media-library')

MediaLibrary(
  mediaLibrary.dataset.package,
  mediaLibrary.dataset.pathMediaSearch,
  mediaLibrary.dataset.translations,
  mediaLibrary.dataset.isWebview,
)
