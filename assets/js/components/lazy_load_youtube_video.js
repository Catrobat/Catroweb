module.exports = function (containerID) {
  const youtube = document.getElementById(containerID)
  if (!youtube) {
    return
  }
  const source = 'https://img.youtube.com/vi/' + youtube.dataset.embed + '/sddefault.jpg'
  const image = document.createElement('img')
  image.setAttribute('src', source)
  image.setAttribute('class', 'youtube-img')
  image.addEventListener('load', function () {
    youtube.appendChild(image)
  })

  youtube.addEventListener('click', function () {
    const iframe = document.createElement('iframe')
    iframe.setAttribute('frameborder', '0')
    iframe.setAttribute('allowfullscreen', '')
    iframe.setAttribute('allow', 'autoplay')
    iframe.setAttribute('src', 'https://www.youtube.com/embed/' + this.dataset.embed + '?&rel=0&autoplay=1&mute=1&showinfo=0')
    this.innerHTML = ''
    this.appendChild(iframe)
  })
}
