var gulp = require('gulp');
var $ = require('gulp-load-plugins')();
var browserSync = require('browser-sync');
var cleanCSS = require('gulp-clean-css');

gulp.task('vendor-css', function() {
  return gulp
    .src([
      './app/vendor/font-awesome.css',
      './node_modules/bootstrap/dist/css/bootstrap.css',
      './node_modules/aos/dist/aos.css',
      './node_modules/owl.carousel/dist/assets/owl.carousel.min.css'
    ])
    .pipe($.concat('vendor.css'))
    .pipe($.autoprefixer('last 4 versions'))
    .pipe(gulp.dest('./dist'));
});

gulp.task('app-sass', function(done) {
  return gulp
    .src('./app/index.scss')
    .pipe(
      $.sass({
        includePaths: [],
        noCache: true,
        style: 'compact',
        errLogToConsole: true
      })
    )
    .on('error', function(error) {
      console.log(error);
      this.emit('end');
    })
    .pipe($.autoprefixer('last 4 versions'))
    .pipe($.rename('app.css'))
    .pipe(gulp.dest('./dist'))
    .on('end', done);
});

gulp.task('build-css', gulp.series('app-sass', 'vendor-css'));

gulp.task(
  'min-css',
  gulp.series('build-css', function createMinCss() {
    return gulp
      .src(['./dist/vendor.css', './dist/app.css'])
      .pipe($.concat('styles.min.css'))
      .pipe(
        cleanCSS({ compatibility: 'ie8', level: { 1: { specialComments: 0 } } })
      )
      .pipe(gulp.dest('./public'));
  })
);

gulp.task(
  'styles-reload',
  gulp.series('app-sass', function stylesSync() {
    return gulp.src('./dist/app.css').pipe(browserSync.stream());
  })
);

gulp.task('watch-css', function() {
  gulp.watch('./app/**/*.scss', gulp.series('styles-reload'));
});
