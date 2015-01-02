module.exports = (grunt) ->
  grunt.initConfig
    pkg: grunt.file.readJSON('package.json')

    clean:
      tmp: ['.tmp']
      configImages: ['site/img/config/']
      compiledScripts: ['site/scripts']
      compiledHead: ['site/head-compiled.html']
      copiedNodeModulesJs: ['site/js/node_modules/']
      generated: ['generated', 'site/images']

    phpunit:
      unit:
        dir: ''       # we specify NO dir so that we only use phpunit.xml
      options:
        bin: 'vendor/bin/phpunit'
        colors: true
        configuration: './phpunit.xml'
        logJunit: 'tests/report.xml'
        followOutput: true

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

      head:
        src: 'layout/head.html'
        dest: 'site/head-compiled.html'

      nodeModulesJs:
        files: [{
          expand: true
          cwd: 'node_modules/'
          src: ['**/*.js', '!**/node_modules/**']
          dest: 'site/js/node_modules/'
        }]

    bgShell:
      # TODO add a grunt-spritify npm task to wrap this
      spritifyDefault:
        cmd: 'php -f vendor/soundasleep/spritify/spritify.php -- --input site/styles/default.css --png ../img/default-sprites.png --output site/styles/default.css'
        fail: true

      spritifyCustom:
        cmd: 'php -f vendor/soundasleep/spritify/spritify.php -- --input site/styles/custom.css --png ../img/custom-sprites.png --output site/styles/custom.css'
        fail: true

      # TODO add a grunt npm task to wrap this
      componentDiscovery:
        cmd: 'php -f vendor/soundasleep/component-discovery/generate.php -- .'
        fail: true

      # TODO add a grunt npm task to wrap this
      assetDiscovery:
        cmd: 'php -f vendor/soundasleep/asset-discovery/generate.php -- .'
        fail: true

    useminPrepare:
      html: 'site/head-compiled.html'
      options:
        dest: 'site/scripts/'

    usemin:
      html: ['site/head-compiled.html']
      options:
        dest: 'site/scripts/'
        blockReplacements:
          js: (block) ->
            return '<script src="<?php echo htmlspecialchars(calculate_relative_path()); ?>scripts/' + block.dest + "<?php echo '?' . get_site_config('openclerk_version'); ?>" + '"></script>'

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

      discovery:
        files: ['**/*.json']
        tasks: ['bgShell:componentDiscovery']

  grunt.loadNpmTasks 'grunt-bg-shell'
  grunt.loadNpmTasks 'grunt-contrib-clean'
  grunt.loadNpmTasks 'grunt-contrib-coffee'
  grunt.loadNpmTasks 'grunt-contrib-concat'
  grunt.loadNpmTasks 'grunt-contrib-copy'
  grunt.loadNpmTasks 'grunt-contrib-sass'
  grunt.loadNpmTasks 'grunt-contrib-uglify'
  grunt.loadNpmTasks 'grunt-contrib-watch'
  grunt.loadNpmTasks 'grunt-phpunit'
  grunt.loadNpmTasks 'grunt-usemin'

  grunt.registerTask 'test', "Run tests", ['build', 'phpunit']

  grunt.registerTask 'build', "Build the static site", [
    'clean',
    'bgShell:componentDiscovery',
    'bgShell:assetDiscovery',
    'copy:sourceFavicon',
    'copy:configImages',
    'copy:head',
    'copy:nodeModulesJs',
    'useminPrepare',
    'concat',
    'uglify',
    'usemin',
    'sass',
    'coffee',
    'bgShell:spritifyDefault',
    'custom'
  ]

  grunt.registerTask 'serve', [
    'clean',
    'bgShell:componentDiscovery',
    'bgShell:assetDiscovery',
    'copy:sourceFavicon',
    'copy:configImages',
    # 'copy:head',
    # 'useminPrepare',
    # 'concat',
    # 'uglify',
    # 'usemin',
    'sass',
    'coffee',
    'bgShell:spritifyDefault',
    'custom'
    'watch'
  ]

  # TODO add feature to spritify for processing dirs rather than files; can then remove this
  grunt.registerTask 'custom', "Build custom content if necessary", ->
    grunt.task.run(['bgShell:spritifyCustom']) if grunt.file.exists('site/styles/custom.css')
    grunt.task.run(['copy:configFavicon']) if grunt.file.exists('config/site/img/favicon.ico')

  grunt.registerTask 'default', ['test']
