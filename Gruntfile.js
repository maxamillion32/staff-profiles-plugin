module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    less: {
      production: {
        options: {
          paths: ["css"],
          cleancss:true
        },
        files: {
          "css/people.min.css": "css/people.less",
          "css/admin.min.css": "css/admin.less"
        }
      },
      development: {
        options: {
          paths: ["css"],
          cleancss:false
        },
        files: {
          "css/people.css": "css/people.less",
          "css/admin.css": "css/admin.less"
        }
      }
    },
    uglify: {
      pluginjs: {
        options: {
          // the banner is inserted at the top of the output
          banner: '/*!\n * University of Leeds Staff Profiles Plugin javascript\n * @author Peter Edwards <p.l.edwards@leeds.ac.uk>\n * @uses bxslider4 by Steven Wanderski http://bxslider.com/\n * @version <%= pkg.version %>\n * generated: <%= grunt.template.today("dd-mm-yyyy") %>\n */\n',
          mangle: false
        },
        files: {
          'js/people.min.js': ['js/people.js']
        }
      },
      adminjs: {
        options: {
          // the banner is inserted at the top of the output
          banner: '/*!\n * University of Leeds Staff Profiles Plugin javascript for Wordpress Dashboard\n * @author Peter Edwards <p.l.edwards@leeds.ac.uk>\n * @uses bxslider4 by Steven Wanderski http://bxslider.com/\n * @version <%= pkg.version %>\n * generated: <%= grunt.template.today("dd-mm-yyyy") %>\n */\n',
          mangle: false
        },
        files: {
          'js/admin.min.js': ['js/admin.js']
        }
      }
  	},
    watch: {
      plugin: {
        files: ['css/admin.less', 'css/people.less', 'js/admin.js', 'js/people.js'],
        tasks: ['less:production', 'less:development', 'uglify:pluginjs', 'uglify:adminjs']
      }
	}
  });

  // Load the plugins that provide the tasks.
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // Default task(s).
  grunt.registerTask('default', ['watch:plugin']);
};