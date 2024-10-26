import { MediaLib } from './MediaLib'

require('./PackageDetailPage.scss')

const mediaLibrary = document.querySelector('.js-media-library')
MediaLib(
  mediaLibrary.dataset.package,
  mediaLibrary.dataset.pathMediaSearch,
  mediaLibrary.dataset.flavor,
  mediaLibrary.dataset.mediaDir,
  JSON.parse(mediaLibrary.dataset.translations),
  mediaLibrary.dataset.isWebview === 'true',
  mediaLibrary.dataset.pathMedialibpackagebynameurl,
)

const categories = document.querySelectorAll('.category')
categories.forEach((category) => {
  const header = category.querySelector('.header')
  header.addEventListener('click', () => {
    category.classList.toggle('active')
  })
})
