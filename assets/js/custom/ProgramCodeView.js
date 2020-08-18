/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function ProgramCodeView (shareRoot, extractedPath, projectHash, language, appenv, catBlocks) {
  const self = this
  self.shareRoot = shareRoot
  self.extractedProjectsPath = self.shareRoot + extractedPath
  self.projectHash = projectHash
  self.language = language
  self.appenv = appenv
  self.catBlocks = catBlocks
  self.init = function () {
    if (appenv === 'test') {
      console.log('Catblocks must be disabled in the test env.  Why?\n' +
        'Catblocks uses blockly which results in a crash our test system (Behat tests + chrome headless)\n' +
        'Where? @ init -> blockly.inject(...)\n' +
        '\n' +
        'What do we know so far?\n' +
        'Pretty sure the fault is not by Catblocks. Even the simple blockly demo crashs -> \n' +
        '  Given I am on "https://blockly-demo.appspot.com/static/demos/fixed/index.html"\n' +
        '  And I should see "Fixed Blockly"\n' +
        '  Then the element "#blocklyDiv" should be visible\n' +
        '\n' +
        'Chrome crash does not give much useful information, only that much memory was allocated\n' +
        'The bug could be either in the chrome headless or the mink implementation\n' +
        'Large sites per se seem not to be the problem. E.g. tested with https://scratch.mit.edu/projects/390060499/\n' +
        '\n'
      )
      $('#catblocks-code-container').text(
        'Disabled in test env due to problems in the chrome headless/mink/blockly interactions'
      )
    } else {
      /**
       * initiate Catblocks
       *
       * define element:
       *  - container to inject catblocks hidden workflow
       *  - renderSize to use for blocks
       *  - shareRoot
       *  - language to use for rendering texts
       *  - i18n to define the path to the translations
       *  - media for catblocks
       *  - noImageFound is unused, just a placeholder for now
       *
       */
      self.catBlocks.init({
        container: 'catblocks-code-container',
        renderSize: 0.75,
        language: self.language,
        shareRoot: 'catblocks',
        media: 'media',
        i18n: 'i18n',
        noImageFound: 'No_Image_Available.jpg'
      })

      /**
       * start rendering program into div
       */
      window.onload = function () {
        self.catBlocks.render(self.extractedProjectsPath, self.projectHash)
          .catch(err => {
            console.error('Failed to parse catroid file.')
            console.error(err)
            console.error('Using old code view instead of new one')
            // Show old view instead
            $('#catblocks-code-container').innerHTML = ''
            $('#codeview-wrapper').removeClass('d-none')
            $('#code-view-spinner').addClass('d-none')
          })
          .finally(() => {
            $('#code-view-spinner').addClass('d-none')
            $('#code-view-toggler').removeClass('d-none')
            // catblocks need a visible container to calculate the svg sizes.
            // Still it should be collapsed on a page load.
            $('#collapseCodeView').addClass('collapse').addClass('hide')
          })
      }
    }
  }
}
