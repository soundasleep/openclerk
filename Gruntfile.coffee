module.exports = (grunt) ->
  grunt.initConfig
    pkg: grunt.file.readJSON('package.json')

    phpunit:
      unit:
        dir: 'tests'
      options:
        bin: 'vendor/bin/phpunit'
        colors: true

    sass:
      dist:
        files: [{
          expand: true
          cwd: 'site/css'
          src: ['*.scss']
          dest: 'site/styles'
          ext: '.css'
        }]

  grunt.loadNpmTasks 'grunt-phpunit'
  grunt.loadNpmTasks 'grunt-contrib-sass'

  grunt.registerTask 'test', "Run tests", ['phpunit']

  grunt.registerTask 'serve', [
    'sass'
  ]

  grunt.registerTask 'default', ['test']
