module.exports = (grunt) ->
  grunt.initConfig
    pkg: grunt.file.readJSON('package.json')

    clean:
      configImages: ['site/img/config/']

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

    copy:
      configImages:
        files: [{
          expand: true
          cwd: 'config/site/img/config/'
          src: ['**/*']
          dest: 'site/img/config/'
        }]

    bgShell:
      # TODO add a grunt-spritify npm task to wrap this
      spritifyDefault:
        cmd: 'php -f vendor/soundasleep/spritify/spritify.php -- --input site/styles/default.css --png ../img/default-sprites.png --output site/styles/default.css'
        fail: true

      spritifyCustom:
        cmd: 'php -f vendor/soundasleep/spritify/spritify.php -- --input site/styles/custom.css --png ../img/custom-sprites.png --output site/styles/custom.css'
        fail: true

  grunt.loadNpmTasks 'grunt-bg-shell'
  grunt.loadNpmTasks 'grunt-contrib-clean'
  grunt.loadNpmTasks 'grunt-contrib-copy'
  grunt.loadNpmTasks 'grunt-contrib-sass'
  grunt.loadNpmTasks 'grunt-phpunit'

  grunt.registerTask 'test', "Run tests", ['phpunit']

  grunt.registerTask 'serve', [
    'clean',
    'copy:configImages',
    'sass',
    'bgShell:spritifyDefault',
    'customSpritifyCustom'
  ]

  # TODO add feature to spritify for processing dirs rather than files; can then remove this
  grunt.registerTask 'customSpritifyCustom', "Build custom content if necessary", ->
    grunt.task.run(['bgShell:spritifyCustom']) if grunt.file.exists('site/styles/custom.css')

  grunt.registerTask 'default', ['test']
