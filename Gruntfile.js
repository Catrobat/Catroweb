
// -------------------------------------------------------------------------------------------------
// Change assets/public dir here:
//
let assetsDir = 'assets'
let publicDir = 'public' // 'public'

// -------------------------------------------------------------------------------------------------
// Defining JavaScript paths:
//
let jsBaseSrc = [ assetsDir + '/js/base/*.js', assetsDir + '/js/globalPlugins/*.js' ]
let jsRegisterSrc = [ assetsDir + '/js/register/*.js' ]
let jsCustomSrc = assetsDir + '/js/custom'
let jsAnalyticsSrc = assetsDir + '/js/analytics'
let jsLocalPluginSrc = assetsDir + '/js/localPlugins'

// -------------------------------------------------------------------------------------------------
// Defining CSS paths for all themes + admin:
//
let themes = [ 'pocketcode', 'pocketalice', 'pocketgalaxy', 'phirocode', 'luna', 'create@school' ]
let sassconfig = {}

for (let index = 0; index < themes.length; index++) {
  let theme = themes[index]
  let baseCssPath = publicDir + '/css/' + theme + '/base.css'
  let baseFileConfig = {}
  baseFileConfig[baseCssPath] = [ assetsDir + '/css/themes/' + theme + '/' + theme + '.scss' ]
  sassconfig[theme] =
  {
    options: {
      loadPath: [ assetsDir + '/css/base', assetsDir + '/css/themes/' + theme ],
      style: 'compressed',
      sourcemap: 'none'
    },
    files: [
      baseFileConfig,
      // copy plugins
      {
        expand: true,
        cwd: assetsDir + '/css/plugins/',
        src: ['*'],
        dest: publicDir + '/css/plugins/',
        extDot: 'first'
      },
      // every css/custom file gets a separate file
      {
        expand: true,
        cwd: assetsDir + '/css/custom/',
        src: ['**/*.scss'],
        dest: publicDir + '/css/' + theme + '/',
        ext: '.css',
        extDot: 'first'
      }
    ]
  }
}

let adminCssPath = publicDir + '/css/admin/admin.css'
let adminFileConfig = {}
adminFileConfig[adminCssPath] = [ assetsDir + '/css/plugins/*' ]

sassconfig['admin'] = {
  files: [
    adminFileConfig,
    {
      expand: true,
      cwd: assetsDir + '/css/admin/',
      src: ['**/*.scss'],
      dest: publicDir + '/css/admin/',
      ext: '.css',
      extDot: 'first'
    }
  ]
}

// -------------------------------------------------------------------------------------------------
// Register all grunt tasks here:
//
module.exports = function (grunt) {
  require('jit-grunt')(grunt)
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    copy: {
      bootstrap_vendor: {
        expand: true,
        cwd: 'node_modules/bootstrap/',
        src: '**',
        dest: publicDir + '/bootstrap_vendor/'
      },
      font_awesome: {
        expand: true,
        cwd: 'node_modules/@fortawesome/',
        src: '**',
        dest: publicDir + '/font_awesome_wrapper/'
      },
      font_awesome_webfonts: {
        expand: true,
        cwd: publicDir + '/font_awesome_wrapper/fontawesome-free/webfonts',
        src: '**',
        dest: publicDir + '/webfonts/'
      },
      fonts: {
        expand: true,
        cwd: assetsDir + '/css/fonts',
        src: '**',
        dest: publicDir + '/css/fonts/'
      },
      images: {
        expand: true,
        cwd: assetsDir + '/images',
        src: '**',
        dest: publicDir + '/images/'
      },
      clipboard_js: {
        expand: true,
        cwd: 'node_modules/clipboard/dist/',
        src: 'clipboard.min.js',
        dest: publicDir + '/js/localPlugins/'
      },
      bootstrap_js: {
        src: 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
        dest: publicDir + '/compiled/bootstrap/bootstrap.min.js'
      },
      popper_js: {
        src: 'node_modules/popper.js/dist/popper.js',
        dest: publicDir + '/compiled/popper/popper.js'
      },
      jquery_ui_js: {
        src: 'node_modules/popper.js/dist/popper.js',
        dest: publicDir + '/compiled/popper/popper.js'
      }
    },
    concat: {
      options: {
        separator: ';',
        banner: '/*\n  Generated File by Grunt\n  Sourcepath: assets/js\n*/\n'
      },
      base: {
        src: jsBaseSrc,
        dest: publicDir + '/compiled/js/<%= pkg.baseJSName %>.js'
      },
      register: {
        src: jsRegisterSrc,
        dest: publicDir + '/compiled/js/<%= pkg.registerJSName %>.js'
      },
      localPlugins: {
        expand: true,
        cwd: jsLocalPluginSrc,
        src: '**/*.js',
        dest: publicDir + '/compiled/js/'
      },
      custom: {
        expand: true,
        cwd: jsCustomSrc,
        src: '**/*.js',
        dest: publicDir + '/compiled/js/'
      },
      analytics: {
        expand: true,
        cwd: jsAnalyticsSrc,
        src: '**/*.js',
        dest: publicDir + '/compiled/js/'
      },
      jquery: {
        expand: true,
        cwd: 'node_modules/jquery/dist',
        src: 'jquery.min.js',
        dest: publicDir + '/compiled/bootstrap/'
      },
      css: {
        expand: true,
        cwd: ''
      }
    },
    uglify: {
      options: {
        mangle: false
      },
      compiledFiles: {
        files: [
          {
            expand: true,
            cwd: publicDir + '/compiled/js',
            src: '**/*.js',
            dest: publicDir + '/compiled/min'
          }
        ]
      }
    },
    sass: sassconfig,
    watch: {
      options: {
        nospawn: true
      },
      styles: {
        files: [publicDir + '/css/**/*.scss'],
        tasks: ['sass'],
        options: {
          nospawn: true
        }
      },
      scripts: {
        files: [publicDir + '/js/**/*.js'],
        tasks: ['concat', 'uglify'],
        options: {
          nospawn: true
        }
      }
    }
  })
  grunt.loadNpmTasks('grunt-contrib-copy')
  grunt.loadNpmTasks('grunt-contrib-concat')
  grunt.loadNpmTasks('grunt-contrib-uglify-es')
  grunt.loadNpmTasks('grunt-contrib-sass')
  grunt.registerTask('default', ['copy', 'concat', 'sass', 'uglify'])
}
