import $ from 'jquery'
import { MediaLibrary } from './custom/MediaLibrary'

require('../styles/custom/medialib.scss')

const $mediaLibrary = $('.js-media-library')
MediaLibrary(
  $mediaLibrary.data('package'),
  $mediaLibrary.data('path-media-search'),
  $mediaLibrary.data('flavor'),
  $mediaLibrary.data('media-dir'),
  $mediaLibrary.data('translations'),
  $mediaLibrary.data('is-webview'),
  $mediaLibrary.data('path-medialibpackagebynameurl'),
)
