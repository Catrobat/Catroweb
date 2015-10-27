module.exports = function(grunt) {
  require('jit-grunt')(grunt);

  grunt.initConfig({
    less: {
      pocketcode: {
        options: {
          compress: true,
          yuicompress: true,
          optimization: 2,
          relativeUrls: true,
          paths: ["web/css/base","web/css/themes/pocketcode"]
        },
        files: [
                 { "web/css/pocketcode/base.css": [
                     "web/css/plugins/*",
                     "web/css/themes/pocketcode/pocketcode.less"
                     ]
                 },
                 {
                     expand: true,
                     cwd: 'web/css/custom/',
                     src: ['**/*.less'],
                     dest: 'web/css/pocketcode/',
                     ext: '.css',
                     extDot: 'first'
                   }
               ]
      },
      gamejam: {
          options: {
            compress: true,
            yuicompress: true,
            optimization: 2,
            relativeUrls: true,
            paths: ["web/css/base","web/css/themes/pocketalice"]
          },
          files: [
                   { "web/css/pocketalice/base.css": [
                       "web/css/plugins/*",
                       "web/css/themes/pocketalice/gamejam.less"
                       ]
                   },
                   {
                       expand: true,
                       cwd: 'web/css/custom/',
                       src: ['**/*.less'],
                       dest: 'web/css/pocketalice/',
                       ext: '.css',
                       extDot: 'first'
                     }
                 ]
        }
    },
    watch: {
      styles: {
        files: ['web/css/**/*.less'], 
        tasks: ['less'],
        options: {
          nospawn: true
        }
      }
    }
  });

  grunt.registerTask('default', ['less', 'watch']);
};