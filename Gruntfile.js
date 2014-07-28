module.exports = function (grunt) {

	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				compress: {
					global_defs: {
						"EO_SCRIPT_DEBUG": false
					},
					dead_code: true
				},
				banner: '/*! <%= pkg.name %> <%= pkg.version %> <%= grunt.template.today("yyyy-mm-dd HH:MM") %> */\n'
			},
			build: {
				files: [
					{
						expand: true, // Enable dynamic expansion.
						src: ['extensions/default-templates/js/*.js', '!extensions/default-templates/js/*.min.js' ], // Actual pattern(s) to match.
						ext: '.min.js' // Dest filepaths will have this extension.
					}
				]
			}
		},
		jshint: {
			options: {
				reporter: require('jshint-stylish'),
				globals: {
					"EO_SCRIPT_DEBUG": false
				},
				'-W099': true, //Mixed spaces and tabs
				'-W083': true, //TODO Fix functions within loop
				'-W082': true, //Todo Function declarations should not be placed in blocks
				'-W020': true  //Read only - error when assigning EO_SCRIPT_DEBUG a value.
			},
			all: [ 'js/*.js', '!js/*.min.js' ]
		},

		compress: {
			//Compress build/foogallery
			main: {
				options: {
					mode: 'zip',
					archive: './dist/foogallery.zip'
				},
				expand: true,
				cwd: 'dist/foogallery/',
				src: ['**/*'],
				dest: 'foogallery/'
			},
			version: {
				options: {
					mode: 'zip',
					archive: './dist/foogallery-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'dist/foogallery/',
				src: ['**/*'],
				dest: 'foogallery/'
			}
		},

		clean: {
			//Clean up build folder
			main: ['dist/foogallery']
		},

		copy: {
			// Copy the plugin to a versioned release directory
			main: {
				src: [
					'**',
					'!node_modules/**',
					'!dist/**',
					'!.git/**',
					'!vendor/**',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!.gitmodules',
					'!*~',
					'!CONTRIBUTING.md'
				],
				dest: 'dist/foogallery/'
			}
		},

		wp_readme_to_markdown: {
			convert: {
				files: {
					'readme.md': 'readme.txt'
				}
			},
			options : {
				banner: 'https://s3.amazonaws.com/foogallery/banner-772x250.jpg',
				afterBannerMarkdown: '[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fooplugins/foogallery/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/fooplugins/foogallery/?branch=develop)',
				screenshots: {
					enabled: true,
					prefix: 'https://s3.amazonaws.com/foogallery/screenshot-',
					suffix: '.jpg'
				}
			}
		},

		checkrepo: {
			deploy: {
				tag: {
					eq: '<%= pkg.version %>' // Check if highest repo tag is equal to pkg.version
				},
				tagged: true, // Check if last repo commit (HEAD) is not tagged
				clean: true // Check if the repo working directory is clean
			}
		},

		watch: {
			readme: {
				files: ['readme.txt'],
				tasks: ['wp_readme_to_markdown'],
				options: {
					spawn: false
				}
			},
			scripts: {
				files: ['js/*.js'],
				tasks: ['newer:jshint', 'newer:uglify'],
				options: {
					spawn: false
				}
			}
		},

		wp_deploy: {
			deploy: {
				options: {
					svn_user: 'bradvin',
					plugin_slug: 'foogallery',
					build_dir: 'dist/foogallery/'
				}
			}
		},

		po2mo: {
			files: {
				src: 'languages/*.po',
				expand: true
			}
		},

		pot: {
			options: {
				text_domain: 'foogallery',
				dest: 'languages/',
				keywords: ['__', '_e', 'esc_html__', 'esc_html_e', 'esc_attr__', 'esc_attr_e', 'esc_attr_x', 'esc_html_x', 'ngettext', '_n', '_ex', '_nx' ]
			},
			files: {
				src: [
					'**/*.php',
					'!node_modules/**',
					'!dist/**',
					'!vendor/**',
					'!*~'
				],
				expand: true
			}
		},

		checktextdomain: {
			options: {
				text_domain: 'foogallery',
				keywords: ['__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: [
					'**/*.php',
					'!node_modules/**',
					'!dist/**',
					'!vendor/**',
					'!*~',
					'!includes/foopluginbase/**'
				],
				expand: true
			}
		}

	});
	
	grunt.registerTask('readme', [ 'wp_readme_to_markdown' ]);

	grunt.registerTask('test', [ 'jshint' ]);

	grunt.registerTask('build', [ 'test', 'pot', 'newer:po2mo', 'wp_readme_to_markdown', 'clean', 'copy' ]);

	grunt.registerTask('deploy', [ 'checkbranch:master', 'checkrepo:deploy', 'build', 'wp_deploy', 'compress' ]);

};
