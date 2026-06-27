var gulp = require('gulp');
var $ = require('gulp-load-plugins')();
var browserSync = require('browser-sync');

gulp.task('scripts-reload', function() {
  return buildScripts().pipe(browserSync.stream());
});

gulp.task('vendor-js', function() {
  return gulp
    .src([
      './node_modules/jquery/dist/jquery.js',
      './node_modules/popper.js/dist/umd/popper.js',
      './node_modules/bootstrap/dist/js/bootstrap.js',
      './node_modules/scrollit/scrollIt.js',
      './node_modules/aos/dist/aos.js',
      './node_modules/owl.carousel/dist/owl.carousel.min.js'
    ])
    .pipe($.concat('vendor.js'))
    .pipe(gulp.dest('./dist'));
});

gulp.task('app-js', function() {
  return gulp
    .src(['./app/js/**/*.js', './app/partials/**/*.js', './app/pages/**/*.js'])
    .pipe($.sourcemaps.init()) // enable source map
    .pipe($.concat('app.js'))
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest('./dist'));
});

gulp.task('build-js', gulp.series('app-js', 'vendor-js'));

gulp.task(
  'min-js',
  gulp.series('build-js', function createMinJs() {
    return gulp
      .src(['./dist/vendor.js', './dist/app.js'])
      .pipe($.concat('scripts.min.js'))
      .pipe($.uglify())
      .pipe(gulp.dest('./public'));
  })
);

gulp.task('watch-js', function() {
  gulp.watch('./app/**/*.js', gulp.series('app-js'));
});
