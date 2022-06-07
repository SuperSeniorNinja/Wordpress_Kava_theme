'use strict';

let gulp            = require( 'gulp' ),
	rename          = require( 'gulp-rename' ),
	notify          = require( 'gulp-notify' ),
	sass            = require( 'gulp-sass')(require('sass')),
	plumber         = require( 'gulp-plumber' ),
	autoprefixer     = require( 'gulp-autoprefixer' );

gulp.task( 'admin-css', () => {
	return gulp.src('./assets/scss/admin.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( { outputStyle: 'compressed' } ))
		.pipe(autoprefixer({
				browsers: ['last 10 versions'],
				cascade: false
		}))
		.pipe(rename('admin.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
} );

gulp.task('templates-library-css', () => {
	return gulp.src('./assets/scss/templates-library.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( { outputStyle: 'compressed' } ))
		.pipe(autoprefixer({
			browsers: ['last 10 versions'],
			cascade: false
		}))
		.pipe(rename('templates-library.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task('preview-css', () => {
	return gulp.src('./includes/elementor/assets/scss/preview.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( { outputStyle: 'compressed' } ))
		.pipe(autoprefixer({
				browsers: ['last 10 versions'],
				cascade: false
		}))

		.pipe(rename('preview.css'))
		.pipe(gulp.dest('./includes/elementor/assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task('editor-css', () => {
	return gulp.src('./includes/elementor/assets/scss/editor.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( { outputStyle: 'compressed' } ))
		.pipe(autoprefixer({
				browsers: ['last 10 versions'],
				cascade: false
		}))

		.pipe(rename('editor.css'))
		.pipe(gulp.dest('./includes/elementor/assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

//watch
gulp.task( 'watch', function() {
	gulp.watch( './assets/scss/**', gulp.series( 'admin-css' ) );
	gulp.watch( './assets/scss/**', gulp.series( 'templates-library-css' ) );
	gulp.watch( './includes/elementor/assets/scss/**', gulp.series( 'preview-css' ) );
	gulp.watch( './includes/elementor/assets/scss/**', gulp.series( 'editor-css' ) );
} );
