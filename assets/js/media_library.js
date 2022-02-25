import $ from 'jquery'
import { MediaLib } from './custom/MediaLib'

require('../styles/custom/medialib.scss')

const $mediaLibrary = $('.js-media-library')
MediaLib(
  $mediaLibrary.data('package'),
  $mediaLibrary.data('path-media-search'),
  $mediaLibrary.data('flavor'),
  $mediaLibrary.data('media-dir'),
  $mediaLibrary.data('trans-element'),
  $mediaLibrary.data('trans-elements'),
  $mediaLibrary.data('is-webview'),
  $mediaLibrary.data('path-medialibpackagebynameurl')
)
