var gulp = require('gulp');
var imagemin = require('gulp-imagemin');
var clean = require('gulp-clean');
var rename = require('gulp-rename');

gulp.task('clean-dist-img', function() {
  return gulp
    .src('./dist/img', { read: false, allowEmpty: true })
    .pipe(clean());
});

gulp.task('app-img', function() {
  return gulp
    .src([
      './app/**/*.svg',
      './app/**/*.png',
      './app/**/*.jpg',
      './app/**/*.ico'
    ])
    .pipe(imagemin())
    .pipe(rename({ dirname: '' }))
    .pipe(gulp.dest('./dist/img'));
});

gulp.task('build-img', gulp.series('clean-dist-img', 'app-img'));

gulp.task('min-img', function() {
  return gulp
    .src([
      './app/**/*.svg',
      './app/**/*.png',
      './app/**/*.jpg',
      './app/**/*.ico'
    ])
    .pipe(imagemin())
    .pipe(rename({ dirname: '' }))
    .pipe(gulp.dest('./public/img'));
});
