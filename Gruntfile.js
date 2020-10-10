// --------------------------------------------------------------------------------------------------
// JS & (S)CSS should always be placed in the assets directory.
// Grunt will tweak the code to our preferences and put it into the assets directory.
//
const ASSETS_DIRECTORY = 'assets'
const PUBLIC_DIRECTORY = 'public'
const TEMPLATE_DIRECTORY = 'templates'


// -------------------------------------------------------------------------------------------------
// Register all grunt tasks here:
//
module.exports = function (grunt) {
  // enable plugins
  grunt.loadNpmTasks('grunt-contrib-copy')
  grunt.loadNpmTasks('grunt-contrib-concat')
  grunt.loadNpmTasks('grunt-contrib-uglify-es')
  grunt.loadNpmTasks('grunt-contrib-sass')
  grunt.loadNpmTasks('grunt-contrib-watch')
  grunt.loadNpmTasks('grunt-purgecss');
  // define project configuration
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    copy: COPY_CONFIG,
    concat: CONCAT_CONFIG,
    uglify: UGLIFY_CONFIG,
    sass: SASS_CONFIG,
    watch: WATCH_CONFIG,
    purgecss: PURGECSS_CONFIG
  })
  // define default tasks to run on `grunt`
  grunt.registerTask('default', ['copy', 'concat', 'sass', 'uglify', 'purgecss'])
}

// -------------------------------------------------------------------------------------------------
// Copy task:
//
//   - copies assets to the public directory
//   - copies node modules to the public directory
//
const COPY_CONFIG =
  {
    bootstrap: {
      expand: true,
      cwd: 'node_modules/bootstrap/',
      src: '**',
      dest: PUBLIC_DIRECTORY + '/bootstrap/'
    },
    vis: { // @deprecated
      // We need the whole dist folder because the css references multiple images
      expand: true,
      cwd: 'node_modules/vis/dist/',
      src: '**',
      dest: PUBLIC_DIRECTORY + '/vis/'
    },
    font_awesome: {
      expand: true,
      cwd: 'node_modules/@fortawesome/',
      src: '**',
      dest: PUBLIC_DIRECTORY + '/font_awesome_wrapper/'
    },
    font_awesome_webfonts: {
      expand: true,
      cwd: PUBLIC_DIRECTORY + '/font_awesome_wrapper/fontawesome-free/webfonts/',
      src: '**',
      dest: PUBLIC_DIRECTORY + '/webfonts/'
    },
    fonts: {
      expand: true,
      cwd: ASSETS_DIRECTORY + '/css/fonts',
      src: '**',
      dest: PUBLIC_DIRECTORY + '/css/fonts/'
    },
    materialIcons: {
      expand: true,
      cwd: 'node_modules/material-design-icons/iconfont',
      src: '**',
      dest: PUBLIC_DIRECTORY + '/css/fonts/'
    },
    material: {
      expand: true,
      cwd: 'node_modules/@material/',
      src: '**',
      dest: PUBLIC_DIRECTORY + '/@material/'
    },
    images: {
      expand: true,
      cwd: ASSETS_DIRECTORY + '/images',
      src: '**',
      dest: PUBLIC_DIRECTORY + '/images/'
    },
    favicon: {
      // must be in root dir of public folder
      expand: true,
      cwd: ASSETS_DIRECTORY + '/images',
      src: 'favicon.ico',
      dest: PUBLIC_DIRECTORY
    },
    catBlocks: {
      expand: true,
      cwd: ASSETS_DIRECTORY + '/catblocks',
      src: '**',
      dest: PUBLIC_DIRECTORY + '/catblocks/'
    },
    custom: {
      expand: true,
      cwd: ASSETS_DIRECTORY + '/js/custom',
      src: '**/*.js',
      dest: PUBLIC_DIRECTORY + '/js/'
    },
    analytics: {
      expand: true,
      cwd: ASSETS_DIRECTORY + '/js/analytics',
      src: '**/*.js',
      dest: PUBLIC_DIRECTORY + '/js/'
    },
    clipboard_js: {
      src: 'node_modules/clipboard/dist/clipboard.min.js',
      dest: PUBLIC_DIRECTORY + '/js/modules/clipboard.min.js'
    },
    bootstrap_js: {
      expand: true,
      cwd: 'node_modules/bootstrap/dist/js',
      src: '*',
      dest: PUBLIC_DIRECTORY + '/js/modules/'
    },
    sweetalert_all: {
      src: 'node_modules/sweetalert2/dist/sweetalert2.all.min.js',
      dest: PUBLIC_DIRECTORY + '/js/modules/sweetalert2.all.min.js'
    },
    jquery: {
      src: 'node_modules/jquery/dist/jquery.min.js',
      dest: PUBLIC_DIRECTORY + '/js/modules/jquery.min.js'
    },
    textfill_js: {
      src: 'node_modules/textfilljs/dist/textfill.min.js',
      dest: PUBLIC_DIRECTORY + '/js/modules/textfill.min.js'
    },
    jquery_ui: {
      src: 'node_modules/jquery-ui-dist/jquery-ui.min.js',
      dest: PUBLIC_DIRECTORY + '/js/modules/jquery-ui.min.js'
    },
    jquery_contextmenu_js: {
      src: 'node_modules/jquery-contextmenu/dist/jquery.contextMenu.min.js',
      dest: PUBLIC_DIRECTORY + '/js/modules/jquery.contextMenu.min.js'
    },
    jquery_contextmenu_css: {
      src: 'node_modules/jquery-contextmenu/dist/jquery.contextMenu.min.css',
      dest: PUBLIC_DIRECTORY + '/css/modules/jquery.contextMenu.min.css'
    },
    jquery_ui_position: {
      src: 'node_modules/jquery-contextmenu/dist/jquery.ui.position.min.js',
      dest: PUBLIC_DIRECTORY + '/js/modules/jquery.ui.position.min.js'
    },
    animatedModal_js: {
      src: 'node_modules/animatedmodal/animatedModal.min.js',
      dest: PUBLIC_DIRECTORY + '/js/modules/animatedModal.min.js'
    },
    animate_css: {
      src: 'node_modules/animate.css/animate.min.css',
      dest: PUBLIC_DIRECTORY + '/css/modules/animate.min.css'
    }
  }

const PURGECSS_CONFIG = {
  pocketcode: {
    options: {
      content: [TEMPLATE_DIRECTORY + '/**/*.html.twig',  PUBLIC_DIRECTORY + '/js/**/*.min.js']
    },
    files: {
      'public/css/pocketcode/base.css': ['public/css/pocketcode/base.css'],
    }
  },
  arduino: {
    options: {
      content: [ TEMPLATE_DIRECTORY + '/**/*.html.twig',  PUBLIC_DIRECTORY + '/js/**/*.min.js']
    },
    files: {
      'public/css/arduino/base.css': ['public/css/arduino/base.css'],
    }
  },
  school: {
    options: {
      content: [ TEMPLATE_DIRECTORY + '/**/*.html.twig',  PUBLIC_DIRECTORY + '/js/**/*.min.js']
    },
    files: {
      'public/css/create@school/base.css': ['public/css/create@school/base.css'],
    }
  },
  embroidery: {
    options: {
      content: [ TEMPLATE_DIRECTORY + '/**/*.html.twig',  PUBLIC_DIRECTORY + '/js/**/*.min.js']
    },
    files: {
      'public/css/embroidery/base.css': ['public/css/embroidery/base.css'],
    }
  },
  luna: {
    options: {
      content: [ TEMPLATE_DIRECTORY + '/**/*.html.twig',  PUBLIC_DIRECTORY + '/js/**/*.min.js']
    },
    files: {
      'public/css/luna/base.css': ['public/css/luna/base.css'],
    }
  },
  phirocode: {
    options: {
      content: [ TEMPLATE_DIRECTORY + '/**/*.html.twig',  PUBLIC_DIRECTORY + '/js/**/*.min.js']
    },
    files: {
      'public/css/phirocode/base.css': ['public/css/phirocode/base.css'],
    }
  },
  pocketalice: {
    options: {
      content: [ TEMPLATE_DIRECTORY + '/**/*.html.twig',  PUBLIC_DIRECTORY + '/js/**/*.min.js']
    },
    files: {
      'public/css/pocketalice/base.css': ['public/css/pocketalice/base.css'],
    }
  },
  pocketgalaxy: {
    options: {
      content: [ TEMPLATE_DIRECTORY + '/**/*.html.twig',  PUBLIC_DIRECTORY + '/js/**/*.min.js']
    },
    files: {
      'public/css/pocketgalaxy/base.css': ['public/css/pocketgalaxy/base.css'],
    }
  }


}

// -------------------------------------------------------------------------------------------------
// Concat task:
//
//   - multiple files are combined into one
//
const CONCAT_CONFIG =
  {
    base: {
      src: ASSETS_DIRECTORY + '/js/base/*.js',
      dest: PUBLIC_DIRECTORY + '/js/base.js'
    }
  }

// -------------------------------------------------------------------------------------------------
// SASS to CSS task:
//
//   - loading all supported themes from the liip config
//   - creating an entry for every theme
//
const SASS_CONFIG = {}

const THEMES = loadThemesFromSymfonyParameters()

THEMES.forEach(function (theme) {
  addThemeConfig(SASS_CONFIG, theme)
})

function loadThemesFromSymfonyParameters () {
  // Requiring all necessary Modules
  const yaml = require('js-yaml')
  const fs = require('fs')

  // load the yaml file
  try {
    const liipConfig = yaml.safeLoad(
      fs.readFileSync('config/packages/liip_theme.yaml', 'utf8')
    )
    const themes = liipConfig.parameters.themes
    if (!Array.isArray(themes) || !themes.length) {
      console.error('Themes array is empty!')
    }
    return themes
  } catch (e) {
    console.error('Themes could not be loaded!\n' + e)
    return undefined
  }
}

function addThemeConfig (SASS_CONFIG, theme) {
  // all css files should be available for every theme
  const PUBLIC_CSS_BASE_FILE_PATH = PUBLIC_DIRECTORY + '/css/' + theme + '/base.css'
  const THEME_CONFIG = {}
  THEME_CONFIG[PUBLIC_CSS_BASE_FILE_PATH] = [ASSETS_DIRECTORY + '/css/themes/' + theme + '/' + theme + '.scss']
  SASS_CONFIG[theme] =
    {
      options: {
        loadPath: [ASSETS_DIRECTORY + '/css/base', ASSETS_DIRECTORY + '/css/themes/' + theme, 'node_modules'],
        style: 'compressed',
        sourceMap: true
      },
      files: [
        THEME_CONFIG,
        {
          expand: true,
          cwd: ASSETS_DIRECTORY + '/css/custom/',
          src: ['**/*.scss'],
          dest: PUBLIC_DIRECTORY + '/css/' + theme + '/',
          ext: '.css',
          extDot: 'first'
        }
      ]
    }
}

// -------------------------------------------------------------------------------------------------
// Uglify task:
//
//   - minify JS files
//
const UGLIFY_CONFIG =
  {
    options: {
      mangle: false
    },
    compiledFiles: {
      files: [
        {
          expand: true,
          src: [PUBLIC_DIRECTORY + '/js/**/*.js', '!' + PUBLIC_DIRECTORY + '/js/**/*.min.js'],
          dest: PUBLIC_DIRECTORY + '/js',
          rename: function (dst, src) {
            return src.replace('.js', '.min.js')
          }
        }
      ]
    }
  }

// -------------------------------------------------------------------------------------------------
// Watch task:
//
//   - detect changes to specified files and run the specified tasks on changes
//
const WATCH_CONFIG =
  {
    options: {
      nospawn: true
    },
    styles: {
      files: [ASSETS_DIRECTORY + '/css/**/*.scss'],
      tasks: ['sass'],
      options: {
        nospawn: true
      }
    },
    scripts: {
      files: [ASSETS_DIRECTORY + '/js/**/*.js'],
      tasks: ['concat', 'uglify'],
      options: {
        nospawn: true
      }
    }
  }
