var jsBaseSrc = ['web/js/base/*.js', 'web/js/globalPlugins/*.js'];
var jsLoginSrc = ['web/js/social/*.js'];
var jsCustomSrc = 'web/js/custom';
var jsAnalyticsSrc = 'web/js/analytics';
var jsLocalPluginSrc = 'web/js/localPlugins';
var themes = ['pocketcode', 'pocketalice', 'pocketgalaxy', 'phirocode', 'luna', 'create@school'];

var sassconfig = {};

for (index = 0; index < themes.length; index++)
{
  var theme = themes[index];
  
  var base_css_path = "web/css/" + theme + "/base.css";
  
  var base_file_config = {};
  base_file_config[base_css_path] = ["web/css/themes/" + theme + "/" + theme +".scss", "web/css/plugins/*" ];
  
  sassconfig[theme] =
    {
      options: {
        loadPath    : ["web/css/base", "web/css/themes/" + theme],
        style: "compressed",
        sourcemap: 'none'
      },
      files  : [
        base_file_config,
        {
          expand: true,
          cwd   : 'web/css/custom/',
          src   : ['**/*.scss'],
          dest  : 'web/css/' + theme + '/',
          ext   : '.css',
          extDot: 'first'
        }

      ]
    };
}

var admin_css_path = "web/css/admin/admin.css";
var admin_file_config = {};
admin_file_config[admin_css_path] = ["web/css/plugins/*"];

sassconfig['admin'] = {
  files  : [
    admin_file_config,
    {
      expand: true,
      cwd   : 'web/css/admin/',
      src   : ['**/*.scss'],
      dest  : 'web/css/admin/',
      ext   : '.css',
      extDot: 'first'
    }
  ]
};

module.exports = function(grunt)
{
  require('jit-grunt')(grunt);
  
  grunt.initConfig({
    pkg   : grunt.file.readJSON('package.json'),
    copy: {
      bootstrap_vendor: {
        expand: true,
        cwd: 'node_modules/bootstrap/',
        src: '**',
        dest: 'web/bootstrap_vendor/'
      },
      clipboard_js: {
        expand: true,
        cwd: 'node_modules/clipboard/dist/',
        src: 'clipboard.min.js',
        dest: 'web/js/localPlugins/'
      },
      bootstrap_js: {
        src: 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
        dest: 'web/compiled/bootstrap/bootstrap.min.js'
      }
    },
    concat: {
      options     : {
        separator: ';',
        banner   : '/*\n  Generated File by Grunt\n  Sourcepath: web/js\n*/\n'
      },
      base        : {
        src : jsBaseSrc,
        dest: 'web/compiled/js/<%= pkg.baseJSName %>.js'
      },
      login       : {
        src : jsLoginSrc,
        dest: 'web/compiled/js/<%= pkg.loginJSName %>.js'
      },
      localPlugins: {
        expand: true,
        cwd   : jsLocalPluginSrc,
        src   : '**/*.js',
        dest  : 'web/compiled/js/'
      },
      custom      : {
        expand: true,
        cwd   : jsCustomSrc,
        src   : '**/*.js',
        dest  : 'web/compiled/js/'
      },
      analytics   : {
        expand: true,
        cwd   : jsAnalyticsSrc,
        src   : '**/*.js',
        dest  : 'web/compiled/js/'
      },
      jquery      : {
        expand: true,
        cwd   : 'node_modules/jquery/dist',
        src   : 'jquery.min.js',
        dest  : 'web/compiled/bootstrap/'
      },
      css : {
        expand: true,
        cwd: ''
      }
    },
    uglify: {
      options      : {
        mangle: false
      },
      compiledFiles: {
        files: [
          {
            expand: true,
            cwd   : 'web/compiled/js',
            src   : '**/*.js',
            dest  : 'web/compiled/min'
          }
        ]
      }
    },
    sass  : sassconfig,
    watch : {
      options: {
        nospawn: true
      },
      styles : {
        files  : ['web/css/**/*.scss'],
        tasks  : ['sass'],
        options: {
          nospawn: true
        }
      },
      scripts: {
        files  : ['web/js/**/*.js'],
        tasks  : ['concat', 'uglify'],
        options: {
          nospawn: true
        }
      }
    }
  });
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify-es');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.registerTask('default', ['copy','concat', 'sass', 'uglify']);
};
