// import { CatBlocks } from '../catblocks/CatBlocks'  // CatBlocks needs export!

require('../styles/components/code_view.scss')

document.addEventListener('DOMContentLoaded', () => {
  //   const codeViewElement = document.querySelector('.js-code-view');
  //   const appEnv = codeViewElement.getAttribute('data-app-env');
  //   const locale = codeViewElement.getAttribute('data-locale');
  //   const extractedProjectPath = codeViewElement.getAttribute('data-extracted-project-path');
  //   const projectHash = codeViewElement.getAttribute('data-project-hash');
  //
  //   if (appEnv === 'test') {
  //     disableCodeViewInTestEnv();
  //   } else {
  //     initializeCodeView();
  //   }
  //
  //   function initializeCodeView() {
  //     /*
  //      * initiate CatBlocks
  //      *
  //      * define element:
  //      *  - container to inject CatBlocks hidden workflow
  //      *  - renderSize to use for blocks
  //      *  - shareRoot
  //      *  - language to use for rendering texts
  //      *  - i18n to define the path to the translations
  //      *  - media for CatBlocks
  //      *  - noImageFound is unused, just a placeholder for now
  //      */
  //     CatBlocks.init({
  //       container: 'catblocks-code-container',
  //       renderSize: 0.75,
  //       language: locale,
  //       shareRoot: 'catblocks',
  //       media: 'media',
  //       i18n: 'i18n',
  //       noImageFound: 'No_Image_Available.jpg'
  //     });
  //
  //     /**
  //      * start rendering program into div
  //      */
  //     window.onload = function () {
  //       CatBlocks.render(extractedProjectPath, projectHash)
  //         .catch(err => {
  //           console.error('Failed to parse catroid file.');
  //           console.error(err);
  //           console.warn('Using old code view instead of new one');
  //           // Show old view instead
  //           document.getElementById('catblocks-code-container').innerHTML = '';
  //           document.getElementById('codeview-wrapper').classList.remove('d-none');
  //           document.getElementById('code-view-spinner').classList.add('d-none');
  //         })
  //         .finally(() => {
  //           document.getElementById('code-view-spinner').classList.add('d-none');
  //           document.getElementById('code-view-toggler').classList.remove('d-none');
  //           // CatBlocks need a visible container to calculate the svg sizes.
  //           // Still it should be collapsed on a page load.
  //           const collapseCodeView = document.getElementById('collapseCodeView');
  //           collapseCodeView.classList.add('collapse', 'hide');
  //         });
  //     };
  //   }
  //
  //   function disableCodeViewInTestEnv() {
  //     console.log('Catblocks must be disabled in the test env.  Why?\n' +
  //       'Catblocks uses blockly which results in a crash our test system (Behat tests + chrome headless)\n' +
  //       'Where? @ init -> blockly.inject(...)\n' +
  //       '\n' +
  //       'What do we know so far?\n' +
  //       'Pretty sure the fault is not by Catblocks. Even the simple blockly demo crashs -> \n' +
  //       '  Given I am on "https://blockly-demo.appspot.com/static/demos/fixed/index.html"\n' +
  //       '  And I should see "Fixed Blockly"\n' +
  //       '  Then the element "#blocklyDiv" should be visible\n' +
  //       '\n' +
  //       'Chrome crash does not give much useful information, only that much memory was allocated\n' +
  //       'The bug could be either in the chrome headless or the mink implementation\n' +
  //       'Large sites per se seem not to be the problem. E.g. tested with https://scratch.mit.edu/projects/390060499/\n' +
  //       '\n'
  //     );
  //     document.getElementById('catblocks-code-container').textContent =
  //       'Disabled in test env due to problems in the chrome headless/mink/blockly interactions';
  //   }

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
