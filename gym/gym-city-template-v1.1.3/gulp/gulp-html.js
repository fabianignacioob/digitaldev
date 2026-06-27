var gulp = require('gulp');
var fileinclude = require('gulp-file-include');

gulp.task('build-html', function() {
  return gulp
    .src(['./app/*.html'])
    .pipe(
      fileinclude({
        prefix: '@@',
        basepath: './app/',
        context: {
          env: 'dev'
        }
      })
    )
    .pipe(gulp.dest('./dist'));
});

gulp.task('min-html', function() {
  return gulp
    .src(['./app/*.html'])
    .pipe(
      fileinclude({
        prefix: '@@',
        basepath: './app/',
        context: {
          env: 'prod'
        }
      })
    )
    .pipe(gulp.dest('./public'));
});

gulp.task('watch-html', function() {
  gulp.watch('./app/**/*.html', gulp.series('build-html'));
});
