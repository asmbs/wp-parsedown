module.exports = function(grunt) {

    require('load-grunt-tasks')(grunt);
    require('time-grunt')(grunt);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        asset_path: 'assets',
        paths: {
            scripts_src:  '<%= asset_path %>/scripts/src',
            scripts_dist: '<%= asset_path %>/scripts/dist',
            styles_src:   '<%= asset_path %>/styles/src',
            styles_dist:  '<%= asset_path %>/styles/dist',
        },
        uglify: {
            build: {
                options: {
                    mangle: { except: ['jQuery'] }
                },
                files: {
                    '<%= paths.scripts_dist %>/editor.min.js': '<%= paths.scripts_src %>/*.js'
                }
            },
            dev: {
                options: {
                    mangle: false,
                    beautify: true
                },
                files: {
                    '<%= paths.scripts_dist %>/editor.min.js': '<%= paths.scripts_src %>/*.js'
                }
            }
        },
        less: {
            build: {
                options: {
                    cleancss: true
                },
                files: {
                    '<%= paths.styles_dist %>/admin.min.css': '<%= paths.styles_src %>/admin.less'
                }
            }
        }
    });

    grunt.registerTask('default', ['less:build', 'uglify:build']);

};
