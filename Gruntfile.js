module.exports = function(grunt) {

  grunt.initConfig({
    uglify: {
      all: {
        options: {
          mangle: {
            except: ['jQuery']
          }
        },
        files: {
          'assets/js/dist/scripts.min.js': ['assets/js/src/*.js']
        }
      }
    },
    less: {
      all: {
        options: {
          cleancss: true
        },
        files: {
          'assets/css/admin.min.css': ['assets/less/admin.less']
        }
      }
    }
  });

  // grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-less');

  grunt.registerTask('default', ['uglify']);

};
