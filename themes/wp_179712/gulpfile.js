'use strict';

const
	gulp         = require('gulp'),
	rename       = require('gulp-rename'),
	notify       = require('gulp-notify'),
	autoprefixer = require('gulp-autoprefixer'),
	sourcemaps   = require('gulp-sourcemaps'),
	sass         = require('gulp-sass');

//css
function css() {
	return gulp.src('./assets/scss/**/*.scss')
		.pipe(sourcemaps.init())
		.pipe(sass().on('error', sass.logError))
		.pipe(sass( { outputStyle: 'expanded' } ))
		.pipe(autoprefixer({
				overrideBrowserslist: ['last 10 versions'],
				cascade: false
		}))
		.pipe(sourcemaps.write())
		.pipe(gulp.dest('./assets/css'))
		.pipe(notify('Compile Sass Done!'));
};

//watch
function watch(){
	gulp.watch('./assets/scss/**/*.scss', css);
};

gulp.task('default', watch);
