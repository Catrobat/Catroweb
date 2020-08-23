function initIFrame () {
  const iFrames = document.getElementsByTagName('iframe')
  Array.from(iFrames).forEach((iframe) => {
    if (iframe.getAttribute('data-src')) {
      iframe.setAttribute('src', iframe.getAttribute('data-src'))
    }
  })
}

window.onload = initIFrame
