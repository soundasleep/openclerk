module.exports = (grunt) ->
  grunt.initConfig
    pkg: grunt.file.readJSON('package.json')

    phpunit:
      unit:
        dir: 'tests'
      options:
        bin: 'vendor/bin/phpunit'
        colors: true

  grunt.loadNpmTasks 'grunt-phpunit'

  grunt.registerTask 'test', "Run tests", ['phpunit']

  grunt.registerTask 'default', ['test']
