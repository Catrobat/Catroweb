import { MediaLib } from './MediaLib'

require('./CategoryDetailPage.scss')

const mediaLibrary = document.querySelector('.js-media-library')
MediaLib(
  mediaLibrary.dataset.categoryId,
  mediaLibrary.dataset.categoryName,
  mediaLibrary.dataset.pathMediaSearch,
  mediaLibrary.dataset.flavor,
  JSON.parse(mediaLibrary.dataset.translations),
  mediaLibrary.dataset.pathMediaAssetsApi,
  mediaLibrary.dataset.searchQuery,
)
