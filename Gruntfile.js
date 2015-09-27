module.exports = function(grunt) {

  require('load-grunt-tasks')(grunt);
  var path = require('path');

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    exec: {
      revision: {
        cmd: 'bin/revision.sh'
      },
      build: {
        cmd: 'bin/build.sh <%= pkg.name %>'
      },
      zip: {
        cmd: 'bin/zip.sh <%= pkg.name %>'
      },
      build_update: {
        cmd: 'bin/build_update.sh <%= pkg.name %>'
      },
      zip_update: {
        cmd: 'bin/zip.sh <%= pkg.name %>_update'
      },
      build_qd: {
          cmd: 'bin/build_qd.sh'
      },
      make_diff: {
        cmd: function(rev) {
          return 'bin/diff.sh ' + rev;
        }
      }
    },

    cssmin: {
      minify: {
        files: {
          "skin/bootstrap/css/bootstrap-custom.min.css": "skin/bootstrap/css/bootstrap-custom.css",
          "plugin/audio/audio.min.css": "plugin/audio/audio.css",
          "plugin/video/video.min.css": "plugin/video/video.css",
          "js/mediaelementplayer/mediaelementplayer.min.css": "js/mediaelementplayer/mediaelementplayer.css"
        }
      }
    },

    coffee: {
      app: {
        files: {
          "js/qhm.js": "js/qhm/**/*.coffee"
        }
      }
    },

    uglify: {
      app: {
        files: {
          "js/qhm.min.js": ["js/qhm.js", "js/jquery.fitvids.js"]
        }
      },
      plugin: {
        files: {
          "plugin/audio/audio.min.js": "plugin/audio/audio.js",
          "js/jquery.prettyembed.min.js" : "js/jquery.prettyembed.js",
          "plugin/video/video.min.js": "plugin/video/video.js"
        }
      }
    },

    watch: {
      cssmin: {
        files: [
          'skin/bootstrap/css/bootstrap-custom.css',
          'plugin/audio/audio.css',
          'plugin/video/video.css',
          'js/mediaelementplayer/mmediaelementplayer.css'
        ],
        tasks: ['cssmin']
      },
      coffee: {
        files: ['js/qhm/**/*.coffee'],
        tasks: ['coffee:app', 'uglify:app']
      },
      uglify: {
        files: ['plugin/audio/audio.js','js/jquery.prettyembed.js','plugin/video/video.js'],
        tasks: ['uglify:plugin']
      }
    }
  });

  grunt.registerTask('build', ['cssmin', 'coffee', 'uglify', 'exec:revision', 'exec:build', 'exec:zip', 'exec:build_update', 'exec:zip_update', 'exec:build_qd']);
  grunt.registerTask('default', ['cssmin', 'coffee', 'uglify', 'watch']);
}
