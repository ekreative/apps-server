var gulp = require('gulp')
var concat = require('gulp-concat')
var uglify = require('gulp-uglify')
var less = require('gulp-less')
var del = require('del')
var sourcemaps = require('gulp-sourcemaps')
var uglifycss = require('gulp-uglifycss')
var imagemin = require('gulp-imagemin')
var babel = require('gulp-babel')
var filter = require('gulp-filter')
var gutil = require('gulp-util')

gulp.task('default', ['build'])

gulp.task('build', ['fonts', 'styles', 'scripts:bundle', 'scripts:pages', 'images'])

gulp.task('clean', function (cb) {
  del(['web/css/*', 'web/js/*', 'web/fonts/*'], cb)
})

gulp.task('styles', function () {
  return gulp.src(['web-src/less/*'])
        .pipe(less())
        .on('error', gutil.log)
        .pipe(uglifycss())
        .pipe(concat('app.css'))
        .pipe(gulp.dest('web/css'))
})

gulp.task('scripts:bundle', function () {
  var myJsFilter = filter(function (file) {
    return /web-src/.test(file.path)
  }, {restore: true})

  return gulp.src([
    'node_modules/jquery/dist/jquery.js',
    'node_modules/bootstrap/dist/js/bootstrap.js',
    'web-src/js/*.js'
  ])
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(myJsFilter)
        .pipe(babel())
        .pipe(myJsFilter.restore)
        .pipe(concat('bundle.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('web/js'))
})

gulp.task('scripts:pages', function () {
  return gulp.src(['web-src/js/*/**/*.js'])
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(babel())
        .pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('web/js'))
})

gulp.task('fonts', function () {
  return gulp.src(['node_modules/bootstrap/dist/fonts/*'])
        .pipe(gulp.dest('web/fonts'))
})

gulp.task('images', function () {
  return gulp.src('web-src/images/**/*')
        .pipe(imagemin({
          progressive: true,
          interlaced: true
        }))
        .pipe(gulp.dest('web/images'))
})

gulp.task('watch', ['build'], function () {
  gulp.watch('web-src/less/*.less', ['styles'])
  gulp.watch('web-src/js/*/**/*.js', ['scripts:pages'])
  gulp.watch('web-src/js/*.js', ['scripts:bundle'])
  gulp.watch('web-src/images/**/*', ['images'])
})
