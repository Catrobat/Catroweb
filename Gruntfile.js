var themes = ['pocketcode', 'pocketalice'];

var lessconfig = {};

for (index = 0; index < themes.length; index++) {
    var theme = themes[index];
    
    var base_css_path = "web/css/" + theme + "/base.css"; 
    
    var base_file_config = {};
    base_file_config[base_css_path] = ["web/css/plugins/*", "web/css/themes/" + theme + "/main.less"];

    lessconfig[theme] = 
    {
        options: {
          compress: true,
          yuicompress: true,
          optimization: 2,
          relativeUrls: true,
          paths: ["web/css/base","web/css/themes/" + theme]
        },
        files: [
                 base_file_config,
                 {
                     expand: true,
                     cwd: 'web/css/custom/',
                     src: ['**/*.less'],
                     dest: 'web/css/' + theme + '/',
                     ext: '.css',
                     extDot: 'first'
                   }
               ]
      };
}

module.exports = function(grunt) {
  require('jit-grunt')(grunt);

  grunt.initConfig({
    less: lessconfig,
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