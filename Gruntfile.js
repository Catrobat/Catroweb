var jsBaseSrc = ['web/js/base/*.js', 'web/js/globalPlugins/*.js'];
var jsLoginSrc = ['web/js/social/*.js'];
var jsCustomSrc = 'web/js/custom';
var jsLocalPluginSrc = 'web/js/localPlugins';
var themes = ['pocketcode', 'pocketalice', 'pocketgalaxy'];

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
            paths: ["web/css/base", "web/css/themes/" + theme]
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

var admin_css_path = "web/css/admin/admin.css";
var admin_file_config = {};
admin_file_config[admin_css_path] = ["web/css/plugins/*"];

lessconfig['admin'] = {
    options: {
        compress: true,
        yuicompress: true,
        optimization: 2,
        relativeUrls: true,
    },
    files: [
        admin_file_config,
        {
            expand: true,
            cwd: 'web/css/admin/',
            src: ['**/*.less'],
            dest: 'web/css/admin/',
            ext: '.css',
            extDot: 'first'
        }
    ]
};

module.exports = function (grunt) {
    require('jit-grunt')(grunt);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        concat: {
            options: {
                separator: ';',
                banner: '/*\n  Generated File by Grunt\n  Sourcepath: web/js\n*/\n'
            },
            base: {
                src: jsBaseSrc,
                dest: 'web/compiled/js/<%= pkg.baseJSName %>.js'
            },
            login: {
                src: jsLoginSrc,
                dest: 'web/compiled/js/<%= pkg.loginJSName %>.js'
            },
            localPlugins: {
                expand: true,
                cwd: jsLocalPluginSrc,
                src: '**/*.js',
                dest: 'web/compiled/js/'
            },
            custom: {
                expand: true,
                cwd: jsCustomSrc,
                src: '**/*.js',
                dest: 'web/compiled/js/'
            },
            bootstrapcss: {
                expand: true,
                cwd: 'vendor/twbs/bootstrap/dist/css',
                src: 'bootstrap.min.css',
                dest: 'web/compiled/bootstrap/'
            },
            bootstrapjs: {
                expand: true,
                cwd: 'vendor/twbs/bootstrap/dist/js',
                src: 'bootstrap.min.js',
                dest: 'web/compiled/bootstrap/'
            },
            jquery: {
                expand: true,
                cwd: 'node_modules/jquery/dist',
                src: 'jquery.min.js',
                dest: 'web/compiled/bootstrap/'
            },
        },
        uglify: {
            options: {
                mangle: false
            },
            compiledFiles: {
                files: [{
                    expand: true,
                    cwd: 'web/compiled/js',
                    src: '**/*.js',
                    dest: 'web/compiled/min'
                }]
            }
        },
        cssmin: {
            target: {
                files: [{
                    expand: true,
                    cwd: 'web/css/bootstrap_yeti/',
                    src: 'bootstrap_yeti.css',
                    dest: 'web/compiled/bootstrap/',
                    ext: '.min.css',
                }]
            }
        },
        less: lessconfig,
        watch: {
            options: {
                nospawn: true
            },
            styles: {
                files: ['web/css/**/*.less'],
                tasks: ['less'],
                options: {
                    nospawn: true
                }
            },
            scripts: {
                files: ['web/js/**/*.js'],
                tasks: ['concat','uglify'],
                options: {
                    nospawn: true
                }
            }
        }
    });
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.registerTask('default', ['concat', 'less', 'uglify', 'cssmin', 'watch']);
};