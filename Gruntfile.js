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
    }
  });

  // grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerTask('default', ['uglify']);

};
