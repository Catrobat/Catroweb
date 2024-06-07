import { MediaLib } from './custom/MediaLib'

require('../styles/custom/medialib.scss')

const mediaLibrary = document.querySelector('.js-media-library')
MediaLib(
  mediaLibrary.dataset.package,
  mediaLibrary.dataset.pathMediaSearch,
  mediaLibrary.dataset.flavor,
  mediaLibrary.dataset.mediaDir,
  JSON.parse(mediaLibrary.dataset.translations),
  mediaLibrary.dataset.isWebview,
  mediaLibrary.dataset.pathMedialibpackagebynameurl,
)
