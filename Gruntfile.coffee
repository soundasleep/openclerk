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
        }, {
          # any custom styles in config/
          expand: true
          cwd: 'config/site/css'
          src: ['*.scss']
          dest: 'site/styles'
          ext: '.css'
        }]

    bgShell:
      spritify:
        cmd: 'php -f vendor/soundasleep/spritify/spritify.php -- --input site/styles/default.css --png ../img/default-sprites.png --output site/styles/default.css'
        fail: true

  grunt.loadNpmTasks 'grunt-phpunit'
  grunt.loadNpmTasks 'grunt-contrib-sass'
  grunt.loadNpmTasks 'grunt-bg-shell'

  grunt.registerTask 'test', "Run tests", ['phpunit']

  grunt.registerTask 'serve', [
    'sass',
    'bgShell:spritify'
  ]

  grunt.registerTask 'default', ['test']
