'use strict';

const gulp = require('gulp');
const del = require('del');
const browserSync = require('browser-sync').create();

const concat = require('gulp-concat');
const autoprefixer = require('gulp-autoprefixer');
const cleanCSS = require('gulp-clean-css');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps'); 
//const babel = require('gulp-babel');  
//const uglify = require('gulp-uglify');
const imagemin = require('gulp-imagemin');
const clone = require('gulp-clone');
const webp =  require('gulp-webp');


const jsFiles = [
	'./src/js/jquery-3.1.1.min.js',
	'./src/js/jquery-ui.min.js',
	/*'./src/js/jquery.editable.select.js',*/
	'./src/js/jquery.touchSwipe.min.js',
	'./src/js/jquery.mousewheel.min.js',
	'./src/js/jquery.animateNumber.js',
	'./src/js/jquery.magnific-popup.js',
	'./src/js/slick.min.js',
	/*'./src/js/wow.min.js',
	'./src/js/cookies_notice.js',*/
	'./src/js/messages.js',
	'./src/js/checks.js',
	'./src/js/common.js',
	'./src/js/scripts.js'
];

sass.compiler = require('node-sass');

function styles() {
	return gulp.src('./src/css/style.scss')
				/*.pipe(sourcemaps.init())*/
				.pipe(concat('style.css'))		
				.pipe(autoprefixer({
		            overrideBrowsersList: ['> 0.1%'],
		            cascade: false
		        }))
		        /*
		        .pipe(cleanCSS({
		        	level: 2
		        }))
		        */
		        .pipe(sass.sync({outputStyle: 'compressed'}).on('error', sass.logError))
		        /*.pipe(sourcemaps.write())*/
				.pipe(gulp.dest('./assets/css'))
				.pipe(browserSync.stream());
}

function scripts() {
	return gulp.src(jsFiles)
				.pipe(babel({
					presets: ['@babel/preset-env']
				}))
				.pipe(concat('scripts.js'))
				.pipe(uglify({
					toplevel: true
				}))
				.pipe(gulp.dest('./assets/js'))
				.pipe(browserSync.stream());
}

function images() {
	return gulp.src('./src/images/*')
        .pipe(imagemin())
        .pipe(gulp.dest('./assets/images'));
}

function fonts() {
	return gulp.src('./src/fonts/**/*')
		.pipe(gulp.dest('./assets/fonts'));
}

function copy() {
	return gulp.src('./src/images/*')
        .pipe(gulp.dest('./assets/images'));
}

function watch() {
	browserSync.init({
        proxy: "psk.local"
    });

	gulp.watch('./src/css/**/*.scss', styles);
	gulp.watch('./src/js/**/*.js', scripts);
	gulp.watch('./src/images/**/*', copy);
	gulp.watch('./*.html').on('change', browserSync.reload);
}

function clean() {
	return del(['assets/*']);
}

function uploadedImagesToWebp() {
	var sink = clone.sink();

	return gulp.src('./uploads/**/*')
        .pipe(imagemin())
        .pipe(sink)
        .pipe(webp())
        .pipe(sink.tap({quality: 80}))
        .pipe(gulp.dest('./uploads'));
}


//gulp.task('styles', styles);
//gulp.task('scripts', scripts);
gulp.task('images', images);
gulp.task('watch', watch);
gulp.task('uploaded_images2webp', uploadedImagesToWebp);

gulp.task('build', gulp.series(clean,
						gulp.parallel(styles, scripts, images, fonts)
					));

gulp.task('dev', gulp.series('build', 'watch'));