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
        logJunit: 'tests/report.xml'
        followOutput: true
        stopOnError: true
        stopOnFailure: true

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

    coffee:
      dist:
        files: [{
          expand: true
          cwd: 'site/js'
          src: ['*.coffee']
          dest: 'site/js'
          ext: '.js'
        }]

    copy:
      sourceFavicon:
        src: 'site/img/favicon.ico',
        dest: 'site/favicon.ico'

      configImages:
        files: [{
          expand: true
          cwd: 'config/site/img/config/'
          src: ['**/*']
          dest: 'site/img/config/'
        }]

      configFavicon:
        src: 'config/site/img/favicon.ico',
        dest: 'site/favicon.ico'

    bgShell:
      # TODO add a grunt-spritify npm task to wrap this
      spritifyDefault:
        cmd: 'php -f vendor/soundasleep/spritify/spritify.php -- --input site/styles/default.css --png ../img/default-sprites.png --output site/styles/default.css'
        fail: true

      spritifyCustom:
        cmd: 'php -f vendor/soundasleep/spritify/spritify.php -- --input site/styles/custom.css --png ../img/custom-sprites.png --output site/styles/custom.css'
        fail: true

    watch:
      styles:
        files: ['**/*.scss']
        tasks: ['sass', 'bgShell:spritifyDefault', 'custom']

      scripts:
        files: ['**/*.coffee']
        tasks: ['coffee']

      config:
        files: 'config/site/img/config/**'
        tasks: ['copy:configImages', 'copy:configFavicon', 'bgShell:spritifyDefault', 'custom']

  grunt.loadNpmTasks 'grunt-bg-shell'
  grunt.loadNpmTasks 'grunt-contrib-clean'
  grunt.loadNpmTasks 'grunt-contrib-coffee'
  grunt.loadNpmTasks 'grunt-contrib-copy'
  grunt.loadNpmTasks 'grunt-contrib-sass'
  grunt.loadNpmTasks 'grunt-contrib-watch'
  grunt.loadNpmTasks 'grunt-phpunit'

  grunt.registerTask 'test', "Run tests", ['phpunit']

  grunt.registerTask 'build', "Build the static site", [
    'clean',
    'copy:sourceFavicon',
    'copy:configImages',
    'sass',
    'coffee',
    'bgShell:spritifyDefault',
    'custom'
  ]

  grunt.registerTask 'serve', [
    'build',
    'watch'
  ]

  # TODO add feature to spritify for processing dirs rather than files; can then remove this
  grunt.registerTask 'custom', "Build custom content if necessary", ->
    grunt.task.run(['bgShell:spritifyCustom']) if grunt.file.exists('site/styles/custom.css')
    grunt.task.run(['copy:configFavicon']) if grunt.file.exists('config/site/img/favicon.ico')

  grunt.registerTask 'default', ['test']
