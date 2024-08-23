/* global CatBlocks */
import './CodeView.scss'

document.addEventListener('DOMContentLoaded', () => {
  const codeView = document.querySelector('.js-code-view')
  const locale = codeView.dataset.locale
  const extractedProjectPath = codeView.dataset.extractedProjectPath
  const projectHash = codeView.dataset.projectHash

  initializeCodeView()

  function initializeCodeView() {
    /*
     * initiate CatBlocks
     *
     * define element:
     *  - container to inject CatBlocks hidden workflow
     *  - renderSize to use for blocks
     *  - shareRoot
     *  - language to use for rendering texts
     *  - i18n to define the path to the translations
     *  - media for CatBlocks
     *  - noImageFound is unused, just a placeholder for now
     */
    CatBlocks.init({
      container: 'catblocks-code-container',
      renderSize: 0.75,
      language: locale,
      shareRoot: 'catblocks',
      media: 'media',
      i18n: 'i18n',
      noImageFound: 'No_Image_Available.jpg',
    })

    /**
     * start rendering project into div
     */
    window.onload = function () {
      CatBlocks.render(extractedProjectPath, projectHash)
        .catch((err) => {
          console.error('Failed to parse catroid file.')
          console.error(err)
          console.error('Using old code view instead of new one')
          // Show old view instead
          document.getElementById('catblocks-code-container').innerHTML = ''
          document.getElementById('codeview-wrapper').classList.remove('d-none')
          document.getElementById('code-view-spinner')?.classList.add('d-none')
        })
        .finally(() => {
          document.getElementById('code-view-spinner').classList.add('d-none')
          document
            .getElementById('code-view-toggler')
            .classList.remove('d-none')
          // CatBlocks need a visible container to calculate the svg sizes.
          // Still it should be collapsed on a page load.
          const collapseCodeView = document.getElementById('collapseCodeView')
          collapseCodeView?.classList.add('collapse')
          collapseCodeView?.classList.add('hide')
        })
    }
  }
})

document.addEventListener('DOMContentLoaded', () => {
  // Old Code View!!
  document.querySelectorAll('.collapse-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const nextElement = this.nextElementSibling
      if (
        nextElement.style.display === 'none' ||
        nextElement.style.display === ''
      ) {
        nextElement.style.display = 'block'
      } else {
        nextElement.style.display = 'none'
      }
      this.querySelector('.arrow').classList.toggle('rotate')
    })
  })
})
